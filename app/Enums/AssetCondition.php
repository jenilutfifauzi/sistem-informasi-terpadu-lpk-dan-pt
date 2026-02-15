<?php

namespace App\Enums;

enum AssetCondition: string
{
    case Baik = 'Baik';
    case Rusak = 'Rusak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::Rusak => 'Rusak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baik => 'success',
            self::Rusak => 'danger',
        };
    }

    public static function options(): array
    {
        return [
            self::Baik->value => self::Baik->getLabel(),
            self::Rusak->value => self::Rusak->getLabel(),
        ];
    }
}
