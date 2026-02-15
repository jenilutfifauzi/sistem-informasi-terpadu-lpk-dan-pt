<?php

namespace App\Models;

use App\Enums\ScreeningStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKScreening extends Model
{
    use HasFactory;

    protected $table = 'c_t_k_screenings';

    protected $fillable = [
        'ctk_id',
        'interviewer_id',
        'interview_date',
        'interview_location',
        'screening_result',
        'screening_stage',
        'interview_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'date',
            'screening_stage' => ScreeningStage::class,
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
