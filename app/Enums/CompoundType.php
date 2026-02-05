<?php

namespace App\Enums;

enum CompoundType: string
{
    case Puyer = 'puyer';
    case Kapsul = 'kapsul';
    case Sirup = 'sirup';
    case Salep = 'salep';
    case Cream = 'cream';

    public function label(): string
    {
        return match ($this) {
            self::Puyer => 'Puyer',
            self::Kapsul => 'Kapsul',
            self::Sirup => 'Sirup',
            self::Salep => 'Salep',
            self::Cream => 'Cream',
        };
    }
}
