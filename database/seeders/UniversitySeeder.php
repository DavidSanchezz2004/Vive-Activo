<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            ['name' => 'Universidad Peruana Unión', 'short_name' => 'UPEU'],
        ];

        foreach ($universities as $uni) {
            DB::table('universities')->updateOrInsert(
                ['name' => $uni['name']],
                [
                    'short_name' => $uni['short_name'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
