<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuIndukSiswaResource\Pages;
use App\Models\BukuIndukSiswa;
use BackedEnum;
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

class BukuIndukSiswaResource extends Resource
{
    protected static ?string $model = BukuIndukSiswa::class;

    protected static ?string $slug = 'buku-induk-siswa';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function getModelLabel(): string
    {
        return 'Buku Induk Siswa';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Buku Induk Siswa';
    }

    public static function getNavigationLabel(): string
    {
        return 'Buku Induk Siswa';
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
                Schemas\Components\Section::make('Identitas Buku Induk')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_path')
                            ->label('Foto Siswa')
                            ->image()
                            ->imageEditor()
                            ->directory('buku-induk-siswa/foto')
                            ->visibility('public')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nomor_induk')
                            ->label('Nomor Induk')
                            ->required()
                            ->maxLength(50)
                            ->rule(fn (?BukuIndukSiswa $record) => Rule::unique('buku_induk_siswa', 'nomor_induk')->ignore($record))
                            ->validationMessages([
                                'required' => 'Nomor induk wajib diisi.',
                                'unique' => 'Nomor induk sudah digunakan oleh siswa lain.',
                            ]),
                        Forms\Components\TextInput::make('program_pendidikan')
                            ->label('Program Pendidikan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('program_bahasa')
                            ->label('Program Bahasa')
                            ->searchable()
                            ->preload()
                            ->options([
                                'Bahasa Jepang' => 'Bahasa Jepang',
                                'Bahasa Inggris' => 'Bahasa Inggris',
                                'Bahasa Mandarin' => 'Bahasa Mandarin',
                                'Bahasa Korea' => 'Bahasa Korea',
                            ]),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_panggilan')
                            ->label('Nama Panggilan')
                            ->maxLength(255),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->required()
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ]),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()),
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
                        Forms\Components\TextInput::make('kewarganegaraan')
                            ->label('Kewarganegaraan')
                            ->default('Indonesia')
                            ->maxLength(100),
                        Forms\Components\Select::make('status_perkawinan')
                            ->label('Status')
                            ->live()
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai Hidup' => 'Cerai Hidup',
                                'Cerai Mati' => 'Cerai Mati',
                            ]),
                        Forms\Components\TextInput::make('nama_suami_istri')
                            ->label('Nama Suami / Istri')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => in_array($get('status_perkawinan'), ['Kawin', 'Cerai Hidup', 'Cerai Mati'], true)),
                        Forms\Components\TextInput::make('no_hp_suami_istri')
                            ->label('No. HP Suami / Istri')
                            ->tel()
                            ->maxLength(25)
                            ->visible(fn (Get $get): bool => in_array($get('status_perkawinan'), ['Kawin', 'Cerai Hidup', 'Cerai Mati'], true)),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Tempat Tinggal')
                    ->schema([
                        Forms\Components\Textarea::make('alamat_siswa')
                            ->label('Alamat Siswa')
                            ->rows(3),
                        Forms\Components\TextInput::make('no_hp_siswa')
                            ->label('No. HP Siswa')
                            ->tel()
                            ->maxLength(25),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat_orang_tua')
                            ->label('Alamat Orang Tua')
                            ->rows(3),
                        Forms\Components\TextInput::make('no_hp_orang_tua')
                            ->label('No. HP Orang Tua')
                            ->tel()
                            ->maxLength(25),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Kesehatan')
                    ->schema([
                        Forms\Components\Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'AB' => 'AB',
                                'O' => 'O',
                                'Tidak Tahu' => 'Tidak Tahu',
                            ]),
                        Forms\Components\Textarea::make('penyakit_pernah_diderita')
                            ->label('Penyakit yang Pernah Diderita')
                            ->rows(3),
                        Forms\Components\Textarea::make('kelainan_jasmani')
                            ->label('Kelainan Jasmani')
                            ->rows(3),
                        Forms\Components\TextInput::make('tinggi_badan_cm')
                            ->label('Tinggi Badan')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('berat_badan_kg')
                            ->label('Berat Badan')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('kg'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nomor_induk')
                    ->label('Nomor Induk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('program_pendidikan')
                    ->label('Program Pendidikan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('program_bahasa')
                    ->label('Program Bahasa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->colors([
                        'primary' => 'Laki-laki',
                        'success' => 'Perempuan',
                    ]),
                Tables\Columns\TextColumn::make('no_hp_siswa')
                    ->label('No. HP Siswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('program_pendidikan')
                    ->label('Program Pendidikan')
                    ->options(fn (): array => BukuIndukSiswa::query()
                        ->whereNotNull('program_pendidikan')
                        ->orderBy('program_pendidikan')
                        ->pluck('program_pendidikan', 'program_pendidikan')
                        ->all()),
                Tables\Filters\SelectFilter::make('program_bahasa')
                    ->label('Program Bahasa')
                    ->options(fn (): array => BukuIndukSiswa::query()
                        ->whereNotNull('program_bahasa')
                        ->orderBy('program_bahasa')
                        ->pluck('program_bahasa', 'program_bahasa')
                        ->all()),
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),
                Tables\Filters\SelectFilter::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        'O' => 'O',
                        'Tidak Tahu' => 'Tidak Tahu',
                    ]),
                Tables\Filters\TrashedFilter::make(),
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
                Schemas\Components\Section::make('Identitas Buku Induk')
                    ->schema([
                        Infolists\Components\ImageEntry::make('foto_path')->label('Foto')->circular(),
                        Infolists\Components\TextEntry::make('nama_lengkap')->label('Nama Lengkap'),
                        Infolists\Components\TextEntry::make('nomor_induk')->label('Nomor Induk'),
                        Infolists\Components\TextEntry::make('program_pendidikan')->label('Program Pendidikan'),
                        Infolists\Components\TextEntry::make('program_bahasa')->label('Program Bahasa')->placeholder('-'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Pribadi')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_panggilan')->label('Nama Panggilan')->placeholder('-'),
                        Infolists\Components\TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                        Infolists\Components\TextEntry::make('tempat_lahir')->label('Tempat Lahir')->placeholder('-'),
                        Infolists\Components\TextEntry::make('tanggal_lahir')->label('Tanggal Lahir')->date('d F Y')->placeholder('-'),
                        Infolists\Components\TextEntry::make('agama')->label('Agama')->placeholder('-'),
                        Infolists\Components\TextEntry::make('kewarganegaraan')->label('Kewarganegaraan')->placeholder('-'),
                        Infolists\Components\TextEntry::make('status_perkawinan')->label('Status')->placeholder('-'),
                        Infolists\Components\TextEntry::make('nama_suami_istri')->label('Nama Suami / Istri')->placeholder('-'),
                        Infolists\Components\TextEntry::make('no_hp_suami_istri')->label('No. HP Suami / Istri')->placeholder('-'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Tempat Tinggal')
                    ->schema([
                        Infolists\Components\TextEntry::make('alamat_siswa')->label('Alamat Siswa')->placeholder('-'),
                        Infolists\Components\TextEntry::make('no_hp_siswa')->label('No. HP Siswa')->placeholder('-'),
                        Infolists\Components\TextEntry::make('email')->label('Email')->placeholder('-'),
                        Infolists\Components\TextEntry::make('alamat_orang_tua')->label('Alamat Orang Tua')->placeholder('-'),
                        Infolists\Components\TextEntry::make('no_hp_orang_tua')->label('No. HP Orang Tua')->placeholder('-'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Keterangan Kesehatan')
                    ->schema([
                        Infolists\Components\TextEntry::make('golongan_darah')->label('Golongan Darah')->placeholder('-'),
                        Infolists\Components\TextEntry::make('penyakit_pernah_diderita')->label('Penyakit yang Pernah Diderita')->placeholder('-'),
                        Infolists\Components\TextEntry::make('kelainan_jasmani')->label('Kelainan Jasmani')->placeholder('-'),
                        Infolists\Components\TextEntry::make('tinggi_badan_cm')->label('Tinggi Badan')->suffix(' cm')->placeholder('-'),
                        Infolists\Components\TextEntry::make('berat_badan_kg')->label('Berat Badan')->suffix(' kg')->placeholder('-'),
                    ])
                    ->columns(2),
                Schemas\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('creator.name')->label('Dibuat Oleh')->placeholder('-'),
                        Infolists\Components\TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d F Y H:i'),
                        Infolists\Components\TextEntry::make('updater.name')->label('Diperbarui Oleh')->placeholder('-'),
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
            'index' => Pages\ListBukuIndukSiswas::route('/'),
            'create' => Pages\CreateBukuIndukSiswa::route('/create'),
            'view' => Pages\ViewBukuIndukSiswa::route('/{record}'),
            'edit' => Pages\EditBukuIndukSiswa::route('/{record}/edit'),
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
        return Auth::user()?->can('viewAny', BukuIndukSiswa::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create', BukuIndukSiswa::class) ?? false;
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
        return Auth::user()?->can('delete_buku_induk_siswa') ?? false;
    }
}
