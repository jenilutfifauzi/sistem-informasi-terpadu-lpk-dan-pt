<?php

namespace App\Filament\Resources\EmployeeLPKResource\Pages;

use App\Filament\Resources\EmployeeLPKResource;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeLPK extends EditRecord
{
    protected static string $resource = EmployeeLPKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
            \Filament\Actions\RestoreAction::make(),
        ];
    }
}
