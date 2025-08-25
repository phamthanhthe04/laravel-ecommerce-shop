<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Tạo user thường
        User::create([
            'name' => 'User Test',
            'email' => 'user@example.com', 
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
