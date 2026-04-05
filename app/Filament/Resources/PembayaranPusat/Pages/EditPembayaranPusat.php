<?php

namespace App\Filament\Resources\PembayaranPusat\Pages;

use App\Filament\Resources\PembayaranPusat\PembayaranPusatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPembayaranPusat extends EditRecord
{
    protected static string $resource = PembayaranPusatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen'),
            RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil diperbarui';
    }
}
