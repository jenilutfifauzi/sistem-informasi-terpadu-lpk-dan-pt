<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DivisiPT: string implements HasLabel
{
    case Manajemen = 'Manajemen';
    case HRD = 'HRD';
    case Keuangan = 'Keuangan';
    case Operasional = 'Operasional';
    case Administrasi = 'Administrasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
