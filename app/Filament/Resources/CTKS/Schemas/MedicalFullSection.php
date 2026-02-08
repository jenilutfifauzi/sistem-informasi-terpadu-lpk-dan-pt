<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class MedicalFullSection
{
    public static function make(): Section
    {
        return Section::make('Medical Full Examination')
            ->schema([
                Placeholder::make('medical_full_summary')
                    ->label('Ringkasan Medical Full')
                    ->content(function ($record) {
                        if (! $record || ! $record->exists) {
                            return 'Belum ada pemeriksaan medical full';
                        }

                        $medicals = $record->medicalFulls;
                        $total = $medicals->count();
                        $selesai = $medicals->where('status', 'Selesai')->count();
                        $needsRenewal = $medicals->filter(fn ($m) => $m->isExpiringSoon())->count();

                        if ($total === 0) {
                            return 'Belum ada pemeriksaan medical full';
                        }

                        $summary = "Total: {$total} pemeriksaan | Selesai: {$selesai}";
                        if ($needsRenewal > 0) {
                            $summary .= " | ⚠️ Perlu Perpanjangan: {$needsRenewal}";
                        }

                        return $summary;
                    }),

                Repeater::make('medicalFulls')
                    ->label('Data Medical Full')
                    ->relationship('medicalFulls')
                    ->schema([
                        Radio::make('status')
                            ->label('Status Pemeriksaan')
                            ->options([
                                'Belum' => 'Belum Selesai',
                                'Selesai' => 'Selesai',
                            ])
                            ->required()
                            ->default('Belum')
                            ->reactive(),

                        DatePicker::make('examination_date')
                            ->label('Tanggal Pemeriksaan')
                            ->required()
                            ->maxDate(now())
                            ->default(now()),

                        FileUpload::make('medical_report_path')
                            ->label('Dokumen Medical Report')
                            ->disk('private')
                            ->directory('medical-full-reports')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->visible(fn ($get) => $get('status') === 'Selesai')
                            ->required(fn ($get) => $get('status') === 'Selesai'),

                        Textarea::make('examination_findings')
                            ->label('Hasil Pemeriksaan')
                            ->rows(4)
                            ->maxLength(2000)
                            ->placeholder('Catatan hasil pemeriksaan kesehatan lengkap...')
                            ->helperText('Tuliskan temuan penting atau kondisi kesehatan yang perlu diperhatikan'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Medical Full')
                    ->itemLabel(fn ($state) => $state['examination_date']
                        ? date('d M Y', strtotime($state['examination_date'])).' - '.($state['status'] ?? 'Belum')
                        : 'Medical Full Baru'),
            ])
            ->collapsible()
            ->collapsed(fn ($record) => ! $record || $record->medicalFulls->isEmpty())
            ->description('Pemeriksaan kesehatan lengkap sebelum keberangkatan. Perpanjangan diperlukan jika lebih dari 90 hari.');
    }
}
