<?php

namespace App\Filament\Resources\CTKS\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class PaymentSection
{
    public static function make(): Section
    {
        return Section::make('Pembayaran')
            ->description('Rekam pembayaran untuk 5 tahap (Stage 1-5)')
            ->schema([
                Placeholder::make('payment_summary')
                    ->label('Ringkasan Pembayaran')
                    ->content(function ($get, $record) {
                        if (! $record || ! $record->exists) {
                            return new HtmlString('<span class="text-gray-500">Belum ada pembayaran yang direkam</span>');
                        }

                        $payments = $record->payments ?? collect();
                        $totalPaid = $payments->where('payment_status', PaymentStatus::Lunas)->count();
                        $totalRequired = 5;
                        $totalAmount = $payments->where('payment_status', PaymentStatus::Lunas)->sum('amount');

                        $percentage = $totalRequired > 0 ? round(($totalPaid / $totalRequired) * 100) : 0;
                        $color = $percentage >= 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');

                        return new HtmlString("
                            <div class='space-y-2'>
                                <div class='flex items-center gap-2'>
                                    <span class='font-semibold'>Progress:</span>
                                    <span class='text-{$color}-600'>{$totalPaid}/{$totalRequired} pembayaran lunas ({$percentage}%)</span>
                                </div>
                                <div class='flex items-center gap-2'>
                                    <span class='font-semibold'>Total Dibayar:</span>
                                    <span class='text-success-600'>Rp ".number_format($totalAmount, 0, ',', '.').'</span>
                                </div>
                            </div>
                        ');
                    }),

                Repeater::make('payments')
                    ->relationship('payments')
                    ->label('Rincian Pembayaran')
                    ->schema([
                        TextInput::make('stage_number')
                            ->label('Tahap Pembayaran')
                            ->numeric()
                            ->required()
                            ->default(fn ($get) => count($get('../../payments') ?? []) + 1)
                            ->minValue(1)
                            ->maxValue(5)
                            ->helperText('Tahap pembayaran (1-5)'),

                        TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->helperText('Nominal pembayaran dalam Rupiah')
                            ->placeholder('5000000'),

                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Bank tujuan transfer (contoh: BCA, Mandiri, BRI)')
                            ->placeholder('BCA'),

                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->maxDate(now())
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->helperText('Tanggal saat pembayaran dilakukan'),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                PaymentStatus::Pending->value => 'Pending - Menunggu Konfirmasi',
                                PaymentStatus::Lunas->value => 'Lunas - Pembayaran Diterima',
                            ])
                            ->required()
                            ->default(PaymentStatus::Pending->value)
                            ->live()
                            ->helperText('Status konfirmasi pembayaran'),

                        FileUpload::make('payment_proof_path')
                            ->label('Bukti Pembayaran')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('ctk-payments')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->nullable()
                            ->helperText('Upload bukti transfer (PDF/JPG/PNG, max 10MB)'),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Pembayaran')
                    ->collapsible()
                    ->collapsed(false)
                    ->itemLabel(fn (array $state): ?string => isset($state['stage_number'])
                        ? 'Pembayaran Tahap '.$state['stage_number'].' - '.($state['payment_status'] ?? 'Pending')
                        : 'Pembayaran Baru')
                    ->reorderable(false)
                    ->deletable(true)
                    ->cloneable(false)
                    ->columns(2),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->columns(1);
    }
}
