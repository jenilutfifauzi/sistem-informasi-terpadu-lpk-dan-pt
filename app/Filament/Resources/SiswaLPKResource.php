<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SiswaLPKExport;
use App\Filament\Resources\SiswaLPKResource\Pages;
use App\Models\SiswaLPK;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class SiswaLPKResource extends Resource
{
    protected static ?string $model = SiswaLPK::class;

    protected static ?string $slug = 'siswa-lpk';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama_siswa';

    public static function getModelLabel(): string
    {
        return 'Siswa LPK';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Data Siswa LPK';
    }

    public static function getNavigationLabel(): string
    {
        return 'Data Siswa LPK';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Data Master';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creator:id,name', 'updater:id,name']);
    }

    public static function form(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Section::make('Data Pendaftaran')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_urut')
                            ->label('No. Urut')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('nomor_induk')
                            ->label('Nomor Induk')
                            ->required()
                            ->maxLength(50)
                            ->rule(fn (?SiswaLPK $record) => Rule::unique('siswa_lpk', 'nomor_induk')->ignore($record))
                            ->validationMessages([
                                'required' => 'Nomor induk wajib diisi.',
                                'unique' => 'Nomor induk sudah digunakan oleh siswa lain.',
                            ]),
                        Forms\Components\TextInput::make('nama_siswa')
                            ->label('Nama Siswa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->required()
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ]),
                        Forms\Components\Select::make('agama')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ]),
                        Forms\Components\Select::make('pendidikan_terakhir')
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                                'D1' => 'D1',
                                'D2' => 'D2',
                                'D3' => 'D3',
                                'S1' => 'S1',
                                'S2' => 'S2',
                            ]),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Data Lahir dan Program')
                    ->schema([
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Jika catatan sumber menggabungkan tempat lahir dan tanggal lahir, pisahkan nilainya ke field masing-masing sebelum menyimpan.'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $tanggalMasuk = $get('tanggal_masuk');

                                    if (blank($value) || blank($tanggalMasuk)) {
                                        return;
                                    }

                                    if (Carbon::parse($value)->gt(Carbon::parse($tanggalMasuk))) {
                                        $fail('Tanggal lahir tidak boleh setelah tanggal masuk.');
                                    }
                                },
                            ]),
                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $tanggalLahir = $get('tanggal_lahir');

                                    if (blank($value) || blank($tanggalLahir)) {
                                        return;
                                    }

                                    if (Carbon::parse($value)->lt(Carbon::parse($tanggalLahir))) {
                                        $fail('Tanggal masuk tidak boleh sebelum tanggal lahir.');
                                    }
                                },
                            ]),
                        Forms\Components\TextInput::make('program_pendidikan')
                            ->label('Program Pendidikan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->rows(3),
                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. HP')
                            ->required()
                            ->tel()
                            ->maxLength(25),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Opsional'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_urut')
                    ->label('No. Urut')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_induk')
                    ->label('Nomor Induk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->colors([
                        'primary' => 'L',
                        'success' => 'P',
                    ]),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('program_pendidikan')
                    ->label('Program Pendidikan')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('program_pendidikan')
                    ->label('Program Pendidikan')
                    ->options(fn (): array => SiswaLPK::query()
                        ->orderBy('program_pendidikan')
                        ->pluck('program_pendidikan', 'program_pendidikan')
                        ->all()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Actions\Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (): bool => Auth::user()?->can('export', SiswaLPK::class) ?? false)
                    ->action(function ($livewire) {
                        abort_unless(Auth::user()?->can('export', SiswaLPK::class), 403);

                        $query = $livewire->getFilteredTableQuery();
                        $recordCount = (clone $query)->count();

                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'export_type' => 'siswa_lpk',
                                'format' => 'xlsx',
                                'record_count' => $recordCount,
                            ])
                            ->log('Data exported');

                        return Excel::download(
                            new SiswaLPKExport($query),
                            'siswa-lpk-'.now()->format('Y-m-d').'.xlsx',
                            ExcelFormat::XLSX
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
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nomor_induk')
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Section::make('Data Pribadi')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_induk')->label('Nomor Induk'),
                        Infolists\Components\TextEntry::make('nama_siswa')->label('Nama Siswa'),
                        Infolists\Components\TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                        Infolists\Components\TextEntry::make('agama')->label('Agama'),
                        Infolists\Components\TextEntry::make('tempat_lahir')->label('Tempat Lahir'),
                        Infolists\Components\TextEntry::make('tanggal_lahir')->label('Tanggal Lahir')->date('d F Y'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Kontak')
                    ->schema([
                        Infolists\Components\TextEntry::make('alamat')->label('Alamat'),
                        Infolists\Components\TextEntry::make('no_hp')->label('No. HP'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('Tidak ada email'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Pendidikan')
                    ->schema([
                        Infolists\Components\TextEntry::make('pendidikan_terakhir')->label('Pendidikan Terakhir'),
                        Infolists\Components\TextEntry::make('program_pendidikan')->label('Program Pendidikan'),
                        Infolists\Components\TextEntry::make('tanggal_masuk')->label('Tanggal Masuk')->date('d F Y'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('creator.name')->label('Dibuat Oleh'),
                        Infolists\Components\TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d F Y H:i'),
                        Infolists\Components\TextEntry::make('updater.name')->label('Diperbarui Oleh'),
                        Infolists\Components\TextEntry::make('updated_at')->label('Diperbarui Pada')->dateTime('d F Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswaLPKS::route('/'),
            'create' => Pages\CreateSiswaLPK::route('/create'),
            'view' => Pages\ViewSiswaLPK::route('/{record}'),
            'edit' => Pages\EditSiswaLPK::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('viewAny', SiswaLPK::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create', SiswaLPK::class) ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('update', $record) ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('delete', $record) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->can('delete_siswa_lpk') ?? false;
    }
}
