# Data Model: Karyawan LPK Management

**Feature**: 002-karyawan-lpk  
**Date**: 2026-01-13  
**Purpose**: Define database schema, validations, relationships for EmployeeLPK entity

---

## 1. Primary Entity: EmployeeLPK (karyawan_lpk table)

### 1.1 Schema

```sql
CREATE TABLE karyawan_lpk (
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
    jabatan ENUM('Instruktur', 'Admin LPK', 'Staff') NOT NULL,
    status ENUM('Aktif', 'Cuti', 'Resign') NOT NULL DEFAULT 'Aktif',
    tanggal_bergabung DATE NOT NULL,
    
    -- Compensation (Nullable - may not be set for all employees)
    honor_pokok DECIMAL(15, 2) NULL COMMENT 'Rupiah',
    honor_per_jam DECIMAL(15, 2) NULL COMMENT 'Rupiah - Only for Instruktur',
    
    -- Certificate (Nullable - only for Instruktur)
    sertifikat_path VARCHAR(255) NULL COMMENT 'Path to certificate file in private storage',
    
    -- Entity Isolation (Constitution Principle II)
    entity ENUM('PT', 'LPK') NOT NULL DEFAULT 'LPK',
    
    -- Audit Fields
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete for data retention',
    
    -- Indexes
    INDEX idx_jabatan (jabatan),
    INDEX idx_status (status),
    INDEX idx_entity (entity),
    INDEX idx_deleted_at (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.2 Model Attributes (Laravel)

```php
// app/Models/EmployeeLPK.php
namespace App\Models;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmployeeLPK extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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
        // entity NOT fillable - auto-assigned
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

    protected static function boot()
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

    // Audit logging configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Karyawan LPK {$eventName}: {$this->nama_lengkap}");
    }
}
```

---

## 2. Supporting Enums

### 2.1 JabatanLPK Enum

```php
// app/Enums/JabatanLPK.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JabatanLPK: string implements HasLabel
{
    case Instruktur = 'Instruktur';
    case AdminLPK = 'Admin LPK';
    case Staff = 'Staff';
    
    public function getLabel(): string
    {
        return $this->value;
    }
    
    public function getColor(): string
    {
        return match($this) {
            self::Instruktur => 'primary',
            self::AdminLPK => 'success',
            self::Staff => 'info',
        };
    }
}
```

### 2.2 StatusKepegawaian Enum

```php
// app/Enums/StatusKepegawaian.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusKepegawaian: string implements HasLabel
{
    case Aktif = 'Aktif';
    case Cuti = 'Cuti';
    case Resign = 'Resign';
    
    public function getLabel(): string
    {
        return $this->value;
    }
    
    public function getColor(): string
    {
        return match($this) {
            self::Aktif => 'success',
            self::Cuti => 'warning',
            self::Resign => 'danger',
        };
    }
}
```

### 2.3 EntityType Enum (if not exists from spec 001)

```php
// app/Enums/EntityType.php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EntityType: string implements HasLabel
{
    case PT = 'PT';
    case LPK = 'LPK';
    
    public function getLabel(): string
    {
        return $this->value;
    }
}
```

---

## 3. Validation Rules

### 3.1 Create Validation (StoreEmployeeLPKRequest)

```php
// app/Http/Requests/StoreEmployeeLPKRequest.php
namespace App\Http\Requests;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreEmployeeLPKRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmployeeLPK::class);
    }

    public function rules(): array
    {
        return [
            // Personal Information (FR-015, FR-016, FR-017)
            'nik' => [
                'required',
                'string',
                'digits:16',
                'unique:karyawan_lpk,nik',
            ],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('karyawan_lpk', 'email')->whereNull('deleted_at'),
            ],
            'tanggal_lahir' => [
                'required',
                'date',
                'before:today', // FR-017
            ],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:20'],
            
            // Employment Information (FR-018)
            'jabatan' => ['required', new Enum(JabatanLPK::class)],
            'status' => ['required', new Enum(StatusKepegawaian::class)],
            'tanggal_bergabung' => [
                'required',
                'date',
                'after_or_equal:tanggal_lahir', // FR-018
            ],
            
            // Compensation (optional)
            'honor_pokok' => ['nullable', 'numeric', 'min:0'],
            'honor_per_jam' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:jabatan,' . JabatanLPK::Instruktur->value,
            ],
            
            // Certificate (FR-008)
            'sertifikat_path' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB in kilobytes
                'required_if:jabatan,' . JabatanLPK::Instruktur->value,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'email.unique' => 'Email sudah terdaftar.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan.',
            'tanggal_bergabung.after_or_equal' => 'Tanggal bergabung tidak boleh sebelum tanggal lahir.',
            'honor_pokok.min' => 'Honor harus berupa angka positif.',
            'sertifikat_path.max' => 'File maksimal 5MB dengan format PDF/JPG/PNG.',
        ];
    }
}
```

### 3.2 Update Validation (UpdateEmployeeLPKRequest)

```php
// Similar to Store, but with unique rule exception for current record:
'nik' => [
    'required',
    'string',
    'digits:16',
    Rule::unique('karyawan_lpk', 'nik')->ignore($this->employee_lpk)->whereNull('deleted_at'),
],
'email' => [
    'required',
    'email:rfc,dns',
    Rule::unique('karyawan_lpk', 'email')->ignore($this->employee_lpk)->whereNull('deleted_at'),
],
```

---

## 4. Relationships

### 4.1 EmployeeLPK → User (Created By / Updated By)

```php
// app/Models/EmployeeLPK.php
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater(): BelongsTo
{
    return $this->belongsTo(User::class, 'updated_by');
}
```

### 4.2 EmployeeLPK → TrainingSession (Future - Pelatihan Module)

```php
// Future relationship (not implemented in this spec)
// Documented for reference when building Pelatihan module

