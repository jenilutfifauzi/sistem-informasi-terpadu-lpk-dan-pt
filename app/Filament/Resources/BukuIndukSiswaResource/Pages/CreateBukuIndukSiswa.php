<?php

namespace App\Filament\Resources\BukuIndukSiswaResource\Pages;

use App\Filament\Resources\BukuIndukSiswaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBukuIndukSiswa extends CreateRecord
{
    protected static string $resource = BukuIndukSiswaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Buku induk siswa berhasil dibuat';
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
