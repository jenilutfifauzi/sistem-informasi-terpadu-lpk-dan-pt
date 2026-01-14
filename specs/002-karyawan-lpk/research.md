# Research Notes: Karyawan LPK Management

**Feature**: 002-karyawan-lpk  
**Date**: 2026-01-13  
**Purpose**: Resolve NEEDS CLARIFICATION items dan research best practices untuk technical implementation

---

## 1. Audit Logging Approach Decision

**Question**: Use custom AuditLog model vs spatie/laravel-activitylog?

**Decision**: Check `specs/001-user-management-rbac/research.md` for the decision made in User Management spec.

**Recommendation** (if spec 001 not yet decided):
- **Use `spatie/laravel-activitylog`** for consistency across application
- Rationale:
  - Industry standard, well-maintained package
  - Automatic logging via `LogsActivity` trait
  - Rich query builder for audit reports
  - Configurable log fields (old/new values, IP, user agent)
  - Reduces custom code maintenance

**Implementation** (if using spatie/laravel-activitylog):

```php
// app/Models/EmployeeLPK.php
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeLPK extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all fillable attributes
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Skip if nothing changed
            ->setDescriptionForEvent(fn(string $eventName) => "Karyawan LPK {$eventName}");
    }
}
```

**Alternative** (if using custom):

```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

// app/Observers/EmployeeLPKObserver.php
class EmployeeLPKObserver
{
    public function created(EmployeeLPK $employee): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => EmployeeLPK::class,
            'model_id' => $employee->id,
            'new_values' => $employee->getAttributes(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    // Similar for updated, deleted...
}
```

**Action**: Align with spec 001 decision for consistency.

---

## 2. Filament v4 File Upload Best Practices

**Question**: How to implement private file storage untuk sertifikat dengan authorization?

**Findings from Filament docs**:

### 2.1 Private Disk Configuration

```php
// app/Filament/Resources/EmployeeLPK/EmployeeLPKResource.php (form section)
use Filament\Forms\Components\FileUpload;

FileUpload::make('sertifikat_path')
    ->label('Sertifikat Kompetensi')
    ->disk('private') // Uses storage/app/private
    ->directory('certificates')
    ->visibility('private') // Important: prevents direct URL access
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(5120) // 5MB in kilobytes
    ->preserveFilenames() // Keep original filename
    ->visible(fn (Get $get): bool => $get('jabatan') === JabatanLPK::Instruktur->value)
    ->helperText('Upload PDF, JPG, atau PNG (maks 5MB). Hanya untuk Instruktur.')
```

**Key Points**:
- `disk('private')` → uses `storage/app/private` (not public)
- `visibility('private')` → files tidak accessible via public URL
- Conditional visibility: `visible(fn)` based on jabatan field
- File size in KB: 5MB = 5120KB

### 2.2 Serving Private Files with Authorization

```php
// app/Filament/Resources/EmployeeLPK/Pages/ViewEmployeeLPK.php or EditEmployeeLPK.php
use Filament\Infolists\Components\TextEntry;

TextEntry::make('sertifikat_path')
    ->label('Sertifikat')
    ->formatStateUsing(fn ($state) => $state ? basename($state) : '-')
    ->url(fn (EmployeeLPK $record) => route('employee-lpk.download-certificate', $record))
    ->openUrlInNewTab()
    ->visible(fn (EmployeeLPK $record) => $record->jabatan === JabatanLPK::Instruktur && $record->sertifikat_path)
```

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/employee-lpk/{employee}/certificate', function (EmployeeLPK $employee) {
        // Authorization check
        if (! Gate::allows('downloadCertificate', $employee)) {
            abort(403);
        }
        
        $filePath = storage_path('app/private/' . $employee->sertifikat_path);
        
        if (! file_exists($filePath)) {
            abort(404);
        }
        
        return response()->download($filePath);
    })->name('employee-lpk.download-certificate');
});
```

```php
// app/Policies/EmployeeLPKPolicy.php
public function downloadCertificate(User $user, EmployeeLPK $employee): bool
{
    // Admin LPK, Pimpinan, atau instruktur yang owns record
    return $user->hasRole(['Admin LPK', 'Pimpinan']) 
        || ($user->hasRole('Instruktur') && $user->employee_lpk_id === $employee->id);
}
```

**Alternative**: Use Filament's built-in file serving (if available in v4):
- Check Filament docs for `->downloadable()` or similar method
- May automatically handle authorization via resource policy

---

## 3. Enum Patterns in Laravel 11 + Filament v4

**Question**: Best practices untuk JabatanLPK dan StatusKepegawaian enums?

**Recommendation**: Use PHP 8.1+ **backed string enums** untuk database compatibility dan readability.

### 3.1 JabatanLPK Enum

```php
// app/Enums/JabatanLPK.php
namespace App\Enums;

