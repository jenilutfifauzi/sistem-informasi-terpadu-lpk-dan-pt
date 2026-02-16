---
description: "Task list for Data Export Functionality implementation"
---

# Tasks: Data Export Functionality

**Input**: Design documents from `/specs/008-data-export-buttons/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/export-actions.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Install dependencies and create directory structure for export functionality

- [X] T001 Install Laravel Excel package via composer require maatwebsite/excel
- [X] T002 [P] Create export directory structure: app/Filament/Exports/
- [X] T003 [P] Create test directory structure: tests/Feature/Exports/
- [X] T004 Verify Laravel Excel installation: composer show maatwebsite/excel

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: No foundational tasks required - all infrastructure (Filament, Livewire, Activity Log) already exists

**Status**: ✅ Foundation ready - user story implementation can begin

---

## Phase 3: User Story 1 - Export LPK Employee Data (Priority: P1) 🎯 MVP

**Goal**: Enable administrators to export Karyawan LPK data in CSV or Excel format with filtering support and audit logging

**Independent Test**: Login as admin → Navigate to Karyawan LPK list → Click "Export Data" button → Select Excel format → Verify download contains all visible employee records → Check activity log for export entry

### Implementation for User Story 1

- [X] T005 [P] [US1] Create EmployeeLPKExport class in app/Filament/Exports/EmployeeLPKExport.php
- [X] T006 [US1] Add export header action to EmployeeLPKResource in app/Filament/Resources/EmployeeLPKResource.php
- [X] T007 [P] [US1] Create EmployeeLPKExportTest in tests/Feature/Exports/EmployeeLPKExportTest.php
- [X] T008 [US1] Run tests for User Story 1: php artisan test --filter=EmployeeLPKExport
- [ ] T009 [US1] Manual testing: verify export button appears, downloads work, NIK excluded, activity logged
- [X] T010 [US1] Run code formatter: vendor/bin/pint

**Checkpoint**: Karyawan LPK export should be fully functional with CSV/Excel support, filter respect, and audit logging

---

## Phase 4: User Story 2 - Export CTK Data (Priority: P1)

**Goal**: Enable recruitment coordinators to export CTK data including screening status, MCU results, and current stages

**Independent Test**: Login as recruitment coordinator → Navigate to CTK list → Apply filters (e.g., status = "In Progress") → Click "Export Data" → Select CSV format → Verify only filtered CTK records downloaded → Check sensitive fields (NIK, passport, visa) excluded

### Implementation for User Story 2

- [X] T011 [P] [US2] Create CTKExport class in app/Filament/Exports/CTKExport.php
- [X] T012 [US2] Add export header action to CTKResource in app/Filament/Resources/CTKS/CTKResource.php
- [X] T013 [P] [US2] Create CTKExportTest in tests/Feature/Exports/CTKExportTest.php
- [X] T014 [US2] Run tests for User Story 2: php artisan test --filter=CTKExport
- [ ] T015 [US2] Manual testing: verify CTK export with relationships (screening, MCU), sensitive field exclusion
- [X] T016 [US2] Run code formatter: vendor/bin/pint

**Checkpoint**: CTK export should work with complex relationships and proper sensitive field exclusion

---

## Phase 5: User Story 3 - Export User Account Data (Priority: P2)

**Goal**: Enable system administrators to export user accounts for access audits and compliance reporting

**Independent Test**: Login as admin → Navigate to Users list → Filter by role (e.g., "Admin LPK") → Click "Export Data" → Select Excel → Verify users exported with roles but passwords excluded → Check activity log

### Implementation for User Story 3

- [X] T017 [P] [US3] Create UserExport class in app/Filament/Exports/UserExport.php
- [X] T018 [US3] Add export header action to UserResource in app/Filament/Resources/Users/UserResource.php
- [X] T019 [P] [US3] Create UserExportTest in tests/Feature/Exports/UserExportTest.php
- [X] T020 [US3] Run tests for User Story 3: php artisan test --filter=UserExport
- [ ] T021 [US3] Manual testing: verify user export, password exclusion, role information included
- [X] T022 [US3] Run code formatter: vendor/bin/pint

**Checkpoint**: User export should properly exclude passwords and include role information

---

## Phase 6: User Story 4 - Export PT Asset Data (Priority: P3)

**Goal**: Enable asset managers to export asset inventory for financial reconciliation and audits

**Independent Test**: Login as asset manager → Navigate to Assets list → Filter by category → Click "Export Data" → Select CSV → Verify assets exported with assignment info and condition

### Implementation for User Story 4

- [X] T023 [P] [US4] Create AssetExport class in app/Filament/Exports/AssetExport.php
- [X] T024 [US4] Add export header action to AssetResource in app/Filament/Resources/Assets/AssetResource.php
- [X] T025 [P] [US4] Create AssetExportTest in tests/Feature/Exports/AssetExportTest.php
- [X] T026 [US4] Run tests for User Story 4: php artisan test --filter=AssetExport
- [ ] T027 [US4] Manual testing: verify asset export with assignment details and depreciation info
- [X] T028 [US4] Run code formatter: vendor/bin/pint

**Checkpoint**: All four resources should have functional export capability

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, documentation, and quality checks

- [X] T029 [P] Run full test suite: php artisan test --compact
- [X] T030 [P] Verify all exports logged in activity_log table
- [X] T031 [P] Test with large datasets (1000+ records) for performance validation
- [X] T032 [P] Test edge cases: empty datasets, special characters in data, concurrent exports
- [ ] T033 Update AGENTS.md if new export patterns should be documented
- [X] T034 Final code format: vendor/bin/pint
- [ ] T035 Create feature demo/walkthrough following quickstart.md validation steps

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: ✅ Already complete (Filament, Livewire, Activity Log exist)
- **User Stories (Phase 3-6)**: Depend on Phase 1 completion
  - All user stories are independent of each other
  - Can proceed in parallel (if multiple developers)
  - Or sequentially by priority (P1 → P1 → P2 → P3)
- **Polish (Phase 7)**: Depends on desired user stories being complete (minimum US1+US2 for MVP)

### User Story Dependencies

- **User Story 1 (P1)**: Depends only on Phase 1 Setup - No dependencies on other stories
- **User Story 2 (P1)**: Depends only on Phase 1 Setup - No dependencies on other stories
- **User Story 3 (P2)**: Depends only on Phase 1 Setup - No dependencies on other stories
- **User Story 4 (P3)**: Depends only on Phase 1 Setup - No dependencies on other stories

**Key Insight**: All user stories are truly independent - can be implemented in any order or in parallel

### Within Each User Story

1. Create Export class (can be parallel with test creation)
2. Add header action to Resource (depends on Export class)
3. Create tests (can be parallel with implementation)
4. Run tests (depends on implementation)
5. Manual testing (depends on tests passing)
6. Code formatting (final step)

### Parallel Opportunities

**Phase 1 (Setup)**:
- T002 and T003 (directory creation) can run in parallel

**Within Each User Story**:
- Export class creation and Test creation can happen in parallel (different files)
- Example for US1: T005 (Export class) and T007 (Tests) can run in parallel

**Across User Stories** (with multiple developers):
- Once Phase 1 complete, all 4 user stories can be worked on in parallel:
  - Developer A: US1 (Karyawan LPK)
  - Developer B: US2 (CTK)
  - Developer C: US3 (Users)
  - Developer D: US4 (Assets)

**Phase 7 (Polish)**:
- T029, T030, T031, T032 (various testing activities) can run in parallel

---

## Parallel Example: User Story 1

If working on User Story 1 with parallelization:

```bash
# Can run in parallel:
Task T005: Create EmployeeLPKExport class in app/Filament/Exports/EmployeeLPKExport.php
Task T007: Create EmployeeLPKExportTest in tests/Feature/Exports/EmployeeLPKExportTest.php

