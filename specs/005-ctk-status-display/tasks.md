# Tasks: CTK Index Status Display Simplification

**Input**: Design documents from `/specs/005-ctk-status-display/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Feature**: Simplify CTK table display by replacing Status column with completion status ("Lengkap"/"Belum Lengkap"), removing Tahap column, and preserving Progress column.

**Tests**: Tests are included per Laravel Boost guidelines - all Filament features should be tested.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Include exact file paths in descriptions

## Implementation Strategy

This is a **single-file display-layer change** affecting `app/Filament/Resources/CTKS/Tables/CTKSTable.php`. All three user stories are implemented together since they modify the same table configuration. However, tasks are organized by user story for clarity and to enable incremental testing of each acceptance scenario.

---

## Phase 1: Setup

**Purpose**: Verify environment and review existing implementation

- [X] T001 Review existing CTKSTable.php structure and column definitions in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T002 Verify CTK model accessors (completed_stages_count, completion_progress, completion_percentage) exist in app/Models/CTK.php
- [X] T003 Review current table filters and identify Tahap filter for removal in app/Filament/Resources/CTKS/Tables/CTKSTable.php

**Checkpoint**: Understanding of current implementation complete ✅

---

## Phase 2: User Story 1 - View CTK Status at a Glance (Priority: P1) 🎯 MVP

**Goal**: Replace Status column to display "Lengkap" when all 15 stages complete, "Belum Lengkap" otherwise

**Independent Test**: Navigate to CTK list, verify Status shows "Lengkap"/"Belum Lengkap" based on completion

### Implementation for User Story 1

- [X] T004 [US1] Modify Status column in CTKSTable.php to use formatStateUsing() with completed_stages_count logic
- [X] T005 [US1] Update Status column badge color (success for Lengkap, warning for Belum Lengkap) in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T006 [US1] Implement sortable behavior for Status column using completed_stages_count in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T007 [US1] Update Status column label to remain "Status" (no change needed, verify only) in app/Filament/Resources/CTKS/Tables/CTKSTable.php

### Tests for User Story 1

- [X] T008 [P] [US1] Create test file CTKTableDisplayTest.php in tests/Feature/
- [X] T009 [US1] Write test for "Lengkap" status display when completed_stages_count equals 15 in tests/Feature/CTKTableDisplayTest.php
- [X] T010 [US1] Write test for "Belum Lengkap" status display when completed_stages_count is less than 15 in tests/Feature/CTKTableDisplayTest.php
- [X] T011 [US1] Write test for Status column sortability in tests/Feature/CTKTableDisplayTest.php
- [X] T012 [US1] Run tests to verify US1 acceptance scenarios: php artisan test --filter=CTKTableDisplayTest

**Checkpoint**: Status column displays completion status correctly ✅

---

## Phase 3: User Story 2 - Monitor Detailed Progress (Priority: P2)

**Goal**: Verify Progress column continues to show "X/15 Z%" format unchanged

**Independent Test**: Examine Progress column, verify format "X/15 Z%" appears correctly

### Verification for User Story 2

- [X] T013 [US2] Verify Progress column configuration remains unchanged in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T014 [US2] Verify Progress column uses completion_progress accessor (no changes needed) in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T015 [US2] Verify Progress column description shows completion_percentage (no changes needed) in app/Filament/Resources/CTKS/Tables/CTKSTable.php

### Tests for User Story 2

- [X] T016 [US2] Write test for Progress column display format "X/15 Z%" in tests/Feature/CTKTableDisplayTest.php
- [X] T017 [US2] Write test verifying Progress column icon logic (check-circle vs clock) in tests/Feature/CTKTableDisplayTest.php
- [X] T018 [US2] Run tests to verify US2 acceptance scenarios: php artisan test --filter=CTKTableDisplayTest

**Checkpoint**: Progress column verified to work correctly without modification ✅

---

## Phase 4: User Story 3 - Simplified Table Navigation (Priority: P3)

**Goal**: Remove Tahap (Stage) column from table display

**Independent Test**: Verify Tahap column does not appear in any table view mode

### Implementation for User Story 3

- [X] T019 [US3] Remove current_stage TextColumn definition from CTKSTable.php columns array in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T020 [US3] Remove SelectFilter for current_stage from filters array in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T021 [US3] Verify column count is now 7 (was 8) in table configuration

### Tests for User Story 3

- [X] T022 [US3] Write test verifying Tahap column is not present in table in tests/Feature/CTKTableDisplayTest.php
- [X] T023 [US3] Write test verifying only 7 columns are displayed (NIK, Nama Lengkap, Status, Entitas, Progress, No. Telepon, Dibuat) in tests/Feature/CTKTableDisplayTest.php
- [X] T024 [US3] Write test verifying Stage filter is removed from filters panel in tests/Feature/CTKTableDisplayTest.php
- [X] T025 [US3] Run tests to verify US3 acceptance scenarios: php artisan test --filter=CTKTableDisplayTest

**Checkpoint**: Tahap column removed, table streamlined to 7 columns ✅

---

## Phase 5: Integration & Edge Cases

**Purpose**: Verify all user stories work together and handle edge cases

- [X] T026 Test edge case: CTK with 0/15 progress shows "Belum Lengkap" in tests/Feature/CTKTableDisplayTest.php
- [X] T027 Test edge case: CTK with 15/15 progress shows "Lengkap" in tests/Feature/CTKTableDisplayTest.php
- [X] T028 Verify table rendering with entity-scoped queries (LPK and PT users) in tests/Feature/CTKTableDisplayTest.php
- [X] T029 Test search functionality still works on remaining columns in tests/Feature/CTKTableDisplayTest.php
- [X] T030 Test existing Entity filter still functions correctly in tests/Feature/CTKTableDisplayTest.php
- [X] T031 Run all CTK table tests: php artisan test --filter=CTKTableDisplayTest

**Checkpoint**: All edge cases handled, integration verified ✅

---

## Phase 6: Code Quality & Finalization

**Purpose**: Ensure code meets quality standards and documentation is current

- [X] T032 Run Laravel Pint code formatter: vendor/bin/pint app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T033 Run Pint on test file: vendor/bin/pint tests/Feature/CTKTableDisplayTest.php
- [X] T034 Review modified code against Filament v4 best practices from research.md
- [X] T035 Verify no introduction of N+1 queries (status uses same accessor as Progress)
- [X] T036 Run full test suite to ensure no regressions: php artisan test --compact
- [X] T037 Verify quickstart.md instructions match implemented behavior
- [X] T038 Test manual verification steps from quickstart.md in browser

**Checkpoint**: Code formatted, tests passing, documentation accurate ✅

---

## Phase 7: Browser Testing & Validation

**Purpose**: Manual verification in browser to ensure UI matches specifications

- [ ] T039 Login as Admin LPK user and verify CTK list displays correctly (stages 1-5 only)
- [ ] T040 Login as Admin PT user and verify CTK list displays correctly (stages 6-15 only)
- [ ] T041 Verify "Lengkap" badge appears green for complete candidates
- [ ] T042 Verify "Belum Lengkap" badge appears orange/warning color for incomplete candidates
- [ ] T043 Test sorting by Status column groups complete/incomplete candidates
- [ ] T044 Verify Tahap column is not visible anywhere in the table
- [ ] T045 Verify Progress column continues to show "X/15 Z%" format
- [ ] T046 Test table on mobile/responsive view to ensure improved layout
- [ ] T047 Test export functionality includes new Status format (manual verification or export test)

**Checkpoint**: UI verified in browser, matches specification contract

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Start immediately - review tasks only
- **User Story 1 (Phase 2)**: Start after Setup - Core status display change
- **User Story 2 (Phase 3)**: Can start in parallel with US1 (verification only, no edits)
- **User Story 3 (Phase 4)**: Start after US1 complete (same file, must be sequential)
- **Integration (Phase 5)**: Start after US1, US2, US3 complete
- **Code Quality (Phase 6)**: Start after Integration tests pass
- **Browser Testing (Phase 7)**: Start after Code Quality complete

### User Story Implementation Order

Since all changes are in a single file (`CTKSTable.php`), tasks must be executed sequentially:

1. **User Story 1 (P1)**: Modify Status column logic (T004-T007)
2. **User Story 3 (P3)**: Remove Tahap column and filter (T019-T021)
3. **User Story 2 (P2)**: Verify Progress column (T013-T015) - read-only verification

**Rationale**: US1 and US3 both edit the same file and array structures, so sequential execution prevents merge conflicts. US2 is verification-only and can be checked at any point.

### Testing Order

Tests can be written in parallel after understanding requirements:

- **T008**: Create test file (first, blocks other test tasks)
- **T009-T012**: US1 tests (parallel after T008)
- **T016-T018**: US2 tests (parallel after T008)
- **T022-T025**: US3 tests (parallel after T008)
- **T026-T031**: Integration tests (after implementation complete)

### Parallel Opportunities

#### Limited Parallel Execution (Single File Change)

Since this feature modifies only one file, true parallelization is limited:

**Can run in parallel**:
- T002 (review model) + T001 (review table) + T003 (review filters) - different files
- T009-T011 (write different tests) - after T008
- T016-T017 (write US2 tests) parallel with T009-T011
- T022-T024 (write US3 tests) parallel with T009-T011 and T016-T017
- T032-T034 (code quality checks) - different static analysis tasks

**Must be sequential**:
- T004-T007 (Status column changes) → T019-T021 (Tahap removal) - same file arrays
- All implementation before tests run
- Test runs before browser testing

---

## Parallel Example: Test Writing

After implementation is complete (T004-T021), tests can be written in parallel by different team members:

```bash
# Team Member 1: US1 Status Tests
- [ ] T009 [US1] Test "Lengkap" display
- [ ] T010 [US1] Test "Belum Lengkap" display  
- [ ] T011 [US1] Test Status sortability

