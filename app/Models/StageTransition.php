<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageTransition extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ctk_id',
        'from_stage',
        'to_stage',
        'transition_timestamp',
        'user_id',
        'transition_reason',
        'approval_id',
    ];

    protected function casts(): array
    {
        return [
            'from_stage' => 'integer',
            'to_stage' => 'integer',
            'transition_timestamp' => 'datetime',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_id');
    }
}
