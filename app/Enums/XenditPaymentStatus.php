<?php

namespace App\Enums;

enum XenditPaymentStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Settled = 'SETTLED';
    case Expired = 'EXPIRED';
    case Failed = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Settled => 'Selesai',
            self::Expired => 'Kedaluwarsa',
            self::Failed => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid, self::Settled => 'success',
            self::Expired => 'gray',
            self::Failed => 'danger',
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::Paid, self::Settled]);
    }
}
