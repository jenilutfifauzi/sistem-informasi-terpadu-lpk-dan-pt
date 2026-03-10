# Data Model: Karyawan PT Management

**Feature**: 009-karyawan-pt-resource
**Date**: 2026-03-09
**Purpose**: Define database schema, validations, relationships, dan enums untuk EmployeePT entity

---

## 1. Primary Entity: EmployeePT (karyawan_pt table)

### 1.1 Schema

```sql
CREATE TABLE karyawan_pt (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Personal Information
    nik CHAR(16) NOT NULL UNIQUE COMMENT 'NIK Indonesia 16 digit',
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) NOT NULL,

    -- Employment Information
    jabatan ENUM('Direktur','Manajer','Staf HRD','Staf Keuangan','Staf Operasional','Staf Administrasi') NOT NULL,
    divisi ENUM('Manajemen','HRD','Keuangan','Operasional','Administrasi') NOT NULL,
    status ENUM('Aktif','Cuti','Resign') NOT NULL DEFAULT 'Aktif',
    jenis_kontrak ENUM('Tetap','PKWT','Probasi') NOT NULL,
    tanggal_bergabung DATE NOT NULL,

    -- Compensation (Nullable - may not be set initially)
    gaji_pokok DECIMAL(15, 2) NULL COMMENT 'Rupiah',
    tunjangan DECIMAL(15, 2) NULL COMMENT 'Rupiah - tunjangan tambahan',

    -- Photo & Document (Nullable)
    foto_path VARCHAR(255) NULL COMMENT 'Path to photo in public disk',
    dokumen_path VARCHAR(255) NULL COMMENT 'Path to HR document in private storage',

    -- Entity Isolation (Constitution Principle II)
    entity ENUM('PT', 'LPK') NOT NULL DEFAULT 'PT' COMMENT 'Entity isolation: PT only',

    -- Audit Fields
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,

    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete for data retention',

    -- Indexes
    INDEX idx_jabatan (jabatan),
    INDEX idx_divisi (divisi),
    INDEX idx_status (status),
    INDEX idx_jenis_kontrak (jenis_kontrak),
    INDEX idx_entity (entity),
    INDEX idx_deleted_at (deleted_at),

    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.2 Laravel Migration

```php
// database/migrations/2026_03_09_000001_create_karyawan_pt_table.php
Schema::create('karyawan_pt', function (Blueprint $table) {
    $table->id();

    // Personal Information
    $table->char('nik', 16)->unique()->comment('NIK Indonesia 16 digit');
    $table->string('nama_lengkap');
    $table->string('email')->unique();
    $table->date('tanggal_lahir');
    $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
    $table->text('alamat');
    $table->string('telepon', 20);

    // Employment Information
    $table->enum('jabatan', ['Direktur', 'Manajer', 'Staf HRD', 'Staf Keuangan', 'Staf Operasional', 'Staf Administrasi']);
    $table->enum('divisi', ['Manajemen', 'HRD', 'Keuangan', 'Operasional', 'Administrasi']);
    $table->enum('status', ['Aktif', 'Cuti', 'Resign'])->default('Aktif');
    $table->enum('jenis_kontrak', ['Tetap', 'PKWT', 'Probasi']);
    $table->date('tanggal_bergabung');

    // Compensation (Nullable)
    $table->decimal('gaji_pokok', 15, 2)->nullable()->comment('Rupiah');
    $table->decimal('tunjangan', 15, 2)->nullable()->comment('Rupiah');

    // Photo & Document (Nullable)
    $table->string('foto_path')->nullable()->comment('Path to photo in public disk');
    $table->string('dokumen_path')->nullable()->comment('Path to HR document in private storage');

    // Entity Isolation (Constitution Principle II)
    $table->enum('entity', ['PT', 'LPK'])->default('PT')->comment('Entity isolation: PT only');

    // Audit Fields
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();

    // Timestamps
    $table->timestamps();
    $table->softDeletes()->comment('Soft delete for data retention');

    // Indexes
    $table->index('jabatan');
    $table->index('divisi');
    $table->index('status');
    $table->index('jenis_kontrak');
    $table->index('entity');
    $table->index('deleted_at');

    // Foreign Keys
    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
});
```

### 1.3 Model: EmployeePT

```php
// app/Models/EmployeePT.php
namespace App\Models;

