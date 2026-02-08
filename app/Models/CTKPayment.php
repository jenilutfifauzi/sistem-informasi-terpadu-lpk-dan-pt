<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKPayment extends Model
{
    use HasFactory;

    protected $table = 'ctk_payments';

    protected $fillable = [
        'ctk_id',
        'stage_number',
        'amount',
        'bank_name',
        'payment_date',
        'payment_method',
        'payment_status',
        'payment_proof_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_status' => PaymentStatus::class,
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'stage_number' => 'integer',
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
