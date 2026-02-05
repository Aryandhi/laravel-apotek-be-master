<?php

namespace App\Enums;

enum BatchStatus: string
{
    case Active = 'active';
    case NearExpired = 'near_expired';
    case Expired = 'expired';
    case Returned = 'returned';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::NearExpired => 'Mendekati Kadaluarsa',
            self::Expired => 'Kadaluarsa',
            self::Returned => 'Dikembalikan',
            self::Damaged => 'Rusak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::NearExpired => 'warning',
            self::Expired => 'danger',
            self::Returned => 'info',
            self::Damaged => 'danger',
        };
    }
}
