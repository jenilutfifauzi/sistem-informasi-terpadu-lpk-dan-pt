# Tasks: CTK Edit Stages Separation

**Input**: Design documents from `/specs/007-ctk-edit-stages-separation/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/sections.md, quickstart.md

**Tests**: Not explicitly requested. Existing test updates included in Polish phase to prevent test regression.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `app/`, `database/`, `tests/` at repository root (Laravel monolith)
- **Filament Sections**: `app/Filament/Resources/CTKS/Schemas/`
- **Edit Page**: `app/Filament/Resources/CTKS/Pages/EditCTK.php`
- **Models**: `app/Models/`
- **Migrations**: `database/migrations/`

---

## Phase 1: Setup

**Purpose**: Database schema change required for reliable screening stage separation

- [X] T001 Create migration `add_screening_stage_to_c_t_k_screenings_table` in `database/migrations/` — add `screening_stage` enum column (`'Screening 1'`, `'Interview User'`) with default `'Screening 1'` after `screening_result` column; include data backfill: set `screening_stage = 'Interview User'` where `LOWER(interview_location) LIKE '%interview%'` OR `'%user%'` OR `'%tahap 2%'`, then set remaining NULL records to `'Screening 1'`
- [X] T002 Run `php artisan migrate` to apply the screening_stage column and backfill existing data

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Model updates that MUST be complete before ANY user story section can work correctly

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T003 [P] Update `app/Models/CTKScreening.php` — add `screening_stage` to `$fillable` array and add cast `'screening_stage' => ScreeningStage::class` in `casts()` method (import `App\Enums\ScreeningStage`)
- [X] T004 [P] Update `app/Models/CTK.php` — refactor `getStage6CompleteAttribute()` to use `$this->screenings()->where('screening_stage', 'Screening 1')->where('screening_result', 'Lolos')->exists()` and refactor `getStage7CompleteAttribute()` to use `$this->screenings()->where('screening_stage', 'Interview User')->where('screening_result', 'Lolos')->exists()` — replacing the fragile LIKE queries on `interview_location`

**Checkpoint**: Migration applied, models updated — section implementation can now begin

---

## Phase 3: User Story 1 — Pisahkan Section Paspor dari Berkas dengan Input Nomor Paspor (Priority: P1) 🎯 MVP

**Goal**: Split combined "3-4. Dokumen CTK (Soal/Berkas & Paspor)" into separate "3. Soal/Berkas" (Stage 3) and "4. Paspor" (Stage 4) with paspor_number input field

**Independent Test**: Open Edit CTK page → confirm section "3. Soal/Berkas" shows only SoalBerkas documents; confirm section "4. Paspor" has paspor_number text input + Paspor documents; save with paspor_number filled → verify persisted

### Implementation for User Story 1

- [X] T005 [P] [US1] Create `app/Filament/Resources/CTKS/Schemas/SoalBerkasSection.php` — Section title "3. Soal/Berkas", status badge from stage 3, Repeater named `soalBerkasDocuments` with `->relationship('documents', modifyQueryUsing: fn($query) => $query->where('document_type', DocumentType::SoalBerkas))`, auto-set `document_type` to `SoalBerkas` on create via `mutateRelationshipDataBeforeCreateUsing()`, use same document fields as current DocumentSection (document_type pre-selected and disabled, file_path FileUpload, filename, notes), collapsible with persistCollapsed — follow MCUSection pattern for class structure
- [X] T006 [P] [US1] Create `app/Filament/Resources/CTKS/Schemas/PasporSection.php` — Section title "4. Paspor", status badge from stage 4, schema: `TextInput::make('paspor_number')->label('Nomor Paspor')->nullable()->maxLength(50)` (direct CTK model field) + Repeater named `pasporDocuments` with `->relationship('documents', modifyQueryUsing: fn($query) => $query->where('document_type', DocumentType::Paspor))`, auto-set `document_type` to `Paspor` on create, collapsible with persistCollapsed — follow MCUSection pattern
- [X] T007 [US1] Update `app/Filament/Resources/CTKS/Pages/EditCTK.php` — replace `DocumentSection::make()` with `SoalBerkasSection::make(), PasporSection::make()` in components array (after PaymentSection); update `mutateFormDataBeforeSave`: extract document handling into private `prepareDocumentData(array &$documents): void` method, call it for `$data['soalBerkasDocuments']` and `$data['pasporDocuments']` instead of `$data['documents']`; update imports (remove DocumentSection, add SoalBerkasSection + PasporSection)
- [X] T008 [US1] Delete `app/Filament/Resources/CTKS/Schemas/DocumentSection.php`

**Checkpoint**: Stage 3 and 4 have independent sections, paspor_number editable, old DocumentSection removed

---

## Phase 4: User Story 2 — Section Terpisah untuk Screening 1 (Priority: P1)

**Goal**: Create dedicated "6. Screening 1" section showing only Screening 1 records, filtered by `screening_stage`

**Independent Test**: Open Edit CTK page → confirm section "6. Screening 1" exists with screening repeater → add a screening record → save → verify record has `screening_stage = 'Screening 1'` and appears only in this section

### Implementation for User Story 2

- [X] T009 [US2] Create `app/Filament/Resources/CTKS/Schemas/Screening1Section.php` — Section title "6. Screening 1", status badge from stage 6, Repeater named `screening1Records` with `->relationship('screenings', modifyQueryUsing: fn($query) => $query->where('screening_stage', 'Screening 1'))`, auto-set `screening_stage` to `ScreeningStage::Screening1` on create via `mutateRelationshipDataBeforeCreateUsing()`, fields: interviewer_id (Select from users), interview_date (DatePicker), interview_location (TextInput), screening_result (Radio: Lolos/Tidak Lolos), interview_notes (Textarea) — copy field structure from current ScreeningSection, collapsible with persistCollapsed

**Checkpoint**: Section "6. Screening 1" created, ready for EditCTK integration in US3

---

## Phase 5: User Story 3 — Section Terpisah untuk Interview User (Priority: P1)

**Goal**: Create dedicated "7. Interview User" section and replace combined ScreeningSection with both new screening sections

**Independent Test**: Open Edit CTK page → confirm sections "6. Screening 1" and "7. Interview User" both exist as separate sections; add a record to each → save → verify each record has correct `screening_stage` value; existing screening records are correctly distributed between the two sections

### Implementation for User Story 3

- [X] T010 [P] [US3] Create `app/Filament/Resources/CTKS/Schemas/InterviewUserSection.php` — Section title "7. Interview User", status badge from stage 7, Repeater named `interviewUserRecords` with `->relationship('screenings', modifyQueryUsing: fn($query) => $query->where('screening_stage', 'Interview User'))`, auto-set `screening_stage` to `ScreeningStage::InterviewUser` on create, same fields as Screening1Section (interviewer_id, interview_date, interview_location, screening_result, interview_notes), collapsible with persistCollapsed
- [X] T011 [US3] Update `app/Filament/Resources/CTKS/Pages/EditCTK.php` — replace `ScreeningSection::make()` with `Screening1Section::make(), InterviewUserSection::make()` (after TrainingSection); update `mutateFormDataBeforeSave`: extract screening handling into private `prepareScreeningData(array &$screenings): void` method, call it for `$data['screening1Records']` and `$data['interviewUserRecords']` instead of `$data['screenings']`; update imports (remove ScreeningSection, add Screening1Section + InterviewUserSection)
- [X] T012 [US3] Delete `app/Filament/Resources/CTKS/Schemas/ScreeningSection.php`

**Checkpoint**: Stage 6 and 7 have independent sections with proper screening_stage filtering, old ScreeningSection removed

---

## Phase 6: User Story 4 — Section Baru untuk Ijin Desa (Priority: P2)

**Goal**: Add new "8. Ijin Desa" section with status radio (Belum Ada / Ada) and filtered document repeater

**Independent Test**: Open Edit CTK page → confirm section "8. Ijin Desa" exists → set status to "Ada" and upload an IjinDesa document → save → verify stage 8 badge shows complete

### Implementation for User Story 4

- [X] T013 [US4] Create `app/Filament/Resources/CTKS/Schemas/IjinDesaSection.php` — Section title "8. Ijin Desa", status badge from stage 8, schema: `Radio::make('ijin_desa_status')->label('Status Ijin Desa')->options(['Belum' => 'Belum Ada', 'Ada' => 'Ada'])->inline()` (direct CTK model field) + Repeater named `ijinDesaDocuments` with `->relationship('documents', modifyQueryUsing: fn($query) => $query->where('document_type', DocumentType::IjinDesa))`, auto-set `document_type` to `IjinDesa` on create, collapsible with persistCollapsed
- [X] T014 [US4] Update `app/Filament/Resources/CTKS/Pages/EditCTK.php` — add `IjinDesaSection::make()` after `InterviewUserSection::make()` in components array; add `prepareDocumentData` call for `$data['ijinDesaDocuments']` in `mutateFormDataBeforeSave`; add import for IjinDesaSection

**Checkpoint**: Stage 8 has a dedicated form section with status and document upload

---

## Phase 7: User Story 5 — Section Baru untuk Rekomendasi (Priority: P2)

**Goal**: Add new "9. Rekomendasi" section with status radio (Belum Ada / Ada) and filtered document repeater

**Independent Test**: Open Edit CTK page → confirm section "9. Rekomendasi" exists → set status to "Ada" and upload a Rekomendasi document → save → verify stage 9 badge shows complete

### Implementation for User Story 5

- [X] T015 [US5] Create `app/Filament/Resources/CTKS/Schemas/RekomendasiSection.php` — Section title "9. Rekomendasi", status badge from stage 9, schema: `Radio::make('rekomendasi_status')->label('Status Rekomendasi')->options(['Belum' => 'Belum Ada', 'Ada' => 'Ada'])->inline()` (direct CTK model field) + Repeater named `rekomendasiDocuments` with `->relationship('documents', modifyQueryUsing: fn($query) => $query->where('document_type', DocumentType::Rekomendasi))`, auto-set `document_type` to `Rekomendasi` on create, collapsible with persistCollapsed
- [X] T016 [US5] Update `app/Filament/Resources/CTKS/Pages/EditCTK.php` — add `RekomendasiSection::make()` after `IjinDesaSection::make()` in components array; add `prepareDocumentData` call for `$data['rekomendasiDocuments']` in `mutateFormDataBeforeSave`; add import for RekomendasiSection

**Checkpoint**: Stage 9 has a dedicated form section with status and document upload

---

## Phase 8: User Story 6 — Pisahkan Section Working Permit dari Visa (Priority: P2)

**Goal**: Create dedicated "10. Working Permit" section split from VisaSection, rename VisaSection to "11-13. Apply Visa & Visa"

**Independent Test**: Open Edit CTK page → confirm section "10. Working Permit" exists with status radio + WP documents; confirm section "11-13. Apply Visa & Visa" no longer references Working Permit; set WP status to "Lengkap" → save → verify stage 10 badge shows complete

### Implementation for User Story 6

- [X] T017 [P] [US6] Create `app/Filament/Resources/CTKS/Schemas/WorkingPermitSection.php` — Section title "10. Working Permit", status badge from stage 10, schema: `Radio::make('wp_status')->label('Status Working Permit')->options(['Belum' => 'Belum Lengkap', 'Lengkap' => 'Lengkap'])->inline()` (direct CTK model field) + Repeater named `wpDocuments` with `->relationship('documents', modifyQueryUsing: fn($query) => $query->where('document_type', DocumentType::WorkingPermit))`, auto-set `document_type` to `WorkingPermit` on create, collapsible with persistCollapsed
- [X] T018 [P] [US6] Update `app/Filament/Resources/CTKS/Schemas/VisaSection.php` — rename section title from "10-11-13. Visa & Working Permit" to "11-13. Apply Visa & Visa"; update `getStatusBadge()` to only check stages 11 and 13 (remove stage 10 reference); update description text to remove Working Permit mention
- [X] T019 [US6] Update `app/Filament/Resources/CTKS/Pages/EditCTK.php` — add `WorkingPermitSection::make()` before `VisaSection::make()` (after RekomendasiSection) in components array; add `prepareDocumentData` call for `$data['wpDocuments']` in `mutateFormDataBeforeSave`; add import for WorkingPermitSection

**Checkpoint**: Stage 10 has a dedicated section, VisaSection only covers stages 11 and 13

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Test regression prevention, code quality, and final validation

- [X] T020 Update `tests/Feature/CTKScreeningTest.php` — add `screening_stage` field to all test data arrays for screening creation/update assertions; verify tests still pass with the new enum column
- [X] T021 Update `tests/Feature/CTKStageTrackingTest.php` — update stage 6 and stage 7 completion test assertions to use `screening_stage` enum-based logic instead of LIKE-based expectations; add test cases verifying that a Screening 1 record does NOT complete stage 7 and vice versa
- [X] T022 Review and update `tests/Feature/CTKDocumentUploadTest.php` — verify document upload tests still pass with filtered repeaters (soalBerkasDocuments, pasporDocuments, ijinDesaDocuments, rekomendasiDocuments, wpDocuments); update any assertions that reference the old combined `documents` repeater key
- [X] T023 Add new test cases for stages 8, 9, 10 sections — create tests verifying: (a) IjinDesaSection saves `ijin_desa_status` and filters IjinDesa documents correctly, (b) RekomendasiSection saves `rekomendasi_status` and filters Rekomendasi documents correctly, (c) WorkingPermitSection saves `wp_status` and filters WorkingPermit documents correctly; add to existing test file or create new test file as appropriate
- [X] T024 Verify final section ordering in `app/Filament/Resources/CTKS/Pages/EditCTK.php` matches stage 1-15: Base form → MCUSection(1) → PaymentSection(2) → SoalBerkasSection(3) → PasporSection(4) → TrainingSection(5) → Screening1Section(6) → InterviewUserSection(7) → IjinDesaSection(8) → RekomendasiSection(9) → WorkingPermitSection(10) → VisaSection(11-13) → MedicalFullSection(12) → OPPSection(14); also verify all 7 new sections display correct status badges (FR-012)
- [X] T025 Run `vendor/bin/pint --dirty` to fix code formatting across all changed files
- [X] T026 Run quickstart.md validation — execute `php artisan test --compact --filter=CTKScreening`, `php artisan test --compact --filter=CTKStageTracking`, `php artisan test --compact --filter=CTKDocumentUpload`, verify Edit CTK page loads without errors

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup (migration must be applied first) — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Foundational — no dependency on other stories
- **US2 (Phase 4)**: Depends on Foundational — no dependency on other stories
- **US3 (Phase 5)**: Depends on Foundational + **US2** (Screening1Section must exist before replacing ScreeningSection in EditCTK)
- **US4 (Phase 6)**: Depends on Foundational — no dependency on other stories (but EditCTK update assumes US3 completed, placing section after InterviewUserSection)
- **US5 (Phase 7)**: Depends on Foundational — no dependency on other stories (but EditCTK update assumes US4 completed, placing section after IjinDesaSection)
- **US6 (Phase 8)**: Depends on Foundational — no dependency on other stories (but EditCTK update assumes US5 completed, placing section after RekomendasiSection)
- **Polish (Phase 9)**: Depends on all user stories being complete

### Within Each User Story

- Create section class(es) first, then update EditCTK, then delete old section (if applicable)
- Section classes marked [P] within a story can be created in parallel
- EditCTK update depends on section classes being created

### Parallel Opportunities

- **Phase 2**: T003 and T004 can run in parallel (different model files)
- **Phase 3**: T005 and T006 can run in parallel (different new files)
- **Phase 3-5**: US1 and US2 can start simultaneously after Foundational (different files)
- **Phase 8**: T017 and T018 can run in parallel (different files)
- **Phase 6-8**: US4, US5, US6 section class creation can run in parallel (if EditCTK integration is deferred)

---

## Parallel Example: User Story 1

```bash
# Launch both section classes in parallel:
Task T005: "Create SoalBerkasSection.php in app/Filament/Resources/CTKS/Schemas/"
Task T006: "Create PasporSection.php in app/Filament/Resources/CTKS/Schemas/"

