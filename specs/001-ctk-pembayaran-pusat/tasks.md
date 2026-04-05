# Tasks: Pembayaran ke Pusat (CTK Payment to Central)

**Input**: Design documents from `/specs/001-ctk-pembayaran-pusat/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, quickstart.md ✅

**Tests**: Not explicitly requested - tests optional but recommended for critical flows.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- All paths relative to repository root

---

## Phase 1: Setup (Database & Model)

**Purpose**: Create database table and Eloquent model

- [x] T001 Create migration file in database/migrations/2026_04_05_000001_create_pembayaran_pusat_table.php
- [x] T002 Run migration: `php artisan migrate`
- [x] T003 Create PembayaranPusat model in app/Models/PembayaranPusat.php
- [x] T004 Add pembayaranPusat() relationship to existing CTK model in app/Models/CTK.php

---

## Phase 2: Foundational (Filament Resource Structure)

**Purpose**: Create Filament Resource skeleton that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T005 Create resource directory structure: app/Filament/Resources/PembayaranPusat/
- [x] T006 Create main resource file: app/Filament/Resources/PembayaranPusat/PembayaranPusatResource.php
- [x] T007 [P] Create Pages directory and ListPembayaranPusat.php in app/Filament/Resources/PembayaranPusat/Pages/
- [x] T008 [P] Create Schemas directory and PembayaranPusatForm.php in app/Filament/Resources/PembayaranPusat/Schemas/
- [x] T009 [P] Create Tables directory and PembayaranPusatTable.php in app/Filament/Resources/PembayaranPusat/Tables/
- [x] T010 Configure navigation: set navigationSort after ASET, navigationIcon, navigationLabel in PembayaranPusatResource.php
- [x] T011 Implement entity global scope in PembayaranPusat model (copy pattern from Asset.php)
- [x] T012 Implement getEloquentQuery() with entity filtering in PembayaranPusatResource.php

**Checkpoint**: Foundation ready - resource appears in navigation, entity isolation works

---

## Phase 3: User Story 1 - Mencatat Pembayaran ke Pusat (Priority: P1) 🎯 MVP

**Goal**: Admin dapat mencatat pembayaran baru dengan CTK, tanggal, nominal, dan bukti transfer

**Independent Test**: Buat pembayaran baru → verifikasi data tersimpan dengan created_by otomatis

### Implementation for User Story 1

- [x] T013 [US1] Implement form schema with CTK select, tanggal, nominal, bukti transfer fields in app/Filament/Resources/PembayaranPusat/Schemas/PembayaranPusatForm.php
- [x] T014 [US1] Add CTK Select field with searchable, relationship to CTK model in PembayaranPusatForm.php
- [x] T015 [US1] Add DatePicker for tanggal_pembayaran with maxDate validation in PembayaranPusatForm.php
- [x] T016 [US1] Add TextInput for nominal with currency mask (IDR), min:1 validation in PembayaranPusatForm.php
- [x] T017 [US1] Add FileUpload for bukti_transfer_path with acceptedFileTypes, maxSize in PembayaranPusatForm.php
- [x] T018 [US1] Add Textarea for keterangan (optional) in PembayaranPusatForm.php
- [x] T019 [US1] Create CreatePembayaranPusat page in app/Filament/Resources/PembayaranPusat/Pages/CreatePembayaranPusat.php
- [x] T020 [US1] Implement mutateFormDataBeforeCreate() to auto-set created_by and entity in CreatePembayaranPusat.php
- [x] T021 [US1] Add form validation messages (Indonesian) for required fields and constraints

**Checkpoint**: User Story 1 complete - can create new payment with all validations

---

## Phase 4: User Story 2 - Melihat Daftar Pembayaran (Priority: P1) 🎯 MVP

**Goal**: Admin dapat melihat daftar pembayaran dengan semua kolom penting dan detail view

**Independent Test**: Akses halaman daftar → verifikasi kolom CTK, tanggal, nominal, bukti ditampilkan

### Implementation for User Story 2

- [x] T022 [US2] Implement table columns in app/Filament/Resources/PembayaranPusat/Tables/PembayaranPusatTable.php
- [x] T023 [US2] Add TextColumn for CTK nama_lengkap with relationship in PembayaranPusatTable.php
- [x] T024 [US2] Add TextColumn for tanggal_pembayaran with date format in PembayaranPusatTable.php
- [x] T025 [US2] Add TextColumn for nominal with money format (IDR) in PembayaranPusatTable.php
- [x] T026 [US2] Add TextColumn for creator.name in PembayaranPusatTable.php
- [x] T027 [US2] Add ImageColumn or link for bukti_transfer_path with preview in PembayaranPusatTable.php
- [x] T028 [US2] Create ViewPembayaranPusat page in app/Filament/Resources/PembayaranPusat/Pages/ViewPembayaranPusat.php
- [x] T029 [US2] Implement infolist schema for detail view with all fields in ViewPembayaranPusat.php
- [x] T030 [US2] Add bukti transfer preview/download in detail view
- [x] T031 [US2] Configure default sort by tanggal_pembayaran DESC in PembayaranPusatTable.php

**Checkpoint**: User Stories 1 & 2 complete - can create AND view payments (MVP!)

---

## Phase 5: User Story 3 - Filter dan Pencarian (Priority: P2)

**Goal**: Admin dapat memfilter berdasarkan CTK dan rentang tanggal

**Independent Test**: Gunakan filter tanggal → verifikasi hasil sesuai kriteria

### Implementation for User Story 3

- [x] T032 [US3] Add searchable() on CTK column for name search in PembayaranPusatTable.php
- [x] T033 [US3] Add SelectFilter for CTK with relationship in PembayaranPusatTable.php
- [x] T034 [US3] Add Filter for tanggal_pembayaran date range (from-to) in PembayaranPusatTable.php
- [x] T035 [US3] Add TrashedFilter for soft-deleted records in PembayaranPusatTable.php

**Checkpoint**: User Story 3 complete - search and filter work

---

## Phase 6: User Story 4 - Ringkasan Total Pembayaran (Priority: P2)

**Goal**: Admin dapat melihat ringkasan total pembayaran bulan ini

**Independent Test**: Verifikasi widget menampilkan total yang sesuai penjumlahan manual

### Implementation for User Story 4

- [x] T036 [US4] Create Widgets directory: app/Filament/Resources/PembayaranPusat/Widgets/
- [x] T037 [US4] Create PembayaranPusatStatsOverview widget in app/Filament/Resources/PembayaranPusat/Widgets/PembayaranPusatStatsOverview.php
- [x] T038 [US4] Implement Stat for total pembayaran bulan ini (SUM nominal) in PembayaranPusatStatsOverview.php
- [x] T039 [US4] Implement Stat for jumlah transaksi bulan ini (COUNT) in PembayaranPusatStatsOverview.php
- [x] T040 [US4] Implement Stat for rata-rata per transaksi (AVG) in PembayaranPusatStatsOverview.php
- [x] T041 [US4] Register widget in ListPembayaranPusat page getHeaderWidgets() in ListPembayaranPusat.php

**Checkpoint**: User Story 4 complete - stats widget displays on list page

---

## Phase 7: Full CRUD & Edit Capability

**Goal**: Complete edit and delete functionality per clarification (Full CRUD)

### Implementation for Full CRUD

- [x] T042 Create EditPembayaranPusat page in app/Filament/Resources/PembayaranPusat/Pages/EditPembayaranPusat.php
- [x] T043 Implement mutateFormDataBeforeSave() to auto-set updated_by in EditPembayaranPusat.php
- [x] T044 Add DeleteAction and RestoreAction in EditPembayaranPusat.php
- [x] T045 Add ForceDeleteAction for permanent delete (if needed) in EditPembayaranPusat.php
- [x] T046 Register all pages in PembayaranPusatResource getPages() method

**Checkpoint**: Full CRUD complete - can create, read, update, delete payments

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final improvements and validation

- [x] T047 [P] Add activity logging verification - ensure all CRUD operations logged
- [x] T048 [P] Run code formatting: `vendor/bin/pint`
- [ ] T049 [P] Test entity isolation: login as LPK admin → verify only LPK payments visible
- [ ] T050 [P] Test entity isolation: login as PT admin → verify only PT payments visible
- [ ] T051 [P] Test Pimpinan access: login as Pimpinan → verify all payments visible
- [ ] T052 Verify file upload works for JPG, PNG, PDF formats
- [ ] T053 Verify file size limit (10MB) enforced
- [ ] T054 Verify nominal validation (must be > 0)
- [ ] T055 Verify tanggal validation (not future date)
- [ ] T056 Run quickstart.md validation checklist

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 - BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Phase 2
- **User Story 2 (Phase 4)**: Depends on Phase 2 (can parallel with US1 if needed)
- **User Story 3 (Phase 5)**: Depends on Phase 4 (needs table to add filters)
- **User Story 4 (Phase 6)**: Depends on Phase 4 (needs list page for widget)
- **Full CRUD (Phase 7)**: Depends on Phase 3
- **Polish (Phase 8)**: Depends on all previous phases

### User Story Dependencies

```
Phase 1 (Setup)
    ↓
