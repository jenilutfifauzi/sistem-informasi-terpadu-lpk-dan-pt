<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusKepegawaian: string implements HasLabel
{
    case Aktif = 'Aktif';
    case Cuti = 'Cuti';
    case Resign = 'Resign';

    public function getLabel(): string
    {
        return $this->value;
    }
}
