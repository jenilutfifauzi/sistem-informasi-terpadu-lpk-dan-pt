<?php

namespace App\Filament\Resources\SiswaLPKResource\Pages;

use App\Filament\Resources\SiswaLPKResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiswaLPKS extends ListRecords
{
    protected static string $resource = SiswaLPKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
