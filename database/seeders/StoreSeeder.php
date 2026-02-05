<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::firstOrCreate(
            ['code' => 'APT001'],
            [
                'name' => 'Apotek Sehat Sejahtera',
                'code' => 'APT001',
                'address' => 'Jl. Kesehatan No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'apotek@sehat.com',
                'sia_number' => 'SIA.01.01.1234.5678',
                'pharmacist_name' => 'apt. Siti Rahayu, S.Farm',
                'pharmacist_sipa' => 'SIPA.01.01.9876.5432',
            ]
        );
    }
}
