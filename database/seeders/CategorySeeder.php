<?php

namespace Database\Seeders;

use App\Enums\CategoryType as CategoryTypeEnum;
use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get CategoryType records
        $categoryTypes = CategoryType::all()->keyBy('code');

        $categories = [
            // Obat Bebas (Hijau)
            [
                'name' => 'Obat Batuk',
                'type' => CategoryTypeEnum::ObatBebas,
                'category_type_code' => 'obat_bebas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Maag',
                'type' => CategoryTypeEnum::ObatBebas,
                'category_type_code' => 'obat_bebas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Demam',
                'type' => CategoryTypeEnum::ObatBebas,
                'category_type_code' => 'obat_bebas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Sakit Kepala',
                'type' => CategoryTypeEnum::ObatBebas,
                'category_type_code' => 'obat_bebas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Luka',
                'type' => CategoryTypeEnum::ObatBebas,
                'category_type_code' => 'obat_bebas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Obat Bebas Terbatas (Biru)
            [
                'name' => 'Obat Flu & Pilek',
                'type' => CategoryTypeEnum::ObatBebasTerbatas,
                'category_type_code' => 'obat_bebas_terbatas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Alergi',
                'type' => CategoryTypeEnum::ObatBebasTerbatas,
                'category_type_code' => 'obat_bebas_terbatas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Diare',
                'type' => CategoryTypeEnum::ObatBebasTerbatas,
                'category_type_code' => 'obat_bebas_terbatas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Mata',
                'type' => CategoryTypeEnum::ObatBebasTerbatas,
                'category_type_code' => 'obat_bebas_terbatas',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Obat Keras (Merah - K)
            [
                'name' => 'Antibiotik',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Antihipertensi',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Antidiabetes',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Jantung',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Hormon',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Psikiatri',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Obat Injeksi',
                'type' => CategoryTypeEnum::ObatKeras,
                'category_type_code' => 'obat_keras',
                'requires_prescription' => true,
                'is_narcotic' => false,
            ],

            // Narkotika & Psikotropika
            [
                'name' => 'Narkotika Golongan II',
                'type' => CategoryTypeEnum::Narkotika,
                'category_type_code' => 'narkotika',
                'requires_prescription' => true,
                'is_narcotic' => true,
            ],
            [
                'name' => 'Narkotika Golongan III',
                'type' => CategoryTypeEnum::Narkotika,
                'category_type_code' => 'narkotika',
                'requires_prescription' => true,
                'is_narcotic' => true,
            ],
            [
                'name' => 'Psikotropika Golongan IV',
                'type' => CategoryTypeEnum::Psikotropika,
                'category_type_code' => 'psikotropika',
                'requires_prescription' => true,
                'is_narcotic' => true,
            ],

            // Alat Kesehatan
            [
                'name' => 'Alat Suntik',
                'type' => CategoryTypeEnum::Alkes,
                'category_type_code' => 'alkes',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Alat Tes',
                'type' => CategoryTypeEnum::Alkes,
                'category_type_code' => 'alkes',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Alat Bantu Jalan',
                'type' => CategoryTypeEnum::Alkes,
                'category_type_code' => 'alkes',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Masker & APD',
                'type' => CategoryTypeEnum::Alkes,
                'category_type_code' => 'alkes',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Perban & Plester',
                'type' => CategoryTypeEnum::Alkes,
                'category_type_code' => 'alkes',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Kosmetik
            [
                'name' => 'Perawatan Kulit',
                'type' => CategoryTypeEnum::Kosmetik,
                'category_type_code' => 'kosmetik',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Perawatan Rambut',
                'type' => CategoryTypeEnum::Kosmetik,
                'category_type_code' => 'kosmetik',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Perawatan Tubuh',
                'type' => CategoryTypeEnum::Kosmetik,
                'category_type_code' => 'kosmetik',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Suplemen
            [
                'name' => 'Vitamin & Suplemen',
                'type' => CategoryTypeEnum::Suplemen,
                'category_type_code' => 'suplemen',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Obat Tradisional
            [
                'name' => 'Jamu',
                'type' => CategoryTypeEnum::ObatTradisional,
                'category_type_code' => 'obat_tradisional',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Herbal Terstandar',
                'type' => CategoryTypeEnum::ObatTradisional,
                'category_type_code' => 'obat_tradisional',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Fitofarmaka',
                'type' => CategoryTypeEnum::ObatTradisional,
                'category_type_code' => 'obat_tradisional',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],

            // Lainnya
            [
                'name' => 'Susu & Nutrisi',
                'type' => CategoryTypeEnum::Lainnya,
                'category_type_code' => 'lainnya',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Perlengkapan Bayi',
                'type' => CategoryTypeEnum::Lainnya,
                'category_type_code' => 'lainnya',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
            [
                'name' => 'Kontrasepsi',
                'type' => CategoryTypeEnum::Lainnya,
                'category_type_code' => 'lainnya',
                'requires_prescription' => false,
                'is_narcotic' => false,
            ],
        ];

        foreach ($categories as $category) {
            $categoryTypeId = $categoryTypes[$category['category_type_code']]->id ?? null;

            Category::firstOrCreate(
                ['name' => $category['name']],
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'category_type_id' => $categoryTypeId,
                    'requires_prescription' => $category['requires_prescription'],
                    'is_narcotic' => $category['is_narcotic'],
                ]
            );
        }
    }
}
