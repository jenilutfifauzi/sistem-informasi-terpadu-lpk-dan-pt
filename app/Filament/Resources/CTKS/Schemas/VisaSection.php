<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class VisaSection
{
    public static function make(): Section
    {
        return Section::make('13. Visa Terbit')
            ->description(fn ($record) => self::getStatusBadge($record, 13) ?? 'Pencatatan penerbitan visa')
            ->schema([
                Placeholder::make('visa_summary')
                    ->label('Ringkasan Visa')
                    ->content(function ($record) {
                        if (! $record || ! $record->exists) {
                            return 'Belum ada data visa';
                        }

                        $visas = $record->visaRecords;
                        $total = $visas->count();
                        $terbit = $visas->where('application_status', 'Terbit')->count();

                        if ($total === 0) {
                            return 'Belum ada pengajuan visa';
                        }

                        return "Total: {$total} visa | Terbit: {$terbit}";
                    }),

                Repeater::make('visaRecords')
                    ->label('Data Visa')
                    ->relationship('visaRecords')
                    ->schema([
                        Radio::make('application_status')
                            ->label('Status Aplikasi')
                            ->options([
                                'Diajukan' => 'Diajukan (Pending)',
                                'Terbit' => 'Terbit (Issued)',
                            ])
                            ->required()
                            ->default('Diajukan')
                            ->reactive(),

                        DatePicker::make('application_date')
                            ->label('Tanggal Pengajuan')
                            ->required()
                            ->maxDate(now())
                            ->default(now()),

                        TextInput::make('visa_number')
                            ->label('Nomor Visa')
                            ->maxLength(50)
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit'),

                        DatePicker::make('issuance_date')
                            ->label('Tanggal Terbit')
                            ->maxDate(now())
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit')
                            ->afterOrEqual('application_date'),

                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit')
                            ->after('issuance_date'),

                        TextInput::make('issuing_country')
                            ->label('Negara Penerbit')
                            ->maxLength(100)
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit')
                            ->placeholder('Contoh: Japan, Taiwan, Malaysia'),

                        TextInput::make('visa_type')
                            ->label('Jenis Visa')
                            ->maxLength(50)
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit')
                            ->placeholder('Contoh: Work Visa, Business Visa'),

                        FileUpload::make('visa_document_path')
                            ->label('Dokumen Visa')
                            ->disk('private')
                            ->directory('visa-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240) // 10MB
                            ->visible(fn ($get) => $get('application_status') === 'Terbit')
                            ->required(fn ($get) => $get('application_status') === 'Terbit'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Data Visa')
                    ->itemLabel(fn ($state) => $state['visa_number'] ?? ($state['application_status'] ?? 'Visa Baru')),
            ])
            ->collapsible()
            ->collapsed(fn ($record) => ! $record || $record->visaRecords->isEmpty());
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
