<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Assets\Widgets\AssetStatsOverview;
use App\Models\Asset;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();
        $isPimpinan = $user?->hasRole('Pimpinan');

        // Only check for entity-specific admins (not Pimpinan)
        if (! $isPimpinan && $user) {
            $count = Asset::where('entity', $user->entity)->count();

            if ($count === 0) {
                $entityLabel = $user->entity === 'LPK' ? 'LPK' : 'PT';

                Notification::make()
                    ->title("No {$entityLabel} assets found")
                    ->body("There are currently no assets registered for {$entityLabel}. Create your first asset to get started.")
                    ->info()
                    ->icon('heroicon-o-information-circle')
                    ->send();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AssetStatsOverview::class,
        ];
    }
}
