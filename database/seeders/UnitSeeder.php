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
            ['name' => 'Tablet', 'code' => 'TAB'],
            ['name' => 'Kapsul', 'code' => 'KAP'],
            ['name' => 'Botol', 'code' => 'BTL'],
            ['name' => 'Ampul', 'code' => 'AMP'],
            ['name' => 'Vial', 'code' => 'VL'],
            ['name' => 'Tube', 'code' => 'TUB'],
            ['name' => 'Sachet', 'code' => 'SCH'],
            ['name' => 'Strip', 'code' => 'STR'],
            ['name' => 'Box', 'code' => 'BOX'],
            ['name' => 'Piece', 'code' => 'PCS'],
            ['name' => 'Mililiter', 'code' => 'ML'],
            ['name' => 'Gram', 'code' => 'GR'],
            ['name' => 'Kaplet', 'code' => 'KPL'],
            ['name' => 'Suppositoria', 'code' => 'SUP'],
            ['name' => 'Ovula', 'code' => 'OVL'],
            ['name' => 'Patch', 'code' => 'PCH'],
            ['name' => 'Inhaler', 'code' => 'INH'],
            ['name' => 'Nebule', 'code' => 'NBL'],
            ['name' => 'Pen', 'code' => 'PEN'],
            ['name' => 'Plester', 'code' => 'PLS'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
