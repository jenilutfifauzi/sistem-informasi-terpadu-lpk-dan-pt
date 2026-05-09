# Tasks: Data Siswa LPK Administration

**Input**: Design documents from `/specs/001-data-lpks-admin/`  
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/lpk-students.openapi.yaml`, `quickstart.md`

**Tests**: Automated tests are required for this feature because the design artifacts and repository rules require PHPUnit and Livewire verification for behavior-critical changes.

**Organization**: Tasks are grouped by user story to keep each slice independently implementable and testable.

## Phase 1: Setup

**Purpose**: Generate the baseline Laravel and Filament files used by the feature.

- [X] T001 Scaffold the feature files in app/Models/SiswaLPK.php, database/factories/SiswaLPKFactory.php, database/migrations/*_create_siswa_lpk_table.php, app/Policies/SiswaLPKPolicy.php, tests/Feature/SiswaLPKResourceTest.php, and tests/Unit/SiswaLPKModelTest.php
- [X] T002 Scaffold the Filament resource, exporter, and page files in app/Filament/Resources/SiswaLPKResource.php, app/Filament/Exports/SiswaLPKExport.php, app/Filament/Resources/SiswaLPKResource/Pages/CreateSiswaLPK.php, app/Filament/Resources/SiswaLPKResource/Pages/EditSiswaLPK.php, app/Filament/Resources/SiswaLPKResource/Pages/ListSiswaLPKS.php, and app/Filament/Resources/SiswaLPKResource/Pages/ViewSiswaLPK.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the shared data and authorization foundation that every story depends on.

**⚠️ CRITICAL**: No user story work should start until this phase is complete.

- [X] T003 Implement the `siswa_lpk` database schema, unique `nomor_induk` that remains reserved after soft delete, audit columns, timestamps, and soft deletes in database/migrations/*_create_siswa_lpk_table.php
- [X] T004 [P] Implement fillable fields, casts, creator or updater relationships, boot hooks, and activity logging in app/Models/SiswaLPK.php
- [X] T005 [P] Implement realistic default data and edge-case states in database/factories/SiswaLPKFactory.php
- [X] T006 Implement permission mapping for `view_any`, `view`, `create`, `update`, and export permission enforcement in app/Policies/SiswaLPKPolicy.php
- [X] T007 Implement the base Filament resource metadata, page routes, labels, navigation group, and record title configuration in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Resources/SiswaLPKResource/Pages/ListSiswaLPKS.php

**Checkpoint**: Foundation ready. User story work can begin.

---

## Phase 3: User Story 1 - Record Siswa LPK Data (Priority: P1) 🎯 MVP

**Goal**: Allow an admin to create a Siswa LPK record that mirrors the registration sheet structure.

**Independent Test**: Open the create form, submit a valid student record with and without email, and confirm the data is stored and visible in the admin list.

### Tests for User Story 1

- [X] T008 [P] [US1] Add create-success, required-field, optional-email, and combined birth-place or birth-date source guidance Livewire tests in tests/Feature/SiswaLPKResourceTest.php
- [X] T009 [P] [US1] Add database integrity and duplicate `nomor_induk` unit coverage in tests/Unit/SiswaLPKModelTest.php

### Implementation for User Story 1

- [X] T010 [US1] Implement the create form fields, sheet-matching labels, and input layout in app/Filament/Resources/SiswaLPKResource.php
- [X] T011 [US1] Implement required validation, unique `nomor_induk` handling, and save behavior in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Resources/SiswaLPKResource/Pages/CreateSiswaLPK.php
- [X] T012 [US1] Implement cross-field date validation, combined-source entry guidance, and nullable email behavior in app/Filament/Resources/SiswaLPKResource.php

**Checkpoint**: User Story 1 should now support complete record creation and be independently testable.

---

## Phase 4: User Story 2 - Find, Review, and Export Siswa LPK Data (Priority: P2)

**Goal**: Allow admins to find student records quickly, review the full stored details, and export the selected dataset from the admin panel.

**Independent Test**: Seed multiple student records, search by `nomor_induk`, `nama_siswa`, and `program_pendidikan`, open a record detail page, and export the list in Excel format.

### Tests for User Story 2

- [X] T013 [P] [US2] Add list-search and program-filter Livewire tests in tests/Feature/SiswaLPKResourceTest.php
- [X] T014 [P] [US2] Add detail-view, Excel export success, and export authorization coverage in tests/Feature/SiswaLPKResourceTest.php

### Implementation for User Story 2

- [X] T015 [US2] Implement searchable list columns for sequence number, student number, student name, gender, phone number, email address, and education program, plus default sorting and program-focused retrieval in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Resources/SiswaLPKResource/Pages/ListSiswaLPKS.php
- [X] T016 [US2] Implement the read-only student detail presentation in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Resources/SiswaLPKResource/Pages/ViewSiswaLPK.php
- [X] T017 [US2] Implement the list Excel export action, exporter class, export permission check, and export audit event logging in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Exports/SiswaLPKExport.php

**Checkpoint**: User Stories 1 and 2 should both work independently.

---

## Phase 5: User Story 3 - Correct Siswa LPK Information (Priority: P3)

**Goal**: Allow admins to correct student information safely while preserving uniqueness and audit behavior.

**Independent Test**: Edit an existing student record, save valid changes, confirm updated values are shown, and verify duplicate `nomor_induk` edits are rejected.

### Tests for User Story 3

- [X] T018 [P] [US3] Add edit-success and duplicate-on-update Livewire tests in tests/Feature/SiswaLPKResourceTest.php
- [X] T019 [P] [US3] Add update audit-log and soft-delete behavior tests in tests/Unit/SiswaLPKModelTest.php

### Implementation for User Story 3

- [X] T020 [US3] Implement the edit form flow with unique-ignore-current-record validation in app/Filament/Resources/SiswaLPKResource.php and app/Filament/Resources/SiswaLPKResource/Pages/EditSiswaLPK.php
- [X] T021 [US3] Finalize update attribution and activity descriptions for corrected records in app/Models/SiswaLPK.php

**Checkpoint**: All user stories should now be independently functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final verification and consistency checks across all stories.

- [X] T022 Run code formatting for the feature changes with vendor/bin/pint --dirty from /Users/indobuzz/Developer/Local/SIT_LPK
- [X] T023 Run focused verification for tests/Feature/SiswaLPKResourceTest.php and tests/Unit/SiswaLPKModelTest.php using `herd php artisan test --compact`
- [X] T024 [P] Reconcile the final implementation with specs/001-data-lpks-admin/quickstart.md and specs/001-data-lpks-admin/contracts/lpk-students.openapi.yaml

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Depends on Setup and blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion.
- **User Story 2 (Phase 4)**: Depends on Foundational completion.
- **User Story 3 (Phase 5)**: Depends on Foundational completion.
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1**: No dependency on other user stories. This is the MVP.
- **US2**: No functional dependency on US1, but it reuses the same model and resource foundation from Phase 2.
- **US3**: No functional dependency on US1 or US2, but it reuses the same model and resource foundation from Phase 2.

### Within Each User Story

- Tests should be written before implementation and should fail before the corresponding implementation tasks are completed.
- Resource configuration tasks should follow the foundational model, migration, and policy work.
- User stories share the same Filament resource file, so concurrent work across stories requires coordination even though the stories are logically independent.

### Parallel Opportunities

- `T004` and `T005` can run in parallel after `T003` starts the schema contract.
- `T008` and `T009` can run in parallel for US1.
- `T013` and `T014` can run in parallel for US2.
- `T018` and `T019` can run in parallel for US3.
- `T024` can run in parallel with final local verification once implementation is complete.

---

## Parallel Example: User Story 1

```bash
# Run US1 test authoring in parallel:
Task: T008 Add create-success, required-field, optional-email, and combined-source guidance Livewire tests in tests/Feature/SiswaLPKResourceTest.php
Task: T009 Add database integrity and duplicate nomor_induk unit coverage in tests/Unit/SiswaLPKModelTest.php
```

## Parallel Example: User Story 2

```bash
# Run US2 verification coverage in parallel:
Task: T013 Add list-search and program-filter Livewire tests in tests/Feature/SiswaLPKResourceTest.php
Task: T014 Add detail-view, Excel export success, and export authorization coverage in tests/Feature/SiswaLPKResourceTest.php
```

## Parallel Example: User Story 3

```bash
# Run US3 regression coverage in parallel:
Task: T018 Add edit-success and duplicate-on-update Livewire tests in tests/Feature/SiswaLPKResourceTest.php
Task: T019 Add update audit-log and soft-delete behavior tests in tests/Unit/SiswaLPKModelTest.php
```

---

## Implementation Strategy

### MVP First

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: User Story 1.
4. Validate create flow, duplicate prevention, and optional email handling.
5. Demo or deploy the MVP if acceptable.

### Incremental Delivery

1. Finish Setup and Foundational once.
2. Deliver US1 for admin record capture.
3. Deliver US2 for retrieval and detail review.
4. Deliver US3 for safe correction workflows.
5. Finish with formatting, focused tests, and spec-contract reconciliation.

### Suggested MVP Scope

- **MVP**: Phase 1, Phase 2, and Phase 3 only.
- **Next Increment**: Add Phase 4 for retrieval efficiency.
- **Final Increment**: Add Phase 5 and Phase 6 for correction flow and hardening.
