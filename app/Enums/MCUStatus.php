<?php

namespace App\Enums;

enum MCUStatus: string
{
    case FIT = 'FIT';
    case UNFIT = 'UNFIT';
    case PENDING = 'PENDING';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FIT => 'success',
            self::UNFIT => 'danger',
            self::PENDING => 'warning',
        };
    }
}
