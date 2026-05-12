<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin SIPS',
                'username' => 'admin.sips',
                'email' => 'admin@sips.test',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Petugas SIPS 1',
                'username' => 'petugas.sips',
                'email' => 'petugas@sips.test',
                'role' => 'petugas',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Petugas SIPS 2',
                'username' => 'petugas.dua',
                'email' => 'petugas2@sips.test',
                'role' => 'petugas',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $attributes) {
            User::updateOrCreate(
                ['email' => $attributes['email']],
                $attributes
            );
        }
    }
}
