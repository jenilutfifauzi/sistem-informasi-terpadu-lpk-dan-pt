<?php

namespace App\Enums;

enum ScreeningResult: string
{
    case Lolos = 'Lolos';
    case TidakLolos = 'Tidak Lolos';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Lolos => 'success',
            self::TidakLolos => 'danger',
        };
    }
}
