<?php

namespace App\Filament\Resources\CTKS\Tables;

use App\Enums\EntityType;
use App\Filament\Resources\CTKS\CTKResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CTKSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                TextColumn::make('current_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'MCU' => 'gray',
                        'Pembayaran' => 'warning',
                        'Soal/Berkas', 'Paspor' => 'info',
                        'Belajar di LPK' => 'primary',
                        'Screening 1', 'Interview User' => 'purple',
                        'Ijin Desa', 'Rekomendasi', 'WP', 'Apply Visa' => 'indigo',
                        'Medical Full' => 'cyan',
                        'Visa', 'OPP' => 'lime',
                        'Terbang' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('current_stage')
                    ->label('Tahap')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "Stage {$state}")
                    ->sortable(),
                TextColumn::make('current_entity')
                    ->label('Entitas')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'LPK' => 'info',
                        'PT' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
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
                SelectFilter::make('current_stage')
                    ->label('Tahap')
                    ->options([
                        1 => 'Stage 1 - MCU',
                        2 => 'Stage 2 - Pembayaran',
                        3 => 'Stage 3 - Soal Berkas',
                        4 => 'Stage 4 - Paspor',
                        5 => 'Stage 5 - Belajar di LPK',
                        6 => 'Stage 6 - Screening 1',
                        7 => 'Stage 7 - Interview User',
                        8 => 'Stage 8 - Ijin Desa',
                        9 => 'Stage 9 - Rekomendasi',
                        10 => 'Stage 10 - Working Permit',
                        11 => 'Stage 11 - Apply Visa',
                        12 => 'Stage 12 - Medical Full',
                        13 => 'Stage 13 - Visa',
                        14 => 'Stage 14 - OPP',
                        15 => 'Stage 15 - Terbang',
                    ]),
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
            ->recordUrl(fn ($record) => CTKResource::getUrl('view', ['record' => $record]));
    }
}
