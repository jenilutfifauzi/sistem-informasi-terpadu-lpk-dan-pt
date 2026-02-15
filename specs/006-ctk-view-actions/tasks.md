# Tasks: CTK Index Action Buttons

**Input**: Design documents from `/specs/006-ctk-view-actions/`
**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [quickstart.md](quickstart.md)

**Tests**: Tests are MANDATORY per constitution (Principle V). All features require PHPUnit tests.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

Laravel application structure:
- `app/` - Application code
- `tests/Feature/` - Feature tests with database transactions
- `app/Filament/Resources/CTKS/` - CTK Filament resources

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify environment and dependencies are ready

- [X] T001 Verify Filament 4.0+ and Livewire 3 are installed (check composer.json)
- [X] T002 Verify Spatie Activity Log is configured (check config/activitylog.php)
- [X] T003 [P] Run `composer install` and `npm install` to ensure dependencies are current

**Checkpoint**: Development environment ready

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core business logic that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Add `canAdvanceToStage(int $targetStage): bool` method to app/Models/CTK.php
- [X] T005 Implement stage validation business rules in CTK::canAdvanceToStage() (cannot go backward, cannot skip stages, stage 15 is immutable)
- [X] T006 [P] Create test base class with database transaction setup in tests/Feature/CTKActionsTestBase.php
- [X] T007 [P] Create test data factories for CTK with various stages and entities (if not exists)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Quick View CTK Details (Priority: P1) 🎯 MVP

**Goal**: Add visible "View" action button to CTK index table that navigates to detail page

**Independent Test**: Click "View" button on any CTK row and verify it opens the CTK detail page

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [X] T008 [P] [US1] Create tests/Feature/CTKViewActionTest.php with database transaction setup
- [X] T009 [P] [US1] Test: all authenticated users can see View action button on CTK rows
- [X] T010 [P] [US1] Test: clicking View action navigates to ViewCTK page with correct record

### Implementation for User Story 1

- [X] T011 [US1] Add ViewAction to ->actions() array in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T012 [US1] Configure ViewAction with proper URL using CTKResource::getUrl('view', ['record' => $record])
- [X] T013 [US1] Add eye icon (heroicon-o-eye) to ViewAction for visual clarity
- [X] T014 [US1] Verify ViewAction appears in table by loading http://localhost:8000/admin/c-t-k-s

**Checkpoint**: At this point, View action should be fully functional and testable independently

---

## Phase 4: User Story 2 - Manage CTK Stage Progress (Priority: P1) 🎯 MVP

**Goal**: Add "Kelola Progress" action button that opens modal for quick stage updates

**Independent Test**: Click "Kelola Progress", update stage in modal, verify CTK stage updates and table refreshes

### Tests for User Story 2

- [X] T015 [P] [US2] Create tests/Feature/CTKManageProgressActionTest.php with database transaction setup
- [X] T016 [P] [US2] Test: Kelola Progress action opens modal with current stage pre-filled
- [X] T017 [P] [US2] Test: updating stage to valid next stage succeeds and creates activity log
- [X] T018 [P] [US2] Test: attempting to update to invalid stage (skip, backward, or violating prerequisites) shows error
- [X] T019 [P] [US2] Test: updating CTK at stage 15 (Terbang) is prevented (immutable final stage)
- [X] T020 [P] [US2] Test: table auto-refreshes after successful stage update without full page reload

### Implementation for User Story 2

- [X] T021 [US2] Add Action::make('kelola_progress') to ->actions() array in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T022 [US2] Configure modal heading, icon (heroicon-o-arrow-path), and submit button label
- [X] T023 [US2] Add form components: Select for current_stage with all 15 stages as options
- [X] T024 [US2] Set current_stage default value to $record->current_stage in form
- [X] T025 [US2] Add stage-specific form fields (e.g., MCU status Select shown when stage=1 using reactive visibility)
- [X] T026 [US2] Add optional Textarea for notes field in form
- [X] T027 [US2] Implement ->action() callback: validate with CTK::canAdvanceToStage(), update record, log activity
- [X] T028 [US2] Add Notification::make() for success (green) and error (red) feedback
- [X] T029 [US2] Add Spatie Activity Log entry with old_stage, new_stage, and notes in properties
- [X] T030 [US2] Verify Livewire auto-refreshes table showing updated stage and progress

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - Role-Based Action Visibility (Priority: P2)

**Goal**: Show/hide "Kelola Progress" action based on user role and CTK entity/stage

**Independent Test**: Login with different roles and verify only authorized users see "Kelola Progress" for appropriate CTKs

### Tests for User Story 3

