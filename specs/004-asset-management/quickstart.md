# Quickstart Guide: Asset Management System

**Feature**: Employee Asset Management System  
**Branch**: `004-asset-management`  
**Target Developers**: Backend engineers implementing PHP/Laravel/Filament module  
**Est. Setup Time**: 15-20 minutes

---

## Prerequisites

Ensure you have:
- ✅ PHP 8.4.5+ installed
- ✅ Composer installed
- ✅ Local database (MySQL/MariaDB) running
- ✅ Existing SIT_LPK application cloned
- ✅ Access to create branches and run migrations

---

## Step 1: Branch Setup

```bash
# Navigate to project root
cd /path/to/SIT_LPK

# Ensure you're on main/master with latest changes
git checkout main
git pull origin main

# Create and checkout feature branch
git checkout -b 004-asset-management

# Verify branch
git branch --show-current
# Expected output: 004-asset-management
```

---

## Step 2: Verify Dependencies

**Good news**: No new Composer or NPM packages required for MVP! 

All dependencies already exist:
- ✅ Laravel 11 (framework)
- ✅ Filament v4 (admin panel)
- ✅ Spatie Activity Log (audit trail)
- ✅ Filament Shield (RBAC)

Run this to confirm:

```bash
composer show | grep -E "laravel/framework|filament/filament|spatie/laravel-activitylog"
```

Expected output:
```
filament/filament                      v4.x.x
laravel/framework                      v11.x.x  
spatie/laravel-activitylog            4.x.x
```

---

## Step 3: Review Specification Documents

Read these in order before coding:

1. **[spec.md](./spec.md)** (10 min) - Feature requirements, user stories, acceptance criteria
2. **[research.md](./research.md)** (15 min) - Technical decisions, edge case handling, architecture rationale
3. **[data-model.md](./data-model.md)** (20 min) - Complete database schema, relationships, validation rules

**Key Takeaways**:
- Entity isolation: Admin LPK sees only LPK assets, Admin PT sees only PT assets
- Polymorphic relationships for employee assignments
- Nomor inventaris auto-generated: `[PT/LPK]-[KATEGORI]-[TAHUN]-[SEQUENCE]`
- Soft delete when quantity becomes 0
- MVP scope: US1 (Asset Registration) + US4 (Entity Isolation)

---

## Step 4: Create Database Migrations

Generate migration files using Artisan:

```bash
# Migration 1: Assets table
php artisan make:migration create_assets_table --no-interaction

# Migration 2: Asset Assignments table
php artisan make:migration create_asset_assignments_table --no-interaction

# Migration 3: Asset Condition Histories table
php artisan make:migration create_asset_condition_histories_table --no-interaction
```

**Copy schema from [data-model.md](./data-model.md)** into generated migration files.

---

## Step 5: Create Enums

Generate enum files:

```bash
# Asset Category enum
php artisan make:enum AssetCategory --no-interaction

# Asset Condition enum
php artisan make:enum AssetCondition --no-interaction

# Asset Assignment Status enum
php artisan make:enum AssetAssignmentStatus --no-interaction
```