# Then sequential:
Task T006: Add export header action to EmployeeLPKResource (needs T005)
Task T008: Run tests (needs T005, T006, T007)
Task T009: Manual testing (needs T008 passing)
Task T010: Code formatting (final)
```

---

## Parallel Example: Multiple User Stories

With 4 developers after Phase 1 completion:

```bash
# All can start simultaneously:
Team Member 1: Phase 3 (US1 - Karyawan LPK)  → T005-T010
Team Member 2: Phase 4 (US2 - CTK)           → T011-T016
Team Member 3: Phase 5 (US3 - Users)         → T017-T022
Team Member 4: Phase 6 (US4 - Assets)        → T023-T028

# When all complete:
Everyone: Phase 7 (Polish) → T029-T035
```

---

## Implementation Strategy

### MVP First (Recommended for Solo Developer)

**Minimum Viable Product = User Stories 1 + 2 (both P1)**

1. ✅ Complete Phase 1: Setup (T001-T004)
2. ✅ Skip Phase 2: Already complete
3. ✅ Complete Phase 3: User Story 1 - Karyawan LPK (T005-T010)
4. **STOP and VALIDATE**: Test export works, filters respected, activity logged
5. ✅ Complete Phase 4: User Story 2 - CTK (T011-T016)
6. **STOP and VALIDATE**: Test CTK export with relationships and sensitive field exclusion
7. Demo/Deploy MVP (both P1 requirements met)
8. ✅ Add Phase 5: User Story 3 - Users (T017-T022) when needed
9. ✅ Add Phase 6: User Story 4 - Assets (T023-T028) when needed
10. ✅ Complete Phase 7: Polish (T029-T035)

### Incremental Delivery Strategy

**Delivery 1 (MVP)**: Phase 1 → Phase 3 (US1)
- Delivers: Karyawan LPK export functionality
- Value: HR can export employee data
- Time: ~2-3 hours

**Delivery 2**: Add Phase 4 (US2)
- Delivers: CTK export functionality
- Value: Recruitment can export candidate data
- Time: +2-3 hours

**Delivery 3**: Add Phase 5 (US3)
- Delivers: User export functionality
- Value: Admins can audit user accounts
- Time: +1-2 hours

**Delivery 4 (Complete)**: Add Phase 6 (US4) + Phase 7 (Polish)
- Delivers: Asset export + full polish
- Value: Finance can export asset inventory
- Time: +2-3 hours

**Total Time**: ~8-11 hours for complete feature

### Parallel Team Strategy

With 2 developers after Phase 1:

**Week 1**:
- Dev A: Phase 3 (US1) + Phase 4 (US2)
- Dev B: Phase 5 (US3) + Phase 6 (US4)
- Both: Phase 7 (Polish) together

**Result**: All 4 exports complete in ~1 week vs 2 weeks solo

---

## Task Count Summary

- **Setup**: 4 tasks
- **Foundational**: 0 tasks (already complete)
- **User Story 1 (P1)**: 6 tasks
- **User Story 2 (P1)**: 6 tasks
- **User Story 3 (P2)**: 6 tasks
- **User Story 4 (P3)**: 6 tasks
- **Polish**: 7 tasks

**Total**: 35 tasks

**Parallel Tasks**: 11 tasks can run in parallel (marked with [P])
**Sequential Tasks**: 24 tasks must run sequentially

**Estimated Time**:
- Solo developer (sequential): 8-11 hours
- 2 developers (parallel): 5-7 hours
- 4 developers (parallel): 3-5 hours

---

## Testing Validation Per User Story

### User Story 1 Validation (Karyawan LPK)
- [ ] Export button visible on Karyawan LPK list page
- [ ] Modal opens with format selection (CSV/Excel)
- [ ] Excel export downloads with correct filename format
- [ ] CSV export downloads with correct filename format
- [ ] Export respects table filters (test with status filter)
- [ ] NIK field excluded from export
- [ ] Enum values show labels (e.g., "Aktif" not enum value)
- [ ] Activity log entry created with correct properties
- [ ] Empty dataset shows warning notification

### User Story 2 Validation (CTK)
- [ ] Export includes screening results (relationship data)
- [ ] Export includes MCU status (relationship data)
- [ ] NIK, nomor_paspor, visa_number all excluded
- [ ] Export respects date range filters
- [ ] Large CTK dataset (1000+ records) exports without timeout
- [ ] Activity log entry created

### User Story 3 Validation (Users)
- [ ] Password field excluded from export
- [ ] Roles included (displayed as comma-separated names)
- [ ] Export respects role filter
- [ ] Activity log entry created

### User Story 4 Validation (Assets)
- [ ] Asset assignment information included
- [ ] Export respects category filter
- [ ] Depreciation-related fields included (purchase date, value, condition)
- [ ] Activity log entry created

---

## Notes

- **[P] tasks**: Can run in parallel (different files, no dependencies)
- **[Story] labels**: Map tasks to user stories for independent tracking
- **File paths**: All paths are absolute from repository root
- **Tests**: Feature tests verify complete user journey (button → download → file content → logging)
- **Code formatting**: Run vendor/bin/pint after each user story completion
- **Constitution compliance**: All tasks follow existing patterns (no violations)
- **Reference docs**: See quickstart.md for detailed implementation steps per task
- **Contracts**: All implementations must follow contracts/export-actions.md
- **Security**: Sensitive field exclusion is CRITICAL - review before each merge

---

## Quick Reference

- **Quickstart Guide**: [quickstart.md](quickstart.md)
- **Contracts**: [contracts/export-actions.md](contracts/export-actions.md)
- **Research**: [research.md](research.md)
- **Data Model**: [data-model.md](data-model.md)
- **Specification**: [spec.md](spec.md)
- **Plan**: [plan.md](plan.md)
