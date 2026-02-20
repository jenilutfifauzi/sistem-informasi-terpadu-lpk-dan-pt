<?php

namespace App\Filament\Resources\CTKS\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CTKForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->description('Informasi data pribadi Calon Tenaga Kerja')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('photo')
                            ->label('Foto CTK')
                            ->image()
                            ->disk('public')
                            ->directory('ctk-photos')
                            ->columnSpanFull(),
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(16)
                            ->placeholder('Nomor Induk Karyawan')
                            ->columnSpan(1),
                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap sesuai KTP')
                            ->columnSpan(1),
                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        Radio::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->inline()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Section::make('Informasi Kontak')
                    ->description('Data kontak dan alamat')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Alamat lengkap sesuai KTP')
                            ->columnSpanFull(),
                        TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->required()
                            ->placeholder('08xxxxxxxxxx')
                            ->columnSpan(1),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->placeholder('email@example.com')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }
}
