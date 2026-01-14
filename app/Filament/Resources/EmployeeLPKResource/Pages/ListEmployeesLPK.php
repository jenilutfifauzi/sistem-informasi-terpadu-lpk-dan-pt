<?php

namespace App\Filament\Resources\EmployeeLPKResource\Pages;

use App\Filament\Resources\EmployeeLPKResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListEmployeesLPK extends ListRecords
{
    protected static string $resource = EmployeeLPKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make(),
            'Aktif' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Aktif')),
            'Cuti' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Cuti')),
            'Resign' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Resign')),
        ];
    }
}
