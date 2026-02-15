<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;

class ApplyVisaSection
{
    public static function make(): Section
    {
        return Section::make('11. Apply Visa Diajukan')
            ->description(fn ($record) => self::getStatusBadge($record, 11) ?? 'Status pengajuan aplikasi visa')
            ->schema([
                Radio::make('apply_visa_status')
                    ->label('Status Apply Visa')
                    ->options([
                        'Belum' => 'Belum Diajukan',
                        'Diajukan' => 'Sudah Diajukan',
                    ])
                    ->inline()
                    ->default('Belum')
                    ->required(),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->collapsed(fn ($record) => $record?->stage_11_complete ?? false);
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
