<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
        'assigned_date',
        'return_date',
        'return_notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'return_date' => 'date',
        ];
    }

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('return_date');
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->whereNotNull('return_date');
    }

    public function scopeForEntity(Builder $query, string $entity): Builder
    {
        return $query->whereHas('asset', function (Builder $q) use ($entity) {
            $q->where('entity', $entity);
        });
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return is_null($this->return_date);
    }

    public function getDurationDaysAttribute(): ?int
    {
        if ($this->return_date) {
            return $this->assigned_date->diffInDays($this->return_date);
        }

        return $this->assigned_date->diffInDays(now());
    }
}
