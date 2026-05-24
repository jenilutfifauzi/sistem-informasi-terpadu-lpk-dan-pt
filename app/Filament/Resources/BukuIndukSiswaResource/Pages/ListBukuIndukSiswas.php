<?php

namespace App\Filament\Resources\BukuIndukSiswaResource\Pages;

use App\Filament\Resources\BukuIndukSiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBukuIndukSiswas extends ListRecords
{
    protected static string $resource = BukuIndukSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
