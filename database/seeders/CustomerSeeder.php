<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Umum',
                'phone' => null,
                'address' => null,
                'email' => null,
                'points' => 0,
                'birth_date' => null,
            ],
            [
                'name' => 'Ahmad Suparjo',
                'phone' => '081234567001',
                'address' => 'Jl. Merdeka No. 10, Jakarta Pusat',
                'email' => 'ahmad.suparjo@email.com',
                'points' => 150,
                'birth_date' => '1985-03-15',
            ],
            [
                'name' => 'Siti Aminah',
                'phone' => '081234567002',
                'address' => 'Jl. Sudirman No. 25, Jakarta Selatan',
                'email' => 'siti.aminah@email.com',
                'points' => 320,
                'birth_date' => '1990-07-22',
            ],
            [
                'name' => 'Budi Hartono',
                'phone' => '081234567003',
                'address' => 'Jl. Gatot Subroto No. 5, Bandung',
                'email' => 'budi.hartono@email.com',
                'points' => 85,
                'birth_date' => '1978-11-08',
            ],
            [
                'name' => 'Dewi Lestari',
                'phone' => '081234567004',
                'address' => 'Jl. Asia Afrika No. 100, Bandung',
                'email' => 'dewi.lestari@email.com',
                'points' => 450,
                'birth_date' => '1992-01-30',
            ],
            [
                'name' => 'Eko Prasetyo',
                'phone' => '081234567005',
                'address' => 'Jl. Pemuda No. 15, Surabaya',
                'email' => 'eko.prasetyo@email.com',
                'points' => 200,
                'birth_date' => '1988-05-12',
            ],
            [
                'name' => 'Fitri Handayani',
                'phone' => '081234567006',
                'address' => 'Jl. Diponegoro No. 50, Semarang',
                'email' => 'fitri.handayani@email.com',
                'points' => 75,
                'birth_date' => '1995-09-18',
            ],
            [
                'name' => 'Gunawan Wibowo',
                'phone' => '081234567007',
                'address' => 'Jl. Malioboro No. 8, Yogyakarta',
                'email' => 'gunawan.wibowo@email.com',
                'points' => 180,
                'birth_date' => '1982-12-05',
            ],
            [
                'name' => 'Hani Safitri',
                'phone' => '081234567008',
                'address' => 'Jl. Ahmad Yani No. 30, Malang',
                'email' => 'hani.safitri@email.com',
                'points' => 95,
                'birth_date' => '1993-04-25',
            ],
            [
                'name' => 'Irfan Hidayat',
                'phone' => '081234567009',
                'address' => 'Jl. Veteran No. 12, Medan',
                'email' => 'irfan.hidayat@email.com',
                'points' => 280,
                'birth_date' => '1987-08-14',
            ],
            [
                'name' => 'Joko Susanto',
                'phone' => '081234567010',
                'address' => 'Jl. Pattimura No. 22, Makassar',
                'email' => 'joko.susanto@email.com',
                'points' => 120,
                'birth_date' => '1980-02-28',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['name' => $customer['name']],
                $customer
            );
        }
    }
}
