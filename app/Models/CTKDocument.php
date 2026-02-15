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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            // Ensure filename is set from file_path
            if (empty($document->filename) && ! empty($document->file_path)) {
                $document->filename = basename($document->file_path);
            }

            // Set file_size if not present
            if (empty($document->file_size) && ! empty($document->file_path)) {
                $fullPath = storage_path('app/public/'.$document->file_path);
                if (file_exists($fullPath)) {
                    $document->file_size = filesize($fullPath);
                } else {
                    $document->file_size = 0;
                }
            }

            // Set mime_type if not present
            if (empty($document->mime_type) && ! empty($document->filename)) {
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                ];
                $ext = strtolower(pathinfo($document->filename, PATHINFO_EXTENSION));
                $document->mime_type = $mimeTypes[$ext] ?? 'application/octet-stream';
            }

            // Set uploader_id if not present
            if (empty($document->uploader_id)) {
                $document->uploader_id = auth()->id();
            }

            // Set upload_timestamp if not present
            if (empty($document->upload_timestamp)) {
                $document->upload_timestamp = now();
            }
        });
    }

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
