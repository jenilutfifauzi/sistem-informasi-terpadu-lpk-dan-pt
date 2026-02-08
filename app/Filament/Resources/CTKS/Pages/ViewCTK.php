<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Filament\Resources\CTKS\Actions\AdvanceStageAction;
use App\Filament\Resources\CTKS\CTKResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewCTK extends ViewRecord
{
    protected static string $resource = CTKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AdvanceStageAction::make(),
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->schema([
                        TextEntry::make('nik')
                            ->label('NIK')
                            ->copyable(),
                        TextEntry::make('nama_lengkap')
                            ->label('Nama Lengkap'),
                        TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d F Y'),
                        TextEntry::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->badge(),
                    ])
                    ->columns(2),
                Section::make('Informasi Kontak')
                    ->schema([
                        TextEntry::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        TextEntry::make('no_telepon')
                            ->label('No. Telepon')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('Tidak ada email'),
                    ])
                    ->columns(2),
                Section::make('Status CTK')
                    ->schema([
                        TextEntry::make('current_status')
                            ->label('Status Saat Ini')
                            ->badge()
                            ->color(fn ($state) => match ($state?->value ?? $state) {
                                'MCU' => 'gray',
                                'Pembayaran' => 'warning',
                                'Soal/Berkas', 'Paspor' => 'info',
                                'Belajar di LPK' => 'primary',
                                'Screening 1', 'Interview User' => 'purple',
                                'Ijin Desa', 'Rekomendasi', 'WP', 'Apply Visa' => 'indigo',
                                'Medical Full' => 'cyan',
                                'Visa', 'OPP' => 'lime',
                                'Terbang' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('current_stage')
                            ->label('Tahap')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state) => "Stage {$state}"),
                        TextEntry::make('current_entity')
                            ->label('Entitas')
                            ->badge()
                            ->color(fn ($state) => match ($state?->value ?? $state) {
                                'LPK' => 'info',
                                'PT' => 'warning',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),
                Section::make('Riwayat MCU (Medical Check-Up)')
                    ->description('Rekaman pemeriksaan kesehatan yang telah dilakukan')
                    ->schema([
                        TextEntry::make('mcuRecords')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->formatStateUsing(function ($record) {
                                $mcuRecords = $record->mcuRecords;

                                if ($mcuRecords->isEmpty()) {
                                    return 'Belum ada rekaman MCU';
                                }

                                return $mcuRecords->map(function ($mcu) {
                                    $status = $mcu->status->value ?? 'N/A';
                                    $date = $mcu->examination_date?->format('d F Y') ?? 'N/A';
                                    $clinic = $mcu->clinic_name ?? 'N/A';
                                    $examiner = $mcu->examiner_name ?? 'N/A';
                                    $notes = $mcu->notes ? " - {$mcu->notes}" : '';

                                    return "Status: {$status} | Tanggal: {$date} | Klinik: {$clinic} | Pemeriksa: {$examiner}{$notes}";
                                })->join("\n");
                            })
                            ->placeholder('Belum ada rekaman MCU'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->mcuRecords->isEmpty()),
                Section::make('Riwayat Pembayaran')
                    ->description('Rekaman pembayaran yang telah dilakukan')
                    ->schema([
                        TextEntry::make('payments')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->formatStateUsing(function ($record) {
                                $payments = $record->payments;

                                if ($payments->isEmpty()) {
                                    return 'Belum ada pembayaran';
                                }

                                $totalPaid = $payments->where('payment_status', \App\Enums\PaymentStatus::Lunas)->count();
                                $totalAmount = $payments->where('payment_status', \App\Enums\PaymentStatus::Lunas)->sum('amount');

                                $summary = "Total: {$totalPaid}/5 pembayaran lunas (Rp ".number_format($totalAmount, 0, ',', '.').')';

                                $list = $payments->map(function ($payment) {
                                    $stage = $payment->stage_number ?? 'N/A';
                                    $amount = 'Rp '.number_format($payment->amount ?? 0, 0, ',', '.');
                                    $bank = $payment->bank_name ?? 'N/A';
                                    $date = $payment->payment_date?->format('d F Y') ?? 'N/A';
                                    $status = $payment->payment_status?->value ?? 'N/A';

                                    return "Tahap {$stage}: {$amount} | Bank: {$bank} | Tanggal: {$date} | Status: {$status}";
                                })->join("\n");

                                return $summary."\n".$list;
                            })
                            ->placeholder('Belum ada pembayaran'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->payments->isEmpty()),
                Section::make('Riwayat Dokumen')
                    ->description('Dokumen-dokumen yang telah diupload')
                    ->schema([
                        TextEntry::make('documents')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->formatStateUsing(function ($record) {
                                $documents = $record->documents;

                                if ($documents->isEmpty()) {
                                    return 'Belum ada dokumen';
                                }

                                $summary = 'Total: '.$documents->count().'/8 dokumen terupload';

                                $list = $documents->map(function ($doc) {
                                    $type = $doc->document_type?->getLabel() ?? 'N/A';
                                    $filename = $doc->filename ?? 'N/A';
                                    $date = $doc->upload_timestamp?->format('d F Y') ?? 'N/A';
                                    $uploader = $doc->uploader?->name ?? 'N/A';

                                    return "{$type}: {$filename} | Upload: {$date} | Oleh: {$uploader}";
                                })->join("\n");

                                return $summary."\n".$list;
                            })
                            ->placeholder('Belum ada dokumen'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->documents->isEmpty()),
                Section::make('Riwayat Pelatihan')
                    ->description('Pelatihan yang telah dilakukan di LPK')
                    ->schema([
                        TextEntry::make('trainings')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->formatStateUsing(function ($record) {
                                $trainings = $record->trainings;

                                if ($trainings->isEmpty()) {
                                    return 'Belum ada pelatihan';
                                }

                                $summary = 'Total: '.$trainings->count().' pelatihan | Jam: '.$trainings->sum('training_hours').' jam';

                                $list = $trainings->map(function ($training) {
                                    $instructor = $training->instructor?->nama_lengkap ?? 'N/A';
                                    $startDate = $training->training_start_date?->format('d/m/Y') ?? 'N/A';
                                    $endDate = $training->training_end_date?->format('d/m/Y') ?? '-';
                                    $location = $training->training_location ?? 'N/A';
                                    $hours = $training->training_hours ?? 0;
                                    $status = $training->completion_status ?? 'N/A';

                                    return "Instruktur: {$instructor} | {$startDate} - {$endDate} | {$location} | {$hours} jam | Status: {$status}";
                                })->join("\n");

                                return $summary."\n".$list;
                            })
                            ->placeholder('Belum ada pelatihan'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->trainings->isEmpty()),
                Section::make('Riwayat Screening')
                    ->schema([
                        TextEntry::make('screening_summary')
                            ->label('Ringkasan Screening')
                            ->formatStateUsing(function ($record) {
                                $screenings = $record->screenings;
                                $total = $screenings->count();
                                $lolos = $screenings->where('screening_result', 'Lolos')->count();

                                if ($total === 0) {
                                    return 'Belum ada screening';
                                }

                                return "Total: {$total} screening | Lolos: {$lolos}";
                            }),

                        TextEntry::make('screening_list')
                            ->label('Daftar Screening')
                            ->formatStateUsing(function ($record) {
                                $screenings = $record->screenings()->with('interviewer')->get();

                                if ($screenings->isEmpty()) {
                                    return 'Belum ada screening';
                                }

                                return view('filament.infolists.screening-list', [
                                    'screenings' => $screenings,
                                ]);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->screenings->isEmpty()),

                Section::make('Riwayat Visa')
                    ->schema([
                        TextEntry::make('visa_summary')
                            ->label('Ringkasan Visa')
                            ->formatStateUsing(function ($record) {
                                $visas = $record->visaRecords;
                                $total = $visas->count();
                                $terbit = $visas->where('application_status', 'Terbit')->count();

                                if ($total === 0) {
                                    return 'Belum ada pengajuan visa';
                                }

                                return "Total: {$total} visa | Terbit: {$terbit}";
                            }),

                        TextEntry::make('visa_list')
                            ->label('Daftar Visa')
                            ->formatStateUsing(function ($record) {
                                $visas = $record->visaRecords;

                                if ($visas->isEmpty()) {
                                    return 'Belum ada pengajuan visa';
                                }

                                return view('filament.infolists.visa-list', [
                                    'visas' => $visas,
                                ]);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->visaRecords->isEmpty()),

                Section::make('Riwayat Medical Full')
                    ->schema([
                        TextEntry::make('medical_full_summary')
                            ->label('Ringkasan Medical Full')
                            ->formatStateUsing(function ($record) {
                                $medicals = $record->medicalFulls;
                                $total = $medicals->count();
                                $selesai = $medicals->where('status', 'Selesai')->count();
                                $needsRenewal = $medicals->filter(fn ($m) => $m->isExpiringSoon())->count();

                                if ($total === 0) {
                                    return 'Belum ada pemeriksaan medical full';
                                }

                                $summary = "Total: {$total} pemeriksaan | Selesai: {$selesai}";
                                if ($needsRenewal > 0) {
                                    $summary .= " | ⚠️ Perlu Perpanjangan: {$needsRenewal}";
                                }

                                return $summary;
                            }),

                        TextEntry::make('medical_full_list')
                            ->label('Daftar Medical Full')
                            ->formatStateUsing(function ($record) {
                                $medicals = $record->medicalFulls;

                                if ($medicals->isEmpty()) {
                                    return 'Belum ada pemeriksaan medical full';
                                }

                                return view('filament.infolists.medical-full-list', [
                                    'medicals' => $medicals,
                                ]);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->medicalFulls->isEmpty()),

                Section::make('OPP & Keberangkatan')
                    ->schema([
                        TextEntry::make('opp_status')
                            ->label('Status OPP')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Belum' => 'gray',
                                'Diterima' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('opp_receipt_date')
                            ->label('Tanggal Penerimaan OPP')
                            ->date('d F Y')
                            ->visible(fn ($record) => $record->opp_status === 'Diterima'),
                        TextEntry::make('opp_document_path')
                            ->label('Dokumen OPP')
                            ->visible(fn ($record) => $record->opp_status === 'Diterima')
                            ->formatStateUsing(fn ($state) => $state ? 'Dokumen tersimpan' : 'Tidak ada dokumen'),
                        TextEntry::make('departure_date')
                            ->label('Tanggal Keberangkatan')
                            ->date('d F Y')
                            ->visible(fn ($record) => $record->opp_status === 'Diterima'),
                        TextEntry::make('flight_number')
                            ->label('Nomor Penerbangan')
                            ->visible(fn ($record) => $record->opp_status === 'Diterima' && $record->flight_number),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->opp_status === 'Belum'),

                Section::make('Riwayat Audit')
                    ->description('Catatan aktivitas dan perubahan data CTK')
                    ->schema([
                        TextEntry::make('activity_summary')
                            ->label('Ringkasan Aktivitas')
                            ->formatStateUsing(function ($record) {
                                $activityCount = \Spatie\Activitylog\Models\Activity::forSubject($record)->count();

                                return "Total aktivitas tercatat: {$activityCount}";
                            }),

                        TextEntry::make('activity_list')
                            ->label('Daftar Aktivitas Terbaru')
                            ->formatStateUsing(function ($record) {
                                $activities = \Spatie\Activitylog\Models\Activity::forSubject($record)
                                    ->orderBy('created_at', 'desc')
                                    ->take(10)
                                    ->get();

                                if ($activities->isEmpty()) {
                                    return 'Tidak ada aktivitas tercatat';
                                }

                                return view('filament.infolists.activity-log', [
                                    'activities' => $activities,
                                ]);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => \Spatie\Activitylog\Models\Activity::forSubject($record)->count() === 0),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Dibuat Oleh'),
                        TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime('d F Y H:i'),
                        TextEntry::make('updater.name')
                            ->label('Diperbarui Oleh'),
                        TextEntry::make('updated_at')
                            ->label('Tanggal Diperbarui')
                            ->dateTime('d F Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
