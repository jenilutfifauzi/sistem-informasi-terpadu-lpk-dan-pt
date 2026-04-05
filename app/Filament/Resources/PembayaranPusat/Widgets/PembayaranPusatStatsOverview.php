<?php

namespace App\Filament\Resources\PembayaranPusat\Widgets;

use App\Models\PembayaranPusat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PembayaranPusatStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $isPimpinan = $user?->hasRole('Pimpinan');

        // Build base query with entity scope
        $query = PembayaranPusat::query();

        if (! $isPimpinan && $user) {
            $query->where('entity', $user->entity);
        }

        // Clone for this month filter
        $thisMonthQuery = (clone $query)
            ->whereMonth('tanggal_pembayaran', now()->month)
            ->whereYear('tanggal_pembayaran', now()->year);

        // Calculate statistics
        $totalThisMonth = $thisMonthQuery->sum('nominal') ?? 0;
        $countThisMonth = $thisMonthQuery->count();
        $avgThisMonth = $countThisMonth > 0 ? $totalThisMonth / $countThisMonth : 0;

        // All time stats
        $totalAllTime = (clone $query)->sum('nominal') ?? 0;

        return [
            Stat::make('Total Bulan Ini', 'Rp '.number_format($totalThisMonth, 0, ',', '.'))
                ->description('Total pembayaran '.now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Jumlah Transaksi', number_format($countThisMonth))
                ->description('Transaksi bulan ini')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Rata-rata per Transaksi', 'Rp '.number_format($avgThisMonth, 0, ',', '.'))
                ->description('Rata-rata nominal')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info'),

            Stat::make('Total Keseluruhan', 'Rp '.number_format($totalAllTime, 0, ',', '.'))
                ->description('Semua pembayaran')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
