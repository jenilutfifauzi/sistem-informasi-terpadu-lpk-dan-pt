<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'Pending';
    case Lunas = 'Lunas';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Lunas => 'success',
        };
    }
}