Phase 2 (Foundational)
    ↓
    ├── Phase 3 (US1: Create) ──→ Phase 7 (Full CRUD)
    │
    └── Phase 4 (US2: List/View)
            ↓
        ├── Phase 5 (US3: Filter)
        │
        └── Phase 6 (US4: Stats Widget)
                ↓
            Phase 8 (Polish)
```

### Parallel Opportunities

**Within Phase 2:**
```
T007, T008, T009 can run in parallel (different directories)
```

**After Phase 2 completes:**
```
Phase 3 (US1) and Phase 4 (US2) can start in parallel
```

**Within Phase 8:**
```
T047, T048, T049, T050, T051 can run in parallel
```

---

## Parallel Example: Phase 2 Foundational

```bash
# Launch these tasks in parallel (different files):
Task T007: "Create ListPembayaranPusat.php"
Task T008: "Create PembayaranPusatForm.php"
Task T009: "Create PembayaranPusatTable.php"
```

---

## Implementation Strategy

### MVP First (User Stories 1 & 2)

1. Complete Phase 1: Setup (T001-T004)
2. Complete Phase 2: Foundational (T005-T012)
3. Complete Phase 3: User Story 1 - Create (T013-T021)
4. Complete Phase 4: User Story 2 - List/View (T022-T031)
5. **STOP and VALIDATE**: Test creating and viewing payments
6. Deploy/demo if ready - this is functional MVP!

### Incremental Delivery

1. Setup + Foundational → Resource visible in navigation
2. Add User Story 1 → Can create payments (MVP partial)
3. Add User Story 2 → Can view payments (MVP complete!)
4. Add User Story 3 → Enhanced with filtering
5. Add User Story 4 → Enhanced with stats
6. Add Full CRUD → Complete feature
7. Polish → Production ready

---

## Notes

- [P] tasks = different files, safe to parallelize
- [US#] label maps task to specific user story
- Commit after each phase completion
- Test entity isolation early (Phase 2 checkpoint)
- MVP = Phase 1-4 complete (create + list/view)
- Full feature = all phases complete
