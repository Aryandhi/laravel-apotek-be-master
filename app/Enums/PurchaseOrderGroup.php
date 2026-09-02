<?php

namespace App\Enums;

/**
 * Grouping rules used to split Surat Pesanan (SP) per BPOM/Kemenkes regulation.
 */
enum PurchaseOrderGroup: string
{
    case Reguler = 'reguler';
    case Oot = 'oot';
    case Prekursor = 'prekursor';
    case Psikotropika = 'psikotropika';
    case Narkotika = 'narkotika';
    case Alkes = 'alkes';

    public function label(): string
    {
        return match ($this) {
            self::Reguler => 'Reguler',
            self::Oot => 'OOT',
            self::Prekursor => 'Prekursor',
            self::Psikotropika => 'Psikotropika',
            self::Narkotika => 'Narkotika',
            self::Alkes => 'Alat Kesehatan',
        };
    }

    public function documentTitle(): string
    {
        return match ($this) {
            self::Reguler => 'Surat Pesanan Reguler',
            self::Oot => 'Surat Pesanan OOT',
            self::Prekursor => 'Surat Pesanan Prekursor',
            self::Psikotropika => 'Surat Pesanan Psikotropika',
            self::Narkotika => 'Surat Pesanan Narkotika',
            self::Alkes => 'Surat Pesanan Alat Kesehatan',
        };
    }

    public function numberPrefix(): string
    {
        return match ($this) {
            self::Reguler => 'SPR',
            self::Oot => 'SPO',
            self::Prekursor => 'SPP',
            self::Psikotropika => 'SPS',
            self::Narkotika => 'SPN',
            self::Alkes => 'SPA',
        };
    }

    /**
     * Maximum number of distinct products allowed on a single SP for this group.
     * Narkotika & Psikotropika are restricted to exactly one item per document.
     */
    public function maxItemsPerDocument(): ?int
    {
        return match ($this) {
            self::Psikotropika, self::Narkotika => 1,
            default => null,
        };
    }

    /**
     * Whether this group's print template uses the strict Narkotika/Psikotropika layout.
     */
    public function usesNarcoticTemplate(): bool
    {
        return match ($this) {
            self::Psikotropika, self::Narkotika => true,
            default => false,
        };
    }
}
