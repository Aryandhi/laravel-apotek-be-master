<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by module
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Products
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Categories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Units
            'units.view',
            'units.create',
            'units.update',
            'units.delete',

            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            // Customers
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',

            // Doctors
            'doctors.view',
            'doctors.create',
            'doctors.update',
            'doctors.delete',

            // Product Batches / Stock
            'stock.view',
            'stock.create',
            'stock.update',
            'stock.delete',
            'stock.adjustment',

            // Purchases
            'purchases.view',
            'purchases.create',
            'purchases.update',
            'purchases.delete',
            'purchases.approve',

            // Purchase Returns
            'purchase-returns.view',
            'purchase-returns.create',
            'purchase-returns.update',
            'purchase-returns.delete',

            // Sales
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
            'sales.void',

            // Sale Returns
            'sale-returns.view',
            'sale-returns.create',
            'sale-returns.update',
            'sale-returns.delete',

            // Cashier Shifts
            'cashier-shifts.view',
            'cashier-shifts.create',
            'cashier-shifts.update',
            'cashier-shifts.delete',
            'cashier-shifts.close',

            // Stock Opname
            'stock-opname.view',
            'stock-opname.create',
            'stock-opname.update',
            'stock-opname.delete',
            'stock-opname.approve',

            // Reports
            'reports.view',
            'reports.sales',
            'reports.purchases',
            'reports.stock',
            'reports.financial',
            'reports.export',

            // Xendit Transactions
            'xendit.view',
            'xendit.manage',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',

            // Settings
            'settings.view',
            'settings.create',
            'settings.update',
            'settings.delete',

            // Stores
            'stores.view',
            'stores.create',
            'stores.update',
            'stores.delete',

            // Activity Logs
            'activity-logs.view',
            'activity-logs.delete',

            // Payment Methods
            'payment-methods.view',
            'payment-methods.create',
            'payment-methods.update',
            'payment-methods.delete',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Owner - All permissions (same as Super Admin)
        $owner = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $owner->syncPermissions(Permission::all());

        // Admin - All permissions (same as Owner)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Pharmacist - Can manage products, prescriptions, stock
        $pharmacist = Role::firstOrCreate(['name' => 'Apoteker', 'guard_name' => 'web']);
        $pharmacist->syncPermissions([
            'dashboard.view',
            'products.view',
            'products.create',
            'products.update',
            'categories.view',
            'units.view',
            'suppliers.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'doctors.view',
            'doctors.create',
            'doctors.update',
            'stock.view',
            'stock.create',
            'stock.update',
            'stock.adjustment',
            'purchases.view',
            'purchases.create',
            'purchase-returns.view',
            'purchase-returns.create',
            'sales.view',
            'sales.create',
            'sale-returns.view',
            'sale-returns.create',
            'cashier-shifts.view',
            'cashier-shifts.create',
            'cashier-shifts.close',
            'stock-opname.view',
            'stock-opname.create',
            'reports.view',
            'reports.sales',
            'reports.stock',
            'xendit.view',
        ]);

        // Cashier - POS and basic operations
        $cashier = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'dashboard.view',
            'products.view',
            'categories.view',
            'customers.view',
            'customers.create',
            'stock.view',
            'sales.view',
            'sales.create',
            'cashier-shifts.view',
            'cashier-shifts.create',
            'cashier-shifts.close',
            'xendit.view',
        ]);

        // Assistant - Limited access
        $assistant = Role::firstOrCreate(['name' => 'Asisten', 'guard_name' => 'web']);
        $assistant->syncPermissions([
            'dashboard.view',
            'products.view',
            'categories.view',
            'customers.view',
            'stock.view',
            'sales.view',
        ]);

        // Assign Super Admin role to user with ID 1 (if exists)
        $user = \App\Models\User::find(1);
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}
