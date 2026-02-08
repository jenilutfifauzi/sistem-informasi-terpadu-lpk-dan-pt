<?php

namespace App\Filament\Resources\CTKS\Actions;

use App\Enums\CTKStatus;
use App\Enums\DocumentType;
use App\Enums\EntityType;
use App\Enums\MCUStatus;
use App\Enums\PaymentStatus;
use App\Models\CTK;
use App\Models\StageTransition;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvanceStageAction
{
    public static function make(): Action
    {
        return Action::make('advanceStage')
            ->label('Lanjutkan ke Tahap Berikutnya')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Lanjutkan ke Tahap Berikutnya?')
            ->modalDescription(fn (CTK $record) => self::getModalDescription($record))
            ->modalSubmitActionLabel('Ya, Lanjutkan')
            ->visible(fn (CTK $record) => self::canAdvance($record))
            ->disabled(fn (CTK $record) => ! self::isEligibleToAdvance($record))
            ->action(function (CTK $record) {
                self::advanceCTK($record);
            });
    }

    protected static function getModalDescription(CTK $record): string
    {
        $currentStage = $record->current_stage;
        $nextStage = $currentStage + 1;
        $nextStatus = self::getNextStatus($nextStage);

        return "CTK akan dipindahkan dari Tahap {$currentStage} ke Tahap {$nextStage} ({$nextStatus->value}).";
    }

    protected static function canAdvance(CTK $record): bool
    {
        // Can only advance if not in final stage (15)
        return $record->current_stage < 15;
    }

    protected static function isEligibleToAdvance(CTK $record): bool
    {
        // Stage 1: Require MCU with FIT status
        if ($record->current_stage === 1) {
            return self::hasFitMCU($record);
        }

        // Stage 2: Require at least one payment marked as "Lunas"
        if ($record->current_stage === 2) {
            return self::hasCompletedPayment($record);
        }

        // Stage 3: Require "Soal Berkas" document
        if ($record->current_stage === 3) {
            return self::hasRequiredDocument($record, DocumentType::SoalBerkas);
        }

        // Stage 4: Require "Paspor" document
        if ($record->current_stage === 4) {
            return self::hasRequiredDocument($record, DocumentType::Paspor);
        }

        // Stage 5: Require completed training
        if ($record->current_stage === 5) {
            return self::hasCompletedTraining($record);
        }

        // Stage 7: Require "Lolos" screening result
        if ($record->current_stage === 7) {
            return self::hasLolosScreening($record);
        }

        // Stage 8: Require "Ijin Desa" document
        if ($record->current_stage === 8) {
            return self::hasRequiredDocument($record, DocumentType::IjinDesa);
        }

        // Stage 9: Require "Rekomendasi" document
        if ($record->current_stage === 9) {
            return self::hasRequiredDocument($record, DocumentType::Rekomendasi);
        }

        // Stage 10: Require "Working Permit" document
        if ($record->current_stage === 10) {
            return self::hasRequiredDocument($record, DocumentType::WorkingPermit);
        }

        // Stage 12: Require Medical Full status "Selesai"
        if ($record->current_stage === 12) {
            return self::hasSelesaiMedicalFull($record);
        }

        // Stage 13: Require visa status is "Terbit"
        if ($record->current_stage === 13) {
            return self::hasTerbitVisa($record);
        }

        // Stage 14: Require OPP received status
        if ($record->current_stage === 14) {
            return self::hasOPPDiterima($record);
        }

        return true;
    }

    protected static function hasOPPDiterima(CTK $record): bool
    {
        return $record->opp_status === 'Diterima' && $record->departure_date !== null;
    }

    protected static function hasFitMCU(CTK $record): bool
    {
        return $record->mcuRecords()
            ->where('status', MCUStatus::FIT)
            ->exists();
    }

    protected static function hasCompletedPayment(CTK $record): bool
    {
        return $record->payments()
            ->where('payment_status', PaymentStatus::Lunas)
            ->exists();
    }

    protected static function hasRequiredDocument(CTK $record, DocumentType $documentType): bool
    {
        return $record->documents()
            ->where('document_type', $documentType)
            ->exists();
    }

    protected static function hasCompletedTraining(CTK $record): bool
    {
        return $record->trainings()
            ->where('completion_status', 'Selesai')
            ->exists();
    }

    protected static function hasLolosScreening(CTK $record): bool
    {
        return $record->screenings()
            ->where('screening_result', 'Lolos')
            ->exists();
    }

    protected static function hasTerbitVisa(CTK $record): bool
    {
        return $record->visaRecords()
            ->where('application_status', 'Terbit')
            ->exists();
    }

    protected static function hasSelesaiMedicalFull(CTK $record): bool
    {
        return $record->medicalFulls()
            ->where('status', 'Selesai')
            ->exists();
    }

    protected static function advanceCTK(CTK $record): void
    {
        $currentStage = $record->current_stage;
        $nextStage = $currentStage + 1;

        // Check eligibility one more time
        if (! self::isEligibleToAdvance($record)) {
            self::sendFailureNotification($record);

            return;
        }

        DB::transaction(function () use ($record, $currentStage, $nextStage) {
            $fromStage = $currentStage;
            $toStage = $nextStage;

            // Determine new status based on stage
            $newStatus = self::getNextStatus($toStage);

            // Determine new entity (LPK for stages 1-5, PT for stages 6-15)
            $newEntity = $toStage <= 5 ? EntityType::LPK : EntityType::PT;

            // Update CTK
            $record->update([
                'current_stage' => $toStage,
                'current_status' => $newStatus,
                'current_entity' => $newEntity,
                'updated_by' => Auth::id(),
            ]);

            // Log stage transition
            StageTransition::create([
                'ctk_id' => $record->id,
                'from_stage' => $fromStage,
                'to_stage' => $toStage,
                'transition_timestamp' => now(),
                'user_id' => Auth::id(),
                'transition_reason' => "Advancement from stage {$fromStage} to {$toStage}",
            ]);
        });

        // Send success notification
        self::sendSuccessNotification($record, $nextStage, self::getNextStatus($nextStage));
    }

    protected static function getNextStatus(int $stage): CTKStatus
    {
        return match ($stage) {
            1 => CTKStatus::MCU,
            2 => CTKStatus::Pembayaran,
            3 => CTKStatus::SoalBerkas,
            4 => CTKStatus::Paspor,
            5 => CTKStatus::BelajarDiLPK,
            6 => CTKStatus::Screening1,
            7 => CTKStatus::InterviewUser,
            8 => CTKStatus::IjinDesa,
            9 => CTKStatus::Rekomendasi,
            10 => CTKStatus::WP,
            11 => CTKStatus::ApplyVisa,
            12 => CTKStatus::MedicalFull,
            13 => CTKStatus::Visa,
            14 => CTKStatus::OPP,
            15 => CTKStatus::Terbang,
            default => CTKStatus::MCU,
        };
    }

    protected static function sendSuccessNotification(CTK $record, int $nextStage, CTKStatus $nextStatus): void
    {
        Notification::make()
            ->success()
            ->title('CTK Berhasil Dilanjutkan')
            ->body("CTK {$record->nama_lengkap} berhasil dilanjutkan ke Tahap {$nextStage} ({$nextStatus->value})")
            ->send();
    }

    protected static function sendFailureNotification(CTK $record): void
    {
        $currentStage = $record->current_stage;

        $message = match ($currentStage) {
            1 => 'Status MCU harus FIT untuk melanjutkan ke tahap pembayaran.',
            2 => 'Minimal 1 pembayaran harus berstatus Lunas untuk melanjutkan ke tahap berikutnya.',
            3 => 'Dokumen "Soal Berkas" harus diupload untuk melanjutkan ke tahap berikutnya.',
            4 => 'Dokumen "Paspor" harus diupload untuk melanjutkan ke tahap berikutnya.',
            5 => 'Minimal 1 pelatihan harus berstatus Selesai untuk melanjutkan ke tahap berikutnya.',
            7 => 'Minimal 1 screening harus berstatus Lolos untuk melanjutkan ke tahap berikutnya.',
            8 => 'Dokumen "Ijin Desa" harus diupload untuk melanjutkan ke tahap berikutnya.',
            9 => 'Dokumen "Rekomendasi" harus diupload untuk melanjutkan ke tahap berikutnya.',
            10 => 'Dokumen "Working Permit" harus diupload untuk melanjutkan ke tahap berikutnya.',
            12 => 'Medical Full harus berstatus Selesai untuk melanjutkan ke tahap berikutnya.',
            13 => 'Visa harus berstatus Terbit untuk melanjutkan ke tahap OPP.',
            14 => 'Dokumen "OPP Document" harus diupload untuk melanjutkan ke tahap berikutnya.',
            default => 'CTK belum memenuhi syarat untuk melanjutkan ke tahap berikutnya.',
        };

        Notification::make()
            ->danger()
            ->title('Tidak Dapat Melanjutkan Tahap')
            ->body($message)
            ->send();
    }
}
