# Data Model: Asset Management System

**Feature**: Employee Asset Management System  
**Branch**: `004-asset-management`  
**Phase**: 1 - Database Schema & Relationships  
**Date**: February 8, 2026  
**References**: [spec.md](./spec.md) | [research.md](./research.md)

## Overview

This data model implements asset inventory management with entity-based isolation (PT vs LPK), employee assignments, condition tracking, and comprehensive audit trails.

---

## Entity Relationship Diagram

```
┌─────────────────┐          ┌──────────────────────┐
│ Users           │          │ EmployeeLPK          │
│  (existing)     │          │   (existing)         │
└──────┬──────────┘          └──────┬───────────────┘
       │                            │
       │ created_by/updated_by      │
       │                            │
       └────────┬───────────────────┘
                │ (polymorphic)
                ▼
       ┌──────────────────┐
       │ Assets           │◄────────┐
       │ ───────────────  │         │
       │ PK: id           │         │
       │ UK: nomor_inv... │         │
       │ FK: created_by   │         │
       │ FK: updated_by   │         │
       │ entity (PT/LPK)  │         │
       │ kategori         │         │
       │ kondisi          │         │
       │ deleted_at       │         │
       └──┬───────────────┘         │ asset_id
          │                         │
          │ 1:N                     │
          ▼                         │
┌──────────────────────┐            │
│ AssetConditionHistory│            │
│ ──────────────────── │            │
│ PK: id               │            │
│ FK: asset_id         │            │
│ FK: changed_by       │            │
│ old_condition        │            │
│ new_condition        │            │
│ reason               │            │
│ changed_at           │            │
└──────────────────────┘            │
                                    │
┌──────────────────────┐            │
│ AssetAssignments     │────────────┘
│ ──────────────────── │
│ PK: id               │
│ FK: asset_id         │
│ FK: assigned_by      │
│ assignable_type *    │
│ assignable_id *      │
│ assigned_date        │
│ return_date          │
│ return_notes         │
└──────────────────────┘
         │ (polymorphic)
         └──┬─────────────────┐
            │                 │
            ▼                 ▼
     ┌──────────────┐   ┌───────────┐
     │ EmployeeLPK  │   │ Users     │
     │ (LPK staff)  │   │ (PT staff)│
     └──────────────┘   └───────────┘

* Polymorphic relationship: can reference either EmployeeLPK or User
```

---

## Table Definitions

### 1. assets

**Purpose**: Main asset inventory table with entity-based isolation

```php
Schema::create('assets', function (Blueprint $table) {
    $table->id();
    
    // Entity & Classification
    $table->string('entity', 10);  // ENUM: 'PT', 'LPK' - from EntityType enum
    $table->string('kategori', 50);  // ENUM: AssetCategory values
    
    // Asset Identification
    $table->string('nomor_inventaris', 50)->unique();  // Format: PT-ELK-2024-001
    $table->string('nama_barang');
    $table->text('deskripsi')->nullable();
    
    // Quantity & Condition
    $table->integer('jumlah')->unsigned();  // Must be >= 1 when active
    $table->string('satuan', 50);  // Unit, Set, Buah, etc.
    $table->string('kondisi', 20);  // ENUM: 'Baik', 'Rusak' - from AssetCondition enum
    $table->string('status_assignment', 20)->default('Available');  // 'Assigned', 'Available'
    
    // Purchase Information
    $table->integer('tahun_pembelian')->unsigned();  // Year (e.g., 2024)
    $table->decimal('nilai_pembelian', 15, 2)->default(0);  // Purchase value in IDR
    
    // Location & Notes
    $table->string('lokasi')->nullable();  // Free-text location
    $table->text('keterangan')->nullable();  // General notes
    
    // Audit Fields
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes for performance
    $table->index('entity');
    $table->index('kategori');
    $table->index('kondisi');
    $table->index('status_assignment');
    $table->index(['entity', 'kategori']);  // Composite for filtered queries
    $table->index('created_at');
});
```

**Validations**:
- `entity`: Must be valid EntityType enum value (PT or LPK)
- `kategori`: Must be valid AssetCategory enum value
- `nomor_inventaris`: Unique across all assets, generated automatically
- `jumlah`: Must be >= 1 (positive integer)
- `tahun_pembelian`: Between 1900 and current year
- `nilai_pembelian`: >= 0 (allow zero for donated items)
- `kondisi`: Must be valid AssetCondition enum value

