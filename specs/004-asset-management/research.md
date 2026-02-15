# Research: Asset Management Technology Decisions

**Feature**: Employee Asset Management System  
**Branch**: `004-asset-management`  
**Phase**: 0 - Research & Architecture Decisions  
**Date**: February 8, 2026

## Purpose

This document resolves all technical unknowns and edge cases from the specification, providing clear decisions that guide Phase 1 design and implementation.

---

## 1. Entity Assignment Strategy

### Decision: Asset Always Belongs to Single Entity

**Problem**: Can assets transfer between PT and LPK entities?

**Research**:
- Constitution Principle II requires strict entity isolation
- Transfer workflows add complexity (approval, audit trail, ownership changes)
- Real-world scenario: Assets purchased by LPK stay with LPK, PT assets stay with PT

**Decision**: Assets CANNOT transfer between entities in MVP
- `entity` field is immutable after creation (set once at asset creation based on logged-in user)
- If real-world transfer needed, create new asset record in target entity and mark old one as "Transferred" (future enhancement)

**Rationale**: Maintains data integrity, simpler audit trail, aligns with constitution principle II

---

## 2. Employee Reference Strategy

### Decision: Polymorphic Relationship for Flexibility

**Problem**: Asset assignments need to reference both `karyawan_lpk` (LPK employees) and `users` (PT employees)

**Research Options**:
1. **Polymorphic Relationship**: `assignable_type` + `assignable_id` columns
2. **Separate Foreign Keys**: `karyawan_lpk_id` (nullable) + `user_id` (nullable)
3. **Unified Employee Table**: Merge all employees into one table

**Decision**: Use Polymorphic `MorphTo` relationship
```php
// AssetAssignment model
public function assignable(): MorphTo
{
    return $this->morphTo();
}
```

**Rationale**:
- ✅ Laravel best practice for multi-type relationships
- ✅ Scales easily if more employee types added (e.g., contractors)
- ✅ Clean Filament Select component integration with `relationship('assignable')`
- ✅ Doesn't require schema changes to existing employee tables
- ❌ Option 2 (separate FKs): nullable FKs create ambiguity, validation complexity
- ❌ Option 3 (unified table): Breaking change, requires migration of existing data

---

## 3. Asset Identification - Nomor Inventaris Generation

### Decision: Database Sequence with Format Helper

**Problem**: FR-005 requires unique nomor_inventaris with format [PT/LPK]-[KATEGORI]-[TAHUN]-[SEQUENCE]

**Research Options**:
1. **UUID**: Globally unique but not human-readable
2. **Database Auto-Increment**: Simple but no format control
3. **Application-Level Sequence**: Check MAX(sequence) in DB, increment
4. **Database Sequence + Helper**: Use DB sequence, format in app layer

**Decision**: Application-level sequence with database locking
```php
// Helper function
public static function generateNomorInventaris(EntityType $entity, AssetCategory $category, int $year): string
{
    DB::transaction(function () use ($entity, $category, $year) {
        $prefix = "{$entity->value}-{$category->abbreviation()}-{$year}";
        
        // Get last sequence for this prefix
        $lastAsset = Asset::where('nomor_inventaris', 'LIKE', "{$prefix}-%")
            ->lockForUpdate()  // Prevent race conditions
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_inventaris, "-", -1) AS UNSIGNED) DESC')
            ->first();
        
        $sequence = $lastAsset 
            ? ((int) explode('-', $lastAsset->nomor_inventaris)[3]) + 1 
            : 1;
        
        return sprintf('%s-%03d', $prefix, $sequence); // LPK-ELK-2024-001
    });
}
```

**Rationale**:
- ✅ Human-readable format for physical labeling
- ✅ Business-meaningful structure (entity, category, year visible)
- ✅ Transaction + lockForUpdate prevents duplicate sequences
- ✅ No database schema changes (sequence management in app code)

**Edge Case Handling**:
- Concurrent inserts: `lockForUpdate()` ensures atomic sequence increment
- Missing sequences: Acceptable (if asset deleted, gap in sequence is audit trail)

