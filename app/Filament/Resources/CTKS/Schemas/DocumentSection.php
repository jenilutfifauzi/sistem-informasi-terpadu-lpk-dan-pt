<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class DocumentSection
{
    public static function make(): Section
    {
        return Section::make('3-4. Dokumen CTK (Soal/Berkas & Paspor)')
            ->description(fn ($record) => self::getStatusBadge($record, 3, 4) ?? 'Upload dokumen-dokumen yang diperlukan untuk proses CTK')
            ->schema([
                Placeholder::make('document_summary')
                    ->label('Ringkasan Dokumen')
                    ->content(function ($record) {
                        if (! $record) {
                            return 'Belum ada dokumen';
                        }

                        $totalDocs = $record->documents->count();
                        $requiredDocs = 1;

                        return new HtmlString("
                            <div class='text-sm'>
                                <span class='font-semibold'>Progress:</span> {$totalDocs}/{$requiredDocs} dokumen terupload
                            </div>
                        ");
                    }),

                Repeater::make('documents')
                    ->relationship('documents')
                    ->label('Daftar Dokumen')
                    ->schema([
                        Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options(DocumentType::class)
                            ->required()
                            ->searchable()
                            ->helperText('Pilih jenis dokumen yang akan diupload'),

                        FileUpload::make('file_path')
                            ->label('File Dokumen')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory(function ($get) {
                                $docType = $get('document_type');
                                if ($docType) {
                                    // Jika sudah enum, gunakan langsung
                                    if ($docType instanceof DocumentType) {
                                        $enum = $docType;
                                    } else {
                                        // Jika string, convert ke enum
                                        $enum = DocumentType::from($docType);
                                    }

                                    return 'ctk-documents/'.$enum->getStorageDirectory();
                                }

                                return 'ctk-documents/misc';
                            })
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->helperText('Upload file PDF, JPG, atau PNG (max 10MB)')
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    if (is_array($state) && isset($state[0])) {
                                        $set('filename', basename($state[0]));
                                    } elseif (is_string($state)) {
                                        $set('filename', basename($state));
                                    }
                                }
                            }),

                        TextInput::make('filename')
                            ->label('Nama File')
                            ->disabled()
                            ->helperText('Nama file akan terisi otomatis saat upload'),

                        TextInput::make('file_size')
                            ->hidden(),

                        TextInput::make('mime_type')
                            ->hidden(),
                    ])
                    ->itemLabel(function (array $state): ?string {
                        $docType = $state['document_type'] ?? null;
                        if (! $docType) {
                            return 'Dokumen Baru';
                        }
                        // Jika sudah enum, ambil label langsung
                        if ($docType instanceof DocumentType) {
                            return $docType->getLabel();
                        }

                        // Jika string, convert ke enum
                        return DocumentType::tryFrom($docType)?->getLabel() ?? 'Dokumen';
                    })
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Dokumen')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed(false);
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
