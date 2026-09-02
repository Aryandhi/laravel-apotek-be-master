<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchasePlanItem;
use App\Services\PurchaseOrderGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class PurchaseOrderGenerationServiceTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    public function test_it_throws_when_there_are_no_plan_items_with_supplier(): void
    {
        $this->expectException(\RuntimeException::class);

        app(PurchaseOrderGenerationService::class)->generate();
    }

    public function test_reguler_category_types_from_the_same_supplier_are_combined_into_one_purchase_order(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();

        $bebas = $this->makeProduct('obat_bebas', $unit);
        $bebasTerbatas = $this->makeProduct('obat_bebas_terbatas', $unit);
        $keras = $this->makeProduct('obat_keras', $unit);

        foreach ([$bebas, $bebasTerbatas, $keras] as $product) {
            PurchasePlanItem::create(['product_id' => $product->id, 'supplier_id' => $supplier->id]);
        }

        $orders = app(PurchaseOrderGenerationService::class)->generate();

        $this->assertCount(1, $orders);
        $order = $orders->first();
        $this->assertSame(PurchaseOrderGroup::Reguler, $order->group);
        $this->assertSame(PurchaseOrderStatus::Draft, $order->status);
        $this->assertCount(3, $order->items);
        $this->assertStringStartsWith('SPR-', $order->po_number);
    }

    public function test_narkotika_products_are_split_into_one_purchase_order_per_item(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();

        $productA = $this->makeProduct('narkotika', $unit);
        $productB = $this->makeProduct('narkotika', $unit);

        PurchasePlanItem::create(['product_id' => $productA->id, 'supplier_id' => $supplier->id]);
        PurchasePlanItem::create(['product_id' => $productB->id, 'supplier_id' => $supplier->id]);

        $orders = app(PurchaseOrderGenerationService::class)->generate();

        $this->assertCount(2, $orders);

        foreach ($orders as $order) {
            $this->assertSame(PurchaseOrderGroup::Narkotika, $order->group);
            $this->assertCount(1, $order->items);
            $this->assertStringStartsWith('SPN-', $order->po_number);
        }
    }

    public function test_psikotropika_products_are_split_into_one_purchase_order_per_item(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();

        $productA = $this->makeProduct('psikotropika', $unit);
        $productB = $this->makeProduct('psikotropika', $unit);
        $productC = $this->makeProduct('psikotropika', $unit);

        foreach ([$productA, $productB, $productC] as $product) {
            PurchasePlanItem::create(['product_id' => $product->id, 'supplier_id' => $supplier->id]);
        }

        $orders = app(PurchaseOrderGenerationService::class)->generate();

        $this->assertCount(3, $orders);

        foreach ($orders as $order) {
            $this->assertCount(1, $order->items);
        }
    }

    public function test_different_groups_and_suppliers_are_never_mixed_in_one_purchase_order(): void
    {
        $unit = $this->makeUnit();
        $supplierA = $this->makeSupplier('Supplier A');
        $supplierB = $this->makeSupplier('Supplier B');

        $regulerA = $this->makeProduct('obat_bebas', $unit);
        $regulerB = $this->makeProduct('obat_bebas', $unit);
        $oot = $this->makeProduct('oot', $unit);
        $prekursor = $this->makeProduct('prekursor', $unit);
        $alkes = $this->makeProduct('alkes', $unit);

        PurchasePlanItem::create(['product_id' => $regulerA->id, 'supplier_id' => $supplierA->id]);
        PurchasePlanItem::create(['product_id' => $regulerB->id, 'supplier_id' => $supplierB->id]);
        PurchasePlanItem::create(['product_id' => $oot->id, 'supplier_id' => $supplierA->id]);
        PurchasePlanItem::create(['product_id' => $prekursor->id, 'supplier_id' => $supplierA->id]);
        PurchasePlanItem::create(['product_id' => $alkes->id, 'supplier_id' => $supplierA->id]);

        $orders = app(PurchaseOrderGenerationService::class)->generate();

        // Reguler(supplierA), Reguler(supplierB), OOT(supplierA), Prekursor(supplierA), Alkes(supplierA)
        $this->assertCount(5, $orders);

        $groups = $orders->pluck('group')->map(fn ($group) => $group->value)->sort()->values()->all();
        $this->assertSame(['alkes', 'oot', 'prekursor', 'reguler', 'reguler'], $groups);
    }

    public function test_generating_purchase_orders_clears_the_planning_list(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct('obat_bebas', $unit);

        PurchasePlanItem::create(['product_id' => $product->id, 'supplier_id' => $supplier->id]);

        app(PurchaseOrderGenerationService::class)->generate();

        $this->assertSame(0, PurchasePlanItem::query()->count());
    }

    public function test_plan_items_without_a_supplier_are_ignored(): void
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $withSupplier = $this->makeProduct('obat_bebas', $unit);
        $withoutSupplier = $this->makeProduct('obat_bebas', $unit);

        PurchasePlanItem::create(['product_id' => $withSupplier->id, 'supplier_id' => $supplier->id]);
        PurchasePlanItem::create(['product_id' => $withoutSupplier->id, 'supplier_id' => null]);

        $orders = app(PurchaseOrderGenerationService::class)->generate();

        $this->assertCount(1, $orders);
        $this->assertCount(1, $orders->first()->items);
        $this->assertSame(1, PurchasePlanItem::query()->count());
    }

    public function test_po_numbers_increment_sequentially_per_group(): void
    {
        $unit = $this->makeUnit();
        $supplierA = $this->makeSupplier('Supplier A');
        $supplierB = $this->makeSupplier('Supplier B');

        $productA = $this->makeProduct('obat_bebas', $unit);
        $productB = $this->makeProduct('obat_bebas', $unit);

        PurchasePlanItem::create(['product_id' => $productA->id, 'supplier_id' => $supplierA->id]);
        $firstBatch = app(PurchaseOrderGenerationService::class)->generate();

        PurchasePlanItem::create(['product_id' => $productB->id, 'supplier_id' => $supplierB->id]);
        $secondBatch = app(PurchaseOrderGenerationService::class)->generate();

        $year = now()->format('Y');
        $this->assertSame("SPR-{$year}-000001", $firstBatch->first()->po_number);
        $this->assertSame("SPR-{$year}-000002", $secondBatch->first()->po_number);
    }
}
