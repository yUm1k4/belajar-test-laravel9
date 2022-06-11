<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Zainudin Abdullah',
            'email' => 'zaiabdullah99@gmail.com',
            'password' => bcrypt('12345678'),
            'is_admin' => 1,
        ]);

        \App\Models\Product::factory()->create([
            'name' => 'Zain Product ' . rand(1, 100),
            'price' => '10.00',
        ]);
    }
}
