<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Pharmacist = 'pharmacist';
    case Assistant = 'assistant';
    case Cashier = 'cashier';
    case Inventory = 'inventory';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Admin => 'Admin',
            self::Pharmacist => 'Apoteker',
            self::Assistant => 'Asisten Apoteker',
            self::Cashier => 'Kasir',
            self::Inventory => 'Staff Gudang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Owner => 'danger',
            self::Admin => 'danger',
            self::Pharmacist => 'success',
            self::Assistant => 'info',
            self::Cashier => 'warning',
            self::Inventory => 'gray',
        };
    }
}
