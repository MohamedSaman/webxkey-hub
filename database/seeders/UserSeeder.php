<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@webxkey.com'],
            [
                'name'     => 'WebXKey Admin',
                'password' => Hash::make('webxkey2025!'),
            ]
        );
    }
}
