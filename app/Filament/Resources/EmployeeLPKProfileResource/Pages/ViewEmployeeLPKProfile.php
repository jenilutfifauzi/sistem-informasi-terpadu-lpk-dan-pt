<?php

namespace App\Filament\Resources\EmployeeLPKProfileResource\Pages;

use App\Filament\Resources\EmployeeLPKProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

/**
 * View page for employee self-service profile.
 *
 * Displays the employee's personal, employment, and compensation information in read-only mode.
 * For Instruktur jabatan, provides a convenient action button to download their sertifikat kompetensi.
 */
class ViewEmployeeLPKProfile extends ViewRecord
{
    protected static string $resource = EmployeeLPKProfileResource::class;

    /**
     * Get the header action buttons for the profile view.
     *
     * Displays a "Unduh Sertifikat" button for Instruktur employees who have an uploaded certificate.
     * Button is conditionally visible based on:
     * - Presence of sertifikat_path (file exists in database)
     * - Jabatan is Instruktur
     *
     * @return array Header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_sertifikat')
                ->label('Unduh Sertifikat')
                ->url(fn () => route('karyawan-lpk.sertifikat.download', $this->record->id))
                ->openUrlInNewTab()
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn () => $this->record->sertifikat_path && $this->record->jabatan->value === 'Instruktur'),
        ];
    }
}