# Then sequentially:
Task T007: "Update EditCTK.php — replace DocumentSection, update mutateFormDataBeforeSave"
Task T008: "Delete DocumentSection.php"
```

## Parallel Example: User Story 6

```bash
# Launch both modifications in parallel:
Task T017: "Create WorkingPermitSection.php in app/Filament/Resources/CTKS/Schemas/"
Task T018: "Update VisaSection.php — rename, remove stage 10"

# Then sequentially:
Task T019: "Update EditCTK.php — add WorkingPermitSection"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migration)
2. Complete Phase 2: Foundational (model updates)
3. Complete Phase 3: User Story 1 (Paspor separation)
4. **STOP and VALIDATE**: Test paspor_number input, document filtering, stage 4 completion
5. Deploy/demo if ready — admin can now input passport numbers

### Incremental Delivery

1. Setup + Foundational → Migration applied, models updated
2. Add US1 (Paspor separation) → Test independently → Paspor number input available (MVP!)
3. Add US2 + US3 (Screening separation) → Test independently → Screening stages separated
4. Add US4 (Ijin Desa) → Test independently → Stage 8 editable
5. Add US5 (Rekomendasi) → Test independently → Stage 9 editable
6. Add US6 (Working Permit) → Test independently → Stage 10 separated from Visa
7. Each story adds value without breaking previous stories

