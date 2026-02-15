<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PasporSection
{
    public static function make(): Section
    {
        return Section::make('4. Paspor')
            ->description(fn ($record) => self::getStatusBadge($record, 4) ?? 'Input nomor paspor dan upload dokumen paspor')
            ->schema([
                TextInput::make('paspor_number')
                    ->label('Nomor Paspor')
                    ->nullable()
                    ->maxLength(50)
                    ->helperText('Masukkan nomor paspor calon TKI'),

                Repeater::make('pasporDocuments')
                    ->relationship('documents', modifyQueryUsing: fn ($query) => $query->where('document_type', DocumentType::Paspor))
                    ->label('Daftar Dokumen Paspor')
                    ->schema([
                        Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options([DocumentType::Paspor->value => DocumentType::Paspor->getLabel()])
                            ->default(DocumentType::Paspor->value)
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->helperText('Jenis dokumen otomatis terisi sebagai Paspor'),

                        FileUpload::make('file_path')
                            ->label('File Dokumen')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('ctk-documents/paspor')
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
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['document_type'] = DocumentType::Paspor->value;

                        return $data;
                    })
                    ->itemLabel(fn (array $state): ?string => $state['filename'] ?? 'Dokumen Baru')
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Dokumen Paspor')
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->columns(1);
    }

    protected static function getStatusBadge($record, int $stage): ?string
    {
        if (! $record) {
            return null;
        }

        $stageAttribute = "stage{$stage}_complete";
        $isComplete = $record->$stageAttribute ?? false;
        $icon = $isComplete ? '✅' : '⬜';
        $status = $isComplete ? 'Selesai' : 'Belum Selesai';

        return "{$icon} Stage {$stage}: {$status}";
    }
}
