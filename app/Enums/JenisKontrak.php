<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisKontrak: string implements HasLabel
{
    case Tetap = 'Tetap';
    case PKWT = 'PKWT';
    case Probasi = 'Probasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
