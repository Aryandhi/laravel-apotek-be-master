<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case ReturnSupplier = 'return_supplier';
    case ReturnCustomer = 'return_customer';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case Damaged = 'damaged';
    case Expired = 'expired';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Pembelian',
            self::Sale => 'Penjualan',
            self::ReturnSupplier => 'Retur ke Supplier',
            self::ReturnCustomer => 'Retur dari Customer',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::Damaged => 'Rusak',
            self::Expired => 'Kadaluarsa',
            self::TransferIn => 'Transfer Masuk',
            self::TransferOut => 'Transfer Keluar',
        };
    }

    public function isIncoming(): bool
    {
        return match ($this) {
            self::Purchase, self::ReturnCustomer, self::AdjustmentIn, self::TransferIn => true,
            default => false,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Purchase, self::ReturnCustomer, self::AdjustmentIn, self::TransferIn => 'success',
            self::Sale, self::ReturnSupplier, self::AdjustmentOut, self::TransferOut => 'danger',
            self::Damaged, self::Expired => 'warning',
        };
    }
}
