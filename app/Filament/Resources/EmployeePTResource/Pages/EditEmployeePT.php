<?php

namespace App\Filament\Resources\EmployeePTResource\Pages;

use App\Filament\Resources\EmployeePTResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeePT extends EditRecord
{
    protected static string $resource = EmployeePTResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
