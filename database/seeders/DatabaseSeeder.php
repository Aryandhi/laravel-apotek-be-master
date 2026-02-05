<?php

namespace Database\Seeders;

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
        $this->call([
            RolesAndPermissionsSeeder::class,
            UnitSeeder::class,
            CategoryTypesSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            StoreSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            DoctorSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