public function trainingAssignments(): HasMany
{
    return $this->hasMany(TrainingSession::class, 'instruktur_id');
}
```

**Note**: Only Instruktur can be assigned to training sessions. Add scope:

```php
public function scopeInstruktur(Builder $query): void
{
    $query->where('jabatan', JabatanLPK::Instruktur);
}

// Usage: EmployeeLPK::instruktur()->get()
```

---

## 5. Scopes & Query Helpers

### 5.1 Status Scopes

```php
// app/Models/EmployeeLPK.php
public function scopeAktif(Builder $query): void
{
    $query->where('status', StatusKepegawaian::Aktif);
}

public function scopeCuti(Builder $query): void
{
    $query->where('status', StatusKepegawaian::Cuti);
}

public function scopeResign(Builder $query): void
{
    $query->where('status', StatusKepegawaian::Resign);
}
```

### 5.2 Jabatan Scopes

```php
public function scopeInstruktur(Builder $query): void
{
    $query->where('jabatan', JabatanLPK::Instruktur);
}

public function scopeAdminLPK(Builder $query): void
{
    $query->where('jabatan', JabatanLPK::AdminLPK);
}

public function scopeStaff(Builder $query): void
{
    $query->where('jabatan', JabatanLPK::Staff);
}
```

### 5.3 Honor Filter Scope

```php
public function scopeHasHonor(Builder $query): void
{
    $query->whereNotNull('honor_pokok');
}

public function scopeNoHonor(Builder $query): void
{
    $query->whereNull('honor_pokok');
}
```

---

## 6. Accessors & Mutators

### 6.1 Full Name with NIK (Helper)

```php
// app/Models/EmployeeLPK.php
public function getFullNameWithNikAttribute(): string
{
    return "{$this->nama_lengkap} ({$this->nik})";
}

// Usage: $employee->full_name_with_nik
```

### 6.2 Certificate File Name (Helper)

```php
public function getCertificateFilenameAttribute(): ?string
{
    return $this->sertifikat_path ? basename($this->sertifikat_path) : null;
}
```

### 6.3 Is Instruktur Check

```php
public function isInstruktur(): bool
{
    return $this->jabatan === JabatanLPK::Instruktur;
}