- [ ] T031 [P] [US3] Create tests/Feature/CTKActionAuthorizationTest.php with database transaction setup
- [ ] T032 [P] [US3] Test: Admin LPK sees Kelola Progress for LPK CTKs (stages 1-5) only
- [ ] T033 [P] [US3] Test: Admin LPK does NOT see Kelola Progress for PT CTKs (stages 6-15)
- [ ] T034 [P] [US3] Test: Admin PT sees Kelola Progress for PT CTKs (stages 6-15) only
- [ ] T035 [P] [US3] Test: Admin PT does NOT see Kelola Progress for LPK CTKs (stages 1-5)
- [ ] T036 [P] [US3] Test: Pimpinan sees View action but NOT Kelola Progress (read-only role)
- [ ] T037 [P] [US3] Test: Super Admin sees Kelola Progress for ALL CTKs regardless of entity/stage
- [ ] T038 [P] [US3] Test: HR PT, Legal PT, Keuangan PT see Kelola Progress for PT CTKs only

### Implementation for User Story 3

- [ ] T039 [US3] Add ->visible() callback to kelola_progress action in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [ ] T040 [US3] Implement role check: if super_admin, return true (full access)
- [ ] T041 [US3] Implement role check: if Pimpinan, return false (read-only, no Kelola Progress)
- [ ] T042 [US3] Implement entity/stage check for Admin LPK: visible only if record is LPK entity AND stages 1-5
- [ ] T043 [US3] Implement entity/stage check for Admin PT/HR PT/Legal PT/Keuangan PT: visible only if record is PT entity AND stages 6-15
- [ ] T044 [US3] Add user entity verification: user->entity must match record->current_entity
- [ ] T045 [US3] Test with different user roles manually via http://localhost:8000/admin/c-t-k-s

**Checkpoint**: All user stories should now be independently functional with proper authorization

---

## Phase 6: User Story 4 - Batch Progress Actions (Priority: P3)

**Goal**: Allow bulk selection and progress updates for multiple CTKs simultaneously

**Independent Test**: Select multiple CTKs, apply bulk "Kelola Progress", verify all updated correctly with success/failure summary

### Tests for User Story 4

- [ ] T046 [P] [US4] Create tests/Feature/CTKBulkProgressActionTest.php with database transaction setup
- [ ] T047 [P] [US4] Test: bulk action appears when multiple CTKs are selected via checkboxes
- [ ] T048 [P] [US4] Test: bulk update succeeds for all CTKs meeting prerequisites, shows success count
- [ ] T049 [P] [US4] Test: bulk update shows failure count for CTKs not meeting prerequisites
- [ ] T050 [P] [US4] Test: bulk update creates activity log entry for each successfully updated CTK
- [ ] T051 [P] [US4] Test: bulk action respects authorization (users only bulk-update CTKs they can modify)

### Implementation for User Story 4

- [ ] T052 [US4] Add BulkAction::make('bulk_kelola_progress') to ->bulkActions() array in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [ ] T053 [US4] Configure bulk action label, icon, and modal heading
- [ ] T054 [US4] Add simplified form: Select for new stage (without stage-specific fields for simplicity)
- [ ] T055 [US4] Implement ->action() callback receiving Collection $records
- [ ] T056 [US4] Loop through each record, validate with canAdvanceToStage(), update if valid, skip if invalid
- [ ] T057 [US4] Track success count and failure count during bulk update loop
- [ ] T058 [US4] Log activity for each successful update with bulk update indicator
- [ ] T059 [US4] Show summary notification: "X CTK berhasil diperbarui, Y CTK gagal"
- [ ] T060 [US4] Add ->deselectRecordsAfterCompletion() to clear selection after bulk action

**Checkpoint**: All user stories complete including bulk operations

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements and cleanup that affect multiple user stories

### Concurrent Update Protection (FR-014)

- [X] T061 [P] Test: concurrent update scenario - two users edit same CTK simultaneously, second update shows conflict notification
- [X] T062 Add `updated_at` timestamp check to Kelola Progress ->action() callback in app/Filament/Resources/CTKS/Tables/CTKSTable.php
- [X] T063 Implement conflict detection: compare form's hidden `original_updated_at` with DB record's current `updated_at` before save
- [X] T064 Add Notification for conflict: "This CTK was updated by [last user] at [time]. Please reload and reapply changes."

### Code Quality & Documentation

- [X] T065 [P] Add inline code comments explaining role-based visibility logic in CTKSTable.php
- [X] T066 [P] Add PHPDoc blocks for CTK::canAdvanceToStage() method documenting validation rules
- [X] T067 Run `vendor/bin/pint` to format all PHP changes per Laravel Pint standards
- [X] T068 Run full test suite `php artisan test --compact` and verify tests pass
- [X] T069 Verify quickstart.md manual testing checklist (all 7 test scenarios) passes
- [X] T070 [P] Update CHANGELOG or feature documentation if project maintains one

### Performance & Accessibility

