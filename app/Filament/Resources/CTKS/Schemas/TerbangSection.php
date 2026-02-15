<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class TerbangSection
{
    public static function make(): Section
    {
        return Section::make('15. Terbang Berangkat')
            ->description(fn ($record) => self::getStatusBadge($record, 15) ?? 'Tanggal keberangkatan CTK')
            ->schema([
                Placeholder::make('status_info')
                    ->label('Status Keberangkatan')
                    ->content(function ($record) {
                        if (! $record || ! $record->exists) {
                            return 'Belum ada data keberangkatan';
                        }

                        if (empty($record->departure_date)) {
                            return '⬜ Belum dijadwalkan';
                        }

                        return '✅ Sudah dijadwalkan pada '.date('d/m/Y', strtotime($record->departure_date));
                    }),

                DatePicker::make('departure_date')
                    ->label('Tanggal Keberangkatan')
                    ->nullable()
                    ->maxDate(now()->addYears(1))
                    ->helperText('Isi tanggal keberangkatan untuk menyelesaikan Stage 15'),

                TextInput::make('flight_number')
                    ->label('Nomor Penerbangan')
                    ->placeholder('Contoh: GA123, JL456')
                    ->maxLength(50)
                    ->nullable(),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->collapsed(fn ($record) => $record?->stage_15_complete ?? false);
    }

    protected static function getStatusBadge($record, int ...$stages): ?string
    {
        if (! $record) {
            return null;
        }

        $statuses = [];
        foreach ($stages as $stage) {
            $stageAttribute = "stage{$stage}_complete";
            $isComplete = $record->$stageAttribute ?? false;
            $icon = $isComplete ? '✅' : '⬜';
            $statuses[] = "{$icon} Stage {$stage}";
        }

        return implode(' | ', $statuses);
    }
}
