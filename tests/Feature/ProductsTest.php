<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    // menyetel ulang database setelah tiap test sehingga data dari test sebelumnya tidak mengganggu test berikutnya
    use RefreshDatabase;

    private $user;

    private function create_user(int $is_admin = 0)
    {
        $this->user =  User::factory()->create([
            'is_admin' => $is_admin,
        ]);
    }

    // ? setUp() adalah method yang akan dijalankan sebelum setiap test
    // public function setUp(): void
    // {
    //     parent::setUp();

    //     // $this->artisan('db:seed');

    //     // $this->user = User::factory()->create();
    //     // $this->user = $this->create_user();
    // }

    public function test_produk_kosong()
    {
        $this->create_user();
        // login as user
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();

        $response->assertSeeText('No products found');
    }

    public function test_produk_ada_1_data()
    {
        $product = Product::create([
            'name' => 'Test Product 1',
            'price' => '10.00',
        ]);

        $this->create_user();
        // login as user
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();

        $response->assertDontSeeText('No products found');

        // $response->assertSeeText($product->name);

        $view_products = $response->viewData('products'); // products dari variable controller

        // ? cek apakah produk yang ditampilkan sama dengan produk yang dibuat
        $this->assertEquals($product->name, $view_products->first()->name);
    }

    public function test_pagination_table_product_tidak_menampilkan_data_ke_11()
    {
        // ? panggil factory
        // $products = Product::factory()->count(11)->create();
        $products = Product::factory()->count(11)->create([ // membuat product dengan harga yg sama (default)
            'price' => '10.00',
        ]);

        // * karena nggk bisa cek di database, maka cek pakai func info(), terus cek di laravel.log
        // utk memastikan bahwa price nya sama 10.00
        // info('products: ' . $products);

        // ? classic :
        // for ($i=1; $i < 11; $i++) { 
        //     $product = Product::create([
        //         'name' => 'Test Product ' . $i,
        //         'price' => rand(10, 99),
        //     ]);
        // }

        $this->create_user();
        // login as user
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();
        // dd($products->last());

        // ? cek seharusnya tidak ada product yg ke 11
        $response->assertDontSeeText($products->last()->name);
    }

    // function utk test view / tampilan
    public function test_admin_bisa_melihat_button_add_new_product()
    {
        $this->create_user(1);
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();
        $response->assertSeeText('Add New Product');
    }

    // function utk test view / tampilan
    public function test_selain_admin_tidak_bisa_melihat_button_add_new_product()
    {
        $this->create_user();
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();
        $response->assertDontSeeText('Add New Product');
    }

    // function utk test logic nya
    public function test_admin_bisa_akses_halaman_tambah_produk()
    {
        $this->create_user(1);
        $response = $this->actingAs($this->user)->get('/products/create');

        $response->assertOk();
        $response->assertSeeText('Create Product');
    }

    // function utk test logic nya
    public function test_selain_admin_tidak_bisa_akses_halaman_tambah_produk()
    {
        $this->create_user();
        $response = $this->actingAs($this->user)->get('/products/create');

        $response->assertStatus(403);
    }

    public function test_hasil_tambah_produk_baru_ada_di_database()
    {
        $this->create_user(1);
        $response = $this->actingAs($this->user)->post('/products', [
            'name' => 'New Product Test',
            'price' => 99.00,
        ]);

        // cek apakah produk yang ditambahkan ada di database
        $this->assertDatabaseHas('products', [
            'name' => 'New Product Test',
            'price' => 99.00,
        ]);

        // assertRedirect() pastikan redirect ke halaman /products
        $response->assertRedirect('/products');

        // cek product yang ditambahkan ada di view ada 2 cara :
        // * Cara 1 :
        // $product = Product::where('name', 'New Product Test')->first();
        // $response = $this->get('/products');
        // $response->assertSeeText($product->name);
        // $response->assertSeeText($product->price);
        // * Cara 2 :
        $product = Product::orderBy('id', 'desc')->first();
        $this->assertEquals('New Product Test', $product->name);
        $this->assertEquals(99.00, $product->price);
    }

    public function test_masuk_ke_halaman_edit_produk()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->get('products/' . $product->id . '/edit');

        // bisa masuk ke halaman edit produk
        $response->assertOk();

        // * bisa melihat judul dan nama product yang diedit
        $response->assertSeeText('Edit Product');
        $response->assertSee($product->name);
        $response->assertSee($product->price);
        // * atau bisa juga dengan cara ini cek nya pakai value
        // $response->assertSee('value="' . $product->name . '"', false); // false utk mematikan escape
        // $response->assertSee('value="' . $product->price . '"', false); // false utk mematikan escape
    }

    public function test_edit_produk_berhasil()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->put('products/' . $product->id, [
            'name' => 'New Product Test',
            'price' => 99.00,
        ]);

        // cek apakah produk yang diedit ada di database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Product Test',
            'price' => 99.00,
        ]);

        // assertRedirect() pastikan redirect ke halaman /products
        $response->assertRedirect('/products');

        // muncul message status
        $response->assertSessionHas('status');

        // cek product yang diedit ada di view :
        $product = Product::orderBy('id', 'desc')->first();
        $this->assertEquals('New Product Test', $product->name);
        $this->assertEquals(99.00, $product->price);
    }

    public function test_edit_produk_dapet_error_validasi()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->put('products/' . $product->id, [
            'name' => 'Name',
            'price' => null,
        ]);

        // cek status redirect
        $response->assertStatus(302);
        // cek error message
        $response->assertSessionHasErrors(['name', 'price']);

        // cek database tidak ada yg diupdate
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Name',
            'price' => null,
        ]);
    }

    // jika mau test API response bisa pakai 
    public function test_edit_produk_dapet_error_validasi_json()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->put('products/' . $product->id, 
                ['name' => 'Name', 'price' => null],
                ['Accept' => 'application/json'] // untuk mengirimkan response dalam format json
            );

        $response->assertStatus(422); // 422 adalah status error dari json

        $response->assertJsonValidationErrors(['name', 'price']); // cek error message

        $response->assertJsonFragment([ // cek response json nya
            'name' => ['The name must be at least 6 characters.'],
            'price' => ['The price field is required.'],
        ]);
    }

    public function test_hapus_produk_tidak_ada_lagi_di_database()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $this->assertEquals(1, Product::count()); // cek jumlah produk sebelum dihapus

        $response = $this->actingAs($this->user)->delete('products/' . $product->id);

        $response->assertRedirect('/products'); // cek redirect ke halaman /products

        $response->assertSessionHas('status'); // cek muncul message status

        $this->assertEquals(0, Product::count()); // cek jumlah produk setelah dihapus
    }

    public function test_tambah_produk_dengan_photo_terupload()
    {
        $this->create_user(1);
        Storage::fake('local'); // fake local folder

        $photo = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->user)->post('/products', [
            'name' => 'New Product With Image',
            'price' => 123,
            'photo' => $photo,
        ]);

        // cek file terupload ada di storage, products folder pertama itu dari controller
        Storage::disk('local')->assertExists('products/product.jpg');

        // file testing bisa cek di : storage\framework\testing\disks\local
    }

    public function test_hapus_photo_product_dari_folder_storage()
    {
        $this->create_user(1);
        Storage::fake('local'); // fake local folder

        $product = Product::factory()->create([
            'photo' => 'product.jpg',
        ]);

        $response = $this->actingAs($this->user)->delete('products/' . $product->id);

        // cek file terhapus ada di storage, products folder pertama itu dari controller
        Storage::disk('local')->assertMissing('products/' . $product->photo);
    }
}
