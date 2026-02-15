<?php

namespace App\Filament\Resources\CTKS\Pages;

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

                // Workflow Stage Tracking - Visual Progress
                Section::make('📋 Progress Tahapan CTK')
                    ->description(fn ($record) => "Progress keseluruhan: {$record->completion_progress} ({$record->completion_percentage}%) - Centang otomatis saat data/dokumen diisi")
                    ->schema([
                        TextEntry::make('nik')
                            ->label('')
                            ->state(fn ($record) => $record->nik) // Dummy state to get record
                            ->formatStateUsing(function ($state, $record) {
                                $stages = [
                                    1 => ['name' => 'MCU', 'details' => 'Status: FIT'],
                                    2 => ['name' => 'Pembayaran', 'details' => $record->payment_progress.' payments complete'],
                                    3 => ['name' => 'Soal / Berkas', 'details' => 'Upload / Lengkap'],
                                    4 => ['name' => 'Paspor', 'details' => 'No: '.($record->paspor_number ?? '...')],
                                    5 => ['name' => 'Belajar di LPK', 'details' => 'Selesai'],
                                    6 => ['name' => 'Screening 1', 'details' => 'Lolos'],
                                    7 => ['name' => 'Interview User', 'details' => 'Lolos'],
                                    8 => ['name' => 'Ijin Desa', 'details' => 'Ada'],
                                    9 => ['name' => 'Rekom', 'details' => 'Ada'],
                                    10 => ['name' => 'WP', 'details' => 'Lengkap'],
                                    11 => ['name' => 'Apply Visa', 'details' => 'Diajukan'],
                                    12 => ['name' => 'Medical Full', 'details' => 'Selesai'],
                                    13 => ['name' => 'Visa', 'details' => 'Terbit'],
                                    14 => ['name' => 'OPP', 'details' => 'Diterima'],
                                    15 => ['name' => 'Terbang', 'details' => 'Berangkat'],
                                ];

                                $html = '<div class="grid grid-cols-1 md:grid-cols-3 gap-3">';

                                foreach ($stages as $stageNum => $stageInfo) {
                                    $isComplete = $record->{"stage{$stageNum}_complete"};
                                    $checkbox = $isComplete ? '✅' : '⬜';
                                    $textColor = $isComplete ? 'text-success-600 font-semibold' : 'text-gray-500';
                                    $bgColor = $isComplete ? 'bg-success-50 border-success-200' : 'bg-gray-50 border-gray-200';

                                    $html .= <<<HTML
                                        <div class="flex items-start gap-2 p-3 rounded-lg border {$bgColor}">
                                            <span class="text-2xl">{$checkbox}</span>
                                            <div class="flex-1">
                                                <div class="{$textColor} font-medium">
                                                    {$stageNum}. {$stageInfo['name']}
                                                </div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {$stageInfo['details']}
                                                </div>
                                            </div>
                                        </div>
                                    HTML;
                                }

                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

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

                                $summary = 'Total: '.$documents->count().'/1 dokumen terupload';

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
