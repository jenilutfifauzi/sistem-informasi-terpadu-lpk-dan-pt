# Quickstart: CTK Edit Stages Separation

**Date**: 2026-02-15  
**Feature**: 007-ctk-edit-stages-separation  
**Branch**: `007-ctk-edit-stages-separation`

## Prerequisites

- PHP 8.4.x, Composer, Node.js, MySQL/MariaDB
- Application database migrated and seeded
- Filament v4, Livewire v3 installed

## Setup

```bash
git checkout 007-ctk-edit-stages-separation
composer install
php artisan migrate
npm run build
```

## What This Feature Changes

### Problem
The Edit CTK form combines multiple stages into single sections:
- Stages 3-4 (Soal/Berkas + Paspor) in one `DocumentSection`
- Stages 6-7 (Screening 1 + Interview User) in one `ScreeningSection`
- Stages 10-11-13 (WP + Apply Visa + Visa) in one `VisaSection`
- Stages 8, 9 have no form sections at all

Paspor number input is missing from the form entirely.

### Solution
Split combined sections and create missing sections so each stage has its own section:

| Stage | Before                        | After                    |
|-------|-------------------------------|--------------------------|
| 3     | DocumentSection (combined)    | SoalBerkasSection        |
| 4     | DocumentSection (combined)    | PasporSection            |
| 6     | ScreeningSection (combined)   | Screening1Section        |
| 7     | ScreeningSection (combined)   | InterviewUserSection     |
| 8     | (missing)                     | IjinDesaSection          |
| 9     | (missing)                     | RekomendasiSection       |
| 10    | VisaSection (combined)        | WorkingPermitSection     |
| 11-13 | VisaSection (combined)        | VisaSection (renamed)    |

### Database Change
One migration: adds `screening_stage` enum column to `c_t_k_screenings` table with data backfill.

## Key Files

### New Schema Section Classes
```
app/Filament/Resources/CTKS/Schemas/
├── SoalBerkasSection.php      (Stage 3)
├── PasporSection.php          (Stage 4)
├── Screening1Section.php      (Stage 6)
├── InterviewUserSection.php   (Stage 7)
├── IjinDesaSection.php        (Stage 8)
├── RekomendasiSection.php     (Stage 9)
└── WorkingPermitSection.php   (Stage 10)
```

### Modified Files
```
app/Filament/Resources/CTKS/Schemas/VisaSection.php    (renamed, removed WP)
app/Filament/Resources/CTKS/Pages/EditCTK.php          (updated section list)
app/Models/CTKScreening.php                             (added screening_stage)
app/Models/CTK.php                                      (updated stage 6/7 completion)
```

### Removed Files
```
app/Filament/Resources/CTKS/Schemas/DocumentSection.php  (replaced by 2 sections)
app/Filament/Resources/CTKS/Schemas/ScreeningSection.php  (replaced by 2 sections)
```

### Migration
```
database/migrations/XXXX_add_screening_stage_to_c_t_k_screenings_table.php
```

## Verification

```bash
# Run affected tests
php artisan test --compact --filter=CTKScreening
php artisan test --compact --filter=CTKStageTracking
php artisan test --compact --filter=CTKDocumentUpload

# Verify form visual
# 1. Open browser → /admin/ctks/{id}/edit
# 2. Confirm each stage (3,4,6,7,8,9,10) has its own section
# 3. Fill paspor number in section "4. Paspor" → save → verify persisted
# 4. Add screening in section "6. Screening 1" → verify stays in that section
# 5. Set ijin desa status to "Ada" in section "8. Ijin Desa" → verify stage 8 badge updates

# Run pint
vendor/bin/pint --dirty
```

## Architecture Decisions

1. **Document filtering**: Each section's Repeater uses `modifyQueryUsing()` to filter by `document_type` and auto-sets the type on new records
2. **Screening stage**: Added `screening_stage` enum column instead of keeping fragile LIKE queries — uses existing `ScreeningStage` enum
3. **No data loss**: All existing data remains accessible; only UI layout changes (plus one additive column migration)
