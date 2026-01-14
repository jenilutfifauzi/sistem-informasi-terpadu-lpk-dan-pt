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

/**
 * Self-service profile resource for employees to view their own personal, employment, and compensation information.
 *
 * This resource provides a read-only view of an employee's profile, including personal details, employment
 * information, compensation (honor), and sertifikat kompetensi for Instruktur users.
 *
 * Access is strictly limited to the currently authenticated user viewing their own profile via email-based
 * scoping in the getEloquentQuery() method.
 *
 * @see EmployeeLPKResource for the administrative full CRUD interface
 */
class EmployeeLPKProfileResource extends Resource
{
    protected static ?string $model = EmployeeLPK::class;

    protected static ?string $slug = 'profil-saya';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('instruktur') ?? false;
    }

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
                            ->maxLength(1000),
                        Forms\Components\TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(20),
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
            'edit' => Pages\EditEmployeeLPKProfile::route('/{record}/edit'),
        ];
    }

    /**
     * Scope the query to only show the authenticated user's employee profile.
     *
     * This method implements the core security control for self-service access by filtering
     * results to only return the employee record matching the current user's email address.
     * This prevents any user from accessing other employees' profiles.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('email', auth()->user()?->email);
    }

    /**
     * Allow all authenticated users to access the self-service profile resource.
     *
     * The actual authorization is enforced at the record level via canView(),
     * which checks if the user's email matches the profile they're trying to view.
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    /**
     * Check if the authenticated user can view this specific employee profile.
     *
     * Users can only view their own profile by matching their email address against
     * the employee record's email. This prevents unauthorized cross-user profile access.
     *
     * @param  Model  $record  The employee profile being accessed
     * @return bool true if the user owns this profile, false otherwise
     */
    public static function canView(Model $record): bool
    {
        // User can view only their own profile
        return auth()->user()?->email === $record->email;
    }

    /**
     * Prevent creating new records via the profile resource.
     *
     * Profile resource is for viewing/editing own profile only.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Check if the authenticated user can update their own profile.
     *
     * Users can only update their own profile by matching their email address.
     *
     * @param  Model  $record  The employee profile being updated
     * @return bool true if the user owns this profile, false otherwise
     */
    public static function canUpdate(Model $record): bool
    {
        // User can update only their own profile
        return auth()->user()?->email === $record->email;
    }

    /**
     * Prevent deleting records via the profile resource.
     *
     * Profile resource is for viewing/editing own profile only.
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    /**
     * Prevent force deleting records via the profile resource.
     */
    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    /**
     * Prevent restoring records via the profile resource.
     */
    public static function canRestore(Model $record): bool
    {
        return false;
    }
}
