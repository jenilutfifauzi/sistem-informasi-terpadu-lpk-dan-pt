<?php

namespace App\Enums;

enum AssetAssignmentStatus: string
{
    case Available = 'Available';
    case Assigned = 'Assigned';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Assigned => 'warning',
        };
    }

    public static function options(): array
    {
        return [
            self::Available->value => self::Available->getLabel(),
            self::Assigned->value => self::Assigned->getLabel(),
        ];
    }
}
