<?php

namespace App\Models;

use App\Enums\DivisiPT;
use App\Enums\EntityType;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeePT extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'karyawan_pt';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'email',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon',
        'jabatan',
        'divisi',
        'status',
        'jenis_kontrak',
        'tanggal_bergabung',
        'gaji_pokok',
        'tunjangan',
        'foto_path',
        'dokumen_path',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'entity' => 'PT',
        'status' => 'Aktif',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'jabatan' => JabatanPT::class,
            'divisi' => DivisiPT::class,
            'jenis_kontrak' => JenisKontrak::class,
            'status' => StatusKepegawaian::class,
            'entity' => EntityType::class,
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
        ];
    }

    /**
     * Boot the model — auto-assign entity='PT', track created_by/updated_by,
     * and soft delete when status transitions to Resign.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $employee): void {
            $employee->entity = EntityType::PT;
            $employee->created_by = auth()->id();
        });

        static::updating(function (self $employee): void {
            $employee->updated_by = auth()->id();

            if ($employee->isDirty('status') && $employee->status === StatusKepegawaian::Resign) {
                $employee->delete();
            }
        });
    }

    /**
     * Get the audit log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Karyawan PT {$eventName}: {$this->nama_lengkap}");
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get download URL for dokumen kepegawaian file.
     */
    public function getDokumenDownloadUrlAttribute(): ?string
    {
        if (! $this->dokumen_path) {
            return null;
        }

        return route('karyawan-pt.dokumen.download', $this);
    }
}
