<?php

namespace App\Filament\Resources\PembayaranPusat\Pages;

use App\Filament\Resources\PembayaranPusat\PembayaranPusatResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPembayaranPusat extends ViewRecord
{
    protected static string $resource = PembayaranPusatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->visible(fn () => ! auth()->user()->hasRole('Pimpinan')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi CTK')
                    ->schema([
                        TextEntry::make('ctk.nama_lengkap')
                            ->label('Nama CTK')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('ctk.nik')
                            ->label('NIK')
                            ->copyable(),
                        TextEntry::make('entity')
                            ->label('Entity')
                            ->badge()
                            ->color(fn ($state): string => match ($state->value) {
                                'PT' => 'warning',
                                'LPK' => 'info',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Detail Pembayaran')
                    ->schema([
                        TextEntry::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->date('d F Y'),
                        TextEntry::make('nominal')
                            ->label('Nominal')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold')
                            ->color('success'),
                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Tidak ada keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Bukti Transfer')
                    ->schema([
                        ImageEntry::make('bukti_transfer_path')
                            ->label('')
                            ->disk('public')
                            ->height(300)
                            ->defaultImageUrl(url('/images/placeholder-document.png'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->bukti_transfer_path),

                Section::make('Informasi Audit')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->placeholder('Unknown'),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y H:i'),
                        TextEntry::make('updater.name')
                            ->label('Diperbarui Oleh')
                            ->placeholder('Belum pernah diperbarui'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y H:i'),
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }
}
