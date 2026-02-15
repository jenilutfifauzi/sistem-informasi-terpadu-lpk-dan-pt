<?php

namespace App\Filament\Resources\CTKS\RelationManagers;

use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Riwayat Pembayaran';

    protected static ?string $recordTitleAttribute = 'stage_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stage_number')
                    ->label('Tahap Pembayaran')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),

                TextInput::make('amount')
                    ->label('Jumlah Pembayaran')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),

                TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->required()
                    ->maxDate(now())
                    ->native(false)
                    ->displayFormat('d F Y'),

                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(PaymentStatus::class)
                    ->required()
                    ->default(PaymentStatus::Pending),

                FileUpload::make('payment_proof_path')
                    ->label('Bukti Pembayaran')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('ctk-payments')
                    ->nullable()
                    ->downloadable()
                    ->openable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stage_number')
            ->columns([
                TextColumn::make('stage_number')
                    ->label('Tahap')
                    ->badge()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable(),

                TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        PaymentStatus::Lunas => 'success',
                        PaymentStatus::Pending => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_proof_path')
                    ->label('Bukti')
                    ->formatStateUsing(fn ($state) => $state ? '✓ Ada' : '✗ Belum')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('stage_number', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();

                        return $data;
                    })
                    ->after(function ($record) {
                        if ($record->payment_proof_path) {
                            Notification::make()
                                ->title("Bukti pembayaran berhasil diunggah untuk tahap {$record->stage_number}")
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function ($record) {
                        if ($record->wasChanged('payment_proof_path') && $record->payment_proof_path) {
                            Notification::make()
                                ->title("Bukti pembayaran berhasil diperbarui untuk tahap {$record->stage_number}")
                                ->success()
                                ->send();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
