<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class UpdateCategoriesWithTypeSeeder extends Seeder
{
    public function run(): void
    {
        // First, ensure CategoryTypes exist
        $this->call(CategoryTypesSeeder::class);

        // Mapping from old type (enum value) to new category_type code
        $typeMapping = [
            'obat_bebas' => 'obat_bebas',
            'obat_bebas_terbatas' => 'obat_bebas_terbatas',
            'obat_keras' => 'obat_keras',
            'narkotika' => 'narkotika',
            'psikotropika' => 'psikotropika',
            'alkes' => 'alkes',
            'kosmetik' => 'kosmetik',
            'suplemen' => 'suplemen',
            'obat_tradisional' => 'obat_tradisional',
            'lainnya' => 'lainnya',
        ];

        // Cache category types by code
        $categoryTypes = CategoryType::all()->keyBy('code');

        // Update each category
        $updated = 0;
        $categories = Category::whereNull('category_type_id')->get();

        foreach ($categories as $category) {
            $oldType = $category->getRawOriginal('type');

            if ($oldType && isset($typeMapping[$oldType])) {
                $newTypeCode = $typeMapping[$oldType];
                $categoryType = $categoryTypes->get($newTypeCode);

                if ($categoryType) {
                    $category->category_type_id = $categoryType->id;
                    $category->save();
                    $updated++;

                    $this->command->info("Updated: {$category->name} -> {$categoryType->name}");
                }
            }
        }

        $this->command->info("Total updated: {$updated} categories");
    }
}
