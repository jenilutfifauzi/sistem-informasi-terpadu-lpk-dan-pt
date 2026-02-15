<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctk_id',
        'application_status',
        'application_date',
        'visa_number',
        'issuance_date',
        'expiry_date',
        'issuing_country',
        'visa_type',
        'visa_document_path',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'issuance_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }
}
