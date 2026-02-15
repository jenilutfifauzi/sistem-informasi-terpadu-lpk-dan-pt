# Contracts: CTK Edit Stages Separation

**Date**: 2026-02-15  
**Feature**: 007-ctk-edit-stages-separation

## Overview

This feature is entirely UI-focused (Filament admin panel form). There are no REST/GraphQL API endpoints to define. The "contracts" are the section component interfaces and data flow expectations.

## Section Component Contracts

Each section follows a standard contract pattern:

```
Section::make(title)
  → description (status badge from stage completion)
  → schema (form fields)
  → collapsible / collapsed behavior
```

### Contract 1: SoalBerkasSection (Stage 3)

- **Title**: "3. Soal/Berkas"
- **Status Badge**: Stage 3 complete/incomplete
- **Fields**:
  - Document repeater filtered by `document_type = SoalBerkas`
- **Behavior**: Documents created in this section auto-set `document_type` to `SoalBerkas`

### Contract 2: PasporSection (Stage 4)

- **Title**: "4. Paspor"
- **Status Badge**: Stage 4 complete/incomplete
- **Fields**:
  - `paspor_number`: text input, nullable, max 50 chars
  - Document repeater filtered by `document_type = Paspor`
- **Behavior**: Documents created in this section auto-set `document_type` to `Paspor`
- **Completion**: Requires both `paspor_number` filled AND at least 1 Paspor document

### Contract 3: Screening1Section (Stage 6)

- **Title**: "6. Screening 1"
- **Status Badge**: Stage 6 complete/incomplete
- **Fields**:
  - Screening repeater filtered by `screening_stage = 'Screening 1'`
  - Each item: interviewer, date, location, result (Lolos/Tidak Lolos), notes
- **Behavior**: New screenings auto-set `screening_stage` to `'Screening 1'`
- **Completion**: At least 1 screening with `screening_result = Lolos`

### Contract 4: InterviewUserSection (Stage 7)

- **Title**: "7. Interview User"
- **Status Badge**: Stage 7 complete/incomplete
- **Fields**:
  - Screening repeater filtered by `screening_stage = 'Interview User'`
  - Each item: interviewer, date, location, result (Lolos/Tidak Lolos), notes
- **Behavior**: New screenings auto-set `screening_stage` to `'Interview User'`
- **Completion**: At least 1 screening with `screening_result = Lolos`

### Contract 5: IjinDesaSection (Stage 8)

- **Title**: "8. Ijin Desa"
- **Status Badge**: Stage 8 complete/incomplete
- **Fields**:
  - `ijin_desa_status`: Radio (Belum Ada / Ada)
  - Document repeater filtered by `document_type = IjinDesa`
- **Behavior**: Documents created in this section auto-set `document_type` to `IjinDesa`
- **Completion**: Requires `ijin_desa_status = 'Ada'` AND at least 1 IjinDesa document

### Contract 6: RekomendasiSection (Stage 9)

- **Title**: "9. Rekomendasi"
- **Status Badge**: Stage 9 complete/incomplete
- **Fields**:
  - `rekomendasi_status`: Radio (Belum Ada / Ada)
  - Document repeater filtered by `document_type = Rekomendasi`
- **Behavior**: Documents created in this section auto-set `document_type` to `Rekomendasi`
- **Completion**: Requires `rekomendasi_status = 'Ada'` AND at least 1 Rekomendasi document

### Contract 7: WorkingPermitSection (Stage 10)

- **Title**: "10. Working Permit"
- **Status Badge**: Stage 10 complete/incomplete
- **Fields**:
  - `wp_status`: Radio (Belum Lengkap / Lengkap)
  - Document repeater filtered by `document_type = WorkingPermit`
- **Behavior**: Documents created in this section auto-set `document_type` to `WorkingPermit`
- **Completion**: Requires `wp_status = 'Lengkap'` (stage completion badge). Note: `AdvanceStageAction` additionally requires a WorkingPermit document to advance beyond stage 10, but the completion badge itself only checks `wp_status`.

### Contract 8: VisaSection (Modified — Stage 11, 13)

- **Title**: "11-13. Apply Visa & Visa" (changed from "10-11-13. Visa & Working Permit")
- **Status Badge**: Stage 11 and 13 complete/incomplete (removed Stage 10)
- **Fields**: No change to existing visa repeater
- **Behavior**: No change

## Data Flow Contracts

### Screening Stage Auto-Assignment

When a new screening record is created via:
- **Screening1Section** → `screening_stage` = `'Screening 1'` (auto-set, hidden field)
- **InterviewUserSection** → `screening_stage` = `'Interview User'` (auto-set, hidden field)

### Document Type Auto-Assignment

When a new document is created via:
- **SoalBerkasSection** → `document_type` = `DocumentType::SoalBerkas` (auto-set or pre-selected)
- **PasporSection** → `document_type` = `DocumentType::Paspor` (auto-set or pre-selected)
- **IjinDesaSection** → `document_type` = `DocumentType::IjinDesa` (auto-set or pre-selected)
- **RekomendasiSection** → `document_type` = `DocumentType::Rekomendasi` (auto-set or pre-selected)
- **WorkingPermitSection** → `document_type` = `DocumentType::WorkingPermit` (auto-set or pre-selected)

## Migration Contract

### New Migration: add_screening_stage_to_c_t_k_screenings_table

```
ALTER TABLE c_t_k_screenings
ADD COLUMN screening_stage ENUM('Screening 1', 'Interview User')
DEFAULT 'Screening 1'
AFTER screening_result;
```

### Data Backfill Logic

```
UPDATE c_t_k_screenings SET screening_stage = 'Interview User'
WHERE LOWER(interview_location) LIKE '%interview%'
   OR LOWER(interview_location) LIKE '%user%'
   OR LOWER(interview_location) LIKE '%tahap 2%';

UPDATE c_t_k_screenings SET screening_stage = 'Screening 1'
WHERE screening_stage IS NULL;
```