### Sequential Execution (Single Developer)

1. Complete Setup + Foundational (T001-T004)
2. Complete US1 (T005-T008) — test paspor section
3. Complete US2 (T009) — create Screening1Section
4. Complete US3 (T010-T012) — integrate both screening sections, delete old
5. Complete US4 (T013-T014) — test ijin desa section
6. Complete US5 (T015-T016) — test rekomendasi section
7. Complete US6 (T017-T019) — test working permit section
8. Complete Polish (T020-T026) — tests, formatting, validation

---

## Notes

- [P] tasks = different files, no dependencies on each other
- [Story] label maps task to specific user story for traceability
- All new section classes follow the established MCUSection/TrainingSection pattern: static `make()` returning a `Section`, `getStatusBadge()` method, collapsible with persistCollapsed
- Repeater naming convention: `{camelCaseSectionName}Documents` or `{camelCaseSectionName}Records` to avoid key clashes with existing `documents`/`screenings` relationship repeaters
- `mutateFormDataBeforeSave` refactored to use helper methods (`prepareDocumentData`, `prepareScreeningData`) to handle multiple document/screening repeater keys
- Stage ordering note: MedicalFullSection (Stage 12) appears after VisaSection (11-13) in the component list per the alur_ctk.md workflow order
- Commit after each phase or logical group of tasks
