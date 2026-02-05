<?php

namespace App\Enums;

enum RefundMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case StoreCredit = 'store_credit';
    case Exchange = 'exchange';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::StoreCredit => 'Kredit Toko',
            self::Exchange => 'Tukar Barang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::Transfer => 'info',
            self::StoreCredit => 'warning',
            self::Exchange => 'gray',
        };
    }
}
