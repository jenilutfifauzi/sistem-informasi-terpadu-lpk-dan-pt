# Quickstart: Data Siswa LPK Administration

## Objective

Implement a new Filament admin resource for Siswa LPK records based on the registration sheet structure defined in the spec.

## Implementation Steps

1. Create the model, migration, and factory:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
herd php artisan make:model SiswaLPK -mf --no-interaction
```

2. Create the Filament resource and default pages:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
herd php artisan make:filament-resource SiswaLPK --no-interaction
```

3. Create the Filament exporter:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
herd php artisan make:filament-exporter SiswaLPKExport --no-interaction
```

4. Create the policy and PHPUnit test classes:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
herd php artisan make:policy SiswaLPKPolicy --model=SiswaLPK --no-interaction
herd php artisan make:test --phpunit SiswaLPKResourceTest --no-interaction
herd php artisan make:test --unit SiswaLPKModelTest --no-interaction
```

5. Implement the migration with business fields, a unique index on `nomor_induk` that prevents reuse after soft delete, audit foreign keys, timestamps, and soft deletes.

6. Implement the `SiswaLPK` model with fillable attributes, date casts, soft deletes, activity logging, and `creator` or `updater` relationships.

7. Implement the Filament resource with:
   - create, list, view, and edit pages
   - validation for required fields
   - uniqueness handling for `nomor_induk`
   - date rule enforcing `tanggal_lahir <= tanggal_masuk`
   - guidance that combined source notes for place of birth and birth date must be separated before save
   - search support for `nomor_induk`, `nama_siswa`, and `program_pendidikan`
   - export support from the admin list

8. Implement the exporter and ensure the export action produces an Excel file for the selected dataset.

9. Implement the policy and ensure Filament Shield permissions cover `view_any`, `view`, `create`, `update`, and export behavior.

10. Add automated tests for:
   - successful create with email present
   - successful create with email omitted
   - duplicate `nomor_induk` rejection
   - invalid birth date versus enrollment date rejection
   - search by student number, name, and program
   - Excel export from the list view
   - model soft delete and activity log behavior

## Verification

Run the smallest relevant test set first:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
herd php artisan test --compact tests/Feature/SiswaLPKResourceTest.php
herd php artisan test --compact tests/Unit/SiswaLPKModelTest.php
```

Format PHP changes before final review:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
vendor/bin/pint --dirty
```

If any Filament assets or frontend UI changes are not visible, rebuild local assets:

```bash
cd /Users/indobuzz/Developer/Local/SIT_LPK
npm run build
```
