<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleResolutionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsOwner(): User
    {
        $owner = User::factory()->create([
            'role' => UserRole::Owner,
            'is_active' => true,
        ]);

        // "Super Admin" is implicitly granted all abilities via Gate::before in AppServiceProvider.
        $owner->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']));

        $this->actingAs($owner);

        return $owner;
    }

    public function test_creating_user_with_super_admin_role_saves_super_admin_legacy_role(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $this->actingAsOwner();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test Super Admin',
                'email' => 'superadmin-test@apotek.com',
                'password' => 'password123',
                'roles' => [Role::where('name', 'Super Admin')->first()->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'superadmin-test@apotek.com')->firstOrFail();

        $this->assertSame(UserRole::SuperAdmin, $user->role);
    }

    public function test_creating_user_with_owner_role_saves_owner_legacy_role(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $this->actingAsOwner();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test Owner',
                'email' => 'owner-test@apotek.com',
                'password' => 'password123',
                'roles' => [Role::where('name', 'Owner')->first()->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'owner-test@apotek.com')->firstOrFail();

        $this->assertSame(UserRole::Owner, $user->role);
    }

    public function test_editing_user_role_from_owner_to_super_admin_updates_legacy_role(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $ownerRole = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $this->actingAsOwner();

        $user = User::factory()->create(['role' => UserRole::Owner]);
        $user->assignRole($ownerRole);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'roles' => [Role::where('name', 'Super Admin')->first()->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(UserRole::SuperAdmin, $user->fresh()->role);
    }
}
