<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\RoleTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([RoleTableSeeder::class]);
        // User::create([
        //     'name' => 'admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('password'),
        //     'role_id' => 8
        // ]);
        // \App\Models\User::factory(100)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'user' . time(),
        //     'email' => time() . 'test@example.com',
        //     'password' => Hash::make('password'),
        //     'role_id' => 1
        // ]);


        // refer seeder
        $this->call([ReferTableSeeder::class]);

    }
}