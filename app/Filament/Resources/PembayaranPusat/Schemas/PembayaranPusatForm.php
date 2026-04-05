<?php

namespace App\Filament\Resources\PembayaranPusat\Schemas;

use App\Models\CTK;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PembayaranPusatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembayaran')
                    ->description('Data pembayaran ke pusat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('ctk_id')
                                    ->label('CTK')
                                    ->relationship('ctk', 'nama_lengkap')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->getOptionLabelFromRecordUsing(fn (CTK $record): string => "{$record->nama_lengkap} ({$record->nik})")
                                    ->helperText('Pilih CTK yang akan dibayarkan'),

                                DatePicker::make('tanggal_pembayaran')
                                    ->label('Tanggal Pembayaran')
                                    ->required()
                                    ->native(false)
                                    ->maxDate(now())
                                    ->default(now())
                                    ->displayFormat('d F Y')
                                    ->helperText('Tanggal tidak boleh melebihi hari ini'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('nominal')
                                    ->label('Nominal Pembayaran')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(1)
                                    ->default(0)
                                    ->helperText('Minimal Rp 1'),

                                FileUpload::make('bukti_transfer_path')
                                    ->label('Bukti Transfer')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(10240) // 10MB
                                    ->disk('public')
                                    ->directory('pembayaran-pusat')
                                    ->downloadable()
                                    ->previewable()
                                    ->helperText('Format: JPG, PNG, PDF. Maks: 10MB'),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan (opsional)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
