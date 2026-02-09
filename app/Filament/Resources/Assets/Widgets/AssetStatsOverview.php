<?php

namespace App\Filament\Resources\Assets\Widgets;

use App\Enums\AssetCondition;
use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AssetStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $isPimpinan = $user?->hasRole('Pimpinan');

        // Build base query with entity scope
        $query = Asset::query();

        if (! $isPimpinan && $user) {
            $query->where('entity', $user->entity);
        }

        // Calculate statistics
        $totalAssets = $query->count();
        $totalValue = $query->sum('nilai_pembelian') ?? 0;

        $conditionCounts = [
            'baik' => (clone $query)->where('kondisi', AssetCondition::Baik)->count(),
            'rusak' => (clone $query)->where('kondisi', AssetCondition::Rusak)->count(),
        ];

        $assignmentCounts = [
            'available' => (clone $query)->where('status_assignment', 'available')->count(),
            'assigned' => (clone $query)->where('status_assignment', 'assigned')->count(),
        ];

        return [
            Stat::make('Total Assets', number_format($totalAssets))
                ->description('Total registered assets')
                ->descriptionIcon('heroicon-o-cube')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Total Value', 'IDR '.number_format($totalValue, 0, ',', '.'))
                ->description('Current book value')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart([15, 4, 10, 22, 20, 27, 31, 32]),

            Stat::make('Good Condition', number_format($conditionCounts['baik']))
                ->description($conditionCounts['rusak'].' need repair')
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->color('success'),

            Stat::make('Available', number_format($assignmentCounts['available']))
                ->description($assignmentCounts['assigned'].' assigned')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),
        ];
    }
}
