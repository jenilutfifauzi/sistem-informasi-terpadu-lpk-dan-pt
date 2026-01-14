<?php

namespace App\Filament\Resources\EmployeeLPKProfileResource\Pages;

use App\Filament\Resources\EmployeeLPKProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * Edit page for employee self-service profile.
 *
 * Allows authenticated employees to update limited personal information:
 * - Alamat (address)
 * - Telepon (phone number)
 *
 * All other fields remain read-only for security and data integrity reasons.
 */
class EditEmployeeLPKProfile extends EditRecord
{
    protected static string $resource = EmployeeLPKProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
