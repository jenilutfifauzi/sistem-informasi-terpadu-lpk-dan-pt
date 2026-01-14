<?php

namespace App\Models;

use App\Enums\EntityType;
use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeLPK extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'karyawan_lpk';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'email',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon',
        'jabatan',
        'status',
        'tanggal_bergabung',
        'honor_pokok',
        'honor_per_jam',
        'sertifikat_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jabatan' => JabatanLPK::class,
        'status' => StatusKepegawaian::class,
        'entity' => EntityType::class,
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
        'honor_pokok' => 'decimal:2',
        'honor_per_jam' => 'decimal:2',
    ];

    protected $attributes = [
        'entity' => 'LPK',
        'status' => 'Aktif',
    ];

    /**
     * Boot the model - auto-assign entity='LPK' on creation
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->entity = EntityType::LPK;
            $employee->created_by = auth()->id();
        });

        static::updating(function ($employee) {
            $employee->updated_by = auth()->id();
        });
    }

    /**
     * Get the audit log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Karyawan LPK {$eventName}: {$this->nama_lengkap}");
    }

    /**
     * Get the user who created this record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get download URL for sertifikat file.
     *
     * Returns a signed URL that allows authorized users to download the sertifikat
     * from private storage. Returns null if sertifikat doesn't exist.
     *
     * @return string|null Signed URL for downloading sertifikat, or null if no file
     */
    public function getSertifikatDownloadUrlAttribute(): ?string
    {
        if (! $this->sertifikat_path) {
            return null;
        }

        return route('karyawan-lpk.sertifikat.download', $this);
    }
}
