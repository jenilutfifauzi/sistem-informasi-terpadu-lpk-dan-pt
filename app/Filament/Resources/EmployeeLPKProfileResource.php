<?php

namespace App\Filament\Resources;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Filament\Resources\EmployeeLPKProfileResource\Pages;
use App\Models\EmployeeLPK;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeeLPKProfileResource extends Resource
{
    protected static ?string $model = EmployeeLPK::class;

    protected static ?string $slug = 'profil-saya';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function getModelLabel(): string
    {
        return 'Profil Saya';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Akun Saya';
    }

    public static function form(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->schema([
                // Personal Information Section (Read-only)
                Schemas\Components\Section::make('Informasi Personal')
                    ->description('Data pribadi Anda')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->disabled(),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->disabled()
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ]),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->disabled(),
                        Forms\Components\TextInput::make('telepon')
                            ->label('Telepon')
                            ->disabled(),
                    ])
                    ->columns(2),

                // Employment Information Section (Read-only)
                Schemas\Components\Section::make('Informasi Kepegawaian')
                    ->description('Posisi dan status kerja Anda')
                    ->schema([
                        Forms\Components\Select::make('jabatan')
                            ->label('Jabatan')
                            ->disabled()
                            ->options(JabatanLPK::class),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->disabled()
                            ->options(StatusKepegawaian::class),
                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->disabled(),
                    ])
                    ->columns(2),

                // Compensation Section (Read-only)
                Schemas\Components\Section::make('Kompensasi')
                    ->description('Data honor dan tunjangan Anda')
                    ->schema([
                        Forms\Components\TextInput::make('honor_pokok')
                            ->label('Honor Pokok')
                            ->disabled()
                            ->prefix('Rp ')
                            ->suffix(' / bulan'),
                        Forms\Components\TextInput::make('honor_per_jam')
                            ->label('Honor per Jam Mengajar')
                            ->disabled()
                            ->prefix('Rp ')
                            ->suffix(' / jam')
                            ->visible(fn (Forms\Get $get) => $get('jabatan') === JabatanLPK::Instruktur),
                    ])
                    ->columns(2),

                // Sertifikat Section (Read-only for display)
                Schemas\Components\Section::make('Sertifikat Kompetensi')
                    ->description('File sertifikat kompetensi Anda')
                    ->schema([
                        Forms\Components\TextInput::make('sertifikat_path')
                            ->label('Sertifikat')
                            ->disabled()
                            ->visible(fn (Forms\Get $get) => ! empty($get('sertifikat_path')) && $get('jabatan') === JabatanLPK::Instruktur),
                    ])
                    ->columns(1)
                    ->visible(fn (Forms\Get $get) => $get('jabatan') === JabatanLPK::Instruktur),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => Pages\ViewEmployeeLPKProfile::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Only show current user's profile
        return parent::getEloquentQuery()
            ->where('email', auth()->user()?->email);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        // User can view only their own profile
        return auth()->user()?->email === $record->email;
    }
}
