<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use App\Filament\Resources\CTKS\CTKResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCTK extends CreateRecord
{
    protected static string $resource = CTKResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['current_status'] = CTKStatus::MCU;
        $data['current_stage'] = 1;
        $data['current_entity'] = EntityType::LPK;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'CTK berhasil didaftarkan';
    }
}
