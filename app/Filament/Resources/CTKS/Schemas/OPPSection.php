<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class OPPSection
{
    public static function make(): Section
    {
        return Section::make('14-15. OPP & Departure (Terbang)')
            ->description(fn ($record) => self::getStatusBadge($record, 14, 15) ?? 'Pencatatan OPP dan keberangkatan CTK')
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

                DatePicker::make('departure_date')
                    ->label('Tanggal Keberangkatan')
                    ->visible(fn ($get) => $get('opp_status') === 'Diterima')
                    ->required(fn ($get) => $get('opp_status') === 'Diterima')
                    ->afterOrEqual('opp_receipt_date')
                    ->helperText('Tanggal keberangkatan harus setelah atau sama dengan tanggal terima OPP'),

                TextInput::make('flight_number')
                    ->label('Nomor Penerbangan')
                    ->placeholder('Contoh: GA123, JL456')
                    ->maxLength(50)
                    ->visible(fn ($get) => $get('opp_status') === 'Diterima'),
            ])
            ->columns(2)
            ->collapsible();
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
