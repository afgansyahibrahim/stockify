<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@stockify.test'],
            [
                'name' => 'Admin Stockify',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@stockify.test'],
            [
                'name' => 'Manager Gudang',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@stockify.test'],
            [
                'name' => 'Staff Gudang',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );
    }
}