- [ ] T071 Check for N+1 query issues when loading CTK table with actions (use Laravel Debugbar if available)
- [ ] T072 Verify table performance with 100+ CTK records loaded
- [ ] T073 Test responsive layout on mobile/tablet (action buttons should remain accessible)
- [ ] T074 [P] Add browser console check for JavaScript errors during action execution

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-6)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 US1 → P1 US2 → P2 US3 → P3 US4)
- **Polish (Phase 7)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories ✅ FULLY INDEPENDENT
- **User Story 2 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories ✅ FULLY INDEPENDENT
- **User Story 3 (P2)**: Depends on US2 implementation (adds visibility control to US2's action) - Can test independently by verifying authorization
- **User Story 4 (P3)**: Similar to US2 but for bulk operations - Can start after Foundational, independently testable

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Business validation (Phase 2) before action implementation
- Core action before role-based visibility
- Single actions before bulk actions
- Story complete before moving to next priority

### Parallel Opportunities

- **Phase 1**: All tasks marked [P] can run in parallel (T002, T003)
- **Phase 2**: T006 and T007 can run in parallel after T004-T005 complete
- **Phase 3**: All tests (T008-T010) can be written in parallel
- **Phase 4**: All tests (T015-T020) can be written in parallel
- **Phase 5**: All tests (T031-T038) can be written in parallel
- **Phase 6**: All tests (T046-T051) can be written in parallel
- **Phase 7**: Most tasks marked [P] can run in parallel (T061, T062, T066, T070)
- **User Stories**: US1 and US2 can be worked simultaneously (different concerns), US3 adds to US2 after US2 completes, US4 is independent

---

## Parallel Example: User Story 2

```bash
# Write all tests for US2 in parallel:
Task T015: "Create tests/Feature/CTKManageProgressActionTest.php"
Task T016: "Test: modal opens with current stage"
Task T017: "Test: valid update succeeds with activity log"
Task T018: "Test: invalid update shows error"
Task T019: "Test: immutable final stage prevented"
Task T020: "Test: table auto-refreshes"

# After tests written, implement sequentially:
Task T021: "Add Action::make('kelola_progress')"
Task T022: "Configure modal properties"
# ... etc
```

---

## Implementation Strategy

### MVP First (User Stories 1 & 2 Only)

1. Complete Phase 1: Setup (verify environment)
2. Complete Phase 2: Foundational (validation logic + test base) - **CRITICAL GATE**
3. Complete Phase 3: User Story 1 (View action)
4. Complete Phase 4: User Story 2 (Kelola Progress action)
5. **STOP and VALIDATE**: Test both stories independently via quickstart.md scenarios
6. Deploy/demo if ready - **This is a viable MVP!**

### Incremental Delivery

1. **Foundation** (Phases 1-2) → Business validation ready
2. **Add US1** (Phase 3) → Test independently → View action works ✅
3. **Add US2** (Phase 4) → Test independently → Progress management works ✅ **MVP COMPLETE**
4. **Add US3** (Phase 5) → Test independently → Role-based security works ✅
5. **Add US4** (Phase 6) → Test independently → Bulk operations work ✅
6. **Polish** (Phase 7) → Production-ready

Each phase adds value without breaking previous functionality.

### Parallel Team Strategy

With multiple developers:

1. **Team together**: Complete Phases 1-2 (Setup + Foundational)
2. **Once Foundational done**:
   - **Developer A**: User Story 1 (Phase 3) - View action
   - **Developer B**: User Story 2 (Phase 4) - Kelola Progress action
   - Both can work in parallel since different concerns
3. **After US2 complete**:
   - **Developer C**: User Story 3 (Phase 5) - Add visibility to US2's action
4. **After US2 complete**:
   - **Developer D**: User Story 4 (Phase 6) - Bulk actions (similar to US2)
5. **Team together**: Polish (Phase 7)

Stories complete and integrate independently.

---

## Testing Strategy (Per DATABASE_SAFETY.md)

**CRITICAL - DO NOT USE RefreshDatabase**

All tests MUST use database transactions:

```php
use Illuminate\Support\Facades\DB;

class CTKViewActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction(); // Start transaction
    }

    protected function tearDown(): void
    {
        DB::rollBack(); // Rollback all changes
        parent::tearDown();
    }
    
    /** @test */
    public function test_view_action_visible()
    {
        // Test code here - changes auto-rollback
    }
}
```

**Why**: Prevents database resets, faster tests, safer for production-like data.

---

## Estimated Effort

- **Phase 1 (Setup)**: 15 minutes - environment verification
- **Phase 2 (Foundational)**: 1-2 hours - business validation logic + test base
- **Phase 3 (US1 - View)**: 1 hour - simple action, minimal tests
- **Phase 4 (US2 - Kelola Progress)**: 3-4 hours - modal form, validation, activity logging
- **Phase 5 (US3 - Role Visibility)**: 2-3 hours - authorization logic + extensive role tests
- **Phase 6 (US4 - Bulk)**: 2-3 hours - similar to US2 but with collection handling
- **Phase 7 (Polish)**: 1-2 hours - formatting, performance checks, documentation

**Total**: 10-16 hours for complete implementation with all user stories and tests

**MVP (US1 + US2)**: 5-7 hours

---

## Notes

- [P] tasks = different files, no dependencies, can parallelize
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Tests MUST fail before implementation (TDD approach)
- Commit after each logical task group (e.g., after each user story phase)
- Stop at any checkpoint to validate story independently
- Constitution compliance verified: RBAC ✅, Audit ✅, Incremental ✅, Tests ✅
- No database migrations needed (UI-only feature)
- Leverage existing Filament patterns (no custom Livewire components needed)
