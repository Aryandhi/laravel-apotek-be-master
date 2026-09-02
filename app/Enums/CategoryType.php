<?php

namespace App\Enums;

enum CategoryType: string
{
    case ObatBebas = 'obat_bebas';
    case ObatBebasTerbatas = 'obat_bebas_terbatas';
    case ObatKeras = 'obat_keras';
    case Narkotika = 'narkotika';
    case Psikotropika = 'psikotropika';
    case Prekursor = 'prekursor';
    case Oot = 'oot';
    case Alkes = 'alkes';
    case Kosmetik = 'kosmetik';
    case Suplemen = 'suplemen';
    case ObatTradisional = 'obat_tradisional';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::ObatBebas => 'Obat Bebas',
            self::ObatBebasTerbatas => 'Obat Bebas Terbatas',
            self::ObatKeras => 'Obat Keras',
            self::Narkotika => 'Narkotika',
            self::Psikotropika => 'Psikotropika',
            self::Prekursor => 'Prekursor',
            self::Oot => 'Obat-Obatan Tertentu (OOT)',
            self::Alkes => 'Alat Kesehatan',
            self::Kosmetik => 'Kosmetik',
            self::Suplemen => 'Suplemen',
            self::ObatTradisional => 'Obat Tradisional',
            self::Lainnya => 'Lainnya',
        };
    }

    public function requiresPrescription(): bool
    {
        return match ($this) {
            self::ObatKeras, self::Narkotika, self::Psikotropika => true,
            default => false,
        };
    }

    public function isNarcotic(): bool
    {
        return match ($this) {
            self::Narkotika, self::Psikotropika => true,
            default => false,
        };
    }

    /**
     * Resolve the Surat Pesanan (Purchase Order) grouping rule for this category type.
     */
    public function purchaseOrderGroup(): PurchaseOrderGroup
    {
        return match ($this) {
            self::Narkotika => PurchaseOrderGroup::Narkotika,
            self::Psikotropika => PurchaseOrderGroup::Psikotropika,
            self::Prekursor => PurchaseOrderGroup::Prekursor,
            self::Oot => PurchaseOrderGroup::Oot,
            self::Alkes => PurchaseOrderGroup::Alkes,
            default => PurchaseOrderGroup::Reguler,
        };
    }
}
