<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Information')
                    ->description('Basic information about the asset')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('entity')
                                    ->label('Entity')
                                    ->default(fn () => Auth::user()?->entity?->value)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => $state === 'PT' ? 'PT (Perusahaan)' : 'LPK (Lembaga Pelatihan Kerja)')
                                    ->visible(fn ($operation) => $operation === 'edit'),

                                Select::make('kategori')
                                    ->label('Category')
                                    ->options(AssetCategory::class)
                                    ->required()
                                    ->native(false),

                                TextInput::make('nama_barang')
                                    ->label('Asset Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('deskripsi')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Quantity & Condition')
                    ->description('Amount and condition details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('jumlah')
                                    ->label('Quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),

                                TextInput::make('satuan')
                                    ->label('Unit')
                                    ->required()
                                    ->maxLength(50)
                                    ->default('Unit'),

                                Select::make('kondisi')
                                    ->label('Condition')
                                    ->options(AssetCondition::class)
                                    ->required()
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Financial Information')
                    ->description('Purchase year and value')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tahun_pembelian')
                                    ->label('Purchase Year')
                                    ->options(array_combine(
                                        range(date('Y'), 1990),
                                        range(date('Y'), 1990)
                                    ))
                                    ->required()
                                    ->native(false)
                                    ->default(date('Y')),

                                TextInput::make('nilai_pembelian')
                                    ->label('Purchase Value (IDR)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                    ]),

                Section::make('Location & Notes')
                    ->description('Where the asset is located and additional notes')
                    ->schema([
                        TextInput::make('lokasi')
                            ->label('Location')
                            ->maxLength(255)
                            ->placeholder('e.g., Kantor Pusat Jakarta, Ruang IT'),

                        Textarea::make('keterangan')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Additional information or remarks'),
                    ]),
            ]);
    }
}