use App\Enums\EntityType;
use App\Enums\JabatanPT;
use App\Enums\DivisiPT;
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
        // created_by and updated_by NOT fillable — set exclusively via boot() to prevent spoofing
        // entity NOT fillable - auto-assigned via boot()
    ];

    protected function casts(): array
    {
        return [
            'jabatan'          => JabatanPT::class,
            'divisi'           => DivisiPT::class,
            'status'           => StatusKepegawaian::class,
            'jenis_kontrak'    => JenisKontrak::class,
            'entity'           => EntityType::class,
            'tanggal_lahir'    => 'date',
            'tanggal_bergabung' => 'date',
            'gaji_pokok'       => 'decimal:2',
            'tunjangan'        => 'decimal:2',
        ];
    }

    protected $attributes = [
        'entity' => 'PT',
        'status' => 'Aktif',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->entity = EntityType::PT;
            $employee->created_by = auth()->id();
        });

        static::updating(function ($employee) {
            $employee->updated_by = auth()->id();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Karyawan PT {$eventName}: {$this->nama_lengkap}");
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDokumenDownloadUrlAttribute(): ?string
    {
        if (! $this->dokumen_path) {
            return null;
        }

        return route('karyawan-pt.dokumen.download', $this);
    }
}
```

---

## 2. Enums

### 2.1 JabatanPT

```php
// app/Enums/JabatanPT.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JabatanPT: string implements HasLabel
{
    case Direktur        = 'Direktur';
    case Manajer         = 'Manajer';
    case StafHRD         = 'Staf HRD';
    case StafKeuangan    = 'Staf Keuangan';
    case StafOperasional = 'Staf Operasional';
    case StafAdministrasi = 'Staf Administrasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
```

### 2.2 DivisiPT

```php
// app/Enums/DivisiPT.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DivisiPT: string implements HasLabel
{
    case Manajemen   = 'Manajemen';
    case HRD         = 'HRD';
    case Keuangan    = 'Keuangan';
    case Operasional = 'Operasional';
    case Administrasi = 'Administrasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
```

### 2.3 JenisKontrak

```php
// app/Enums/JenisKontrak.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JenisKontrak: string implements HasLabel
{
    case Tetap   = 'Tetap';
    case PKWT    = 'PKWT';
    case Probasi = 'Probasi';

    public function getLabel(): string
    {
        return $this->value;
    }
}
```

---

## 3. Validation Rules

### 3.1 StoreEmployeePTRequest

```php
public function rules(): array
{
    return [
        'nik'              => ['required', 'string', 'size:16', 'unique:karyawan_pt,nik'],
        'nama_lengkap'     => ['required', 'string', 'max:255'],
        'email'            => ['required', 'email', 'max:255', 'unique:karyawan_pt,email'],
        'tanggal_lahir'    => ['required', 'date', 'before:today'],
        'jenis_kelamin'    => ['required', 'in:Laki-laki,Perempuan'],
        'alamat'           => ['required', 'string', 'max:1000'],
        'telepon'          => ['required', 'string', 'max:20'],
        'jabatan'          => ['required', Rule::enum(JabatanPT::class)],
        'divisi'           => ['required', Rule::enum(DivisiPT::class)],
        'status'           => ['required', Rule::enum(StatusKepegawaian::class)],
        'jenis_kontrak'    => ['required', Rule::enum(JenisKontrak::class)],
        'tanggal_bergabung' => ['required', 'date'],
        'gaji_pokok'       => ['nullable', 'numeric', 'min:0'],
        'tunjangan'        => ['nullable', 'numeric', 'min:0'],
        'foto_path'        => ['nullable', 'image', 'max:2048'],
        'dokumen_path'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
    ];
}
```

### 3.2 UpdateEmployeePTRequest

```php
public function rules(): array
{
    $id = $this->record->id ?? $this->route('record');

    return [
        // nik & tanggal_bergabung: NOT present (disabled on edit form, not submitted)
        'nama_lengkap'     => ['required', 'string', 'max:255'],
        'email'            => ['required', 'email', 'max:255', Rule::unique('karyawan_pt', 'email')->ignore($id)],
        'tanggal_lahir'    => ['required', 'date', 'before:today'],
        'jenis_kelamin'    => ['required', 'in:Laki-laki,Perempuan'],
        'alamat'           => ['required', 'string', 'max:1000'],
        'telepon'          => ['required', 'string', 'max:20'],
        'jabatan'          => ['required', Rule::enum(JabatanPT::class)],
        'divisi'           => ['required', Rule::enum(DivisiPT::class)],
        'status'           => ['required', Rule::enum(StatusKepegawaian::class)],
        'jenis_kontrak'    => ['required', Rule::enum(JenisKontrak::class)],
        'gaji_pokok'       => ['nullable', 'numeric', 'min:0'],
        'tunjangan'        => ['nullable', 'numeric', 'min:0'],
        'foto_path'        => ['nullable', 'image', 'max:2048'],
        'dokumen_path'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
    ];
}
```

---

## 4. Database Relationship Diagram

```
users (existing)
  │
  ├── created_by ──► karyawan_pt.created_by
  └── updated_by ──► karyawan_pt.updated_by

karyawan_pt (NEW)
  ├── id (PK)
  ├── nik (UNIQUE)
  ├── email (UNIQUE)
  ├── jabatan → JabatanPT enum
  ├── divisi → DivisiPT enum
  ├── status → StatusKepegawaian enum (shared dengan karyawan_lpk)
  ├── jenis_kontrak → JenisKontrak enum
  ├── entity → 'PT' (hardcoded)
  ├── deleted_at (soft delete)
  ├── created_by → users.id
  └── updated_by → users.id
```

**Note**: Tidak ada relationship ke CTK, Asset, atau tabel lain pada scope fitur ini. Future fitur dapat menambahkan FK jika diperlukan.

---

## 5. Factory

```php
// database/factories/EmployeePTFactory.php
namespace Database\Factories;

use App\Enums\DivisiPT;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeePT;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeePTFactory extends Factory
{
    protected $model = EmployeePT::class;

    public function definition(): array
    {
        return [
            'nik'              => $this->faker->unique()->numerify('################'),
            'nama_lengkap'     => $this->faker->name(),
            'email'            => $this->faker->unique()->safeEmail(),
            'tanggal_lahir'    => $this->faker->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'jenis_kelamin'    => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'alamat'           => $this->faker->address(),
            'telepon'          => '08' . $this->faker->numerify('#########'),
            'jabatan'          => $this->faker->randomElement(JabatanPT::cases())->value,
            'divisi'           => $this->faker->randomElement(DivisiPT::cases())->value,
            'status'           => StatusKepegawaian::Aktif->value,
            'jenis_kontrak'    => $this->faker->randomElement(JenisKontrak::cases())->value,
            'tanggal_bergabung' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'gaji_pokok'       => $this->faker->randomFloat(2, 3000000, 20000000),
            'tunjangan'        => $this->faker->optional()->randomFloat(2, 500000, 5000000),
        ];
    }

    public function aktif(): static
    {
        return $this->state(['status' => StatusKepegawaian::Aktif->value]);
    }

    public function resign(): static
    {
        return $this->state(['status' => StatusKepegawaian::Resign->value]);
    }

    public function withDokumen(): static
    {
        return $this->state(['dokumen_path' => 'documents/sample-dokumen.pdf']);
    }
}
```
