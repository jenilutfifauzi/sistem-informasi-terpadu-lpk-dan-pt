# Data Model: CTK Edit Stages Separation

**Date**: 2026-02-15  
**Feature**: 007-ctk-edit-stages-separation

## Existing Entities (Modified)

### CTK (No schema change — fields already exist)

| Field               | Type                           | Notes                            |
|---------------------|--------------------------------|----------------------------------|
| paspor_number       | string, nullable               | Already exists, needs form field |
| ijin_desa_status    | enum('Belum','Ada'), nullable  | Already exists, needs form field |
| rekomendasi_status  | enum('Belum','Ada'), nullable  | Already exists, needs form field |
| wp_status           | enum('Belum','Lengkap'), nullable | Already exists, needs form field |

### CTKScreening (Schema change required)

| Field            | Type                                        | Notes                          |
|------------------|---------------------------------------------|--------------------------------|
| id               | bigint, PK                                  | Existing                       |
| ctk_id           | FK → ctk.id                                 | Existing                       |
| interviewer_id   | FK → users.id                               | Existing                       |
| interview_date   | date                                        | Existing                       |
| interview_location | string                                    | Existing                       |
| screening_result | enum('Lolos','Tidak Lolos')                 | Existing                       |
| interview_notes  | text, nullable                              | Existing                       |
| **screening_stage** | **enum('Screening 1','Interview User')** | **NEW — required for stage separation** |
| created_by       | FK → users.id, nullable                     | Existing                       |

**Migration**: Add `screening_stage` column with default `'Screening 1'`  
**Data Migration**: Backfill existing records using current LIKE-based logic from `getStage6CompleteAttribute` / `getStage7CompleteAttribute`

### CTKDocument (No schema change)

No changes needed. The `document_type` enum already supports all required types:
- `SoalBerkas` (Stage 3)
- `Paspor` (Stage 4)
- `IjinDesa` (Stage 8)
- `Rekomendasi` (Stage 9)
- `WorkingPermit` (Stage 10)

## New UI Components (Filament Schema Classes)

### Section Classes to Create

| Class Name              | Stage | Replaces                      |
|-------------------------|-------|-------------------------------|
| SoalBerkasSection       | 3     | DocumentSection (split)       |
| PasporSection           | 4     | DocumentSection (split)       |
| Screening1Section       | 6     | ScreeningSection (split)      |
| InterviewUserSection    | 7     | ScreeningSection (split)      |
| IjinDesaSection         | 8     | (new — no previous section)   |
| RekomendasiSection      | 9     | (new — no previous section)   |
| WorkingPermitSection    | 10    | VisaSection (split)           |

### Section Classes to Modify

| Class Name    | Change                                           |
|---------------|--------------------------------------------------|
| VisaSection   | Remove WP reference, rename to "11-13. Apply Visa & Visa" |

### Section Classes to Delete

| Class Name        | Reason                              |
|-------------------|-------------------------------------|
| DocumentSection   | Replaced by SoalBerkasSection + PasporSection |
| ScreeningSection  | Replaced by Screening1Section + InterviewUserSection |

## Relationships

```
CTK (1) ──→ (N) CTKDocument [filtered by document_type per section]
CTK (1) ──→ (N) CTKScreening [filtered by screening_stage per section]
```

## State Transitions

No new state transitions. Stage completion logic for stages 6 and 7 changes from LIKE-based to enum-based filtering:

- **Stage 6**: `screenings.where(screening_stage, 'Screening 1').where(screening_result, 'Lolos').exists()`
- **Stage 7**: `screenings.where(screening_stage, 'Interview User').where(screening_result, 'Lolos').exists()`

## Validation Rules

| Section              | Field             | Rules                                    |
|----------------------|-------------------|------------------------------------------|
| PasporSection        | paspor_number     | nullable, string, max:50                |
| IjinDesaSection      | ijin_desa_status  | nullable, in:Belum,Ada                  |
| RekomendasiSection   | rekomendasi_status| nullable, in:Belum,Ada                  |
| WorkingPermitSection | wp_status         | nullable, in:Belum,Lengkap              |
| Screening1Section    | screening_stage   | required, pre-set to 'Screening 1'      |
| InterviewUserSection | screening_stage   | required, pre-set to 'Interview User'   |
