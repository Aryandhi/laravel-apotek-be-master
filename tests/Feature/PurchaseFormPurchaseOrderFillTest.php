<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class PurchaseFormPurchaseOrderFillTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    private function actingUser(): User
    {
        Permission::firstOrCreate(['name' => 'purchases.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'purchases.create', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Apoteker', 'guard_name' => 'web']);
        $role->givePermissionTo(['purchases.view', 'purchases.create']);

        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_selecting_a_purchase_order_fills_items_with_zero_quantity_by_default(): void
    {
        $this->actingUser();

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

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 10,
        ]);

        $items = Livewire::test(CreatePurchase::class)
            ->fillForm(['purchase_order_id' => $order->id])
            ->get('data.items');

        $this->assertNotEmpty($items);

        $firstItem = collect($items)->first();
        $this->assertSame($product->id, $firstItem['product_id']);
        $this->assertSame($orderItem->id, $firstItem['purchase_order_item_id']);
        $this->assertSame(0, (int) $firstItem['quantity']);
        $this->assertSame(0, (int) $firstItem['subtotal']);
        $this->assertSame(0, (int) $firstItem['total']);
    }
}
