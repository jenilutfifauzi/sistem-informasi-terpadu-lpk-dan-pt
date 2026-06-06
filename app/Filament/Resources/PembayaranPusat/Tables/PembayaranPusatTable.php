<?php

namespace App\Filament\Resources\PembayaranPusat\Tables;

use App\Enums\EntityType;
use App\Filament\Exports\PembayaranPusatExport;
use App\Filament\Resources\PembayaranPusat\PembayaranPusatResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class PembayaranPusatTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ctk.nama_lengkap')
                    ->label('Nama CTK')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->ctk?->nik),

                TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(),

                ImageColumn::make('bukti_transfer_path')
                    ->label('Bukti')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-document.png'))
                    ->visibility('public'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->keterangan)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entity')
                    ->label('Entity')
                    ->options(EntityType::class)
                    ->visible(fn () => Auth::user()?->hasRole('Pimpinan')),

                SelectFilter::make('ctk_id')
                    ->label('CTK')
                    ->relationship('ctk', 'nama_lengkap')
                    ->searchable()
                    ->preload(),

                Filter::make('tanggal_pembayaran')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pembayaran', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pembayaran', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['dari_tanggal'] = 'Dari: '.\Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Sampai: '.\Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }

                        return $indicators;
                    }),

                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel (.xlsx)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $count = $query->count();

                        if ($count === 0) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak Ada Data')
                                ->body('Tidak ada data pembayaran yang sesuai dengan filter.')
                                ->send();

                            return;
                        }

                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'exported_count' => $count,
                                'format' => 'xlsx',
                                'model' => 'PembayaranPusat',
                            ])
                            ->log('Exported '.$count.' pembayaran pusat to xlsx');

                        $filename = 'pembayaran_pusat_'.now()->format('Y-m-d_His').'.xlsx';

                        return Excel::download(
                            new PembayaranPusatExport($query),
                            $filename,
                            ExcelFormat::XLSX
                        );
                    }),
            ])
            ->recordUrl(fn ($record) => PembayaranPusatResource::getUrl('view', ['record' => $record]))
            ->defaultSort('tanggal_pembayaran', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
