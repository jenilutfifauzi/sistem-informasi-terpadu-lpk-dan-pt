# Research: CTK Edit Stages Separation

**Date**: 2026-02-15  
**Feature**: 007-ctk-edit-stages-separation

## Research Task 1: CTKScreening `screening_stage` Field

### Decision: Add `screening_stage` column to `c_t_k_screenings` table

### Findings
- The `c_t_k_screenings` table does NOT have a `screening_stage` column
- Current Stage 6/7 completion logic in `CTK.php` uses fragile LIKE queries on `interview_location`:
  - Stage 6: `LOWER(interview_location) LIKE '%screening%'` OR `'%tahap 1%'`
  - Stage 7: `LOWER(interview_location) LIKE '%interview%'` OR `'%user%'` OR `'%tahap 2%'`
- Enum `ScreeningStage` already exists with values `Screening 1` and `Interview User`
- CTKScreening model `$fillable` does not include `screening_stage`

### Required Changes
1. Migration to add `screening_stage` enum column (`Screening 1`, `Interview User`) to `c_t_k_screenings`
2. Update CTKScreening model to add `screening_stage` to `$fillable` and `casts()`
3. Update `getStage6CompleteAttribute()` and `getStage7CompleteAttribute()` to use `screening_stage` instead of LIKE queries
4. Data migration: existing screenings need a best-effort `screening_stage` assignment based on current LIKE logic

### Rationale
Using a dedicated enum column is far more reliable than parsing location strings. The `ScreeningStage` enum already exists, confirming this was the intended design.

### Alternatives Considered
- Keep LIKE queries: Rejected — fragile, breaks if user types different location format
- Add a boolean `is_screening_1`: Rejected — enum is more extensible and `ScreeningStage` already exists

---

## Research Task 2: Document Filtering per Section

### Decision: Use `modifyQueryUsing` on Repeater relationship to filter by `document_type`

### Findings
- Current `DocumentSection` creates a single Repeater over `documents()` relationship with no filtering
- `CTKDocument` model has `document_type` cast to `DocumentType` enum
- DocumentType enum has: `SoalBerkas`, `Paspor`, `IjinDesa`, `Rekomendasi`, `WorkingPermit`, `VisaDocument`, `MedicalFullReport`, `OPPDocument`
- Filament v4 Repeater supports `modifyQueryUsing()` for filtering the relationship query

### Required Changes
1. Split `DocumentSection` into `SoalBerkasSection` (Stage 3) and `PasporSection` (Stage 4)
2. Create `IjinDesaSection` (Stage 8), `RekomendasiSection` (Stage 9), `WorkingPermitSection` (Stage 10)
3. Each section's Repeater uses `modifyQueryUsing()` to filter by specific `document_type`
4. Each section's Repeater pre-sets the `document_type` value via `mutateRelationshipDataBeforeCreateUsing()` or `default()`

### Rationale
Filtering at the query level ensures each section only shows and creates documents of the correct type. This matches the stage-per-section architecture.

### Alternatives Considered
- Single section with tabs: Rejected — user requested separate sections per stage
- Client-side filtering: Rejected — would still load all documents, less reliable

---

## Research Task 3: Paspor Number Input in Edit Form

### Decision: Add `TextInput::make('paspor_number')` directly in the new PasporSection

### Findings
- `paspor_number` field exists on CTK model's `$fillable` array
- Database column exists: `string('paspor_number')->nullable()` in migration
- Stage 4 completion logic (`getStage4CompleteAttribute`) already checks `!empty($this->paspor_number)` AND paspor document exists
- Currently no form field exposes this in the Edit CTK form

### Required Changes
1. Add `TextInput::make('paspor_number')` in new `PasporSection`
2. The field maps directly to the CTK model — no relationship needed

### Rationale
Direct model field, simplest approach. Stage completion logic already accounts for it.

---

## Research Task 4: VisaSection Working Permit Separation

### Decision: Remove WP (Stage 10) from VisaSection, create dedicated WorkingPermitSection