---

## 4. Asset with Jumlah = 0 (Zero Quantity)

### Decision: Soft Delete Instead of Zero Quantity

**Problem**: How to handle assets that are depleted, discarded, or fully assigned?

**Research Scenarios**:
- Asset fully consumed (e.g., office supplies depleted)
- Asset discarded/disposed
- Asset temporarily out of circulation (maintenance)

**Decision**: 
- `jumlah` field MUST be >= 1 when active (FR-023 validation)
- When asset is fully depleted or disposed → Soft Delete (`deleted_at`)
- Add `keterangan` field to explain deletion reason
- Soft-deleted assets remain in audit trail and can be restored if needed

**Rationale**:
- ✅ Clearer data model (active assets always have quantity)
- ✅ Soft delete aligns with FR-027 requirement
- ✅ Avoids filtering `WHERE jumlah > 0` on every query
- ✅ Disposal reason captured in deletion notes via activity log

---

## 5. Duplicate Asset Names

### Decision: Unique Nomor Inventaris, Allow Duplicate Names

**Problem**: Multiple "Laptop Lenovo" items - how to differentiate?

**Research**:
- Real-world scenario: Same product purchased multiple times
- Nomor inventaris is the true unique identifier (like serial number)

**Decision**:
- `nama_barang` field is NOT unique (same name allowed)
- `nomor_inventaris` MUST be unique (database unique constraint)
- UI displays both name + nomor_inventaris for clarity
- Search works on both fields

**Example**:
```
LPK-ELK-2024-001 | Laptop Lenovo | 3 units
LPK-ELK-2024-002 | Laptop Lenovo | 2 units (purchased later)
```

**Rationale**: Real-world asset management allows same product names. Nomor inventaris provides unique identification.

---

## 6. Bulk Import for Existing Data

### Decision: Out of MVP, Manual Entry Only

**Problem**: Organization may have 100+ existing assets in Excel/CSV

**Research Options**:
1. **CSV Import**: Read file, validate, bulk insert
2. **Excel Import**: Use maatwebsite/excel package
3. **Manual Entry**: Admin inputs one-by-one
4. **Seeder for Demo**: Load sample data via database seeder

