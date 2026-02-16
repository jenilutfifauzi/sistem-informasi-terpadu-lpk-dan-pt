<?php

namespace App\Filament\Resources;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Filament\Exports\EmployeeLPKExport;
use App\Filament\Resources\EmployeeLPKResource\Pages;
use App\Models\EmployeeLPK;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeLPKResource extends Resource
{
    protected static ?string $model = EmployeeLPK::class;

    protected static ?string $slug = 'karyawan-lpks';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function getModelLabel(): string
    {
        return 'Karyawan LPK';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Data Master';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Karyawan LPK';
    }

    public static function form(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->schema([
                // Personal Information Section
                Schemas\Components\Section::make('Informasi Personal')
                    ->description('Data pribadi karyawan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->length(16)
                            ->disabled(fn (?EmployeeLPK $record) => $record !== null)
                            ->dehydrated(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->required()
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ]),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\TextInput::make('telepon')
                            ->label('Telepon')
                            ->required()
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                // Employment Information Section
                Schemas\Components\Section::make('Informasi Kepegawaian')
                    ->description('Posisi dan status karyawan')
                    ->schema([
                        Forms\Components\Select::make('jabatan')
                            ->label('Jabatan')
                            ->required()
                            ->options(JabatanLPK::class),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(StatusKepegawaian::class)
                            ->default('Aktif'),
                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->required()
                            ->disabled(fn (?EmployeeLPK $record) => $record !== null)
                            ->dehydrated(),
                        Forms\Components\Hidden::make('entity')
                            ->default('LPK'),
                    ])
                    ->columns(2),

                // Compensation Section
                Schemas\Components\Section::make('Kompensasi')
                    ->description('Data honor dan tunjangan')
                    ->schema([
                        Forms\Components\TextInput::make('honor_pokok')
                            ->label('Honor Pokok')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp ')
                            ->suffix(' / bulan'),
                        Forms\Components\TextInput::make('honor_per_jam')
                            ->label('Honor per Jam Mengajar')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp ')
                            ->suffix(' / jam')
                            ->visible(fn (Get $get) => $get('jabatan') === JabatanLPK::Instruktur),
                    ])
                    ->columns(2),

                // Sertifikat Section (Instruktur only)
                Schemas\Components\Section::make('Sertifikat Kompetensi')
                    ->description('Upload dokumen sertifikat kompetensi (PDF, JPG, PNG max 5MB)')
                    ->schema([
                        Forms\Components\FileUpload::make('sertifikat_path')
                            ->label('File Sertifikat')
                            ->disk('local')
                            ->directory('certificates')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120) // 5MB in KB
                            ->preserveFilenames(),
                    ])
                    ->columns(1)
                    ->visible(fn (Get $get) => $get('jabatan') === JabatanLPK::Instruktur),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jabatan')
                    ->label('Jabatan')
                    ->sortable()
                    ->colors([
                        'primary' => 'Instruktur',
                        'success' => 'Admin LPK',
                        'info' => 'Staff',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->colors([
                        'success' => 'Aktif',
                        'warning' => 'Cuti',
                        'danger' => 'Resign',
                    ]),
                Tables\Columns\TextColumn::make('tanggal_bergabung')
                    ->label('Tanggal Bergabung')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('honor_pokok')
                    ->label('Honor Pokok')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('sertifikat_path')
                    ->label('Sertifikat')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jabatan')
                    ->label('Jabatan')
                    ->options(JabatanLPK::class),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusKepegawaian::class),
                Tables\Filters\Filter::make('has_honor')
                    ->label('Ada Honor')
                    ->toggle()
                    ->query(fn ($query) => $query->whereNotNull('honor_pokok')),
                Tables\Filters\TrashedFilter::make()
                    ->label('Tampilkan Data Resign'),
            ])
            ->headerActions([
                Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $export = new EmployeeLPKExport($query);

                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'export_type' => 'karyawan_lpk',
                                'format' => 'csv',
                                'record_count' => $query->count(),
                            ])
                            ->log('Data exported');

                        return Excel::download(
                            $export,
                            'karyawan-lpk-'.now()->format('Y-m-d').'.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_bergabung', 'desc');
    }

    public static function infolist(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->schema([
                // Personal Information Section
                Schemas\Components\Section::make('Informasi Personal')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_lengkap')
                            ->label('Nama Lengkap'),
                        Infolists\Components\TextEntry::make('nik')
                            ->label('NIK'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d M Y'),
                        Infolists\Components\TextEntry::make('jenis_kelamin')
                            ->label('Jenis Kelamin'),
                        Infolists\Components\TextEntry::make('alamat')
                            ->label('Alamat'),
                        Infolists\Components\TextEntry::make('telepon')
                            ->label('Telepon'),
                    ])
                    ->columns(2),

                // Employment Information Section
                Schemas\Components\Section::make('Informasi Kepegawaian')
                    ->schema([
                        Infolists\Components\TextEntry::make('jabatan')
                            ->label('Jabatan'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status'),
                        Infolists\Components\TextEntry::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->date('d M Y'),
                    ])
                    ->columns(2),

                // Compensation Section
                Schemas\Components\Section::make('Kompensasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('honor_pokok')
                            ->label('Honor Pokok')
                            ->money('IDR', locale: 'id'),
                        Infolists\Components\TextEntry::make('honor_per_jam')
                            ->label('Honor per Jam')
                            ->money('IDR', locale: 'id')
                            ->visible(fn (EmployeeLPK $record) => $record->jabatan === JabatanLPK::Instruktur),
                    ])
                    ->columns(2),

                // Sertifikat Section
                Schemas\Components\Section::make('Sertifikat Kompetensi')
                    ->schema([
                        Infolists\Components\TextEntry::make('sertifikat_path')
                            ->label('Status Sertifikat')
                            ->state(fn (EmployeeLPK $record) => $record->sertifikat_path ? '✓ Tersedia' : '✗ Belum ada'),
                    ])
                    ->visible(fn (EmployeeLPK $record) => $record->sertifikat_path),
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
            'index' => Pages\ListEmployeesLPK::route('/'),
            'create' => Pages\CreateEmployeeLPK::route('/create'),
            'view' => Pages\ViewEmployeeLPK::route('/{record}'),
            'edit' => Pages\EditEmployeeLPK::route('/{record}/edit'),
        ];
    }
}
