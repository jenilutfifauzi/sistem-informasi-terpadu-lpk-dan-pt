<?php

namespace App\Filament\Resources\EmployeeLPKProfileResource\Pages;

use App\Filament\Resources\EmployeeLPKProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeLPKProfile extends ViewRecord
{
    protected static string $resource = EmployeeLPKProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_sertifikat')
                ->label('Unduh Sertifikat')
                ->url(fn () => route('karyawan-lpk.sertifikat.download', $this->record->id))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->sertifikat_path && $this->record->jabatan->value === 'Instruktur'),
        ];
    }
}
