<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SiswaLPK extends Model
{
    /** @use HasFactory<\Database\Factories\SiswaLPKFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'siswa_lpk';

    protected $fillable = [
        'nomor_urut',
        'nomor_induk',
        'nama_siswa',
        'jenis_kelamin',
        'agama',
        'pendidikan_terakhir',
        'tanggal_masuk',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'program_pendidikan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'nomor_urut' => 'integer',
        'tanggal_masuk' => 'date',
        'tanggal_lahir' => 'date',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SiswaLPK $siswaLPK): void {
            $userId = auth()->id();

            if ($siswaLPK->created_by === null) {
                $siswaLPK->created_by = $userId;
            }

            if ($siswaLPK->updated_by === null) {
                $siswaLPK->updated_by = $userId;
            }
        });

        static::updating(function (SiswaLPK $siswaLPK): void {
            $siswaLPK->updated_by = auth()->id();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('siswa_lpk')
            ->logOnly([
                'nomor_urut',
                'nomor_induk',
                'nama_siswa',
                'jenis_kelamin',
                'agama',
                'pendidikan_terakhir',
                'tanggal_masuk',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'no_hp',
                'email',
                'program_pendidikan',
                'created_by',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Siswa LPK {$eventName}: {$this->nama_siswa}");
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