**Business Rules**:
- `entity` is immutable after creation (set based on logged-in user)
- `nomor_inventaris` auto-generated on creation, never editable
- When `status_assignment` = 'Assigned', must have active AssetAssignment record
- Soft delete when fully depleted (jumlah becomes 0) or disposed

---

### 2. asset_assignments

**Purpose**: Track which employees are using which assets (custody tracking)

```php
Schema::create('asset_assignments', function (Blueprint $table) {
    $table->id();
    
    // Asset Reference
    $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
    
    // Polymorphic Employee Reference
    $table->string('assignable_type');  // 'App\\Models\\EmployeeLPK' or 'App\\Models\\User'
    $table->unsignedBigInteger('assignable_id');
    
    // Assignment Tracking
    $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
    $table->date('assigned_date');
    $table->date('return_date')->nullable();  // NULL = currently assigned
    $table->text('return_notes')->nullable();  // Notes when returned
    
    $table->timestamps();
    
    // Indexes
    $table->index('asset_id');
    $table->index(['assignable_type', 'assignable_id']);  // Polymorphic lookup
    $table->index('return_date');  // Filter active assignments (WHERE return_date IS NULL)
    $table->index('assigned_date');
});
```

**Validations**:
- `asset_id`: Must reference existing asset
- `assignable_type`: Must be valid model class (EmployeeLPK::class or User::class)
- `assignable_id`: Must exist in referenced table
- `assigned_date`: Cannot be future date
- `return_date`: If set, must be >= assigned_date

**Business Rules**:
- Only ONE active assignment per asset (return_date = NULL)
- When assigned, parent Asset.status_assignment = 'Assigned'
- When returned (return_date filled), Asset.status_assignment = 'Available'
- Assignment validation: Check no active assignment exists before creating new one

**Relationships**:
```php
// In Asset model
public function assignments()
{
    return $this->hasMany(AssetAssignment::class);
}

public function currentAssignment()
{
    return $this->hasOne(AssetAssignment::class)
        ->whereNull('return_date')
        ->latestOfMany('assigned_date');
}

// In AssetAssignment model
public function asset()
{
    return $this->belongsTo(Asset::class);
}

public function assignable()
{
    return $this->morphTo();  // Can be EmployeeLPK or User
}

public function assignedBy()
{
    return $this->belongsTo(User::class, 'assigned_by');
}
```

---

### 3. asset_condition_histories

**Purpose**: Audit trail for asset condition changes (maintenance tracking)

```php
Schema::create('asset_condition_histories', function (Blueprint $table) {
    $table->id();
    
    // Asset Reference
    $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
    
    // Condition Change
    $table->string('old_condition', 20);  // Previous condition
    $table->string('new_condition', 20);  // New condition
    $table->text('reason')->nullable();  // Why changed (e.g., "Layar retak", "Sudah diperbaiki")
    
    // Audit Fields
    $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('changed_at');  // When condition changed
    
    // Indexes
    $table->index('asset_id');
    $table->index('changed_at');
});
```

**Validations**:
- `asset_id`: Must reference existing asset
- `old_condition`: Must be valid AssetCondition enum value
- `new_condition`: Must be valid AssetCondition enum value
- `new_condition`: Must differ from old_condition
- `changed_at`: Cannot be future timestamp

**Business Rules**:
- Record created automatically when Asset.kondisi changes
- Cannot be edited after creation (immutable audit record)
- No delete allowed (historical record)

**Relationships**:
```php
// In Asset model
public function conditionHistories()
{
    return $this->hasMany(AssetConditionHistory::class);
}

// In AssetConditionHistory model
public function asset()
{
    return $this->belongsTo(Asset::class);
}

public function changedBy()
{
    return $this->belongsTo(User::class, 'changed_by');
}
```

---

## Enums

### AssetCategory

