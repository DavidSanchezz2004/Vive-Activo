<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vive-activo.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin12345*'),
                'role' => UserRole::Admin,
            ]
        );
    }
}