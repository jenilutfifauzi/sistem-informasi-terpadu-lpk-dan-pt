<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => ! auth()->user()->hasRole('Pimpinan')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Asset Information')
                    ->schema([
                        TextEntry::make('nomor_inventaris')
                            ->label('Inventory Number')
                            ->badge()
                            ->color('primary')
                            ->copyable(),
                        TextEntry::make('entity')
                            ->label('Entity')
                            ->badge()
                            ->color(fn ($state): string => match ($state->value) {
                                'PT' => 'warning',
                                'LPK' => 'info',
                            }),
                        TextEntry::make('kategori')
                            ->label('Category')
                            ->badge()
                            ->color(fn ($record) => $record->kategori->color()),
                        TextEntry::make('nama_barang')
                            ->label('Asset Name')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                        TextEntry::make('deskripsi')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Quantity & Condition')
                    ->schema([
                        TextEntry::make('jumlah')
                            ->label('Quantity')
                            ->numeric()
                            ->formatStateUsing(fn ($record) => "{$record->jumlah} {$record->satuan}"),
                        TextEntry::make('kondisi')
                            ->label('Condition')
                            ->badge()
                            ->color(fn ($record) => $record->kondisi->getColor()),
                        TextEntry::make('status_assignment')
                            ->label('Assignment Status')
                            ->badge()
                            ->color(fn ($record) => $record->status_assignment->getColor()),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Financial Information')
                    ->schema([
                        TextEntry::make('tahun_pembelian')
                            ->label('Purchase Year')
                            ->placeholder('N/A'),
                        TextEntry::make('nilai_pembelian')
                            ->label('Purchase Value')
                            ->money('IDR')
                            ->placeholder('N/A'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Location & Notes')
                    ->schema([
                        TextEntry::make('lokasi')
                            ->label('Location')
                            ->placeholder('N/A'),
                        TextEntry::make('keterangan')
                            ->label('Notes')
                            ->placeholder('No notes available')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Audit Information')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('Unknown'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d F Y H:i'),
                        TextEntry::make('updater.name')
                            ->label('Updated By')
                            ->placeholder('Unknown'),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('d F Y H:i'),
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }
}
