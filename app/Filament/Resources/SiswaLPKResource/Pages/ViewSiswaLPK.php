<?php

namespace App\Filament\Resources\SiswaLPKResource\Pages;

use App\Filament\Resources\SiswaLPKResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSiswaLPK extends ViewRecord
{
    protected static string $resource = SiswaLPKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
