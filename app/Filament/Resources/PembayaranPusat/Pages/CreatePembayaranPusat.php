<?php

namespace App\Filament\Resources\PembayaranPusat\Pages;

use App\Filament\Resources\PembayaranPusat\PembayaranPusatResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePembayaranPusat extends CreateRecord
{
    protected static string $resource = PembayaranPusatResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['entity'] = Auth::user()->entity;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil dicatat';
    }
}
