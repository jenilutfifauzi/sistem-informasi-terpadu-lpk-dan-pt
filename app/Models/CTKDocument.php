<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CTKDocument extends Model
{
    use HasFactory;

    protected $table = 'ctk_documents';

    public $timestamps = false;

    protected $fillable = [
        'ctk_id',
        'document_type',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'uploader_id',
        'upload_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'upload_timestamp' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
