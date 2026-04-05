<?php

namespace App\Filament\Resources\PembayaranPusat;

use App\Filament\Resources\PembayaranPusat\Pages\CreatePembayaranPusat;
use App\Filament\Resources\PembayaranPusat\Pages\EditPembayaranPusat;
use App\Filament\Resources\PembayaranPusat\Pages\ListPembayaranPusat;
use App\Filament\Resources\PembayaranPusat\Pages\ViewPembayaranPusat;
use App\Filament\Resources\PembayaranPusat\Schemas\PembayaranPusatForm;
use App\Filament\Resources\PembayaranPusat\Tables\PembayaranPusatTable;
use App\Models\PembayaranPusat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PembayaranPusatResource extends Resource
{
    protected static ?string $model = PembayaranPusat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pembayaran ke Pusat';

    protected static ?string $modelLabel = 'Pembayaran ke Pusat';

    protected static ?string $pluralModelLabel = 'Pembayaran ke Pusat';

    public static function getNavigationLabel(): string
    {
        $user = Auth::user();

        if ($user?->hasRole('Pimpinan')) {
            return 'Semua Pembayaran ke Pusat';
        }

        if ($user?->entity) {
            return 'Pembayaran ke Pusat '.$user->entity->value;
        }

        return 'Pembayaran ke Pusat';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['ctk:id,nama_lengkap,nik', 'creator:id,name'])
            ->orderByDesc('tanggal_pembayaran');

        $user = Auth::user();

        // Apply entity scoping - only skip for Pimpinan role
        if ($user && ! $user->hasRole('Pimpinan')) {
            $query->where('entity', $user->entity);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return PembayaranPusatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PembayaranPusatTable::configure($table);
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
            'index' => ListPembayaranPusat::route('/'),
            'create' => CreatePembayaranPusat::route('/create'),
            'view' => ViewPembayaranPusat::route('/{record}'),
            'edit' => EditPembayaranPusat::route('/{record}/edit'),
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
        return Auth::user()?->can('view_any_pembayaran_pusat') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create_pembayaran_pusat') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('update_pembayaran_pusat') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('delete_pembayaran_pusat') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->can('delete_pembayaran_pusat') ?? false;
    }
}
