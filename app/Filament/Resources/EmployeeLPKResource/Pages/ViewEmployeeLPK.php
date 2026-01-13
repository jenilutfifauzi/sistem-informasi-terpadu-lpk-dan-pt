<?php

namespace App\Filament\Resources\EmployeeLPKResource\Pages;

use App\Filament\Resources\EmployeeLPKResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeLPK extends ViewRecord
{
    protected static string $resource = EmployeeLPKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}
