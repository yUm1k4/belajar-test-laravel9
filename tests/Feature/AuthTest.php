<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirect_success()
    {
        // create 1 user
        $user = User::factory()->create([
            'password' => bcrypt('12345678')
        ]);

        // pastikan sudah ada di database
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);

        // visit to login
        $responseLogin = $this->get('/login');
        $responseLogin->assertSeeText('Login');

        // Post to /login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);

        // redirect to home page
        $response->assertRedirect('/home');

        // 302 adalah status code dari redirect, bisa pakai 2 cara :
        // $this->assertEquals(302, $response->getStatusCode());
        $response->assertStatus(302);
    }

    public function test_authenticaed_user_can_access_products_table() // positif case
    {
        // create 1 user
        $user = User::factory()->create();

        // login as user
        $this->actingAs($user)->get('/');

        // Go to products table /products
        $response = $this->get('/products');

        // Assert status 200
        $response->assertOk();
    }

    public function test_authenticaed_user_cannot_access_products_table() // negative case
    {
        // Go to products table /products
        $response = $this->get('/products');

        // $response->dump();

        // Assert status not 200
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
