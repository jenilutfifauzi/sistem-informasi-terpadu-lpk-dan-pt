<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Section;

class OPPSection
{
    public static function make(): Section
    {
        return Section::make('14. OPP')
            ->description(fn ($record) => self::getStatusBadge($record, 14) ?? 'Pencatatan penerimaan OPP (Offer Placement Paper)')
            ->schema([
                Radio::make('opp_status')
                    ->label('Status OPP')
                    ->options([
                        'Belum' => 'Belum Diterima',
                        'Diterima' => 'Sudah Diterima',
                    ])
                    ->required()
                    ->default('Belum')
                    ->reactive(),

                DatePicker::make('opp_receipt_date')
                    ->label('Tanggal Terima OPP')
                    ->visible(fn ($get) => $get('opp_status') === 'Diterima')
                    ->required(fn ($get) => $get('opp_status') === 'Diterima')
                    ->maxDate(now()),

                FileUpload::make('opp_document_path')
                    ->label('Dokumen OPP')
                    ->disk('private')
                    ->directory('opp-documents')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(10240) // 10MB
                    ->visible(fn ($get) => $get('opp_status') === 'Diterima')
                    ->required(fn ($get) => $get('opp_status') === 'Diterima'),
            ])
            ->columns(2)
            ->collapsible()
            ->persistCollapsed()
            ->collapsed(fn ($record) => $record?->stage_14_complete ?? false);
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
