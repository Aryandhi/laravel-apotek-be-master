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

        dump([
            'purchase_price' => $batch?->purchase_price,
            'margin_percentage' => $batch?->margin_percentage,
            'selling_price' => $batch?->selling_price,
            'item_margin' => PurchaseItem::where('purchase_id', $purchase->id)->first()->margin_percentage,
            'item_purchase_price' => PurchaseItem::where('purchase_id', $purchase->id)->first()->purchase_price,
        ]);

        $this->assertNotNull($batch);
        $this->assertSame(3000.00, (float) $batch->purchase_price);
        $this->assertSame(30.00, (float) $batch->margin_percentage);
        $this->assertSame(3900.00, (float) $batch->selling_price);
        $this->assertSame(10, $batch->stock);
    }
}
