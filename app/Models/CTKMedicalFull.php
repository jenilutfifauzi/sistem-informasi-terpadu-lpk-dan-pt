<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKMedicalFull extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctk_id',
        'status',
        'examination_date',
        'medical_report_path',
        'examination_findings',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
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

    public function isExpiringSoon(): bool
    {
        return $this->examination_date->diffInDays(now()) > 90;
    }
}
