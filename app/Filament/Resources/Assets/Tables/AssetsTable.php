<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Enums\EntityType;
use App\Filament\Exports\AssetExport;
use App\Filament\Resources\Assets\AssetResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_inventaris')
                    ->label('Inventory Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('nama_barang')
                    ->label('Asset Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->nama_barang),

                TextColumn::make('kategori')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($state) => $state->color())
                    ->formatStateUsing(fn ($state) => $state->getLabel()),

                TextColumn::make('jumlah')
                    ->label('Qty')
                    ->formatStateUsing(fn ($record) => "{$record->jumlah} {$record->satuan}")
                    ->alignCenter(),

                TextColumn::make('kondisi')
                    ->label('Condition')
                    ->badge()
                    ->color(fn ($state) => $state->getColor())
                    ->formatStateUsing(fn ($state) => $state->getLabel()),

                TextColumn::make('status_assignment')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state->getColor())
                    ->formatStateUsing(fn ($state) => $state->getLabel()),

                TextColumn::make('tahun_pembelian')
                    ->label('Year')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('nilai_pembelian')
                    ->label('Value')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('lokasi')
                    ->label('Location')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entity')
                    ->label('Entity')
                    ->options(EntityType::class)
                    ->visible(fn () => Auth::user()?->hasRole('Pimpinan')),

                SelectFilter::make('kategori')
                    ->label('Category')
                    ->options(AssetCategory::class),

                SelectFilter::make('kondisi')
                    ->label('Condition')
                    ->options(AssetCondition::class),

                SelectFilter::make('status_assignment')
                    ->label('Assignment Status')
                    ->options(AssetAssignmentStatus::class),

                SelectFilter::make('tahun_pembelian')
                    ->label('Purchase Year')
                    ->options(array_combine(
                        range(date('Y'), 1990),
                        range(date('Y'), 1990)
                    )),

                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel (.xlsx)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $count = $query->count();

                        if ($count === 0) {
                            Notification::make()
                                ->warning()
                                ->title('No Data to Export')
                                ->body('There are no assets matching the current filters.')
                                ->send();

                            return;
                        }

                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'exported_count' => $count,
                                'format' => 'xlsx',
                                'model' => 'Asset',
                            ])
                            ->log('Exported '.$count.' assets to xlsx');

                        $filename = 'assets_'.now()->format('Y-m-d_His').'.xlsx';

                        return Excel::download(
                            new AssetExport($query),
                            $filename,
                            ExcelFormat::XLSX
                        );
                    }),
            ])
            ->recordUrl(fn ($record) => AssetResource::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
