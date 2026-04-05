<?php

namespace App\Filament\Resources\PembayaranPusat\Pages;

use App\Filament\Resources\PembayaranPusat\PembayaranPusatResource;
use App\Filament\Resources\PembayaranPusat\Widgets\PembayaranPusatStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranPusat extends ListRecords
{
    protected static string $resource = PembayaranPusatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pembayaran'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PembayaranPusatStatsOverview::class,
        ];
    }
}
