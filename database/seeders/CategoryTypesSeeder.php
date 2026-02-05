<?php

namespace Database\Seeders;

use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class CategoryTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Obat Bebas',
                'code' => 'obat_bebas',
                'description' => 'Obat yang dapat dibeli tanpa resep dokter',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#10b981',
                'sort_order' => 1,
            ],
            [
                'name' => 'Obat Bebas Terbatas',
                'code' => 'obat_bebas_terbatas',
                'description' => 'Obat yang dapat dibeli tanpa resep dengan peringatan khusus',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#3b82f6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Obat Keras',
                'code' => 'obat_keras',
                'description' => 'Obat yang hanya dapat dibeli dengan resep dokter',
                'requires_prescription' => true,
                'is_narcotic' => false,
                'color' => '#ef4444',
                'sort_order' => 3,
            ],
            [
                'name' => 'Narkotika',
                'code' => 'narkotika',
                'description' => 'Obat golongan narkotika dengan pengawasan ketat',
                'requires_prescription' => true,
                'is_narcotic' => true,
                'color' => '#dc2626',
                'sort_order' => 4,
            ],
            [
                'name' => 'Psikotropika',
                'code' => 'psikotropika',
                'description' => 'Obat yang mempengaruhi fungsi psikis',
                'requires_prescription' => true,
                'is_narcotic' => true,
                'color' => '#b91c1c',
                'sort_order' => 5,
            ],
            [
                'name' => 'Alat Kesehatan',
                'code' => 'alkes',
                'description' => 'Peralatan dan perlengkapan kesehatan',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#6366f1',
                'sort_order' => 6,
            ],
            [
                'name' => 'Kosmetik',
                'code' => 'kosmetik',
                'description' => 'Produk perawatan kulit dan kecantikan',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#ec4899',
                'sort_order' => 7,
            ],
            [
                'name' => 'Suplemen',
                'code' => 'suplemen',
                'description' => 'Suplemen makanan dan vitamin',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#f59e0b',
                'sort_order' => 8,
            ],
            [
                'name' => 'Obat Tradisional',
                'code' => 'obat_tradisional',
                'description' => 'Obat herbal dan jamu',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#84cc16',
                'sort_order' => 9,
            ],
            [
                'name' => 'Lainnya',
                'code' => 'lainnya',
                'description' => 'Produk lainnya',
                'requires_prescription' => false,
                'is_narcotic' => false,
                'color' => '#6b7280',
                'sort_order' => 99,
            ],
        ];

        foreach ($types as $type) {
            CategoryType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