public function isAktif(): bool
{
    return $this->status === StatusKepegawaian::Aktif;
}
```

---

## 7. Business Rules & Constraints

### 7.1 Data Integrity Rules

1. **NIK Uniqueness** (FR-002)
   - Database: UNIQUE constraint
   - Validation: Rule::unique with case-insensitive check
   - Business: NIK cannot be changed after creation (UI disabled)

2. **Email Uniqueness** (FR-002)
   - Database: UNIQUE constraint
   - Validation: email format + unique check (excluding soft-deleted)
   - Business: Email is primary contact identifier

3. **Entity Immutability** (FR-006)
   - Database: DEFAULT 'LPK' NOT NULL
   - Model: boot() event auto-assigns
   - Form: Hidden or disabled field
   - Business: NEVER editable, always 'LPK' for EmployeeLPK

4. **Date Logic** (FR-017, FR-018)
   - tanggal_lahir < today
   - tanggal_bergabung >= tanggal_lahir
   - Enforced via validation rules

### 7.2 Soft Delete Rules

1. **Trigger**: Status change to 'Resign' OR explicit delete action
2. **Behavior**: Sets deleted_at timestamp, preserves all data
3. **Visibility**: Hidden from default queries (withoutTrashed)
4. **Restoration**: Can be restored by Admin LPK (clears deleted_at, sets status='Aktif')
5. **Force Delete**: Admin LPK only, permanent removal (check for relationships first)

### 7.3 Honor Rules

1. **honor_pokok**: Nullable, applies to all jabatan
2. **honor_per_jam**: Nullable, typically only for Instruktur
3. **Validation**: Must be numeric, >= 0 (if provided)
4. **Display**: Currency format (Rp {amount})

### 7.4 Certificate Rules (FR-007, FR-008, FR-009)

1. **Visibility**: Only visible/required if jabatan = Instruktur
2. **File Types**: PDF, JPG, JPEG, PNG
3. **Max Size**: 5MB (5120KB)
4. **Storage**: Private disk, certificates/ directory
5. **Naming**: {nik}_{timestamp}.{ext}
6. **Download**: Authorized users only (Policy check)

---

## 8. State Transitions

### 8.1 Status Transitions

```
Aktif ←→ Cuti       (Bidirectional: employee takes leave, returns)
Aktif → Resign      (One-way: employee resigns, triggers soft delete)
Resign → Aktif      (Restore: clear deleted_at, restore access)
```

**Business Rules**:
- Aktif → Cuti: Allowed, no restrictions
- Cuti → Aktif: Allowed, no restrictions
- Aktif → Resign: Allowed, triggers soft delete
- Resign → Aktif: Requires restore action (Admin LPK only)
- Cuti → Resign: Allowed, triggers soft delete

### 8.2 Jabatan Changes

- Changing jabatan does NOT delete existing data
- Instruktur → Staff/AdminLPK: sertifikat_path preserved (hidden in UI)
- Staff/AdminLPK → Instruktur: Can upload new certificate
- honor_per_jam preserved even if jabatan changes (for audit trail)

---

## 9. Factory Definition (Testing)

```php
// database/factories/EmployeeLPKFactory.php
namespace Database\Factories;

use App\Models\EmployeeLPK;
use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeLPKFactory extends Factory
{
    protected $model = EmployeeLPK::class;

    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'), // 16 digits
            'nama_lengkap' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tanggal_lahir' => $this->faker->dateTimeBetween('-50 years', '-20 years'),
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'jabatan' => $this->faker->randomElement(JabatanLPK::cases()),
            'status' => StatusKepegawaian::Aktif,
            'tanggal_bergabung' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'honor_pokok' => $this->faker->numberBetween(3000000, 10000000),
            'entity' => EntityType::LPK,
        ];
    }

    public function instruktur(): static
    {
        return $this->state(fn (array $attributes) => [
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => $this->faker->numberBetween(50000, 200000),
        ]);
    }

    public function resign(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusKepegawaian::Resign,
            'deleted_at' => now(),
        ]);
    }
}
```

---

## 10. Migration Files

### 10.1 Main Table Migration

```php
// database/migrations/2026_01_13_000001_create_karyawan_lpk_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan_lpk', function (Blueprint $table) {
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
            $table->enum('jabatan', ['Instruktur', 'Admin LPK', 'Staff']);
            $table->enum('status', ['Aktif', 'Cuti', 'Resign'])->default('Aktif');
            $table->date('tanggal_bergabung');
            
            // Compensation
            $table->decimal('honor_pokok', 15, 2)->nullable()->comment('Rupiah');
            $table->decimal('honor_per_jam', 15, 2)->nullable()->comment('Rupiah - Only for Instruktur');
            
            // Certificate
            $table->string('sertifikat_path')->nullable()->comment('Path to certificate file');
            
            // Entity Isolation
            $table->enum('entity', ['PT', 'LPK'])->default('LPK')->nullable(false);
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('jabatan');
            $table->index('status');
            $table->index('entity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan_lpk');
    }
};
```

---

## Summary

**Entity**: EmployeeLPK (karyawan_lpk table)  
**Enums**: JabatanLPK, StatusKepegawaian, EntityType  
**Relationships**: BelongsTo User (creator, updater), Future: HasMany TrainingSession  
**Validation**: 18 field-level rules + 2 cross-field rules (date logic)  
**Features**: Soft delete, audit logging, entity isolation, file upload  
**Constraints**: NIK unique (16 digits), email unique, entity immutable, dates validated  

**Status**: Data model COMPLETE ✅  
**Next**: Create quickstart.md, contracts/, update agent context
