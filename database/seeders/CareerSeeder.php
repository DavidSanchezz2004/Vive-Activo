<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $careers = [
            'Nutrición',
        ];

        foreach ($careers as $name) {
            DB::table('careers')->updateOrInsert(
                ['name' => $name],
                ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
