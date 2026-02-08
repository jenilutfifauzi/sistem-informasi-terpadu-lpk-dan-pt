<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Filament\Resources\CTKS\CTKResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCTKS extends ListRecords
{
    protected static string $resource = CTKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
