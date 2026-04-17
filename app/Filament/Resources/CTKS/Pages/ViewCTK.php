<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Enums\DocumentType;
use App\Enums\ScreeningStage;
use App\Filament\Resources\CTKS\CTKResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
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
                // ==================== DATA PRIBADI ====================
                Section::make('Data Pribadi')
                    ->description('Informasi data pribadi Calon Tenaga Kerja')
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('Foto CTK')
                            ->disk('public')
                            ->columnSpanFull(),
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

                // ==================== INFORMASI KONTAK ====================
                Section::make('Informasi Kontak')
                    ->description('Data kontak dan alamat')
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

                Section::make('Semua Dokumen CTK')
                    ->description('Seluruh dokumen dan lampiran yang tersimpan pada proses CTK')
                    ->schema([
                        ViewEntry::make('all_documents_view')
                            ->label('Daftar Semua Dokumen')
                            ->view('filament.infolists.all-ctk-documents', fn ($record): array => [
                                'documents' => $record ? $this->collectAllDocuments($record) : [],
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $this->collectAllDocuments($record) === [])
                    ->columns(1),

                // ==================== STAGE 1: MCU ====================
                Section::make('1. MCU (Medical Check-Up)')
                    ->description(fn ($record) => $this->getStatusBadge($record, 1) ?? 'Rekam hasil pemeriksaan kesehatan untuk calon TKI')
                    ->schema([
                        ViewEntry::make('mcu_records_view')
                            ->label('Riwayat MCU')
                            ->view('filament.infolists.mcu-list', fn ($record): array => [
                                'mcuRecords' => $record?->mcuRecords ?? collect(),
                            ])
                            ->placeholder('Belum ada rekaman MCU')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 2: PEMBAYARAN ====================
                Section::make('2. Pembayaran')
                    ->description(fn ($record) => $this->getStatusBadge($record, 2) ?? 'Rekam pembayaran untuk tahap LPK')
                    ->schema([
                        ViewEntry::make('payments_view')
                            ->label('Rincian Pembayaran')
                            ->view('filament.infolists.payment-list', fn ($record): array => [
                                'payments' => $record?->payments ?? collect(),
                            ])
                            ->placeholder('Belum ada pembayaran')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 3: SOAL/BERKAS ====================
                Section::make('3. Soal/Berkas')
                    ->description(fn ($record) => $this->getStatusBadge($record, 3) ?? 'Upload dokumen soal dan berkas untuk proses CTK')
                    ->schema([
                        ViewEntry::make('soal_berkas_docs')
                            ->label('Daftar Dokumen Soal/Berkas')
                            ->view('filament.infolists.document-gallery', fn ($record): array => [
                                'documents' => $record
                                    ? $record->documents()->where('document_type', DocumentType::SoalBerkas)->with('uploader')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 4: PASPOR ====================
                Section::make('4. Paspor')
                    ->description(fn ($record) => $this->getStatusBadge($record, 4) ?? 'Input nomor paspor dan upload dokumen paspor')
                    ->schema([
                        TextEntry::make('paspor_number')
                            ->label('Nomor Paspor')
                            ->placeholder('Belum diisi'),
                        ViewEntry::make('paspor_docs')
                            ->label('Daftar Dokumen Paspor')
                            ->view('filament.infolists.document-gallery', fn ($record): array => [
                                'documents' => $record
                                    ? $record->documents()->where('document_type', DocumentType::Paspor)->with('uploader')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 5: PELATIHAN ====================
                Section::make('5. Pelatihan di LPK')
                    ->description(fn ($record) => $this->getStatusBadge($record, 5) ?? 'Pencatatan pelatihan CTK di LPK dengan instruktur')
                    ->schema([
                        ViewEntry::make('trainings_view')
                            ->label('Riwayat Pelatihan')
                            ->view('filament.infolists.training-list', fn ($record): array => [
                                'trainings' => $record ? $record->trainings()->with('instructor')->get() : collect(),
                            ])
                            ->placeholder('Belum ada pelatihan')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columns(1),

                // ==================== STAGE 6: SCREENING 1 ====================
                Section::make('6. Screening 1')
                    ->description(fn ($record) => $this->getStatusBadge($record, 6) ?? 'Pencatatan screening tahap 1 di PT')
                    ->schema([
                        ViewEntry::make('screening1_view')
                            ->label('Daftar Screening')
                            ->view('filament.infolists.screening-list', fn ($record): array => [
                                'screenings' => $record
                                    ? $record->screenings()->where('screening_stage', ScreeningStage::Screening1->value)->with('interviewer')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada screening')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 7: INTERVIEW USER ====================
                Section::make('7. Interview User')
                    ->description(fn ($record) => $this->getStatusBadge($record, 7) ?? 'Pencatatan interview user di PT')
                    ->schema([
                        ViewEntry::make('interview_user_view')
                            ->label('Daftar Interview')
                            ->view('filament.infolists.screening-list', fn ($record): array => [
                                'screenings' => $record
                                    ? $record->screenings()->where('screening_stage', ScreeningStage::InterviewUser->value)->with('interviewer')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada interview')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 8: IJIN DESA ====================
                Section::make('8. Ijin Desa')
                    ->description(fn ($record) => $this->getStatusBadge($record, 8) ?? 'Status ijin desa dan upload dokumen')
                    ->schema([
                        TextEntry::make('ijin_desa_status')
                            ->label('Status Ijin Desa')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Ada' => 'success',
                                default => 'gray',
                            }),
                        ViewEntry::make('ijin_desa_docs')
                            ->label('Daftar Dokumen Ijin Desa')
                            ->view('filament.infolists.document-gallery', fn ($record): array => [
                                'documents' => $record
                                    ? $record->documents()->where('document_type', DocumentType::IjinDesa)->with('uploader')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 9: REKOMENDASI ====================
                Section::make('9. Rekomendasi')
                    ->description(fn ($record) => $this->getStatusBadge($record, 9) ?? 'Status rekomendasi dan upload dokumen')
                    ->schema([
                        TextEntry::make('rekomendasi_status')
                            ->label('Status Rekomendasi')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Ada' => 'success',
                                default => 'gray',
                            }),
                        ViewEntry::make('rekomendasi_docs')
                            ->label('Daftar Dokumen Rekomendasi')
                            ->view('filament.infolists.document-gallery', fn ($record): array => [
                                'documents' => $record
                                    ? $record->documents()->where('document_type', DocumentType::Rekomendasi)->with('uploader')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 10: WORKING PERMIT ====================
                Section::make('10. Working Permit')
                    ->description(fn ($record) => $this->getStatusBadge($record, 10) ?? 'Status working permit dan upload dokumen')
                    ->schema([
                        TextEntry::make('wp_status')
                            ->label('Status Working Permit')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Lengkap' => 'success',
                                default => 'gray',
                            }),
                        ViewEntry::make('wp_docs')
                            ->label('Daftar Dokumen Working Permit')
                            ->view('filament.infolists.document-gallery', fn ($record): array => [
                                'documents' => $record
                                    ? $record->documents()->where('document_type', DocumentType::WorkingPermit)->with('uploader')->get()
                                    : collect(),
                            ])
                            ->placeholder('Belum ada dokumen')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 11: APPLY VISA ====================
                Section::make('11. Apply Visa Diajukan')
                    ->description(fn ($record) => $this->getStatusBadge($record, 11) ?? 'Status pengajuan aplikasi visa')
                    ->schema([
                        TextEntry::make('apply_visa_status')
                            ->label('Status Apply Visa')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Diajukan' => 'success',
                                default => 'gray',
                            }),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                // ==================== STAGE 12: MEDICAL FULL ====================
                Section::make('12. Medical Full Examination')
                    ->description(fn ($record) => $this->getStatusBadge($record, 12) ?? 'Pencatatan pemeriksaan kesehatan lengkap')
                    ->schema([
                        ViewEntry::make('medical_full_view')
                            ->label('Daftar Medical Full')
                            ->view('filament.infolists.medical-full-list', fn ($record): array => [
                                'medicals' => $record?->medicalFulls ?? collect(),
                            ])
                            ->placeholder('Belum ada pemeriksaan medical full')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => ! $record || $record->medicalFulls->isEmpty()),

                // ==================== STAGE 13: VISA TERBIT ====================
                Section::make('13. Visa Terbit')
                    ->description(fn ($record) => $this->getStatusBadge($record, 13) ?? 'Pencatatan penerbitan visa')
                    ->schema([
                        ViewEntry::make('visa_view')
                            ->label('Daftar Visa')
                            ->view('filament.infolists.visa-list', fn ($record): array => [
                                'visas' => $record?->visaRecords ?? collect(),
                            ])
                            ->placeholder('Belum ada pengajuan visa')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => ! $record || $record->visaRecords->isEmpty()),

                // ==================== STAGE 14: OPP ====================
                Section::make('14. OPP')
                    ->description(fn ($record) => $this->getStatusBadge($record, 14) ?? 'Pencatatan penerimaan OPP (Offer Placement Paper)')
                    ->schema([
                        TextEntry::make('opp_status')
                            ->label('Status OPP')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Diterima' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('opp_receipt_date')
                            ->label('Tanggal Terima OPP')
                            ->date('d F Y')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('opp_document_path')
                            ->label('Dokumen OPP')
                            ->formatStateUsing(fn ($state) => $state ? basename($state) : 'Tidak ada dokumen')
                            ->url(fn ($record) => $record?->opp_document_path ? route('ctk.opp.download', $record) : null)
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // ==================== STAGE 15: TERBANG BERANGKAT ====================
                Section::make('15. Terbang Berangkat')
                    ->description(fn ($record) => $this->getStatusBadge($record, 15) ?? 'Tanggal keberangkatan CTK')
                    ->schema([
                        TextEntry::make('departure_date')
                            ->label('Tanggal Keberangkatan')
                            ->date('d F Y')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('flight_number')
                            ->label('Nomor Penerbangan')
                            ->placeholder('Belum diisi'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // ==================== RIWAYAT AUDIT ====================
                Section::make('Riwayat Audit')
                    ->description('Catatan aktivitas dan perubahan data CTK')
                    ->schema([
                        TextEntry::make('activity_summary')
                            ->label('Ringkasan Aktivitas')
                            ->state(fn ($record) => \Spatie\Activitylog\Models\Activity::forSubject($record)->count())
                            ->formatStateUsing(function ($state) {
                                $activityCount = $state;

                                return "Total aktivitas tercatat: {$activityCount}";
                            }),

                        ViewEntry::make('activity_list')
                            ->label('Daftar Aktivitas Terbaru')
                            ->view('filament.infolists.activity-log', fn ($record): array => [
                                'activities' => $record
                                    ? \Spatie\Activitylog\Models\Activity::forSubject($record)->orderBy('created_at', 'desc')->take(10)->get()
                                    : collect(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => \Spatie\Activitylog\Models\Activity::forSubject($record)->count() === 0),

                // ==================== METADATA ====================
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

    private function getStatusBadge(?object $record, int $stage): ?string
    {
        if (! $record) {
            return null;
        }

        $stageAttribute = "stage{$stage}_complete";
        $isComplete = $record->{$stageAttribute} ?? false;
        $icon = $isComplete ? '✅' : '⬜';
        $status = $isComplete ? 'Selesai' : 'Belum Selesai';

        return "{$icon} Stage {$stage}: {$status}";
    }

    private function collectAllDocuments(object $record): array
    {
        $documents = [];

        foreach ($record->documents()->with('uploader')->orderBy('upload_timestamp')->get() as $document) {
            $filename = $document->filename ?: basename((string) $document->file_path);

            $documents[] = [
                'title' => $document->document_type?->getLabel() ?? 'Dokumen CTK',
                'source' => 'Dokumen Tahapan',
                'filename' => $filename,
                'path' => $document->file_path,
                'disk' => 'public',
                'uploaded_at' => $document->upload_timestamp?->format('d F Y'),
                'uploader' => $document->uploader?->name,
                'is_image' => $this->isImagePath($filename),
            ];
        }

        foreach ($record->payments()->orderBy('stage_number')->get() as $payment) {
            if (! $payment->payment_proof_path) {
                continue;
            }

            $documents[] = [
                'title' => 'Bukti Pembayaran Tahap '.$payment->stage_number,
                'source' => 'Pembayaran',
                'filename' => basename($payment->payment_proof_path),
                'path' => $payment->payment_proof_path,
                'disk' => 'public',
                'uploaded_at' => $payment->payment_date?->format('d F Y'),
                'uploader' => null,
                'is_image' => $this->isImagePath($payment->payment_proof_path),
            ];
        }

        foreach ($record->medicalFulls()->with('creator')->orderByDesc('examination_date')->get() as $medical) {
            if (! $medical->medical_report_path) {
                continue;
            }

            $documents[] = [
                'title' => 'Medical Full Report',
                'source' => 'Medical Full',
                'filename' => basename($medical->medical_report_path),
                'path' => $medical->medical_report_path,
                'disk' => 'private',
                'private_type' => 'medical',
                'private_id' => $medical->id,
                'uploaded_at' => $medical->examination_date?->format('d F Y'),
                'uploader' => $medical->creator?->name,
                'is_image' => $this->isImagePath($medical->medical_report_path),
            ];
        }

        foreach ($record->visaRecords()->orderByDesc('application_date')->get() as $visa) {
            if (! $visa->visa_document_path) {
                continue;
            }

            $documents[] = [
                'title' => $visa->visa_number ? 'Visa '.$visa->visa_number : 'Dokumen Visa',
                'source' => 'Visa',
                'filename' => basename($visa->visa_document_path),
                'path' => $visa->visa_document_path,
                'disk' => 'private',
                'private_type' => 'visa',
                'private_id' => $visa->id,
                'uploaded_at' => $visa->issuance_date?->format('d F Y') ?? $visa->application_date?->format('d F Y'),
                'uploader' => null,
                'is_image' => $this->isImagePath($visa->visa_document_path),
            ];
        }

        if ($record->opp_document_path) {
            $documents[] = [
                'title' => 'Dokumen OPP',
                'source' => 'OPP',
                'filename' => basename($record->opp_document_path),
                'path' => $record->opp_document_path,
                'disk' => 'private',
                'private_type' => 'opp',
                'private_id' => $record->id,
                'uploaded_at' => $record->opp_receipt_date?->format('d F Y'),
                'uploader' => null,
                'is_image' => $this->isImagePath($record->opp_document_path),
            ];
        }

        return $documents;
    }

    private function isImagePath(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
