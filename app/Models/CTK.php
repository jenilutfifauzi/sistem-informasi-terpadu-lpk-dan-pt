<?php

namespace App\Models;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CTK extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'ctk';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon',
        'email',
        'current_status',
        'current_stage',
        'current_entity',
        'soal_berkas_status',
        'paspor_number',
        'ijin_desa_status',
        'rekomendasi_status',
        'wp_status',
        'apply_visa_status',
        'opp_status',
        'opp_receipt_date',
        'opp_document_path',
        'departure_date',
        'flight_number',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'current_status' => CTKStatus::class,
            'current_entity' => EntityType::class,
            'tanggal_lahir' => 'date',
            'opp_receipt_date' => 'date',
            'departure_date' => 'date',
            'current_stage' => 'integer',
        ];
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nik', 'nama_lengkap', 'current_status', 'current_stage', 'current_entity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function mcuRecords(): HasMany
    {
        return $this->hasMany(MCURecord::class, 'ctk_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CTKPayment::class, 'ctk_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CTKDocument::class, 'ctk_id');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(CTKTraining::class, 'ctk_id');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(CTKScreening::class, 'ctk_id');
    }

    public function medicalFulls(): HasMany
    {
        return $this->hasMany(CTKMedicalFull::class, 'ctk_id');
    }

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class, 'ctk_id');
    }

    public function screeningResults(): HasMany
    {
        return $this->hasMany(ScreeningResult::class, 'ctk_id');
    }

    public function visaRecords(): HasMany
    {
        return $this->hasMany(VisaRecord::class, 'ctk_id');
    }

    public function stageTransitions(): HasMany
    {
        return $this->hasMany(StageTransition::class, 'ctk_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CTKNote::class, 'ctk_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByEntity($query, EntityType $entity)
    {
        return $query->where('current_entity', $entity);
    }

    public function scopeInLPKStages($query)
    {
        return $query->whereBetween('current_stage', [1, 5]);
    }

    public function scopeInPTStages($query)
    {
        return $query->whereBetween('current_stage', [6, 15]);
    }

    public function scopeSearchByName($query, string $search)
    {
        return $query->where('nama_lengkap', 'like', "%{$search}%");
    }

    public function scopeSearchByNIK($query, string $search)
    {
        return $query->where('nik', 'like', "%{$search}%");
    }

    // Accessors
    public function getIsInLPKStagesAttribute(): bool
    {
        return $this->current_stage >= 1 && $this->current_stage <= 5;
    }

    public function getIsInPTStagesAttribute(): bool
    {
        return $this->current_stage >= 6 && $this->current_stage <= 15;
    }

    public function getCanAdvanceToNextStageAttribute(): bool
    {
        // Basic check - will be enhanced with specific gate logic
        return $this->current_stage < 15;
    }

    /**
     * Validate if CTK can advance to a target stage.
     *
     * @param  int  $targetStage  The stage number to advance to (1-15)
     * @return bool True if advancement is allowed, false otherwise
     */
    public function canAdvanceToStage(int $targetStage): bool
    {
        // Cannot advance to invalid stage numbers
        if ($targetStage < 1 || $targetStage > 15) {
            return false;
        }

        // Cannot modify stage 15 (Terbang) - it's immutable
        if ($this->current_stage === 15) {
            return false;
        }

        // Cannot go backward
        if ($targetStage < $this->current_stage) {
            return false;
        }

        // Can stay at same stage (for updates to stage-specific data)
        if ($targetStage === $this->current_stage) {
            return true;
        }

        // Cannot skip stages - must advance sequentially
        if ($targetStage > $this->current_stage + 1) {
            return false;
        }

        // Check if current stage is complete before advancing
        $currentStageAttribute = "stage{$this->current_stage}_complete";
        if (! $this->$currentStageAttribute) {
            return false;
        }

        return true;
    }

    public function getPaymentCompletionStatusAttribute(): string
    {
        $payments = $this->payments;
        if ($payments->isEmpty()) {
            return 'none';
        }

        $lunas = $payments->where('payment_status', \App\Enums\PaymentStatus::Lunas)->count();
        if ($lunas >= 5) {
            return 'complete';
        }

        return 'partial';
    }

    // ==================== Stage Completion Tracking ====================
    // Based on alur_ctk.md template and FR-037

    /**
     * Stage 1: MCU - Complete when status = FIT
     */
    public function getStage1CompleteAttribute(): bool
    {
        return $this->mcuRecords()
            ->where('status', \App\Enums\MCUStatus::FIT)
            ->exists();
    }

    /**
     * Stage 2: Pembayaran - Complete when all 5 payments have proof uploaded and status = Lunas
     */
    public function getStage2CompleteAttribute(): bool
    {
        $payments = $this->payments()
            ->where('payment_status', \App\Enums\PaymentStatus::Lunas)
            ->whereNotNull('payment_proof_path')
            ->get();

        return $payments->count() >= 5;
    }

    /**
     * Get payment progress (e.g., "3/5")
     */
    public function getPaymentProgressAttribute(): string
    {
        $completed = $this->payments()
            ->where('payment_status', \App\Enums\PaymentStatus::Lunas)
            ->whereNotNull('payment_proof_path')
            ->count();

        return "{$completed}/5";
    }

    /**
     * Stage 3: Soal/Berkas - Complete when at least 1 document uploaded (excluding Paspor)
     */
    public function getStage3CompleteAttribute(): bool
    {
        return $this->documents()
            ->where('document_type', '!=', \App\Enums\DocumentType::Paspor)
            ->exists();
    }

    /**
     * Stage 4: Paspor - Complete when paspor_number is filled AND paspor document uploaded
     */
    public function getStage4CompleteAttribute(): bool
    {
        return ! empty($this->paspor_number) &&
            $this->documents()
                ->where('document_type', \App\Enums\DocumentType::Paspor)
                ->exists();
    }

    /**
     * Stage 5: Belajar di LPK - Complete when training_status = Selesai
     */
    public function getStage5CompleteAttribute(): bool
    {
        return $this->trainings()
            ->where('completion_status', 'Selesai')
            ->exists();
    }

    /**
     * Stage 6: Screening 1 - Complete when result = Lolos
     */
    public function getStage6CompleteAttribute(): bool
    {
        return $this->screenings()
            ->where('screening_stage', 'Screening 1')
            ->where('screening_result', 'Lolos')
            ->exists();
    }

    /**
     * Stage 7: Interview User - Complete when result = Lolos
     */
    public function getStage7CompleteAttribute(): bool
    {
        return $this->screenings()
            ->where('screening_stage', 'Interview User')
            ->where('screening_result', 'Lolos')
            ->exists();
    }

    /**
     * Stage 8: Ijin Desa - Complete when document uploaded AND status = Ada
     */
    public function getStage8CompleteAttribute(): bool
    {
        return $this->ijin_desa_status === 'Ada' &&
            $this->documents()
                ->where('document_type', \App\Enums\DocumentType::IjinDesa)
                ->exists();
    }

    /**
     * Stage 9: Rekomendasi - Complete when document uploaded AND status = Ada
     */
    public function getStage9CompleteAttribute(): bool
    {
        return $this->rekomendasi_status === 'Ada' &&
            $this->documents()
                ->where('document_type', \App\Enums\DocumentType::Rekomendasi)
                ->exists();
    }

    /**
     * Stage 10: WP - Complete when status = Lengkap
     */
    public function getStage10CompleteAttribute(): bool
    {
        return $this->wp_status === 'Lengkap';
    }

    /**
     * Stage 11: Apply Visa - Complete when status = Diajukan
     */
    public function getStage11CompleteAttribute(): bool
    {
        return $this->apply_visa_status === 'Diajukan';
    }

    /**
     * Stage 12: Medical Full - Complete when status = Selesai
     */
    public function getStage12CompleteAttribute(): bool
    {
        return $this->medicalFulls()
            ->where('status', 'Selesai')
            ->exists();
    }

    /**
     * Stage 13: Visa - Complete when status = Terbit AND visa_number filled
     */
    public function getStage13CompleteAttribute(): bool
    {
        return $this->visaRecords()
            ->where('application_status', 'Terbit')
            ->whereNotNull('visa_number')
            ->exists();
    }

    /**
     * Stage 14: OPP - Complete when status = Diterima AND receipt_date filled
     */
    public function getStage14CompleteAttribute(): bool
    {
        return $this->opp_status === 'Diterima' && ! empty($this->opp_receipt_date);
    }

    /**
     * Stage 15: Terbang - Complete when departure_date filled
     */
    public function getStage15CompleteAttribute(): bool
    {
        return ! empty($this->departure_date);
    }

    /**
     * Get total number of completed stages (X out of 15)
     */
    public function getCompletedStagesCountAttribute(): int
    {
        $count = 0;

        for ($i = 1; $i <= 15; $i++) {
            $attribute = "stage{$i}_complete";
            if ($this->$attribute) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get completion progress as string (e.g., "8/15")
     */
    public function getCompletionProgressAttribute(): string
    {
        return "{$this->completed_stages_count}/15";
    }

    /**
     * Get completion percentage (0-100)
     */
    public function getCompletionPercentageAttribute(): int
    {
        return (int) round(($this->completed_stages_count / 15) * 100);
    }

    /**
     * Get array of all stage completion statuses
     */
    public function getStageCompletionsAttribute(): array
    {
        $stages = [];

        for ($i = 1; $i <= 15; $i++) {
            $attribute = "stage{$i}_complete";
            $stages[$i] = [
                'stage_number' => $i,
                'complete' => $this->$attribute,
                'checkbox' => $this->$attribute ? '[x]' : '[ ]',
            ];
        }

        return $stages;
    }
}
