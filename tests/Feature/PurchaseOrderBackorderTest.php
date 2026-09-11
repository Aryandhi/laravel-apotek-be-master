<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class PurchaseOrderBackorderTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    private function actingUser(): User
    {
        Permission::firstOrCreate(['name' => 'purchases.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchases.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchases.update', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Apoteker', 'guard_name' => 'web']);
        $role->givePermissionTo(['purchases.view', 'purchases.create', 'purchases.update']);

        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_second_po_item_remains_available_after_partial_invoice(): void
    {
        $this->actingUser();

        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $genoint = $this->makeProduct('obat_bebas', $unit);
        $betason = $this->makeProduct('obat_bebas', $unit);

        $order = PurchaseOrder::create([
            'po_number' => 'SPR-2026-000001',
            'title' => 'Surat Pesanan Reguler',
            'group' => PurchaseOrderGroup::Reguler,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::Order,
            'order_date' => now()->toDateString(),
        ]);

        $genointItem = $order->items()->create([
            'product_id' => $genoint->id,
            'unit_id' => $unit->id,
            'quantity' => 3,
        ]);

        $betasonItem = $order->items()->create([
            'product_id' => $betason->id,
            'unit_id' => $unit->id,
            'quantity' => 4,
        ]);

        Livewire::test(CreatePurchase::class)
            ->fillForm([
                'invoice_number' => 'INV-01',
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $genoint->id,
                        'purchase_order_item_id' => $genointItem->id,
                        'quantity' => 2,
                        'unit_id' => $unit->id,
                        'purchase_price' => 1000,
                        'margin_percentage' => 50,
                        'selling_price' => 1500,
                        'discount' => 0,
                        'subtotal' => 2000,
                        'total' => 2000,
                        'received_quantity' => 0,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $genointItem->refresh();
        $betasonItem->refresh();

        $this->assertSame(2, $genointItem->received_quantity);
        $this->assertSame(1, $genointItem->remaining_quantity);
        $this->assertSame(0, $betasonItem->received_quantity);
        $this->assertSame(4, $betasonItem->remaining_quantity);
        $this->assertSame(PurchaseOrderStatus::Partial, $order->fresh()->status);

        // A second invoice against the same PO must still offer BOTH items for backorder.
        $items = Livewire::test(CreatePurchase::class)
            ->fillForm(['purchase_order_id' => $order->id])
            ->get('data.items');

        $this->assertCount(2, $items);
    }

    public function test_quantity_exceeding_remaining_po_quantity_is_rejected(): void
    {
        $this->actingUser();

        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $genoint = $this->makeProduct('obat_bebas', $unit);

        $order = PurchaseOrder::create([
            'po_number' => 'SPR-2026-000002',
            'title' => 'Surat Pesanan Reguler',
            'group' => PurchaseOrderGroup::Reguler,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::Order,
            'order_date' => now()->toDateString(),
        ]);

        $genointItem = $order->items()->create([
            'product_id' => $genoint->id,
            'unit_id' => $unit->id,
            'quantity' => 3,
        ]);

        // First invoice consumes 2 of 3, leaving only 1 remaining.
        Livewire::test(CreatePurchase::class)
            ->fillForm([
                'invoice_number' => 'INV-01',
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $genoint->id,
                        'purchase_order_item_id' => $genointItem->id,
                        'quantity' => 2,
                        'unit_id' => $unit->id,
                        'purchase_price' => 1000,
                        'margin_percentage' => 50,
                        'selling_price' => 1500,
                        'discount' => 0,
                        'subtotal' => 2000,
                        'total' => 2000,
                        'received_quantity' => 0,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Second invoice tries to receive 2, but only 1 remains — must be rejected.
        Livewire::test(CreatePurchase::class)
            ->fillForm([
                'invoice_number' => 'INV-02',
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $genoint->id,
                        'purchase_order_item_id' => $genointItem->id,
                        'quantity' => 2,
                        'unit_id' => $unit->id,
                        'purchase_price' => 1000,
                        'margin_percentage' => 50,
                        'selling_price' => 1500,
                        'discount' => 0,
                        'subtotal' => 2000,
                        'total' => 2000,
                        'received_quantity' => 0,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.quantity' => 'max']);

        $this->assertSame(0, Purchase::query()->where('invoice_number', 'INV-02')->count());
        $this->assertSame(0, PurchaseItem::query()->whereHas('purchase', fn ($q) => $q->where('invoice_number', 'INV-02'))->count());

        $genointItem->refresh();
        $this->assertSame(2, $genointItem->received_quantity);
    }

    public function test_editing_an_invoiced_quantity_resyncs_the_purchase_order_item(): void
    {
        $this->actingUser();

        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $genoint = $this->makeProduct('obat_bebas', $unit);

        $order = PurchaseOrder::create([
            'po_number' => 'SPR-2026-000003',
            'title' => 'Surat Pesanan Reguler',
            'group' => PurchaseOrderGroup::Reguler,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrderStatus::Order,
            'order_date' => now()->toDateString(),
        ]);

        $genointItem = $order->items()->create([
            'product_id' => $genoint->id,
            'unit_id' => $unit->id,
            'quantity' => 5,
        ]);

        Livewire::test(CreatePurchase::class)
            ->fillForm([
                'invoice_number' => 'INV-01',
                'purchase_order_id' => $order->id,
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'product_id' => $genoint->id,
                        'purchase_order_item_id' => $genointItem->id,
                        'quantity' => 2,
                        'unit_id' => $unit->id,
                        'purchase_price' => 1000,
                        'margin_percentage' => 50,
                        'selling_price' => 1500,
                        'discount' => 0,
                        'subtotal' => 2000,
                        'total' => 2000,
                        'received_quantity' => 0,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $genointItem->refresh();
        $this->assertSame(2, $genointItem->received_quantity);

        $purchase = Purchase::query()->where('invoice_number', 'INV-01')->firstOrFail();

        // Edit the invoice and correct the actually-received quantity from 2 to 4,
        // preserving the repeater's existing item key so it updates in place instead
        // of creating a duplicate PurchaseItem row.
        $editForm = Livewire::test(EditPurchase::class, ['record' => $purchase->getRouteKey()]);
        $existingKeys = array_keys($editForm->get('data.items'));
        $itemKey = $existingKeys[0];

        $editForm
            ->set("data.items.{$itemKey}.quantity", 4)
            ->set("data.items.{$itemKey}.subtotal", 4000)
            ->set("data.items.{$itemKey}.total", 4000)
            ->call('save')
            ->assertHasNoFormErrors();

        $genointItem->refresh();
        $this->assertSame(4, $genointItem->received_quantity);
        $this->assertSame(1, $genointItem->remaining_quantity);
    }
}
