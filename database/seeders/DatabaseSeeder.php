<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create specific users with roles
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@demo.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);

        User::create([
            'name' => 'Alumno User',
            'email' => 'alumno@demo.com',
            'password' => bcrypt('password'),
            'role' => 'alumno',
        ]);

        User::create([
            'name' => 'Paciente User',
            'email' => 'paciente@demo.com',
            'password' => bcrypt('password'),
            // 'role' => 'paciente', // Default
        ]);
    }
}
