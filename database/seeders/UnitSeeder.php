<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'pcs', 'name' => 'Pieces', 'description' => 'Satuan per buah'],
            ['code' => 'box', 'name' => 'Box', 'description' => 'Satuan per kotak'],
            ['code' => 'ctn', 'name' => 'Karton', 'description' => 'Satuan per karton'],
            ['code' => 'pak', 'name' => 'Pak', 'description' => 'Satuan per pak'],
            ['code' => 'lsn', 'name' => 'Lusin', 'description' => 'Satuan per 12 buah'],
            ['code' => 'kg', 'name' => 'Kilogram', 'description' => 'Satuan berat kilogram'],
            ['code' => 'gr', 'name' => 'Gram', 'description' => 'Satuan berat gram'],
            ['code' => 'ltr', 'name' => 'Liter', 'description' => 'Satuan volume liter'],
            ['code' => 'ml', 'name' => 'Mililiter', 'description' => 'Satuan volume mililiter'],
            ['code' => 'mtr', 'name' => 'Meter', 'description' => 'Satuan panjang meter'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
