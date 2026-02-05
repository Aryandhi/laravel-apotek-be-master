<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'dr. Ahmad Yani, Sp.PD',
                'sip_number' => 'SIP.DKI.01.2024.001',
                'specialization' => 'Penyakit Dalam',
                'phone' => '081234560001',
                'hospital_clinic' => 'RS Mitra Keluarga',
                'address' => 'Jl. Raya Kemang No. 1, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Siti Aminah, Sp.A',
                'sip_number' => 'SIP.DKI.01.2024.002',
                'specialization' => 'Anak',
                'phone' => '081234560002',
                'hospital_clinic' => 'RS Hermina',
                'address' => 'Jl. Pemuda Kav. 88, Jakarta Timur',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Budi Santoso, Sp.S',
                'sip_number' => 'SIP.DKI.01.2024.003',
                'specialization' => 'Saraf',
                'phone' => '081234560003',
                'hospital_clinic' => 'RS Siloam',
                'address' => 'Jl. Semanggi No. 8, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Dewi Kartika, Sp.KK',
                'sip_number' => 'SIP.DKI.01.2024.004',
                'specialization' => 'Kulit dan Kelamin',
                'phone' => '081234560004',
                'hospital_clinic' => 'Klinik Derma Sehat',
                'address' => 'Jl. Sudirman No. 15, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Eko Prasetyo, Sp.M',
                'sip_number' => 'SIP.DKI.01.2024.005',
                'specialization' => 'Mata',
                'phone' => '081234560005',
                'hospital_clinic' => 'RS Mata Nusantara',
                'address' => 'Jl. Casablanca Kav. 10, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Fitri Handayani, Sp.OG',
                'sip_number' => 'SIP.DKI.01.2024.006',
                'specialization' => 'Kandungan',
                'phone' => '081234560006',
                'hospital_clinic' => 'RS Bunda',
                'address' => 'Jl. Teuku Cik Ditiro No. 28, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Gunawan Wibisono, Sp.JP',
                'sip_number' => 'SIP.DKI.01.2024.007',
                'specialization' => 'Jantung',
                'phone' => '081234560007',
                'hospital_clinic' => 'RS Harapan Kita',
                'address' => 'Jl. Letjen S. Parman Kav. 87, Jakarta Barat',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Hendra Kusuma, Sp.THT',
                'sip_number' => 'SIP.DKI.01.2024.008',
                'specialization' => 'THT',
                'phone' => '081234560008',
                'hospital_clinic' => 'RS OMNI',
                'address' => 'Jl. Alam Sutera Boulevard No. 25, Tangerang',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Indra Wijaya, Sp.B',
                'sip_number' => 'SIP.DKI.01.2024.009',
                'specialization' => 'Bedah Umum',
                'phone' => '081234560009',
                'hospital_clinic' => 'RS Fatmawati',
                'address' => 'Jl. RS Fatmawati Raya No. 4, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Julia Sari, Sp.KJ',
                'sip_number' => 'SIP.DKI.01.2024.010',
                'specialization' => 'Jiwa',
                'phone' => '081234560010',
                'hospital_clinic' => 'RS Jiwa Dr. Soeharto Heerdjan',
                'address' => 'Jl. Prof. Dr. Latumenten No. 1, Jakarta Barat',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Kevin Hartono',
                'sip_number' => 'SIP.DKI.01.2024.011',
                'specialization' => 'Umum',
                'phone' => '081234560011',
                'hospital_clinic' => 'Klinik Pratama Sehat',
                'address' => 'Jl. Mangga Besar No. 88, Jakarta Barat',
                'is_active' => true,
            ],
            [
                'name' => 'drg. Lisa Permata',
                'sip_number' => 'SIP.DKI.01.2024.012',
                'specialization' => 'Gigi',
                'phone' => '081234560012',
                'hospital_clinic' => 'Klinik Gigi Senyum',
                'address' => 'Jl. Gatot Subroto Kav. 27, Jakarta Selatan',
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doctor) {
            Doctor::firstOrCreate(
                ['sip_number' => $doctor['sip_number']],
                $doctor
            );
        }
    }
}
