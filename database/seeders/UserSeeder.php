<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Mapping dari UserRole enum ke nama role Spatie.
     */
    private function getSpatieRoleName(UserRole $role): string
    {
        return match ($role) {
            UserRole::Owner => 'Owner',
            UserRole::Admin => 'Admin',
            UserRole::Pharmacist => 'Apoteker',
            UserRole::Assistant => 'Asisten',
            UserRole::Cashier => 'Kasir',
            UserRole::Inventory => 'Asisten',
        };
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first();

        $users = [
            [
                'name' => 'Admin Owner',
                'email' => 'owner@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'phone' => '081234567890',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
            [
                'name' => 'Administrator',
                'email' => 'admin@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'phone' => '081234567895',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
            [
                'name' => 'apt. Siti Rahayu',
                'email' => 'apoteker@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Pharmacist,
                'phone' => '081234567891',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'asisten@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Assistant,
                'phone' => '081234567892',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'kasir@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Cashier,
                'phone' => '081234567893',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'gudang@apotek.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Inventory,
                'phone' => '081234567894',
                'is_active' => true,
                'store_id' => $store?->id,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign Spatie role untuk permissions
            $spatieRole = $this->getSpatieRoleName($userData['role']);
            if (! $user->hasRole($spatieRole)) {
                $user->assignRole($spatieRole);
            }
        }
    }
}
