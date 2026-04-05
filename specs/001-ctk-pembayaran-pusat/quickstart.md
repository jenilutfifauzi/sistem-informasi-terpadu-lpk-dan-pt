# Quickstart: Pembayaran ke Pusat

**Feature**: 001-ctk-pembayaran-pusat  
**Date**: 2026-04-05

## Prerequisites

- PHP 8.4.x installed
- Composer installed
- MySQL/MariaDB running
- Project dependencies installed (`composer install`)
- Database migrated (`php artisan migrate`)

## Quick Setup

### 1. Create Migration

```bash
php artisan make:migration create_pembayaran_pusat_table
```

### 2. Create Model

```bash
php artisan make:model PembayaranPusat
```

### 3. Create Filament Resource

```bash
php artisan make:filament-resource PembayaranPusat --generate --view
```

Then reorganize into the Pages/Schemas/Tables structure.

### 4. Run Migration

```bash
php artisan migrate
```

### 5. Seed Test Data (Optional)

```bash
php artisan make:factory PembayaranPusatFactory
php artisan db:seed --class=PembayaranPusatSeeder
```

## File Checklist

Create these files in order:

| # | File | Purpose |
|---|------|---------|
| 1 | `database/migrations/XXXX_create_pembayaran_pusat_table.php` | Database table |
| 2 | `app/Models/PembayaranPusat.php` | Eloquent model |
| 3 | `app/Filament/Resources/PembayaranPusat/PembayaranPusatResource.php` | Main resource |
| 4 | `app/Filament/Resources/PembayaranPusat/Pages/ListPembayaranPusat.php` | List page |
| 5 | `app/Filament/Resources/PembayaranPusat/Pages/CreatePembayaranPusat.php` | Create page |
| 6 | `app/Filament/Resources/PembayaranPusat/Pages/EditPembayaranPusat.php` | Edit page |
| 7 | `app/Filament/Resources/PembayaranPusat/Pages/ViewPembayaranPusat.php` | View page |
| 8 | `app/Filament/Resources/PembayaranPusat/Schemas/PembayaranPusatForm.php` | Form schema |
| 9 | `app/Filament/Resources/PembayaranPusat/Tables/PembayaranPusatTable.php` | Table config |
| 10 | `app/Filament/Resources/PembayaranPusat/Widgets/PembayaranPusatStatsOverview.php` | Stats widget |
| 11 | `tests/Feature/PembayaranPusatTest.php` | Feature tests |

## Verification Commands

```bash
# Check migration status
php artisan migrate:status

# Verify model exists
php artisan tinker --execute="new App\Models\PembayaranPusat"

# Check Filament resources
php artisan filament:resources

# Run tests
php artisan test --filter=PembayaranPusat

# Format code
vendor/bin/pint
```

## Key Patterns to Follow

### Entity Scope (copy from Asset.php)
```php
protected static function booted(): void
{
    static::addGlobalScope('entity', function (Builder $builder) {
        // ... same pattern as Asset
    });
}
```

### Auto-set created_by (in CreatePage)
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['created_by'] = Auth::id();
    $data['entity'] = Auth::user()->entity;
    return $data;
}
```

### Auto-set updated_by (in EditPage)
```php
protected function mutateFormDataBeforeSave(array $data): array
{
    $data['updated_by'] = Auth::id();
    return $data;
}
```

## Testing Checklist

- [ ] Can create pembayaran with all required fields
- [ ] Validation rejects nominal ≤ 0
- [ ] Validation rejects future dates
- [ ] File upload accepts JPG/PNG/PDF only
- [ ] File upload rejects files > 10MB
- [ ] Entity isolation works (LPK user sees only LPK data)
- [ ] Pimpinan sees all data
- [ ] Edit works correctly
- [ ] Delete (soft) works correctly
- [ ] Restore works correctly
- [ ] Stats widget shows correct totals
- [ ] Search by CTK name works
- [ ] Date filter works
