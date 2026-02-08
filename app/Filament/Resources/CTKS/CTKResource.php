<?php

namespace App\Filament\Resources\CTKS;

use App\Enums\EntityType;
use App\Filament\Resources\CTKS\Pages\CreateCTK;
use App\Filament\Resources\CTKS\Pages\EditCTK;
use App\Filament\Resources\CTKS\Pages\ListCTKS;
use App\Filament\Resources\CTKS\Pages\ViewCTK;
use App\Filament\Resources\CTKS\Schemas\CTKForm;
use App\Filament\Resources\CTKS\Tables\CTKSTable;
use App\Models\CTK;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CTKResource extends Resource
{
    protected static ?string $model = CTK::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Calon Tenaga Kerja';

    protected static ?string $modelLabel = 'CTK';

    protected static ?string $pluralModelLabel = 'CTK';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CTKForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CTKSTable::configure($table);
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
            'index' => ListCTKS::route('/'),
            'create' => CreateCTK::route('/create'),
            'view' => ViewCTK::route('/{record}'),
            'edit' => EditCTK::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = Auth::user();

        // Super Admin can see all
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Pimpinan can see all (read-only)
        if ($user->hasRole('Pimpinan')) {
            return $query;
        }

        // Apply entity scoping
        $userEntity = $user->entity;

        // Admin LPK - only stages 1-5 (LPK)
        if ($user->hasRole('Admin LPK') && $userEntity === EntityType::LPK) {
            return $query->where('current_entity', EntityType::LPK)
                ->whereBetween('current_stage', [1, 5]);
        }

        // Admin PT, HR PT, Legal PT, Keuangan PT - only stages 6-15 (PT)
        if ($user->hasAnyRole(['Admin PT', 'HR PT', 'Legal PT', 'Keuangan PT']) && $userEntity === EntityType::PT) {
            return $query->where('current_entity', EntityType::PT)
                ->whereBetween('current_stage', [6, 15]);
        }

        // Keuangan LPK - LPK stages
        if ($user->hasRole('Keuangan LPK') && $userEntity === EntityType::LPK) {
            return $query->where('current_entity', EntityType::LPK);
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user->hasRole('super_admin') || $user->hasRole('Admin LPK');
    }
}
