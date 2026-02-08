<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctk_id',
        'instructor_id',
        'start_date',
        'completion_date',
        'training_status',
        'training_location',
        'training_hours',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'completion_date' => 'date',
            'training_hours' => 'integer',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(EmployeeLPK::class, 'instructor_id');
    }
}