### Findings
- Current `VisaSection` title is "10-11-13. Visa & Working Permit" but only contains visa-related `Repeater`
- No WP-specific form fields exist in VisaSection — WP status is on CTK model (`wp_status`)
- Stage 10 completion: `$this->wp_status === 'Lengkap'` (does not check for WP document, but AdvanceStageAction requires WP document)

### Required Changes
1. Create `WorkingPermitSection` with:
   - Radio/Select for `wp_status` (Belum/Lengkap)
   - Document repeater filtered for `DocumentType::WorkingPermit`
2. Rename VisaSection to "11-13. Apply Visa & Visa"
3. Remove stage 10 from VisaSection's `getStatusBadge()` call

### Rationale
Aligns with the 15-stage workflow where WP is a distinct step.

---

## Research Task 5: Ijin Desa and Rekomendasi Sections

### Decision: Create new section classes with status select + filtered document repeater

### Findings
- `ijin_desa_status`: enum `['Belum', 'Ada']` on CTK model
- `rekomendasi_status`: enum `['Belum', 'Ada']` on CTK model
- Stage 8 completion: `ijin_desa_status === 'Ada'` AND `IjinDesa` document exists
- Stage 9 completion: `rekomendasi_status === 'Ada'` AND `Rekomendasi` document exists
- No form sections exist for these stages currently

### Required Changes
1. Create `IjinDesaSection` (Stage 8): Radio for status + document repeater filtered for `IjinDesa`
2. Create `RekomendasiSection` (Stage 9): Radio for status + document repeater filtered for `Rekomendasi`

### Rationale
Both follow the same pattern: status field + supporting document. Consistent with existing section patterns.

---

## Research Task 6: Impact on Existing Tests

### Decision: Update affected tests, no existing tests should be removed

### Findings
- `CTKScreeningTest.php`: Tests screening operations — needs updates for `screening_stage` field
- `CTKStageTrackingTest.php`: Tests stage completion logic — needs updates for new stage 6/7 logic
- `CTKDocumentUploadTest.php`: Tests document uploads — may need updates for filtered repeaters
- Constitution requires: "You must not remove any tests or test files from the tests directory without approval"

### Required Changes
1. Update CTKScreeningTest to include `screening_stage` in test data
2. Update CTKStageTrackingTest for new stage 6/7 completion logic
3. Add new test cases for each new section (stages 8, 9, 10 separately)
4. Ensure EditCTK form tests cover all new separate sections

---

## Research Task 7: EditCTK Section Ordering

### Decision: Sections in EditCTK form MUST follow stage order 1-15

### Findings
- Current order in EditCTK.php `form()`:
  1. Base form (Data Pribadi, Informasi Kontak)
  2. MCUSection (Stage 1)
  3. PaymentSection (Stage 2)
  4. DocumentSection (Stage 3-4 combined)
  5. TrainingSection (Stage 5)
  6. ScreeningSection (Stage 6-7 combined)
  7. VisaSection (Stage 10-11-13 combined)
  8. MedicalFullSection (Stage 12)
  9. OPPSection (Stage 14)

### Required New Order
  1. Base form (Data Pribadi, Informasi Kontak)
  2. MCUSection (Stage 1)
  3. PaymentSection (Stage 2)
  4. SoalBerkasSection (Stage 3) — NEW
  5. PasporSection (Stage 4) — NEW
  6. TrainingSection (Stage 5)
  7. Screening1Section (Stage 6) — NEW
  8. InterviewUserSection (Stage 7) — NEW
  9. IjinDesaSection (Stage 8) — NEW
  10. RekomendasiSection (Stage 9) — NEW
  11. WorkingPermitSection (Stage 10) — NEW
  12. VisaSection (renamed to Stage 11-13)
  13. MedicalFullSection (Stage 12)
  14. OPPSection (Stage 14)

### Rationale
Sequential ordering matches the alur_ctk.md workflow and user mental model.
