<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JabatanLPK: string implements HasLabel
{
    case Instruktur = 'Instruktur';
    case AdminLPK = 'Admin LPK';
    case Staff = 'Staff';

    public function getLabel(): string
    {
        return $this->value;
    }
}
