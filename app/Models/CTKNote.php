<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKNote extends Model
{
    use HasFactory;

    protected $table = 'ctk_notes';

    protected $fillable = [
        'ctk_id',
        'note_text',
        'note_category',
        'author_id',
    ];

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
