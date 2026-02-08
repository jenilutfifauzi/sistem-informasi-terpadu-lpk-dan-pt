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
}
