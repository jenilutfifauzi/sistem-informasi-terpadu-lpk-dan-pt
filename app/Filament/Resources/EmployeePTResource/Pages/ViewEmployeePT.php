<?php

namespace App\Filament\Resources\EmployeePTResource\Pages;

use App\Filament\Resources\EmployeePTResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas;

class ViewEmployeePT extends ViewRecord
{
    protected static string $resource = EmployeePTResource::class;

    public function infolist(Schemas\Schema $schema): Schemas\Schema
    {
        return EmployeePTResource::infolist($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
