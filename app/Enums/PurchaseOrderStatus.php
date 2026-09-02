<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Approval = 'approval';
    case Order = 'order';
    case Partial = 'partial';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approval => 'Menunggu Approval',
            self::Order => 'Order',
            self::Partial => 'Diterima Sebagian',
            self::Received => 'Diterima',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Approval => 'warning',
            self::Order => 'info',
            self::Partial => 'warning',
            self::Received => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isDeletable(): bool
    {
        return $this === self::Draft;
    }

    public function isPrintable(): bool
    {
        return in_array($this, [self::Order, self::Partial, self::Received], true);
    }
}
