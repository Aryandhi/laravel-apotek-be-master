<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Selesai',
            self::Pending => 'Pending',
            self::Cancelled => 'Dibatalkan',
            self::Returned => 'Diretur',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::Pending => 'warning',
            self::Cancelled => 'danger',
            self::Returned => 'info',
        };
    }
}
