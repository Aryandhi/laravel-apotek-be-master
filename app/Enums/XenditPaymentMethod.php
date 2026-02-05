<?php

namespace App\Enums;

enum XenditPaymentMethod: string
{
    case Invoice = 'INVOICE';
    case Ewallet = 'EWALLET';
    case Qris = 'QR_CODE';
    case VirtualAccount = 'VIRTUAL_ACCOUNT';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Invoice',
            self::Ewallet => 'E-Wallet',
            self::Qris => 'QRIS',
            self::VirtualAccount => 'Virtual Account',
        };
    }
}