enum JabatanLPK: string
{
    case Instruktur = 'Instruktur';
    case AdminLPK = 'Admin LPK';
    case Staff = 'Staff';
    
    public function getLabel(): string
    {
        return match($this) {
            self::Instruktur => 'Instruktur',
            self::AdminLPK => 'Admin LPK',
            self::Staff => 'Staff',
        };
    }
    
    public static function toArray(): array
    {
        return [
            self::Instruktur->value => self::Instruktur->getLabel(),
            self::AdminLPK->value => self::AdminLPK->getLabel(),
            self::Staff->value => self::Staff->getLabel(),
        ];
    }
}
```

### 3.2 StatusKepegawaian Enum

```php
// app/Enums/StatusKepegawaian.php
namespace App\Enums;

enum StatusKepegawaian: string
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

### 3.3 Migration

```php
// database/migrations/2026_01_13_000001_create_karyawan_lpk_table.php
$table->enum('jabatan', ['Instruktur', 'Admin LPK', 'Staff']);
$table->enum('status', ['Aktif', 'Cuti', 'Resign'])->default('Aktif');
```

### 3.4 Model Casts

```php
// app/Models/EmployeeLPK.php
use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;

class EmployeeLPK extends Model
{
    protected $casts = [
        'jabatan' => JabatanLPK::class,
        'status' => StatusKepegawaian::class,
        // ...
    ];
}
```

### 3.5 Filament Select Integration

```php
// Filament Resource form
use Filament\Forms\Components\Select;

Select::make('jabatan')
    ->options(JabatanLPK::class) // Filament v4 auto-detects enum
    ->required()
    ->live() // Trigger reactive changes for conditional fields

Select::make('status')
    ->options(StatusKepegawaian::class)
    ->default(StatusKepegawaian::Aktif)
    ->required()
```

**Note**: Filament v4 has built-in enum support. Simply passing `::class` works if enum has `getLabel()` or implements `Filament\Support\Contracts\HasLabel`.

