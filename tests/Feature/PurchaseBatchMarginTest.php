<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\BatchPricingSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseBatchMarginTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_batch_selling_price_is_calculated_from_purchase_price_and_margin_percentage(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Paracetamol 500mg',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'INV-2026-001',
            'supplier_id' => $supplier->id,
            'date' => now()->toDateString(),
            'status' => PurchaseStatus::Draft,
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'paid_amount' => 0,
            'notes' => 'Test purchase',
            'user_id' => null,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_id' => $unit->id,
            'purchase_price' => 3000,
            'margin_percentage' => 30,
            'selling_price' => 0,
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'received_quantity' => 0,
        ]);

        $purchase->update(['status' => PurchaseStatus::Received]);

        $batch = ProductBatch::where('purchase_id', $purchase->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($batch);
        $this->assertSame(3000.00, (float) $batch->purchase_price);
        $this->assertSame(30.00, (float) $batch->margin_percentage);
        $this->assertSame(3900.00, (float) $batch->selling_price);
        $this->assertSame(10, $batch->stock);
    }

    public function test_sync_service_updates_existing_batch_pricing_from_purchase_item_input(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Paracetamol 500mg',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'expired_date' => now()->addYear(),
            'purchase_price' => 3000,
            'margin_percentage' => 20,
            'selling_price' => 3600,
            'stock' => 5,
            'initial_stock' => 5,
            'supplier_id' => $supplier->id,
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'INV-2026-003',
            'supplier_id' => $supplier->id,
            'date' => now()->toDateString(),
            'status' => PurchaseStatus::Draft,
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'paid_amount' => 0,
            'notes' => 'Test purchase existing batch sync',
            'user_id' => null,
        ]);

        $item = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'quantity' => 10,
            'unit_id' => $unit->id,
            'purchase_price' => 3000,
            'margin_percentage' => 0,
            'selling_price' => 4500,
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'received_quantity' => 0,
        ]);

        app(BatchPricingSyncService::class)->syncPurchaseItemsToExistingBatches($purchase);

        $updatedBatch = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('batch_number', 'BATCH-001')
            ->first();

        $updatedItem = $item->fresh();

        $this->assertNotNull($updatedBatch);
        $this->assertSame(3000.00, (float) $updatedBatch->purchase_price);
        $this->assertSame(50.00, (float) $updatedBatch->margin_percentage);
        $this->assertSame(4500.00, (float) $updatedBatch->selling_price);
        $this->assertSame(50.00, (float) $updatedItem?->margin_percentage);
        $this->assertSame(4500.00, (float) $updatedItem?->selling_price);
    }

    public function test_receiving_purchase_fails_when_batch_number_already_used_by_another_product(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $existingProduct = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Hufagripp',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        ProductBatch::create([
            'product_id' => $existingProduct->id,
            'batch_number' => 'BATCH-DUPLICATE',
            'expired_date' => now()->addYear(),
            'purchase_price' => 3000,
            'margin_percentage' => 30,
            'selling_price' => 3900,
            'stock' => 20,
            'initial_stock' => 20,
        ]);

        $otherProduct = Product::create([
            'code' => 'PRD002',
            'barcode' => '9876543210123',
            'name' => 'Sutra',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'INV-2026-002',
            'supplier_id' => $supplier->id,
            'date' => now()->toDateString(),
            'status' => PurchaseStatus::Draft,
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'total' => 30000,
            'paid_amount' => 0,
            'notes' => 'Test purchase',
            'user_id' => null,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $otherProduct->id,
            'batch_number' => 'BATCH-DUPLICATE',
            'quantity' => 10,
            'unit_id' => $unit->id,
            'purchase_price' => 3000,
            'margin_percentage' => 30,
            'selling_price' => 0,
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'received_quantity' => 0,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sudah digunakan oleh batch produk lain');

        $purchase->update(['status' => PurchaseStatus::Received]);
    }
}
