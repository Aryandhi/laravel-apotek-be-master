<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class UnitConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tablet = Unit::where('name', 'Tablet')->first();
        $kapsul = Unit::where('name', 'Kapsul')->first();
        $strip = Unit::where('name', 'Strip')->first();
        $box = Unit::where('name', 'Box')->first();
        $botol = Unit::where('name', 'Botol')->first();
        $dus = Unit::where('name', 'Dus')->first();

        // Get products for conversions
        $products = Product::all();

        foreach ($products as $product) {
            // Tablet/Kapsul products: Strip contains 10, Box contains 10 strips
            if (in_array($product->base_unit_id, [$tablet?->id, $kapsul?->id])) {
                // 1 Strip = 10 Tablet/Kapsul
                if ($strip && $product->baseUnit) {
                    UnitConversion::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $strip->id,
                            'to_unit_id' => $product->base_unit_id,
                        ],
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $strip->id,
                            'to_unit_id' => $product->base_unit_id,
                            'conversion_value' => 10,
                        ]
                    );
                }

                // 1 Box = 10 Strip = 100 Tablet/Kapsul
                if ($box && $strip) {
                    UnitConversion::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $box->id,
                            'to_unit_id' => $strip->id,
                        ],
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $box->id,
                            'to_unit_id' => $strip->id,
                            'conversion_value' => 10,
                        ]
                    );
                }
            }

            // Botol products: Dus contains 12 bottles
            if ($product->base_unit_id === $botol?->id) {
                if ($dus && $botol) {
                    UnitConversion::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $dus->id,
                            'to_unit_id' => $botol->id,
                        ],
                        [
                            'product_id' => $product->id,
                            'from_unit_id' => $dus->id,
                            'to_unit_id' => $botol->id,
                            'conversion_value' => 12,
                        ]
                    );
                }
            }
        }
    }
}
