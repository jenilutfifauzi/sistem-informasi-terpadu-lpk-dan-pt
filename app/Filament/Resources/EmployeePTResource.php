<?php

namespace App\Filament\Resources;

use App\Enums\DivisiPT;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Filament\Exports\EmployeePTExport;
use App\Filament\Resources\EmployeePTResource\Pages;
use App\Models\EmployeePT;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class EmployeePTResource extends Resource
{
    protected static ?string $model = EmployeePT::class;

    protected static ?string $slug = 'karyawan-pts';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function getModelLabel(): string
    {
        return 'Karyawan PT';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Data Master';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Karyawan PT';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', EmployeePT::class) ?? false;
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
                            ->inputMode('numeric')
                            ->rules(fn (?EmployeePT $record) => $record === null ? [
                                'regex:/^\d{16}$/',
                                Rule::unique('karyawan_pt', 'nik')->whereNull('deleted_at'),
                            ] : ['regex:/^\d{16}$/'])
                            ->disabled(fn (?EmployeePT $record) => $record !== null)
                            ->dehydrated(),
                        Forms\Components\FileUpload::make('foto_path')
                            ->label('Foto Karyawan')
                            ->image()
                            ->disk('public')
                            ->directory('karyawan-pt-photos')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->rules(fn (?EmployeePT $record) => [
                                Rule::unique('karyawan_pt', 'email')
                                    ->whereNull('deleted_at')
                                    ->when($record, fn ($rule) => $rule->ignore($record->id)),
                            ]),
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
                            ->options(JabatanPT::class),
                        Forms\Components\Select::make('divisi')
                            ->label('Divisi')
                            ->required()
                            ->options(DivisiPT::class),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(StatusKepegawaian::class)
                            ->default('Aktif'),
                        Forms\Components\Select::make('jenis_kontrak')
                            ->label('Jenis Kontrak')
                            ->required()
                            ->options(JenisKontrak::class),
                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->required()
                            ->disabled(fn (?EmployeePT $record) => $record !== null)
                            ->dehydrated(),
                        Forms\Components\Hidden::make('entity')
                            ->default('PT'),
                    ])
                    ->columns(2),

                // Compensation Section
                Schemas\Components\Section::make('Kompensasi')
                    ->description('Data gaji dan tunjangan')
                    ->schema([
                        Forms\Components\TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp ')
                            ->suffix('/ bulan'),
                        Forms\Components\TextInput::make('tunjangan')
                            ->label('Tunjangan')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp '),
                    ])
                    ->columns(2),

                // Dokumen Kepegawaian Section
                Schemas\Components\Section::make('Dokumen Kepegawaian')
                    ->description('Upload dokumen kepegawaian (PDF/JPG/PNG max 5MB)')
                    ->schema([
                        Forms\Components\FileUpload::make('dokumen_path')
                            ->label('File Dokumen')
                            ->disk('private')
                            ->directory('documents')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->preserveFilenames(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('jabatan')
                    ->label('Jabatan')
                    ->sortable()
                    ->colors([
                        'primary' => 'Direktur',
                        'success' => 'Manajer',
                        'info' => 'Staf HRD',
                        'warning' => 'Staf Keuangan',
                        'danger' => 'Staf Operasional',
                        'secondary' => 'Staf Administrasi',
                    ]),
                Tables\Columns\TextColumn::make('divisi')
                    ->label('Divisi')
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tunjangan')
                    ->label('Tunjangan')
                    ->money('IDR', locale: 'id')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('dokumen_path')
                    ->label('Dokumen')
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
                    ->options(JabatanPT::class),
                Tables\Filters\SelectFilter::make('divisi')
                    ->label('Divisi')
                    ->options(DivisiPT::class),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusKepegawaian::class),
                Tables\Filters\SelectFilter::make('jenis_kontrak')
                    ->label('Jenis Kontrak')
                    ->options(JenisKontrak::class),
                Tables\Filters\Filter::make('has_gaji')
                    ->label('Ada Gaji')
                    ->toggle()
                    ->query(fn ($query) => $query->whereNotNull('gaji_pokok')),
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
                        $export = new EmployeePTExport($query);

                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'export_type' => 'karyawan_pt',
                                'format' => 'csv',
                                'record_count' => $query->count(),
                            ])
                            ->log('Data exported');

                        return Excel::download(
                            $export,
                            'karyawan-pt-'.now()->format('Y-m-d').'.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
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
                        Infolists\Components\ImageEntry::make('foto_path')
                            ->label('Foto')
                            ->circular()
                            ->disk('public')
                            ->defaultImageUrl(url('/images/default-avatar.png'))
                            ->columnSpanFull(),
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
                        Infolists\Components\TextEntry::make('divisi')
                            ->label('Divisi'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status'),
                        Infolists\Components\TextEntry::make('jenis_kontrak')
                            ->label('Jenis Kontrak'),
                        Infolists\Components\TextEntry::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->date('d M Y'),
                    ])
                    ->columns(2),

                // Compensation Section
                Schemas\Components\Section::make('Kompensasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->money('IDR', locale: 'id'),
                        Infolists\Components\TextEntry::make('tunjangan')
                            ->label('Tunjangan')
                            ->money('IDR', locale: 'id')
                            ->visible(fn (EmployeePT $record) => $record->tunjangan !== null),
                    ])
                    ->columns(2),

                // Document Section
                Schemas\Components\Section::make('Dokumen Kepegawaian')
                    ->schema([
                        Infolists\Components\TextEntry::make('dokumen_path')
                            ->label('Status Dokumen')
                            ->state(fn (EmployeePT $record) => $record->dokumen_path ? '✓ Tersedia' : '✗ Belum ada'),
                        Infolists\Components\TextEntry::make('dokumen_download_url')
                            ->label('Unduh Dokumen')
                            ->state(function (EmployeePT $record): string {
                                if (! $record->dokumen_path) {
                                    return '-';
                                }
                                if (! auth()->user()?->can('downloadDokumen', $record)) {
                                    return 'Tidak diizinkan';
                                }

                                return $record->dokumen_download_url ?? '-';
                            })
                            ->visible(fn (EmployeePT $record) => $record->dokumen_path !== null),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('entity', 'PT')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeesPT::route('/'),
            'create' => Pages\CreateEmployeePT::route('/create'),
            'view' => Pages\ViewEmployeePT::route('/{record}'),
            'edit' => Pages\EditEmployeePT::route('/{record}/edit'),
        ];
    }
}
