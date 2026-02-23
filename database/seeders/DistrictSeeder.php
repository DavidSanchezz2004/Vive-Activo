<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'Ate',
            'Barranco',
            'Breña',
            'Jesús María',
            'La Molina',
            'Lince',
            'Miraflores',
            'San Borja',
            'San Isidro',
            'San Juan de Lurigancho',
            'San Martín de Porres',
            'Santiago de Surco',
            'Surquillo',
        ];

        foreach ($districts as $name) {
            DB::table('districts')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
