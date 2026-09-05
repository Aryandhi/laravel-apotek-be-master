<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchasePlanItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class ProductPurchasePlanItemTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    public function test_setting_the_plan_supplier_creates_a_purchase_plan_item(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct('obat_bebas', $unit);

        $product->purchase_plan_supplier_id = $supplier->id;

        $this->assertDatabaseHas('purchase_plan_items', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_changing_the_plan_supplier_updates_the_existing_record_instead_of_duplicating(): void
    {
        $unit = $this->makeUnit();
        $supplierA = $this->makeSupplier('Supplier A');
        $supplierB = $this->makeSupplier('Supplier B');
        $product = $this->makeProduct('obat_bebas', $unit);

        $product->purchase_plan_supplier_id = $supplierA->id;
        $product->purchase_plan_supplier_id = $supplierB->id;

        $this->assertSame(1, PurchasePlanItem::query()->where('product_id', $product->id)->count());
        $this->assertSame($supplierB->id, $product->fresh()->purchase_plan_supplier_id);
    }

    public function test_clearing_the_plan_supplier_deletes_the_purchase_plan_item(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct('obat_bebas', $unit);

        $product->purchase_plan_supplier_id = $supplier->id;
        $product->purchase_plan_supplier_id = null;

        $this->assertDatabaseMissing('purchase_plan_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_low_stock_query_only_includes_products_strictly_below_min_stock(): void
    {
        $unit = $this->makeUnit();

        $lowStockProduct = $this->makeProduct('obat_bebas', $unit, minStock: 10);
        ProductBatch::create([
            'product_id' => $lowStockProduct->id,
            'batch_number' => 'BTH-1',
            'expired_date' => now()->addYear(),
            'stock' => 5,
            'initial_stock' => 5,
            'status' => 'active',
        ]);

        $equalStockProduct = $this->makeProduct('obat_bebas', $unit, minStock: 10);
        ProductBatch::create([
            'product_id' => $equalStockProduct->id,
            'batch_number' => 'BTH-2',
            'expired_date' => now()->addYear(),
            'stock' => 10,
            'initial_stock' => 10,
            'status' => 'active',
        ]);

        $sufficientStockProduct = $this->makeProduct('obat_bebas', $unit, minStock: 10);
        ProductBatch::create([
            'product_id' => $sufficientStockProduct->id,
            'batch_number' => 'BTH-3',
            'expired_date' => now()->addYear(),
            'stock' => 50,
            'initial_stock' => 50,
            'status' => 'active',
        ]);

        $results = Product::query()
            ->active()
            ->withSum(['activeBatches as stock_qty'], 'stock')
            ->whereRaw(
                'COALESCE((select sum(stock) from product_batches
                    where product_batches.product_id = products.id
                    and product_batches.status = ?
                    and product_batches.stock > 0), 0) < products.min_stock',
                ['active']
            )
            ->pluck('id');

        $this->assertTrue($results->contains($lowStockProduct->id));
        $this->assertFalse($results->contains($equalStockProduct->id));
        $this->assertFalse($results->contains($sufficientStockProduct->id));
    }
}
