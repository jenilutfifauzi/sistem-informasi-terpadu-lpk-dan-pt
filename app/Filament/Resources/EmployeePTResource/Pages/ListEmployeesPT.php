<?php

namespace App\Filament\Resources\EmployeePTResource\Pages;

use App\Filament\Resources\EmployeePTResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListEmployeesPT extends ListRecords
{
    protected static string $resource = EmployeePTResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
