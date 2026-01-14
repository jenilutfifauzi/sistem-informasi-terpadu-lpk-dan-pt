<?php

namespace App\Filament\Resources\EmployeeLPKResource\Pages;

use App\Filament\Resources\EmployeeLPKResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas;

class ViewEmployeeLPK extends ViewRecord
{
    protected static string $resource = EmployeeLPKResource::class;

    public function infolist(Schemas\Schema $schema): Schemas\Schema
    {
        return EmployeeLPKResource::infolist($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}
