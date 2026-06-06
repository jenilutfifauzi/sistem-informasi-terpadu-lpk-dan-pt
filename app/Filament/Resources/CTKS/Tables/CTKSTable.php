<?php

namespace App\Filament\Resources\CTKS\Tables;

use App\Enums\EntityType;
use App\Filament\Exports\CTKExport;
use App\Filament\Resources\CTKS\CTKResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class CTKSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-identification')
                    ->sortable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('completed_stages_count')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->completed_stages_count === 15 ? 'Lengkap' : 'Belum Lengkap')
                    ->color(fn ($record) => $record->completed_stages_count === 15 ? 'success' : 'warning')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('current_stage', $direction);
                    }),
                TextColumn::make('current_entity')
                    ->label('Entitas')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'LPK' => 'info',
                        'PT' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('completion_progress')
                    ->label('Progress')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->completed_stages_count === 15 => 'success',
                        $record->completed_stages_count >= 10 => 'warning',
                        $record->completed_stages_count >= 5 => 'info',
                        default => 'gray',
                    })
                    ->icon(fn ($record) => $record->completed_stages_count === 15 ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->description(fn ($record) => $record->completion_percentage.'%')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('current_stage', $direction);
                    }),
                TextColumn::make('no_telepon')
                    ->label('No. Telepon')
                    ->icon('heroicon-o-phone')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('current_entity')
                    ->label('Entitas')
                    ->options(EntityType::class),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat Dari'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'none' => 'Belum Ada Pembayaran',
                        'partial' => 'Pembayaran Sebagian',
                        'complete' => 'Pembayaran Lunas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'none' => $query->whereDoesntHave('payments'),
                            'partial' => $query->whereHas('payments', function ($q) {
                                $q->where('payment_status', \App\Enums\PaymentStatus::Lunas);
                            })->whereHas('payments', function ($q) {
                                $q->havingRaw('COUNT(*) < 5');
                            }),
                            'complete' => $query->whereHas('payments', function ($q) {
                                $q->where('payment_status', \App\Enums\PaymentStatus::Lunas)
                                    ->havingRaw('COUNT(*) >= 5');
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->headerActions([
                Actions\Action::make('export')
                    ->label('Export Excel (.xlsx)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $count = $query->count();

                        if ($count === 0) {
                            Notification::make()
                                ->warning()
                                ->title('No Data to Export')
                                ->body('There are no CTK records matching the current filters.')
                                ->send();

                            return;
                        }

                        $export = new CTKExport($query);

                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'export_type' => 'ctk',
                                'format' => 'xlsx',
                                'record_count' => $count,
                            ])
                            ->log('Exported CTK data to xlsx');

                        return Excel::download(
                            $export,
                            'ctk-'.now()->format('Y-m-d_His').'.xlsx',
                            ExcelFormat::XLSX
                        );
                    }),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => CTKResource::getUrl('view', ['record' => $record])),
                Actions\Action::make('kelola_progress')
                    ->label('Kelola Progress')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->url(fn ($record) => CTKResource::getUrl('edit', ['record' => $record])),
            ])
            ->recordUrl(fn ($record) => CTKResource::getUrl('view', ['record' => $record]));
    }
}
