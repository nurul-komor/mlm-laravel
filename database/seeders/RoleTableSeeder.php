<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['role' => 'normal_user']);
        Role::create(['role' => 'mfs_member']);
        Role::create(['role' => 'mfs_leader']);
        Role::create(['role' => 'mfs_manager']);
        Role::create(['role' => 'mfs_executive']);
        Role::create(['role' => 'mfs_director']);
        Role::create(['role' => 'mfs_coe']);
        Role::create(['role' => 'mfs_ceo']);
    }
}