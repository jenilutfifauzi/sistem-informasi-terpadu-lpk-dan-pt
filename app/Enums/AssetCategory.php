<?php

namespace App\Enums;

enum AssetCategory: string
{
    case Elektronik = 'Elektronik';
    case Furniture = 'Furniture';
    case DokumenIjin = 'DokumenIjin';
    case PerlengkapanKantor = 'PerlengkapanKantor';
    case Kendaraan = 'Kendaraan';
    case Lainnya = 'Lainnya';

    public function getLabel(): string
    {
        return match ($this) {
            self::Elektronik => 'Elektronik',
            self::Furniture => 'Furniture',
            self::DokumenIjin => 'Dokumen & Ijin',
            self::PerlengkapanKantor => 'Perlengkapan Kantor',
            self::Kendaraan => 'Kendaraan',
            self::Lainnya => 'Lainnya',
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::Elektronik => 'ELK',
            self::Furniture => 'FRN',
            self::DokumenIjin => 'DOK',
            self::PerlengkapanKantor => 'PKT',
            self::Kendaraan => 'KND',
            self::Lainnya => 'LYN',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Elektronik => 'info',
            self::Furniture => 'warning',
            self::DokumenIjin => 'primary',
            self::PerlengkapanKantor => 'success',
            self::Kendaraan => 'danger',
            self::Lainnya => 'gray',
        };
    }

    public static function options(): array
    {
        return [
            self::Elektronik->value => self::Elektronik->getLabel(),
            self::Furniture->value => self::Furniture->getLabel(),
            self::DokumenIjin->value => self::DokumenIjin->getLabel(),
            self::PerlengkapanKantor->value => self::PerlengkapanKantor->getLabel(),
            self::Kendaraan->value => self::Kendaraan->getLabel(),
            self::Lainnya->value => self::Lainnya->getLabel(),
        ];
    }
}
