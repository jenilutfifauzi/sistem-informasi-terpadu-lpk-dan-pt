<?php

namespace App\Filament\Resources\SiswaLPKResource\Pages;

use App\Filament\Resources\SiswaLPKResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSiswaLPK extends CreateRecord
{
    protected static string $resource = SiswaLPKResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Siswa LPK berhasil didaftarkan';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
