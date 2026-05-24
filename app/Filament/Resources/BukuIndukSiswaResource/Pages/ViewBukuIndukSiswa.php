<?php

namespace App\Filament\Resources\BukuIndukSiswaResource\Pages;

use App\Filament\Resources\BukuIndukSiswaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBukuIndukSiswa extends ViewRecord
{
    protected static string $resource = BukuIndukSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
