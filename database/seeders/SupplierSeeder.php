<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Kimia Farma Tbk',
                'code' => 'SUP001',
                'contact_person' => 'Ahmad Wijaya',
                'phone' => '021-5711234',
                'email' => 'sales@kimiafarma.co.id',
                'address' => 'Jl. Veteran No. 9, Jakarta Pusat',
                'npwp' => '01.234.567.8-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Kalbe Farma Tbk',
                'code' => 'SUP002',
                'contact_person' => 'Sari Indah',
                'phone' => '021-42873888',
                'email' => 'order@kalbe.co.id',
                'address' => 'Gedung Kalbe, Jl. Let. Jend. Suprapto Kav. 4, Jakarta',
                'npwp' => '01.345.678.9-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Sanbe Farma',
                'code' => 'SUP003',
                'contact_person' => 'Dedi Kurniawan',
                'phone' => '022-7801234',
                'email' => 'sales@sanbe.co.id',
                'address' => 'Jl. Industri Cimareme No. 8, Bandung',
                'npwp' => '01.456.789.0-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Dexa Medica',
                'code' => 'SUP004',
                'contact_person' => 'Lina Susanti',
                'phone' => '021-29302888',
                'email' => 'order@dexa-medica.com',
                'address' => 'Titan Center Lt. 10, Jl. Boulevard Bintaro, Tangerang',
                'npwp' => '01.567.890.1-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Tempo Scan Pacific',
                'code' => 'SUP005',
                'contact_person' => 'Rudi Hartono',
                'phone' => '021-5303335',
                'email' => 'sales@temposcan.com',
                'address' => 'Tempo Scan Tower, Jl. HR Rasuna Said Kav. 3-4, Jakarta',
                'npwp' => '01.678.901.2-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Indofarma Tbk',
                'code' => 'SUP006',
                'contact_person' => 'Maya Sari',
                'phone' => '021-8904601',
                'email' => 'marketing@indofarma.co.id',
                'address' => 'Jl. Indofarma No. 1, Cikarang Barat, Bekasi',
                'npwp' => '01.789.012.3-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Phapros Tbk',
                'code' => 'SUP007',
                'contact_person' => 'Agus Setiawan',
                'phone' => '024-7602020',
                'email' => 'sales@phapros.co.id',
                'address' => 'Jl. Simongan No. 131, Semarang',
                'npwp' => '01.890.123.4-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Enseval Putera Megatrading',
                'code' => 'SUP008',
                'contact_person' => 'Hendra Gunawan',
                'phone' => '021-4683366',
                'email' => 'order@enseval.com',
                'address' => 'Jl. Pulo Lentut No. 10, Kawasan Industri Pulogadung, Jakarta',
                'npwp' => '01.901.234.5-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Millennium Pharmacon International',
                'code' => 'SUP009',
                'contact_person' => 'Yanti Permata',
                'phone' => '021-6590505',
                'email' => 'sales@mpi.co.id',
                'address' => 'Jl. Tomang Raya No. 15, Jakarta Barat',
                'npwp' => '01.012.345.6-012.000',
                'is_active' => true,
            ],
            [
                'name' => 'PT Anugrah Argon Medica',
                'code' => 'SUP010',
                'contact_person' => 'Bambang Suryadi',
                'phone' => '021-4603737',
                'email' => 'order@aam.co.id',
                'address' => 'Jl. Pulo Kambing II No. 20, Jakarta Timur',
                'npwp' => '01.123.456.7-012.000',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['code' => $supplier['code']],
                $supplier
            );
        }
    }
}
