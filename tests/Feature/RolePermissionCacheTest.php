<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_removing_view_permission_from_role_hides_resource_immediately(): void
    {
        Permission::firstOrCreate(['name' => 'categories.view', 'guard_name' => 'web']);

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
        $cashierRole->givePermissionTo('categories.view');

        $owner = User::factory()->create(['role' => UserRole::Owner, 'is_active' => true]);
        $owner->assignRole($superAdmin);
        $this->actingAs($owner);

        $cashier = User::factory()->create(['role' => UserRole::Cashier]);
        $cashier->assignRole($cashierRole);

        $this->assertTrue($cashier->can('categories.view'));

        // Simulate unchecking the "view" permission via the Roles form (relationship sync).
        Livewire::test(EditRole::class, ['record' => $cashierRole->getRouteKey()])
            ->fillForm(['permissions' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($cashier->fresh()->can('categories.view'));

        $this->actingAs($cashier->fresh());
        $this->assertFalse(CategoryResource::canAccess());
    }
}
