<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\CategoryType as CategoryTypeModel;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;

trait CreatesPurchaseOrderFixtures
{
    protected function makeUnit(string $name = 'Strip', string $code = 'STR'): Unit
    {
        return Unit::create(['name' => $name, 'code' => $code.'-'.uniqid()]);
    }

    protected function makeSupplier(string $name = 'Supplier A'): Supplier
    {
        return Supplier::create([
            'code' => 'SUP-'.uniqid(),
            'name' => $name,
            'is_active' => true,
        ]);
    }

    protected function makeCategory(string $typeCode): Category
    {
        $categoryType = CategoryTypeModel::firstOrCreate(
            ['code' => $typeCode],
            [
                'name' => $typeCode,
                'requires_prescription' => false,
                'is_narcotic' => in_array($typeCode, ['narkotika', 'psikotropika'], true),
                'is_active' => true,
            ]
        );

        return Category::create([
            'name' => ucfirst($typeCode).'-'.uniqid(),
            'type' => $typeCode,
            'category_type_id' => $categoryType->id,
        ]);
    }

    protected function makeProduct(string $typeCode, ?Unit $unit = null, int $minStock = 10, int $currentStock = 0): Product
    {
        $unit ??= $this->makeUnit();
        $category = $this->makeCategory($typeCode);

        return Product::create([
            'code' => 'PRD-'.uniqid(),
            'barcode' => null,
            'name' => 'Produk '.$typeCode.'-'.uniqid(),
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'min_stock' => $minStock,
            'is_active' => true,
        ]);
    }
}
