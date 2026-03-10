<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JabatanPT: string implements HasLabel
{
    case Direktur = 'Direktur';
    case Manajer = 'Manajer';
    case StafHRD = 'Staf HRD';
    case StafKeuangan = 'Staf Keuangan';
    case StafOperasional = 'Staf Operasional';
    case StafAdministrasi = 'Staf Administrasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
