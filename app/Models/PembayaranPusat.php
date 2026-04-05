<?php

namespace App\Models;

use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PembayaranPusat extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'pembayaran_pusat';

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
        'ctk_id',
        'tanggal_pembayaran',
        'nominal',
        'bukti_transfer_path',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'entity' => EntityType::class,
            'tanggal_pembayaran' => 'date',
            'nominal' => 'decimal:2',
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
    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
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
    public function scopeByEntity(Builder $query, EntityType $entity): Builder
    {
        return $query->where('entity', $entity);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('tanggal_pembayaran', now()->month)
            ->whereYear('tanggal_pembayaran', now()->year);
    }

    /**
     * Bypass entity scope to see all records (for debugging/admin)
     * Usage: PembayaranPusat::withoutEntityScope()->get()
     */
    public function scopeWithoutEntityScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('entity');
    }
}