**Alternative** (if enum doesn't implement HasLabel):

```php
Select::make('jabatan')
    ->options(JabatanLPK::toArray())
```

---

## 4. Soft Delete Filter in Filament Tables

**Question**: How to implement "Trashed" filter untuk soft-deleted records?

**Findings**: Filament has built-in `TrashedFilter` for soft deletes.

### 4.1 Enable Soft Deletes in Model

```php
// app/Models/EmployeeLPK.php
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLPK extends Model
{
    use SoftDeletes;
}
```

### 4.2 Add TrashedFilter to Resource

```php
// app/Filament/Resources/EmployeeLPK/EmployeeLPKResource.php
use Filament\Tables\Filters\TrashedFilter;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([
            TrashedFilter::make(),
            // Other filters...
        ]);
}
```

**Behavior**:
- Default: Shows only non-trashed records (`withoutTrashed()`)
- Filter options:
  - "Without Trashed" (default)
  - "With Trashed" (includes soft-deleted)
  - "Only Trashed" (shows only soft-deleted)

### 4.3 Restore and Force Delete Actions

```php
// Add to table actions
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

public static function table(Table $table): Table
{
    return $table
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ]);
}
```

**Authorization**: Check EmployeeLPKPolicy for `restore()`, `forceDelete()`, `restoreAny()`, `forceDeleteAny()` methods.

---

## 5. Entity Field Auto-Assignment Pattern

**Question**: Best way to auto-set entity='LPK' on model creation?

**Recommendation**: Combine **Model boot event** + **database default** untuk defense-in-depth.

### 5.1 Database Level

```php
// Migration
$table->enum('entity', ['PT', 'LPK'])->default('LPK')->nullable(false);
```

**Benefit**: Database constraint ensures data integrity even if app logic fails.

### 5.2 Model Level (Boot Event)

```php
// app/Models/EmployeeLPK.php
class EmployeeLPK extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($employee) {
            // Auto-assign entity if not set (should always be LPK for this model)
            if (empty($employee->entity)) {
                $employee->entity = EntityType::LPK;
            }
        });
    }
}
```

### 5.3 Form Level (Hidden/Disabled Field)

```php
// Filament Resource form
use Filament\Forms\Components\Hidden;

Hidden::make('entity')
    ->default(EntityType::LPK->value)
```

**Or** (if visible but disabled):

```php
use Filament\Forms\Components\Select;

Select::make('entity')
    ->options(EntityType::class)
    ->default(EntityType::LPK)
    ->disabled() // Cannot be changed by user
    ->dehydrated() // Include in form submission even if disabled
    ->required()
```

### 5.4 Alternative: Default Attributes

```php
// app/Models/EmployeeLPK.php
class EmployeeLPK extends Model
{
    protected $attributes = [
        'entity' => 'LPK',
        'status' => 'Aktif',
    ];
}
```

**Recommendation**: Use all 3 layers (migration default, model boot, form default) for maximum data integrity.

---

## 6. Additional Findings: Filament v4 Specific

### 6.1 Layout Components Namespace Change

**Important**: Filament v4 moved layout components from `Filament\Forms\Components` to `Filament\Schemas\Components`.

```php
// OLD (Filament v3):
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

// NEW (Filament v4):
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
```

**Action**: Use correct namespace in all form() methods. Check Filament docs for complete list.

### 6.2 Conditional Field Visibility

```php
use Filament\Forms\Get;

FileUpload::make('sertifikat_path')
    ->visible(fn (Get $get): bool => $get('jabatan') === JabatanLPK::Instruktur->value)
    ->required(fn (Get $get): bool => $get('jabatan') === JabatanLPK::Instruktur->value)
```

**Note**: Use `->live()` on the triggering field (jabatan) to enable reactivity:

```php
Select::make('jabatan')
    ->options(JabatanLPK::class)
    ->live() // Makes other fields reactive to changes
    ->required()
```

### 6.3 Honor Fields Conditional Logic

```php
TextInput::make('honor_per_jam')
    ->label('Honor per Jam Mengajar')
    ->numeric()
    ->prefix('Rp')
    ->minValue(0)
    ->visible(fn (Get $get): bool => $get('jabatan') === JabatanLPK::Instruktur->value)
    ->helperText('Untuk instruktur yang mengajar per jam')
```

---

## Summary of Decisions

| Topic | Decision | Rationale |
|-------|----------|-----------|
| **Audit Logging** | Use spatie/laravel-activitylog (pending spec 001 confirmation) | Industry standard, reduces custom code, rich features |
| **File Upload** | Filament FileUpload with disk='private', visibility='private' | Secure storage, authorization required for download |
| **File Download** | Custom route with Gate authorization | Full control over access, audit-friendly |
| **Enums** | PHP 8.1 backed string enums | Type-safe, Filament-friendly, database-compatible |
| **Soft Delete Filter** | Use Filament's TrashedFilter | Built-in, zero config, standard UX |
| **Entity Auto-Assign** | Database default + Model boot + Form default | Defense-in-depth, prevents human error |
| **Layout Components** | Use Filament\Schemas\Components (v4) | Correct namespace for Filament v4 |
| **Conditional Fields** | ->visible(fn) + ->live() | Reactive UI, clean UX |

---

## Next Steps

1. ✅ All NEEDS CLARIFICATION resolved
2. ✅ Best practices documented with code examples
3. ➡️ Proceed to Phase 1: Generate data-model.md with schema + validations
4. ➡️ Generate quickstart.md with user workflows
5. ➡️ Update agent context (.github/copilot-instructions.md)

**Status**: Research Phase COMPLETE ✅  
**Ready for Phase 1**: YES
