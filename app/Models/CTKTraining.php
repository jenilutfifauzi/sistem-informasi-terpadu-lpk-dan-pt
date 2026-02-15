<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKTraining extends Model
{
    use HasFactory;

    protected $table = 'c_t_k_trainings';

    protected $fillable = [
        'ctk_id',
        'instructor_id',
        'training_start_date',
        'training_end_date',
        'training_location',
        'training_hours',
        'completion_notes',
        'completion_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'training_start_date' => 'date',
            'training_end_date' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