**Copy enum code from [data-model.md](./data-model.md#enums)** into generated files.

---

## Step 6: Create Models

Generate Eloquent model files:

```bash
# Asset model
php artisan make:model Asset --no-interaction

# AssetAssignment model
php artisan make:model AssetAssignment --no-interaction

# AssetConditionHistory model
php artisan make:model AssetConditionHistory --no-interaction
```

**Define relationships, scopes, and business logic** per [data-model.md](./data-model.md#model-relationships-summary).

---

## Step 7: Create Observer

Generate observer for Asset model:

```bash
php artisan make:observer AssetObserver --model=Asset --no-interaction
```

**Implement logic for**:
- Auto-generate `nomor_inventaris`
- Auto-set `entity` from authenticated user
- Log condition changes to `asset_condition_histories`
- Enforce entity immutability

Register observer in `App\Providers\EventServiceProvider`:

```php
use App\Models\Asset;
use App\Observers\AssetObserver;

public function boot(): void
{
    Asset::observe(AssetObserver::class);
}
```

---

## Step 8: Run Migrations

Execute migrations in development environment:

```bash
# Run all pending migrations
php artisan migrate

# Verify tables created
php artisan db:show --table=assets
php artisan db:show --table=asset_assignments
php artisan db:show --table=asset_condition_histories
```

**Rollback if needed**:
```bash
php artisan migrate:rollback --step=3
```

---

## Step 9: Create Seeders

Generate seeder files:

```bash
# Permissions seeder
php artisan make:seeder AssetPermissionsSeeder --no-interaction

# Demo data seeder
php artisan make:seeder AssetDemoSeeder --no-interaction
```

**AssetPermissionsSeeder** should create:
- Permissions: `view_asset`, `view_any_asset`, `create_asset`, `update_asset`, `delete_asset`, `restore_asset`, `force_delete_asset`
- Assign to roles: Admin LPK (all for LPK), Admin PT (all for PT), Keuangan (view only), Pimpinan (view all)

**AssetDemoSeeder** should create:
- 25 LPK assets (mix of categories, conditions, assignments)
- 25 PT assets (mix of categories, conditions, assignments)
- Realistic Indonesian equipment names

Run seeders:

```bash
php artisan db:seed --class=AssetPermissionsSeeder
php artisan db:seed --class=AssetDemoSeeder
```

---

## Step 10: Create Filament Resource

Generate Filament Resource for Asset CRUD:

```bash
php artisan make:filament-resource Asset --generate --no-interaction
```

This creates:
- `app/Filament/Resources/AssetResource.php`
- `app/Filament/Resources/AssetResource/Pages/ListAssets.php`
- `app/Filament/Resources/AssetResource/Pages/CreateAsset.php`
- `app/Filament/Resources/AssetResource/Pages/EditAsset.php`

**Customize as per spec.md FR-001 to FR-029**:
- Add entity scoping (global scope)
- Configure form fields (kategori select, kondisi badges, etc.)
- Add table filters (entity, kategori, kondisi, assignment status)
- Implement search on nama_barang, nomor_inventaris, lokasi
- Add bulk actions (export, delete)

---

## Step 11: Create Policy

Generate policy for authorization:

```bash
php artisan make:policy AssetPolicy --model=Asset --no-interaction
```

**Implement entity-based authorization**:
- Admin LPK: Full access to LPK assets only
- Admin PT: Full access to PT assets only
- Pimpinan: View all assets (PT + LPK), no edit/delete
- Keuangan: View only (scoped to own entity)

Register in `AuthServiceProvider`:

```php
use App\Models\Asset;
use App\Policies\AssetPolicy;

protected $policies = [
    Asset::class => AssetPolicy::class,
];
```

---

## Step 12: Run Code Formatter

Ensure code style compliance:

```bash
vendor/bin/pint --dirty
```

Fix any formatting issues reported.

---

## Step 13: Run Tests

Create and run feature tests:

```bash
# Create test file
php artisan make:test --phpunit AssetManagementTest --no-interaction

# Run tests (use transactions, not RefreshDatabase)
php artisan test --filter=AssetManagementTest --compact
```

**Test coverage should include**:
- ✅ US1: Create asset with auto-generated nomor_inventaris
- ✅ US1: List assets scoped to user entity
- ✅ US1: Search assets by name, location
- ✅ US1: Update asset details
- ✅ US1: Soft delete asset
- ✅ US4: Admin LPK cannot see PT assets
- ✅ US4: Admin PT cannot see LPK assets
- ✅ US4: Pimpinan can see all assets

**Important**: Use database transactions in tests:

```php
use Illuminate\Support\Facades\DB;

protected function setUp(): void
{
    parent::setUp();
    DB::beginTransaction();
}

protected function tearDown(): void
{
    DB::rollBack();
    parent::tearDown();
}
```

---

## Common Development Tasks

### Task 1: Create a New Asset

Via Filament UI:
1. Navigate to `/admin/assets`
2. Click "New Asset" button
3. Fill form:
   - Entity: Auto-set based on logged-in user
   - Kategori: Select from dropdown
   - Nama Barang: Enter name
   - Jumlah: Enter quantity (>= 1)
   - Satuan: Enter unit (Unit, Set, Buah, etc.)
   - Kondisi: Select "Baik" or "Rusak"
   - Tahun Pembelian: Select year
   - Nilai Pembelian: Enter price in IDR
   - Lokasi: Optional location
   - Keterangan: Optional notes
4. Save → Nomor inventaris auto-generated

Via Tinker:
```php
php artisan tinker

use App\Models\Asset;
use App\Enums\EntityType;
use App\Enums\AssetCategory;
use App\Enums\AssetCondition;

$asset = Asset::create([
    'entity' => EntityType::LPK->value,
    'kategori' => AssetCategory::Elektronik->value,
    'nama_barang' => 'Laptop HP EliteBook',
    'jumlah' => 5,
    'satuan' => 'Unit',
    'kondisi' => AssetCondition::Baik->value,
    'tahun_pembelian' => 2024,
    'nilai_pembelian' => 12000000,
    'lokasi' => 'Kantor LPK - Ruang IT',
    'keterangan' => 'Untuk staff IT',
    'created_by' => 1,
]);

echo "Created: " . $asset->nomor_inventaris;
// Output: LPK-ELK-2024-001
```

### Task 2: Assign Asset to Employee

Via Filament Action:
1. Navigate to asset detail page
2. Click "Assign" action button
3. Select employee from dropdown (LPK staff or PT staff)
4. Select assignment date
5. Save → Asset status changes to "Assigned"

Via code:
```php
use App\Models\Asset;
use App\Models\EmployeeLPK;

$asset = Asset::find(1);
$employee = EmployeeLPK::find(5);
$assignedBy = auth()->user();

$assignment = $asset->assignTo($employee, $assignedBy);
```

### Task 3: Update Asset Condition

Via Filament Form:
1. Navigate to asset edit page
2. Change "Kondisi" field
3. Fill "Alasan Perubahan Kondisi" field (required when changing condition)
4. Save → Condition history record auto-created

Via code:
```php
use App\Models\Asset;
use App\Enums\AssetCondition;

$asset = Asset::find(1);
$asset->updateCondition(
    newCondition: AssetCondition::Rusak,
    reason: 'Layar retak akibat terjatuh',
    changedBy: auth()->user()
);
```

### Task 4: View Condition History

Via Filament Relation Manager:
1. Navigate to asset detail page
2. Click "Condition History" tab
3. View timeline of all condition changes

Via code:
```php
$asset = Asset::with('conditionHistories')->find(1);

foreach ($asset->conditionHistories as $history) {
    echo "{$history->changed_at}: {$history->old_condition} → {$history->new_condition} ({$history->reason})\n";
}
```

### Task 5: Export Assets Report

Via Filament Bulk Action:
1. Navigate to assets list
2. Select assets (or select all)
3. Click "Export" bulk action
4. Choose format (Excel, CSV)
5. Download file

Exports include: nomor inventaris, nama barang, kategori, jumlah, kondisi, lokasi, nilai pembelian, tahun pembelian.

---

## Troubleshooting

### Issue 1: Nomor Inventaris Not Auto-Generated

**Symptom**: When creating asset, nomor_inventaris is null or empty.

**Solution**:
1. Verify `AssetObserver` is registered in `EventServiceProvider`
2. Check observer `creating()` method implementation
3. Ensure `AssetNumberGenerator` helper exists and works
4. Test in tinker:
   ```php
   use App\Helpers\AssetNumberGenerator;
   
   $number = AssetNumberGenerator::generate('LPK', 'elektronik', 2024);
   echo $number;  // Should output: LPK-ELK-2024-001
   ```

### Issue 2: Entity Isolation Not Working

**Symptom**: Admin LPK sees PT assets or vice versa.

**Solution**:
1. Verify global scope applied in `Asset` model:
   ```php
   protected static function booted(): void
   {
       static::addGlobalScope('entity', function (Builder $builder) {
           $user = auth()->user();
           if ($user && $user->hasRole(['Admin LPK', 'Admin PT', 'Keuangan LPK', 'Keuangan PT'])) {
               $builder->where('entity', $user->entity);
           }
       });
   }
   ```
2. Check Filament Resource `getEloquentQuery()` method also applies entity filter
3. Verify policy checks entity match in `view()` and `update()` methods

### Issue 3: Tests Failing - Database Not Clean

**Symptom**: Tests fail due to duplicate data or foreign key violations.

**Solution**:
1. Ensure tests use transactions (NOT `RefreshDatabase` trait):
   ```php
   protected function setUp(): void
   {
       parent::setUp();
       DB::beginTransaction();
   }
   
   protected function tearDown(): void
   {
       DB::rollBack();
       parent::tearDown();
   }
   ```
2. Run tests in isolation:
   ```bash
   php artisan test --filter=specific_test_name
   ```

### Issue 4: Polymorphic Relationship Error

**Symptom**: Error when accessing `$assignment->assignable` or assigning to employee.

**Solution**:
1. Verify `AssetAssignment` model has correct relationship definition:
   ```php
   public function assignable()
   {
       return $this->morphTo();
   }
   ```
2. Check `assignable_type` stores full class name with namespace:
   ```php
   // Correct
   'assignable_type' => 'App\\Models\\EmployeeLPK',
   
   // Incorrect
   'assignable_type' => 'EmployeeLPK',
   ```
3. Use `::class` constant when assigning:
   ```php
   $assignment->assignable_type = EmployeeLPK::class;
   ```

### Issue 5: Pint Formatting Failures

**Symptom**: `vendor/bin/pint` reports style violations.

**Solution**:
1. Run Pint to auto-fix (not just test):
   ```bash
   vendor/bin/pint --dirty
   ```
2. If violations persist, check `pint.json` config:
   ```json
   {
       "preset": "laravel"
   }
   ```

---

## Performance Considerations

### Database Indexes

Ensure migrations include these indexes for optimal query performance:

```php
// In create_assets_table migration
$table->index('entity');  // Filter by entity
$table->index('kategori');  // Filter by category
$table->index('kondisi');  // Filter by condition
$table->index('status_assignment');  // Filter assigned vs available
$table->index(['entity', 'kategori']);  // Composite for common filter combo
$table->index('created_at');  // Sort by newest/oldest

// In create_asset_assignments_table migration
$table->index('asset_id');  // Join to assets
$table->index(['assignable_type', 'assignable_id']);  // Polymorphic lookup
$table->index('return_date');  // Filter active assignments (NULL)

// In create_asset_condition_histories_table migration
$table->index('asset_id');  // Join to assets
$table->index('changed_at');  // Sort timeline
```

### Eager Loading

Prevent N+1 queries by eager loading relationships:

```php
// In Filament Resource table query
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['currentAssignment.assignable', 'creator', 'updater'])
        ->withCount('assignments');
}
```

---

## Next Steps

After completing setup:

1. **Implement US1 (Asset Registration)**: Full CRUD for assets
2. **Implement US4 (Entity Isolation)**: Ensure policies and scopes work correctly
3. **Write Tests**: Cover all functional requirements (FR-001 to FR-029)
4. **Test in Browser**: Use demo data to verify UI/UX
5. **Code Review**: Request review from team lead
6. **Merge to Main**: After approval, merge branch

For detailed task breakdown, see [tasks.md](./tasks.md) (generated via `/speckit.tasks` command).

---

## Useful Commands Reference

```bash
# Database
php artisan migrate                    # Run migrations
php artisan migrate:rollback           # Rollback last batch
php artisan migrate:fresh --seed       # Reset and reseed (DEV ONLY)
php artisan db:seed --class=AssetDemoSeeder  # Run specific seeder

# Code Generation
php artisan make:model {Name}          # Create model
php artisan make:migration {name}      # Create migration
php artisan make:seeder {Name}Seeder   # Create seeder
php artisan make:filament-resource {Name}  # Create Filament resource
php artisan make:policy {Name}Policy   # Create policy
php artisan make:test {Name}Test       # Create PHPUnit test

# Testing
php artisan test                       # Run all tests
php artisan test --filter={name}       # Run specific test
php artisan test --compact             # Compact output

# Code Quality
vendor/bin/pint                        # Format code
vendor/bin/pint --dirty                # Format only changed files
vendor/bin/pint --test                 # Check without fixing

# Debug
php artisan tinker                     # Interactive shell
php artisan route:list                 # List all routes
php artisan db:show                    # Show database info
```

---

## Questions or Issues?

- **Spec unclear?** → Review [spec.md](./spec.md) and [research.md](./research.md)
- **ERD questions?** → Check [data-model.md](./data-model.md)
- **Implementation stuck?** → Ask team lead or senior developer
- **Bug found?** → Create issue in project tracker with reproduction steps

---

**Version**: 1.0  
**Last Updated**: February 8, 2026  
**Maintained by**: SIT_LPK Development Team
