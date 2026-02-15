<?php

namespace App\Models;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected static function booted(): void
    {
        // Add global scope for entity isolation
        // Skip scope for Pimpinan role who can see all entities
        // Only apply in web context (not in console/tinker)
        static::addGlobalScope('entity', function (Builder $builder) {
            // Skip in console/artisan commands to prevent data hiding
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $user = Auth::user();

            // Only apply filter if user is authenticated and has entity attribute
            if ($user && isset($user->entity) && ! $user->hasRole('Pimpinan')) {
                $builder->where('entity', $user->entity);
            }
        });
    }

    protected $fillable = [
        'entity',
        'kategori',
        'nomor_inventaris',
        'nama_barang',
        'deskripsi',
        'jumlah',
        'satuan',
        'kondisi',
        'status_assignment',
        'tahun_pembelian',
        'nilai_pembelian',
        'lokasi',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'entity' => EntityType::class,
            'kategori' => AssetCategory::class,
            'kondisi' => AssetCondition::class,
            'status_assignment' => AssetAssignmentStatus::class,
            'jumlah' => 'integer',
            'tahun_pembelian' => 'integer',
            'nilai_pembelian' => 'decimal:2',
        ];
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')
            ->whereNull('return_date')
            ->latestOfMany('assigned_date');
    }

    public function conditionHistories(): HasMany
    {
        return $this->hasMany(AssetConditionHistory::class, 'asset_id')
            ->orderBy('changed_at', 'desc');
    }

    // Scopes
    public function scopeByEntity(Builder $query, EntityType $entity): Builder
    {
        return $query->where('entity', $entity);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status_assignment', AssetAssignmentStatus::Available);
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('status_assignment', AssetAssignmentStatus::Assigned);
    }

    public function scopeInGoodCondition(Builder $query): Builder
    {
        return $query->where('kondisi', AssetCondition::Baik);
    }

    public function scopeNeedsRepair(Builder $query): Builder
    {
        return $query->where('kondisi', AssetCondition::Rusak)
            ->whereHas('conditionHistories', function (Builder $q) {
                $q->where('new_condition', AssetCondition::Rusak->value)
                    ->where('changed_at', '<', now()->subDays(30));
            });
    }

    /**
     * Bypass entity scope to see all assets (for debugging/admin)
     * Usage: Asset::withoutEntityScope()->get()
     */
    public function scopeWithoutEntityScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('entity');
    }

    /**
     * Update asset condition and create history record
     */
    public function updateCondition(AssetCondition $newCondition, string $reason, User $changedBy): void
    {
        $oldCondition = $this->kondisi;

        AssetConditionHistory::create([
            'asset_id' => $this->id,
            'old_condition' => $oldCondition->value,
            'new_condition' => $newCondition->value,
            'reason' => $reason,
            'changed_by' => $changedBy->id,
            'changed_at' => now(),
        ]);

        $this->update(['kondisi' => $newCondition]);
    }

    /**
     * Assign asset to an employee
     */
    public function assignTo(Model $employee, User $assignedBy): AssetAssignment
    {
        // Check if already assigned
        if ($this->status_assignment === AssetAssignmentStatus::Assigned) {
            throw new \Exception('Asset is already assigned. Please return it first.');
        }

        $assignment = AssetAssignment::create([
            'asset_id' => $this->id,
            'assignable_type' => get_class($employee),
            'assignable_id' => $employee->id,
            'assigned_by' => $assignedBy->id,
            'assigned_date' => now()->toDateString(),
        ]);

        $this->update(['status_assignment' => AssetAssignmentStatus::Assigned]);

        return $assignment;
    }

    /**
     * Return asset from assignment
     */
    public function returnFromAssignment(?string $notes = null): void
    {
        $activeAssignment = $this->currentAssignment;

        if (! $activeAssignment) {
            throw new \Exception('No active assignment found for this asset.');
        }

        $activeAssignment->update([
            'return_date' => now()->toDateString(),
            'return_notes' => $notes,
        ]);

        $this->update(['status_assignment' => AssetAssignmentStatus::Available]);
    }
}
