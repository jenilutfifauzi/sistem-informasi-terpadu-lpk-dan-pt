<?php

namespace App\Models;

use App\Enums\MCUStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MCURecord extends Model
{
    use HasFactory;

    protected $table = 'mcu_records';

    protected $fillable = [
        'ctk_id',
        'status',
        'examination_date',
        'clinic_name',
        'examiner_name',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => MCUStatus::class,
            'examination_date' => 'date',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
