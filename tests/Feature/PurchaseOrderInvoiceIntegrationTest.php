<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class PurchaseOrderInvoiceIntegrationTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    private function makeOrder(int $quantity = 10): PurchaseOrderItem
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct('obat_bebas', $unit);

        $order = PurchaseOrder::create([
            'po_number' => 'SPR-2026-000001',
            'title' => 'Surat Pesanan Reguler',
            'group' => PurchaseOrderGroup::Reguler,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::Order,
            'order_date' => now()->toDateString(),
        ]);

        return $order->items()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => $quantity,
        ]);
    }

    private function invoiceItem(PurchaseOrderItem $orderItem, int $quantity): PurchaseItem
    {
        $purchase = Purchase::create([
            'invoice_number' => 'INV-'.uniqid(),
            'supplier_id' => $orderItem->purchaseOrder->supplier_id,
            'purchase_order_id' => $orderItem->purchase_order_id,
            'date' => now()->toDateString(),
            'status' => PurchaseStatus::Draft,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
            'paid_amount' => 0,
        ]);

        return PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $orderItem->product_id,
            'purchase_order_item_id' => $orderItem->id,
            'quantity' => $quantity,
            'unit_id' => $orderItem->unit_id,
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'subtotal' => $quantity * 1000,
            'total' => $quantity * 1000,
        ]);
    }

    public function test_remaining_quantity_reflects_backorder_before_any_invoice(): void
    {
        $orderItem = $this->makeOrder(10);

        $this->assertSame(10, $orderItem->remaining_quantity);
        $this->assertFalse($orderItem->isFullyReceived());
    }

    public function test_invoicing_part_of_the_quantity_marks_the_order_as_partial(): void
    {
        $orderItem = $this->makeOrder(10);

        $this->invoiceItem($orderItem, 4);

        $orderItem->refresh();
        $this->assertSame(4, $orderItem->received_quantity);
        $this->assertSame(6, $orderItem->remaining_quantity);
        $this->assertSame(PurchaseOrderStatus::Partial, $orderItem->purchaseOrder->fresh()->status);
    }

    public function test_invoicing_the_full_quantity_marks_the_order_as_received(): void
    {
        $orderItem = $this->makeOrder(10);

        $this->invoiceItem($orderItem, 10);

        $orderItem->refresh();
        $this->assertSame(0, $orderItem->remaining_quantity);
        $this->assertTrue($orderItem->isFullyReceived());
        $this->assertSame(PurchaseOrderStatus::Received, $orderItem->purchaseOrder->fresh()->status);
    }

    public function test_a_second_invoice_only_receives_the_remaining_backorder_quantity(): void
    {
        $orderItem = $this->makeOrder(10);

        $this->invoiceItem($orderItem, 4);
        $orderItem->refresh();
        $this->assertSame(6, $orderItem->remaining_quantity);

        $this->invoiceItem($orderItem, 6);
        $orderItem->refresh();
        $this->assertSame(0, $orderItem->remaining_quantity);
        $this->assertSame(PurchaseOrderStatus::Received, $orderItem->purchaseOrder->fresh()->status);
    }

    public function test_removing_an_invoiced_item_reverts_the_order_status(): void
    {
        $orderItem = $this->makeOrder(10);
        $item = $this->invoiceItem($orderItem, 10);

        $orderItem->refresh();
        $this->assertSame(PurchaseOrderStatus::Received, $orderItem->purchaseOrder->fresh()->status);

        $item->delete();

        $orderItem->refresh();
        $this->assertSame(0, $orderItem->received_quantity);
        $this->assertSame(PurchaseOrderStatus::Order, $orderItem->purchaseOrder->fresh()->status);
    }
}
