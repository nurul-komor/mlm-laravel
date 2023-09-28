<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Refer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReferTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $user = User::create([
                'name' => 'user' . time(),
                'email' => time() . uniqid() . 'test@example.com',
                'password' => Hash::make('password'),
                'role_id' => 1
            ]);
            $refer = Refer::create([
                'referer_id' => 158,
                'registered_user_id' => $user->id
            ]);
        }
    }
}