**Decision for MVP**: Manual entry + demo seeder only
- Import functionality OUT OF SCOPE for MVP (noted in spec Out of Scope #7)
- Create comprehensive demo seeder with 50+ sample assets for testing
- Phase 2 enhancement: Add Filament Import action using maatwebsite/excel

**Rationale**:
- ✅ Simpler MVP delivery
- ✅ Validates UI/UX with real data entry workflow
- ✅ Import can be added later without schema changes
- ✅ Some organizations have small asset count (< 50 items), manual entry acceptable

---

## 7. Photo/Documentation Attachments

### Decision: Out of MVP, Text Description Only

**Problem**: Should assets have photos, invoices, warranty documents?

**Research**:
- Photo upload: Storage (private disk), thumbnail generation, file validation
- Document upload: File type restrictions, virus scanning, versioning

**Decision for MVP**: Text-based only
- `deskripsi` field (text, 1000 chars) for detailed description
- `keterangan` field (text, nullable) for notes
- Photo/document upload OUT OF SCOPE (spec Out of Scope #4)

**Future Enhancement** (Phase 2):
- Add `asset_documents` table with `morphMany` relationship
- Store files in `storage/app/private/asset-documents/`
- File types: PDF, JPG, PNG (invoices, photos, warranties)
- Use Filament FileUpload component with `multiple()` modifier

**Rationale**: Text descriptions sufficient for MVP. Visual documentation adds storage complexity not critical for initial release.

---

## 8. Depreciation Tracking

### Decision: Out of MVP, Static Purchase Value

**Problem**: Should system calculate asset depreciation over time?

**Research**:
- Accounting depreciation: Straight-line, declining balance methods
- Requires depreciation rules per asset category
- Tax implications vary by jurisdiction

**Decision for MVP**: NO depreciation calculation
- `nilai_pembelian` field stores original purchase price (static, never changes)
- Current book value = purchase value (no depreciation applied)
- Reports show original purchase value only

**Future Enhancement** (if accounting integration needed):
- Add `depreciation_method`, `useful_life_years`, `salvage_value` fields
- Calculate current book value dynamically
- Integration with accounting software

**Rationale**: Most organizations track assets for custody/accountability, not accounting. Depreciation adds complexity requiring financial expertise.

---

## 9. Multi-Location Tracking

### Decision: Single Text Location Field (MVP)

**Problem**: Assets may be in different offices, floors, rooms

**Research Options**:
1. **Simple Text Field**: 'Kantor Pusat - Lantai 2 - Ruang IT'
2. **Hierarchical Locations Table**: `` relationships
3. **Full Location Management**: Separate location module with transfers

**Decision for MVP**: Simple text field
```php
$table->string('lokasi', 255)->nullable();  // Free-text location description
```

**UI**: TextInput with suggestions/autocomplete (Filament datalist) for common locations

**Future Enhancement** (Phase 2):
- Create `locations` table with hierarchy (office → floor → room)
- Add `location_id` foreign key to assets
- Track location transfers with audit trail

**Rationale**:
- ✅ Flexible for different organization structures
- ✅ No upfront location setup required
- ✅ Adequate for MVP custody tracking
- ✅ Easy to upgrade to structured locations later

---

## 10. Maintenance Scheduling

### Decision: Manual Condition Updates Only (MVP)

**Problem**: Should system send reminders for scheduled maintenance?

**Research**:
- Preventive maintenance schedules (e.g., AC serviced every 6 months)
- Calendar integration, email notifications
- Maintenance vendor management

**Decision for MVP**: Manual tracking only
- Admin updates `kondisi` when maintenance performed (US2)
- `AssetConditionHistory` records all condition changes with dates and notes
- NO automatic scheduling or reminders in MVP

**Future Enhancement** (Phase 3):
- Add `next_maintenance_date`, `maintenance_interval_days` fields
- Scheduled job checks daily and sends notifications
- Dashboard widget shows "Maintenance Due" alerts

**Rationale**: Manual condition updates sufficient for MVP. Automated scheduling is "nice to have" feature that doesn't block core asset tracking functionality.

---

## 11. Warranty Tracking

### Decision: Out of MVP Scope

**Problem**: Track warranty expiration, vendor info, claim process?

**Research**:
- Warranty dates, vendor contact, claim procedures
- Alerts when warranty expiring
- Document storage (warranty certificates)

**Decision for MVP**: Not included
- Can be noted in `keterangan` text field if needed
- OUT OF SCOPE (spec Out of Scope #10)

**Future Enhancement**:
- Add `warranty_start_date`, `warranty_duration_months`, `vendor_name`, `vendor_contact` fields
- Alert when warranty expires soon
- Link to warranty documents (if document upload added)

**Rationale**: Warranty management is peripheral to core asset tracking. Most organizations track this in separate systems or physical files.

---

## 12. QR Code / Barcode for Physical Labels

### Decision: Out of MVP, Print Nomor Inventaris Instead

**Problem**: Physical asset labeling for easy identification

**Research Options**:
1. **QR Code Generation**: Encode nomor_inventaris, generate QR image
2. **Barcode Generation**: Linear barcode (CODE39, CODE128)
3. **Print Templates**: PDF labels with asset info
4. **No Physical Labels**: Digital-only tracking

**Decision for MVP**: Digital tracking only, physical labels via manual printing
- Display `nomor_inventaris` prominently in UI (large font, copyable)
- Admin can copy-paste nomor_inventaris to create labels in Excel/Word
- QR/barcode generation OUT OF SCOPE (spec Out of Scope #5)

**Future Enhancement** (Phase 3):
- Generate QR codes using `simplesoftwareio/simple-qrcode` package
- Bulk print labels as PDF (A4 sheet with multiple labels)
- Mobile scanning app (scan QR → view asset details)

**Rationale**: Physical labeling nice-to-have but not blocking. Organizations can print labels manually using nomor_inventaris. QR codes add value but require additional testing and mobile interface.

---

## Technology Stack Finalization

### Confirmed Technologies

**Core Framework**:
- Laravel 11 (existing)
- PHP 8.4.5 (existing)
- MySQL/MariaDB (existing)

**Admin Panel**:
- Filament v4 (existing)
- Livewire v3 (existing)
- Filament Shield for permissions (existing)

**Supporting Packages**:
- Spatie Laravel Activity Log (existing) - audit trails
- Spatie Laravel Permission (existing, via Shield) - RBAC
- *Optional*: maatwebsite/laravel-excel (for US5 export) - may be installed if not present

**No New Infrastructure**:
- No Redis required (activity log uses database)
- No queue workers required (all synchronous operations)
- No external APIs (internal-only system)

---

## Implementation Patterns

### Entity Scoping Pattern

Apply entity isolation at multiple levels:

```php
// 1. Global Eloquent Scope (automatic filtering)
class Asset extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('entity', function (Builder $query) {
            $user = auth()->user();
            if ($user && ! $user->hasRole('Pimpinan')) {
                // Non-Pimpinan users see only their entity's assets
                $query->where('entity', $user->entity ?? EntityType::LPK);
            }
            // Pimpinan sees all
        });
    }
}

// 2. Filament Resource Query (UI-level filtering)
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()->withoutGlobalScope('entity');
    
    $user = auth()->user();
    if (! $user->hasRole('Pimpinan')) {
        $query->where('entity', $user->entity);
    }
    
    return $query;
}

// 3. Policy Authorization (action-level)
public function update(User $user, Asset $asset): bool
{
    // Must be same entity + have permission
    return $user->can('update_asset') && $user->entity === $asset->entity;
}
```

### Audit Logging Pattern

```php
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class Asset extends Model
{
    use LogsActivityTrait;
    
    protected static $logAttributes = [
        'nama_barang', 'jumlah', 'kondisi', 'nilai_pembelian',
        'tahun_pembelian', 'kategori', 'lokasi', 'keterangan'
    ];
    
    protected static $logOnlyDirty = true; // Log only changed fields
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Assignment Pattern

```php
// Polymorphic assignment tracking
$assignment = AssetAssignment::create([
    'asset_id' => $asset->id,
    'assignable_type' => EmployeeLPK::class,  // or User::class for PT
    'assignable_id' => $employee->id,
    'assigned_by' => auth()->id(),
    'assigned_date' => now(),
]);

// Eager load in queries to avoid N+1
$assets = Asset::with(['currentAssignment.assignable'])->get();
```

---

## Performance Optimization Strategy

Based on Success Criteria performance requirements:

### Database Indexes

```php
// assets table
$table->index('entity');               // Entity filtering (used in every query)
$table->index('kategori');             // Category filtering
$table->index('kondisi');              // Condition filtering
$table->index(['entity', 'kategori']); // Composite for combined filters
$table->unique('nomor_inventaris');    // Uniqueness + lookup by inventory number
$table->index('created_at');           // Sorting by date

// asset_assignments table
$table->index('asset_id');             // Asset lookups
$table->index(['assignable_type', 'assignable_id']); // Polymorphic lookups
$table->index('return_date');          // Filter active assignments (WHERE return_date IS NULL)
```

### Query Optimization

```php
// List page - eager load relationships
Asset::with([
    'creator:id,name',
    'updater:id,name',
    'currentAssignment.assignable',  // Polymorphic eager load
])
->select([  // Only needed columns
    'id', 'entity', 'kategori', 'nama_barang', 'nomor_inventaris',
    'jumlah', 'kondisi', 'nilai_pembelian', 'created_at', 'created_by'
])
->paginate(50);
```

### Caching Strategy (Future, if needed)

```php
// Dashboard statistics - cache for 5 minutes
Cache::remember('asset-stats-' . auth()->user()->entity, 300, function () {
    return [
        'total_assets' => Asset::count(),
        'total_value' => Asset::sum('nilai_pembelian'),
        'by_condition' => Asset::groupBy('kondisi')->selectRaw('kondisi, COUNT(*) as count')->pluck('count', 'kondisi'),
        'by_category' => Asset::groupBy('kategori')->selectRaw('kategori, COUNT(*) as count')->pluck('count', 'kategori'),
    ];
});
```

---

## Testing Strategy

### Test Database Setup

**CRITICAL**: Do NOT use `RefreshDatabase` trait (per guidelines)

```php
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AssetManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();  // Start transaction
    }

    protected function tearDown(): void
    {
        DB::rollBack();  // Rollback all changes
        parent::tearDown();
    }
}
```

### Test Coverage Requirements

**Feature Tests** (must cover):
- US1: CRUD operations with entity auto-assignment
- US2: Condition history tracking with audit trail
- US3: Employee assignments (polymorphic relationships)
- US4: Entity isolation (Admin LPK cannot see PT assets)
- US5: Statistics and Excel export

**Unit Tests**:
- Nomor inventaris generation logic
- Entity scope filters
- Assignment status calculations

### Example Test Structure

```php
public function test_admin_lpk_can_only_see_lpk_assets(): void
{
    $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
    $adminLPK->assignRole('Admin LPK');
    
    $lpkAsset = Asset::factory()->create(['entity' => EntityType::LPK]);
    $ptAsset = Asset::factory()->create(['entity' => EntityType::PT]);
    
    $this->actingAs($adminLPK);
    
    $response = livewire(ListAssets::class)
        ->assertCanSeeTableRecords([$lpkAsset])
        ->assertCanNotSeeTableRecords([$ptAsset]);
}
```

---

## Security Considerations

### Authorization Layers

1. **Route Middleware**: `auth` + Filament built-in auth
2. **Policy Gates**: Entity matching + permission checks
3. **Eloquent Scopes**: Automatic query filtering
4. **UI Guards**: Filament `authorize()` method on actions

### Data Validation

```php
// StoreAssetRequest
public function rules(): array
{
    return [
        'nama_barang' => ['required', 'string', 'max:255'],
        'jumlah' => ['required', 'integer', 'min:1'],  // Must be positive
        'satuan' => ['required', 'string', 'max:50'],
        'kondisi' => ['required', Rule::enum(AssetCondition::class)],
        'kategori' => ['required', Rule::enum(AssetCategory::class)],
        'tahun_pembelian' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],  // Cannot be future year
        'nilai_pembelian' => ['required', 'numeric', 'min:0'],  // Allow zero for donated assets
        'lokasi' => ['nullable', 'string', 'max:255'],
        'keterangan' => ['nullable', 'string', 'max:1000'],
    ];
}
```

### SQL Injection Prevention

Laravel's Eloquent ORM provides automatic parameterization. For raw queries (if any), always use:
```php
DB::select('SELECT * FROM assets WHERE entity = ?', [$entity]);
```

---

## Conclusion

All research complete. No NEEDS CLARIFICATION items remain. Ready to proceed to Phase 1 (Data Model & Design).

**Key Decisions Summary**:
1. ✅ Entity immutable after creation (no transfers)
2. ✅ Polymorphic relationships for employee assignments
3. ✅ App-level sequence for nomor_inventaris with DB locking
4. ✅ Soft delete for zero-quantity assets
5. ✅ Duplicate names OK, unique nomor_inventaris enforced
6. ✅ Manual entry only (no bulk import in MVP)
7. ✅ Text descriptions only (no photo uploads in MVP)
8. ✅ No depreciation calculation (static values)
9. ✅ Simple text location field (no hierarchy)
10. ✅ Manual maintenance tracking (no scheduling)
11. ✅ No warranty tracking (out of scope)
12. ✅ No QR/barcode generation (print nomor_inventaris manually)

**Technology Stack**: Laravel 11 + Filament v4 + existing packages (no new infrastructure)

**Next Phase**: Generate data-model.md with complete database schema