```php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssetCategory: string implements HasLabel
{
    case Elektronik = 'elektronik';
    case Furniture = 'furniture';
    case DokumenIjin = 'dokumen_ijin';
    case PerlengkapanKantor = 'perlengkapan_kantor';
    case Kendaraan = 'kendaraan';
    case Lainnya = 'lainnya';
    
    public function getLabel(): string
    {
        return match($this) {
            self::Elektronik => 'Elektronik',
            self::Furniture => 'Furniture',
            self::DokumenIjin => 'Dokumen/Ijin',
            self::PerlengkapanKantor => 'Perlengkapan Kantor',
            self::Kendaraan => 'Kendaraan',
            self::Lainnya => 'Lainnya',
        };
    }
    
    public function abbreviation(): string
    {
        return match($this) {
            self::Elektronik => 'ELK',
            self::Furniture => 'FUR',
            self::DokumenIjin => 'DOK',
            self::PerlengkapanKantor => 'PLK',
            self::Kendaraan => 'KDR',
            self::Lainnya => 'LN',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::Elektronik => 'info',
            self::Furniture => 'success',
            self::DokumenIjin => 'warning',
            self::PerlengkapanKantor => 'gray',
            self::Kendaraan => 'danger',
            self::Lainnya => 'secondary',
        };
    }
}
```

### AssetCondition

```php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum AssetCondition: string implements HasLabel, HasColor
{
    case Baik = 'baik';
    case Rusak = 'rusak';
    
    public function getLabel(): string
    {
        return match($this) {
            self::Baik => 'Baik',
            self::Rusak => 'Rusak',
        };
    }
    
    public function getColor(): string
    {
        return match($this) {
            self::Baik => 'success',
            self::Rusak => 'danger',
        };
    }
}
```

### AssetAssignmentStatus

```php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum AssetAssignmentStatus: string implements HasLabel, HasColor
{
    case Available = 'available';
    case Assigned = 'assigned';
    
    public function getLabel(): string
    {
        return match($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
        };
    }
    
    public function getColor(): string
    {
        return match($this) {
            self::Available => 'success',
            self::Assigned => 'warning',
        };
    }
}
```

---

## Model Relationships Summary

### Asset Model

```php
class Asset extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    
    // Relationships
    public function creator(): BelongsTo { }           // User who created
    public function updater(): BelongsTo { }           // User who last updated
    public function assignments(): HasMany { }         // All assignments
    public function currentAssignment(): HasOne { }    // Active assignment
    public function conditionHistories(): HasMany { }  // Condition changes
    
    // Scopes
    public function scopeByEntity(Builder $query, EntityType $entity): Builder { }
    public function scopeAvailable(Builder $query): Builder { }  // Not assigned
    public function scopeAssigned(Builder $query): Builder { }   // Currently assigned
    public function scopeInGoodCondition(Builder $query): Builder { }
    public function scopeNeedsRepair(Builder $query): Builder { }
    
    // Accessors
    public function getIsAssignedAttribute(): bool { }  // Has active assignment?
    public function getCurrentAssigneeAttribute(): ?Model { }  // Who has it?
    
    // Business Logic
    public function assignTo(Model $employee, User $assignedBy): AssetAssignment { }
    public function returnFromAssignment(?string $notes = null): void { }
    public function updateCondition(AssetCondition $newCondition, string $reason, User $changedBy): void { }
}
```

### AssetAssignment Model

```php
class AssetAssignment extends Model
{
    use HasFactory;
    
    // Relationships
    public function asset(): BelongsTo { }
    public function assignable(): MorphTo { }  // EmployeeLPK or User
    public function assignedBy(): BelongsTo { }
    
    // Scopes
    public function scopeActive(Builder $query): Builder { }  // return_date = NULL
    public function scopeReturned(Builder $query): Builder { }
    public function scopeForEntity(Builder $query, EntityType $entity): Builder { }
    
    // Accessors
    public function getIsActiveAttribute(): bool { }
    public function getDurationDaysAttribute(): ?int { }  // Days assigned
}
```

### AssetConditionHistory Model

```php
class AssetConditionHistory extends Model
{
    use HasFactory;
    
    public $timestamps = false;  // Uses changed_at instead
    
    // Relationships
    public function asset(): BelongsTo { }
    public function changedBy(): BelongsTo { }
    
    // Scopes
    public function scopeForAsset(Builder $query, int $assetId): Builder { }
    public function scopeRecent(Builder $query, int $days = 30): Builder { }
}
```

---

## Data Integrity Rules

### Constraints

1. **Entity Immutability**: 
   - `assets.entity` cannot be changed after creation
   - Enforced by form logic (field disabled in edit mode)

2. **Unique Nomor Inventaris**:
   - Database unique constraint on `assets.nomor_inventaris`
   - Generated automatically, never exposed for editing

3. **Active Assignment Uniqueness**:
   - Only one AssetAssignment per asset with `return_date = NULL`
   - Enforced by validation in assignment creation logic

4. **Condition Change Validation**:
   - Cannot change to same condition (old != new)
   - Enforced by validation rule

