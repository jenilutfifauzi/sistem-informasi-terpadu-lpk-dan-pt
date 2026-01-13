<?php

namespace App\Enums;

enum EntityType: string
{
    case PT = 'PT';
    case LPK = 'LPK';

    public function label(): string
    {
        return match ($this) {
            self::PT => 'PT (Perusahaan Jasa Tenaga Kerja Indonesia)',
            self::LPK => 'LPK (Lembaga Pelatihan Kerja)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PT => 'success',
            self::LPK => 'primary',
        };
    }

    public static function options(): array
    {
        return [
            self::PT->value => self::PT->label(),
            self::LPK->value => self::LPK->label(),
        ];
    }
}
