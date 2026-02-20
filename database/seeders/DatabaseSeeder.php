<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123'),
                'role' => UserRole::Admin, // o 'admin' si tu columna no es enum cast
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor@demo.com'],
            [
                'name' => 'Supervisor User',
                'password' => Hash::make('123'),
                'role' => UserRole::Supervisor, // o 'supervisor'
            ]
        );

        User::updateOrCreate(
            ['email' => 'alumno@demo.com'],
            [
                'name' => 'Alumno User',
                'password' => Hash::make('123'),
                'role' => UserRole::Alumno, // o 'alumno'
            ]
        );

        User::updateOrCreate(
            ['email' => 'paciente@demo.com'],
            [
                'name' => 'Paciente User',
                'password' => Hash::make('123'),
                // 'role' => UserRole::Paciente, // si aplica, sino omite
            ]
        );

        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}