5. **Soft Delete Cascade**:
   - Assignments and condition histories remain when asset soft-deleted
   - Use `withTrashed()` to access soft-deleted assets' history

### Triggers/Observers

Asset model observer handles:
- Auto-generate `nomor_inventaris` on creation
- Auto-set `entity` based on logged-in user
- Log condition changes to asset_condition_histories
- Update `status_assignment` when assignment created/returned

```php
class AssetObserver
{
    public function creating(Asset $asset): void
    {
        // Set entity from authenticated user
        if (! $asset->entity) {
            $asset->entity = auth()->user()->entity ?? EntityType::LPK;
        }
        
        // Generate nomor inventaris
        if (! $asset->nomor_inventaris) {
            $asset->nomor_inventaris = AssetNumberGenerator::generate(
                $asset->entity,
                $asset->kategori,
                $asset->tahun_pembelian
            );
        }
        
        // Set audit fields
        $asset->created_by = auth()->id();
    }
    
    public function updating(Asset $asset): void
    {
        // Prevent entity change
        if ($asset->isDirty('entity')) {
            throw new \Exception('Entity cannot be changed after creation');
        }
        
        // Log condition change
        if ($asset->isDirty('kondisi')) {
            AssetConditionHistory::create([
                'asset_id' => $asset->id,
                'old_condition' => $asset->getOriginal('kondisi'),
                'new_condition' => $asset->kondisi,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                // reason will be provided via separate field in form
            ]);
        }
        
        $asset->updated_by = auth()->id();
    }
}
```

---

## Sample Data Structures

### Asset Record Example

```json
{
  "id": 1,
  "entity": "LPK",
  "kategori": "elektronik",
  "nomor_inventaris": "LPK-ELK-2024-001",
  "nama_barang": "Laptop Lenovo ThinkPad X1",
  "deskripsi": "Intel Core i7, 16GB RAM, 512GB SSD",
  "jumlah": 3,
  "satuan": "Unit",
  "kondisi": "baik",
  "status_assignment": "assigned",
  "tahun_pembelian": 2024,
  "nilai_pembelian": 15000000.00,
  "lokasi": "Kantor LPK - Ruang Admin",
  "keterangan": "Dibeli untuk staff administrasi",
  "created_by": 1,
  "updated_by": 1,
  "created_at": "2024-01-15 10:30:00",
  "updated_at": "2024-02-08 14:20:00",
  "deleted_at": null
}
```

### Assignment Record Example

```json
{
  "id": 1,
  "asset_id": 1,
  "assignable_type": "App\\Models\\EmployeeLPK",
  "assignable_id": 5,
  "assigned_by": 1,
  "assigned_date": "2024-01-20",
  "return_date": null,
  "return_notes": null,
  "created_at": "2024-01-20 09:00:00",
  "updated_at": "2024-01-20 09:00:00"
}
```

### Condition History Record Example

```json
{
  "id": 1,
  "asset_id": 1,
  "old_condition": "baik",
  "new_condition": "rusak",
  "reason": "Layar retak akibat terjatuh",
  "changed_by": 1,
  "changed_at": "2024-02-05 15:30:00"
}
```

---

## Migration Order

Execute migrations in this sequence:

1. `2026_02_08_000001_create_assets_table.php`
2. `2026_02_08_000002_create_asset_assignments_table.php`
3. `2026_02_08_000003_create_asset_condition_histories_table.php`

All three migrations must run successfully before seeding or testing.

---

## Seeding Strategy

### AssetPermissionsSeeder

Create permissions:
- `view_asset`
- `view_any_asset`
- `create_asset`
- `update_asset`
- `delete_asset`
- `restore_asset`
- `force_delete_asset`

Assign to roles:
- **Admin LPK**: All permissions (scoped to LPK entity)
- **Admin PT**: All permissions (scoped to PT entity)
- **Keuangan LPK/PT**: view_any, view only
- **Pimpinan**: view_any, view only (all entities)

### AssetDemoSeeder

Create 50+ sample assets:
- 25 LPK assets (various categories)
- 25 PT assets (various categories)
- Mix of conditions (80% Baik, 20% Rusak)
- Mix of assignments (60% available, 40% assigned)
- Realistic Indonesian equipment names

---

## Next Phase

Data model complete. Proceed to:
- [quickstart.md](./quickstart.md) - Developer setup guide
- Phase 2: Generate tasks.md with implementation breakdown