# Team Member 2: US2 Progress Tests (parallel)
- [ ] T016 [US2] Test Progress format "X/15 Z%"
- [ ] T017 [US2] Test Progress icon logic

# Team Member 3: US3 Removal Tests (parallel)
- [ ] T022 [US3] Test Tahap column absence
- [ ] T023 [US3] Test 7-column count
- [ ] T024 [US3] Test Stage filter removal
```

---

## Sequential Example: Recommended MVP Implementation

For a single developer implementing the complete feature:

**Day 1: Foundation + US1 (MVP Core)**
1. T001-T003: Review existing code (30 min)
2. T004-T007: Implement Status column logic (1-2 hours)
3. T008: Create test file (10 min)
4. T009-T012: Write and run US1 tests (1 hour)
5. Manual verification in browser (30 min)

**Day 2: US3 + US2 Verification**
1. T019-T021: Remove Tahap column/filter (30 min)
2. T022-T025: Write and run US3 tests (45 min)
3. T013-T015: Verify Progress (review, 15 min)
4. T016-T018: Write and run US2 tests (30 min)

**Day 3: Integration + Finalization**
1. T026-T031: Integration tests (1 hour)
2. T032-T038: Code quality and full test run (30 min)
3. T039-T049: Browser testing across roles (1-2 hours)

**Total Estimated Time**: 2-3 days for full feature with comprehensive testing

---

## MVP Delivery Options

### Option 1: User Story 1 Only (Minimum Viable Product)

**Delivers**: Status shows "Lengkap"/"Belum Lengkap"  
**Tasks**: T001-T012  
**Value**: HR admins can instantly identify complete vs incomplete candidates  
**Time**: ~4 hours including tests

### Option 2: US1 + US3 (Recommended MVP)

**Delivers**: New status display + remove Tahap column  
**Tasks**: T001-T025  
**Value**: Complete UI simplification  
**Time**: ~1 day including tests

### Option 3: Full Feature (All User Stories)

**Delivers**: Everything in specification  
**Tasks**: T001-T049  
**Value**: Complete, tested, production-ready implementation  
**Time**: 2-3 days including comprehensive testing

---

## Summary

**Total Tasks**: 49
- Setup: 3
- User Story 1 (P1): 9 tasks (4 implementation + 5 tests)
- User Story 2 (P2): 6 tasks (3 verification + 3 tests)
- User Story 3 (P3): 7 tasks (3 implementation + 4 tests)
- Integration: 6 tasks
- Code Quality: 7 tasks
- Browser Testing: 9 tasks
- Polish: 2 tasks (within other phases)

**Files Modified**: 1 (`app/Filament/Resources/CTKS/Tables/CTKSTable.php`)  
**Files Created**: 1 (`tests/Feature/CTKTableDisplayTest.php`)  
**Database Migrations**: 0  
**Estimated Effort**: 2-3 days (includes comprehensive testing and validation)

**MVP Scope**: Tasks T001-T025 deliver 95% of user value (Status + Tahap removal)  
**Parallel Opportunities**: Test writing (T009-T024 after T008), static analysis (T032-T034)  
**Critical Path**: T001 → T004-T007 → T019-T021 → T026-T031 → T032-T038 → T039-T049
