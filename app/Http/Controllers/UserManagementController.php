<?php

namespace App\Http\Controllers;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Admin Stockify',
                'email' => 'admin@stockify.test',
                'role' => 'admin',
                'status' => 'active',
                'last_login' => 'Hari ini, 09:15',
            ],
            [
                'id' => 2,
                'name' => 'Dimas Pratama',
                'email' => 'manager@stockify.test',
                'role' => 'manager',
                'status' => 'active',
                'last_login' => 'Hari ini, 08:40',
            ],
            [
                'id' => 3,
                'name' => 'Rina Amelia',
                'email' => 'staff@stockify.test',
                'role' => 'staff',
                'status' => 'active',
                'last_login' => 'Kemarin, 16:20',
            ],
            [
                'id' => 4,
                'name' => 'Budi Santoso',
                'email' => 'budi@stockify.test',
                'role' => 'staff',
                'status' => 'inactive',
                'last_login' => '10 Jul 2026, 10:10',
            ],
        ];

        return view('pages.users.index', compact('users'));
    }
}