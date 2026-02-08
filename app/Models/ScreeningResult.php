<?php

namespace App\Models;

use App\Enums\ScreeningResult as ScreeningResultEnum;
use App\Enums\ScreeningStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctk_id',
        'stage_name',
        'result',
        'screening_date',
        'screener_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stage_name' => ScreeningStage::class,
            'result' => ScreeningResultEnum::class,
            'screening_date' => 'date',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function screener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screener_id');
    }
}
