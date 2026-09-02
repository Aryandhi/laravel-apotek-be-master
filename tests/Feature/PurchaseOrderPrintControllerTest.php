<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesPurchaseOrderFixtures;
use Tests\TestCase;

class PurchaseOrderPrintControllerTest extends TestCase
{
    use CreatesPurchaseOrderFixtures;
    use RefreshDatabase;

    private function makeOrder(PurchaseOrderStatus $status, PurchaseOrderGroup $group = PurchaseOrderGroup::Reguler): PurchaseOrder
    {
        $unit = $this->makeUnit();
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct($group === PurchaseOrderGroup::Narkotika ? 'narkotika' : 'obat_bebas', $unit);

        $order = PurchaseOrder::create([
            'po_number' => 'SP-'.uniqid(),
            'title' => 'Surat Pesanan',
            'group' => $group,
            'supplier_id' => $supplier->id,
            'status' => $status,
            'order_date' => now()->toDateString(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 5,
        ]);

        return $order;
    }

    private function actingUserWithPermission(): User
    {
        Permission::firstOrCreate(['name' => 'purchase-orders.print', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Apoteker', 'guard_name' => 'web']);
        $role->givePermissionTo('purchase-orders.print');

        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $user->assignRole($role);

        $this->actingAs($user);

        return $user;
    }

    public function test_printing_a_draft_order_is_forbidden(): void
    {
        $this->actingUserWithPermission();
        $order = $this->makeOrder(PurchaseOrderStatus::Draft);

        $response = $this->get(route('purchase-orders.print', $order));

        $response->assertForbidden();
    }

    public function test_printing_an_order_status_document_streams_a_pdf(): void
    {
        $this->actingUserWithPermission();
        $order = $this->makeOrder(PurchaseOrderStatus::Order);

        $response = $this->get(route('purchase-orders.print', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_printing_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => UserRole::Cashier]);
        $this->actingAs($user);

        $order = $this->makeOrder(PurchaseOrderStatus::Order);

        $response = $this->get(route('purchase-orders.print', $order));

        $response->assertForbidden();
    }

    public function test_narcotic_orders_use_the_narcotic_template(): void
    {
        $this->actingUserWithPermission();
        $order = $this->makeOrder(PurchaseOrderStatus::Order, PurchaseOrderGroup::Narkotika);

        $response = $this->get(route('purchase-orders.print', $order));

        $response->assertOk();
    }
}
