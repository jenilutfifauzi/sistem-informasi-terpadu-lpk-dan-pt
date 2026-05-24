<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BukuIndukSiswa extends Model
{
    /** @use HasFactory<\Database\Factories\BukuIndukSiswaFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'buku_induk_siswa';

    protected $fillable = [
        'foto_path',
        'nama_lengkap',
        'nomor_induk',
        'program_pendidikan',
        'program_bahasa',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'kewarganegaraan',
        'status_perkawinan',
        'nama_suami_istri',
        'no_hp_suami_istri',
        'alamat_siswa',
        'no_hp_siswa',
        'email',
        'alamat_orang_tua',
        'no_hp_orang_tua',
        'golongan_darah',
        'penyakit_pernah_diderita',
        'kelainan_jasmani',
        'tinggi_badan_cm',
        'berat_badan_kg',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tinggi_badan_cm' => 'integer',
        'berat_badan_kg' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BukuIndukSiswa $bukuIndukSiswa): void {
            $userId = auth()->id();

            if ($bukuIndukSiswa->created_by === null) {
                $bukuIndukSiswa->created_by = $userId;
            }

            if ($bukuIndukSiswa->updated_by === null) {
                $bukuIndukSiswa->updated_by = $userId;
            }
        });

        static::updating(function (BukuIndukSiswa $bukuIndukSiswa): void {
            $bukuIndukSiswa->updated_by = auth()->id();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('buku_induk_siswa')
            ->logOnly([
                'foto_path',
                'nama_lengkap',
                'nomor_induk',
                'program_pendidikan',
                'program_bahasa',
                'nama_panggilan',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'agama',
                'kewarganegaraan',
                'status_perkawinan',
                'nama_suami_istri',
                'no_hp_suami_istri',
                'alamat_siswa',
                'no_hp_siswa',
                'email',
                'alamat_orang_tua',
                'no_hp_orang_tua',
                'golongan_darah',
                'penyakit_pernah_diderita',
                'kelainan_jasmani',
                'tinggi_badan_cm',
                'berat_badan_kg',
                'created_by',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Buku Induk Siswa {$eventName}: {$this->nama_lengkap}");
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
