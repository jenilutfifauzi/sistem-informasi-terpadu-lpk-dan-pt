<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\MCUStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class MCUSection
{
    public static function make(): Section
    {
        return Section::make('MCU (Medical Check-Up)')
            ->description('Rekam hasil pemeriksaan kesehatan untuk calon TKI')
            ->schema([
                Repeater::make('mcuRecords')
                    ->relationship('mcuRecords')
                    ->label('Riwayat MCU')
                    ->schema([
                        Radio::make('status')
                            ->label('Status MCU')
                            ->options([
                                MCUStatus::FIT->value => 'FIT - Lolos Pemeriksaan',
                                MCUStatus::UNFIT->value => 'UNFIT - Tidak Lolos',
                                MCUStatus::PENDING->value => 'PENDING - Menunggu Hasil',
                            ])
                            ->inline()
                            ->required()
                            ->live()
                            ->helperText('Status MCU diperlukan untuk melanjutkan ke tahap pembayaran'),

                        DatePicker::make('examination_date')
                            ->label('Tanggal Pemeriksaan')
                            ->required()
                            ->maxDate(now())
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->helperText('Tanggal saat pemeriksaan MCU dilakukan'),

                        TextInput::make('clinic_name')
                            ->label('Nama Klinik/RS')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama klinik atau rumah sakit tempat MCU dilakukan'),

                        TextInput::make('examiner_name')
                            ->label('Nama Pemeriksa')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama dokter atau petugas yang melakukan pemeriksaan'),

                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->nullable()
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Catatan hasil pemeriksaan, kondisi khusus, atau tindak lanjut'),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Rekaman MCU')
                    ->collapsible()
                    ->collapsed(false)
                    ->itemLabel(fn (array $state): ?string => isset($state['examination_date'])
                        ? 'MCU - '.$state['examination_date'].' ('.$state['status'].')'
                        : 'MCU Baru')
                    ->reorderable(false)
                    ->deletable(true)
                    ->cloneable(false),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->columns(1);
    }
}
