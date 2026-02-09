# Tasks: Employee Asset Management System

**Feature**: 004-asset-management  
**Input**: Design documents from `/specs/004-asset-management/`  
**Prerequisites**: ✅ plan.md, ✅ spec.md, ✅ research.md, ✅ data-model.md, ✅ quickstart.md  
**Branch**: `004-asset-management`  
**Generated**: February 8, 2026

## Format: `- [ ] [TaskID] [P?] [Story?] Description with file path`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4, US5)
- All file paths are absolute or relative to repository root `/Users/indobuzz/Documents/Local/SIT_LPK`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure  
**Duration**: ~30 minutes  
**Assignable**: Any developer

- [X] T001 Verify branch `004-asset-management` is checked out and clean
- [X] T002 [P] Run `php artisan --version` to confirm PHP 8.4.5 and Laravel 11
- [X] T003 [P] Verify Filament v4, Spatie Activity Log, and Filament Shield installed via `composer show`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete (especially enums and migrations)

### Enums (Core Data Types)

- [X] T004 [P] Create AssetCategory enum in `app/Enums/AssetCategory.php` with values: Elektronik, Furniture, DokumenIjin, PerlengkapanKantor, Kendaraan, Lainnya (include abbreviation(), color(), getLabel() methods per data-model.md)
- [X] T005 [P] Create AssetCondition enum in `app/Enums/AssetCondition.php` with values: Baik, Rusak (include getLabel(), getColor() methods)
- [X] T006 [P] Create AssetAssignmentStatus enum in `app/Enums/AssetAssignmentStatus.php` with values: Available, Assigned (include getLabel(), getColor() methods)

### Database Migrations

- [X] T007 Create migration `2026_02_08_000001_create_assets_table.php` with schema: id, entity, kategori, nomor_inventaris (unique), nama_barang, deskripsi (nullable), jumlah, satuan, kondisi, status_assignment, tahun_pembelian, nilai_pembelian, lokasi (nullable), keterangan (nullable), created_by (FK users), updated_by (FK users), timestamps, softDeletes, indexes per data-model.md
- [X] T008 Create migration `2026_02_08_000002_create_asset_assignments_table.php` with schema: id, asset_id (FK assets), assignable_type, assignable_id, assigned_by (FK users), assigned_date, return_date (nullable), return_notes (nullable), timestamps, polymorphic indexes
- [X] T009 Create migration `2026_02_08_000003_create_asset_condition_histories_table.php` with schema: id, asset_id (FK assets), old_condition, new_condition, reason (nullable), changed_by (FK users), changed_at (timestamp), indexes
- [X] T010 Run `php artisan migrate` to execute all three migrations and verify tables created

### Helper Classes

- [X] T011 Create `app/Helpers/AssetNumberGenerator.php` with static method `generate(EntityType $entity, AssetCategory $kategori, int $tahun): string` that returns format `[PT/LPK]-[KATEGORI_ABBR]-[TAHUN]-[SEQUENCE]` using DB locking per research.md Decision 3

### Models (Core Eloquent Models)

- [X] T012 [P] Create Asset model in `app/Models/Asset.php` with fillable, casts (use enum casting for entity, kategori, kondisi, status_assignment), SoftDeletes trait, LogsActivity trait, relationships (creator, updater, assignments, currentAssignment, conditionHistories), scopes (byEntity, available, assigned, inGoodCondition, needsRepair)
- [X] T013 [P] Create AssetAssignment model in `app/Models/AssetAssignment.php` with fillable, relationships (asset, assignable as morphTo, assignedBy), scopes (active, returned, forEntity), accessors (getIsActiveAttribute, getDurationDaysAttribute)
- [X] T014 [P] Create AssetConditionHistory model in `app/Models/AssetConditionHistory.php` with fillable, `public $timestamps = false`, relationships (asset, changedBy), scopes (forAsset, recent)

### Observer (Business Logic Automation)

- [X] T015 Create AssetObserver in `app/Observers/AssetObserver.php` with methods: creating() to auto-set entity and nomor_inventaris via AssetNumberGenerator and created_by, updating() to prevent entity change and log condition changes to asset_condition_histories and set updated_by
- [X] T016 Register AssetObserver in `app/Providers/EventServiceProvider.php` boot() method with `Asset::observe(AssetObserver::class);`

### Permissions Seeder

- [X] T017 Create `database/seeders/AssetPermissionsSeeder.php` to create permissions: view_asset, view_any_asset, create_asset, update_asset, delete_asset, restore_asset, force_delete_asset and assign to roles: Admin LPK (all for LPK), Admin PT (all for PT), Keuangan LPK/PT (view only), Pimpinan (view_any, view for all entities)
- [X] T018 Run `php artisan db:seed --class=AssetPermissionsSeeder` and verify permissions created

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Asset Registration (P1) 🎯 MVP

**Goal**: Enable Admin PT/LPK to register and manage asset inventory with full CRUD operations, search, and filtering

**Independent Test**: Admin LPK login → navigate to Asset LPK menu → create new Laptop asset (3 units, Baik condition) → save → verify entity='LPK' auto-set, nomor_inventaris generated, asset visible in list → search for "Laptop" → verify found → update condition to Rusak → verify updated

**Priority**: P1 MVP - MUST complete before deployment

### Factories & Seeders

- [X] T019 [P] [US1] Create AssetFactory in `database/factories/AssetFactory.php` with realistic Indonesian equipment names, varied categories, conditions (80% Baik, 20% Rusak), entity (PT/LPK), jumlah (1-10), tahun (2020-2026), nilai (1M-50M IDR)
- [X] T020 [P] [US1] Create AssetDemoSeeder in `database/seeders/AssetDemoSeeder.php` to generate 25 LPK assets + 25 PT assets using AssetFactory and run `php artisan db:seed --class=AssetDemoSeeder`

### Form Request Validation

- [X] T021 [P] [US1] Create StoreAssetRequest in `app/Http/Requests/StoreAssetRequest.php` with validation rules: nama_barang (required, max 255), kategori (required, enum AssetCategory), jumlah (required, integer, min:1), satuan (required, max 50), kondisi (required, enum AssetCondition), tahun_pembelian (required, integer, between:1900,current_year), nilai_pembelian (nullable, numeric, min:0), lokasi (nullable, max 255), keterangan (nullable), deskripsi (nullable)
- [X] T022 [P] [US1] Create UpdateAssetRequest in `app/Http/Requests/UpdateAssetRequest.php` with same validation as StoreAssetRequest plus custom rule to prevent entity field changes

### Filament Resource - Core Structure

- [X] T023 [US1] Generate Filament Resource with `php artisan make:filament-resource Asset --generate --no-interaction` to create `app/Filament/Resources/AssetResource.php` and pages (List, Create, Edit, View)
- [X] T024 [US1] Configure AssetResource form schema with sections: Asset Information (entity readonly badge, kategori select, nama_barang text, deskripsi textarea), Quantity (jumlah number, satuan text, kondisi select with colors), Financial (tahun_pembelian select years, nilai_pembelian currency IDR), Location & Notes (lokasi text, keterangan textarea), using Filament v4 Schema components from data-model.md
- [X] T025 [US1] Configure AssetResource table columns: nomor_inventaris (searchable, sortable), nama_barang (searchable, sortable, limit 50 chars), kategori (badge using AssetCategory colors), jumlah + satuan (combined), kondisi (badge using AssetCondition colors), status_assignment (badge), tahun_pembelian (sortable), nilai_pembelian (money IDR format), lokasi (searchable), created_at (date format)

### Filament Resource - Search & Filters

- [X] T026 [US1] Add search to AssetResource table on fields: nama_barang, nomor_inventaris, lokasi, keterangan per FR-006
- [X] T027 [US1] Add filters to AssetResource table: entity filter (if Pimpinan role - multiselect PT/LPK), kategori filter (select AssetCategory with icons), kondisi filter (select AssetCondition with colors), status_assignment filter (select Available/Assigned), tahun_pembelian range filter per FR-007
- [X] T028 [US1] Configure AssetResource table default sort by created_at DESC, pagination 25 per page, bulk actions (delete selected)

### Filament Resource - Entity Scoping

- [X] T029 [US1] Override `getEloquentQuery()` in AssetResource to apply entity scope: if user has role Admin LPK → filter entity='LPK', if Admin PT → filter entity='PT', if Pimpinan → no filter (see all), eager load relationships (creator, updater, currentAssignment.assignable) per research.md
- [X] T030 [US1] Disable entity field in edit mode (make readonly TextInput with formatStateUsing to display EntityType label) to enforce immutability per research.md Decision 1

### Policy & Authorization

- [X] T031 [US1] Create AssetPolicy in `app/Policies/AssetPolicy.php` with methods: viewAny (check permission + entity scope), view (check permission + entity match), create (check permission), update (check permission + entity match + prevent entity change), delete (check permission + entity match), restore (check permission), forceDelete (check permission), Pimpinan gets viewAny for all entities but cannot update/delete per FR-016 to FR-019
- [X] T032 [US1] Register AssetPolicy in `app/Providers/AuthServiceProvider.php` protected $policies array with `Asset::class => AssetPolicy::class`

### Filament Resource - Authorization Gates

- [X] T033 [US1] Add authorization check to AssetResource::canViewAny(), canCreate(), canEdit(), canDelete() methods calling policy methods
- [X] T034 [US1] Add infolist view to AssetResource ViewAsset page showing all asset details in readable format with sections (Asset Info, Quantity & Condition, Financial Details, Location & Notes, Audit Info with created_by, updated_by, timestamps)

### Header Statistics

- [X] T035 [US1] Add header widgets to ListAssets page showing: Total Assets count, Total Value sum (money format IDR), Condition breakdown (Baik count with green, Rusak count with red), Assignment Status breakdown (Available/Assigned) per FR-008

**Checkpoint**: User Story 1 complete - Admin can perform full CRUD on assets with entity isolation, search, filters, and statistics

---

## Phase 4: User Story 4 - Entity Isolation (P1) 🔒 Security

**Goal**: Enforce entity-based access control at all layers (database scope, policy, UI) to prevent unauthorized cross-entity access

**Independent Test**: Login as Admin LPK → verify only "Asset LPK" menu visible, only LPK assets in table, cannot access PT asset by direct URL (403) → logout → login as Admin PT → verify only PT assets → logout → login as Pimpinan → verify can see both PT and LPK assets but no edit/delete actions

**Priority**: P1 Security - MUST complete before deployment alongside US1

### Global Scope (Database Layer)

- [X] T036 [US4] Add global scope to Asset model in `booted()` method: `static::addGlobalScope('entity', fn($builder) => $builder->where('entity', auth()->user()->entity))` with conditional logic: apply only if user has role Admin LPK, Admin PT, or Keuangan LPK/PT; skip for Pimpinan role per research.md

### Policy Enforcement (Authorization Layer)

- [X] T037 [US4] Enhance AssetPolicy::view() to check: `$user->entity === $asset->entity` OR `$user->hasRole('Pimpinan')` per FR-016 to FR-018
- [X] T038 [US4] Enhance AssetPolicy::update() and delete() to prevent Pimpinan from editing/deleting (return false if Pimpinan) and check entity match for other roles
- [X] T039 [US4] Add test in AssetPolicy that Admin LPK cannot view/edit/delete PT assets and vice versa (use `$this->deny('You cannot access assets from other entity')`)

### UI Layer (Filament Navigation)

- [X] T040 [US4] Modify Filament navigation to show "Asset Management" menu only if user has asset permissions, label dynamically: "Asset LPK" if user entity='LPK', "Asset PT" if entity='PT', "All Assets" if Pimpinan
- [X] T041 [US4] Hide edit/delete actions in table and view page for Pimpinan role using `visible(fn() => !auth()->user()->hasRole('Pimpinan'))` on actions
- [X] T042 [US4] Add notification on AssetResource::getEloquentQuery() when query returns 0 results for Admin LPK: "No LPK assets found" or Admin PT: "No PT assets found"

### Entity Filter for Pimpinan

- [X] T043 [US4] Add entity multiselect filter to AssetResource table visible only for Pimpinan role, allowing filter by PT, LPK, or both per FR-018

**Checkpoint**: User Story 4 complete - Entity isolation enforced at all layers (scope, policy, UI), tested with all role types

---

## Phase 5: User Story 2 - Condition History (P2)

**Goal**: Enable Admin to update asset condition with audit trail showing all condition changes over time with reason, timestamp, and user

**Independent Test**: Admin select Laptop asset (Baik condition) → click "Update Condition" action → change to Rusak → add reason "Layar retak" → save → navigate to View Asset page → click "Condition History" tab → verify history entry shows: changed from Baik to Rusak, reason, timestamp, changed by current user

**Priority**: P2 - Implement after US1 and US4

### Factory & Model Methods

- [ ] T044 [P] [US2] Create AssetConditionHistoryFactory in `database/factories/AssetConditionHistoryFactory.php` with realistic condition changes (Baik→Rusak: "Rusak karena...", Rusak→Baik: "Sudah diperbaiki...")
- [ ] T045 [P] [US2] Add method `updateCondition(AssetCondition $newCondition, string $reason, User $changedBy): void` to Asset model that creates AssetConditionHistory record and updates asset kondisi field per data-model.md

### Filament Action - Update Condition

- [ ] T046 [US2] Create UpdateConditionAction in `app/Filament/Resources/AssetResource/Actions/UpdateConditionAction.php` as modal action with fields: new_condition (select AssetCondition), reason (required textarea), on submit: call `$record->updateCondition()` and show success notification per FR-013
- [ ] T047 [US2] Add UpdateConditionAction to AssetResource table actions column and view page actions
- [ ] T048 [US2] Disable UpdateConditionAction if new_condition equals current kondisi (validation: must differ) per data-model.md validation rules

### Filament Relation Manager - History Timeline

- [ ] T049 [US2] Create ConditionHistoryRelationManager in `app/Filament/Resources/AssetResource/RelationManagers/ConditionHistoryRelationManager.php` to display asset_condition_histories as timeline table: changed_at (datetime), old_condition (badge), new_condition (badge), reason (text), changedBy user name
- [ ] T050 [US2] Register ConditionHistoryRelationManager in AssetResource::getRelations() array
- [ ] T051 [US2] Configure relation manager table: sort by changed_at DESC, paginate 10 per page, searchable on reason field, no create/edit/delete actions (immutable audit records) per data-model.md

### Badge Warning for Old Damages

- [ ] T052 [US2] Add badge to Asset table row: if kondisi='Rusak' AND last condition change > 30 days ago → show warning badge "Needs attention" per FR-015
- [ ] T053 [US2] Create scope `scopeNeedsRepair()` in Asset model: `where('kondisi', AssetCondition::Rusak)->whereHas('conditionHistories', fn($q) => $q->where('new_condition', 'rusak')->where('changed_at', '<', now()->subDays(30)))`

**Checkpoint**: User Story 2 complete - Condition updates with full audit trail, timeline view, and warnings for old damages

---

## Phase 6: User Story 3 - Employee Assignment (P2)

**Goal**: Enable Admin to assign assets to employees (LPK staff or PT staff) with tracking of assignment date, return date, and assignment history

**Independent Test**: Admin select Laptop asset (Available) → click "Assign to Employee" action → select employee "John Doe" (karyawan_lpk) → set assigned_date today → save → verify asset status changes to "Assigned", employee name shown → navigate to employee profile → verify Laptop appears in "My Assets" → Admin click "Return Asset" → add return notes → save → verify status back to "Available"

**Priority**: P2 - Implement after US1 and US4

### Factory & Model Methods

- [ ] T054 [P] [US3] Create AssetAssignmentFactory in `database/factories/AssetAssignmentFactory.php` with assigned_date (past 30 days), return_date (nullable 50% null for active), assignable_type (50% EmployeeLPK, 50% User), realistic return_notes
- [ ] T055 [P] [US3] Add method `assignTo(Model $employee, User $assignedBy): AssetAssignment` to Asset model that validates no active assignment exists, creates AssetAssignment record with polymorphic relationship, updates status_assignment to 'Assigned' per data-model.md
- [ ] T056 [P] [US3] Add method `returnFromAssignment(?string $notes = null): void` to Asset model that finds active assignment, sets return_date and return_notes, updates status_assignment to 'Available'

### Filament Actions - Assignment & Return

- [ ] T057 [US3] Create AssignToEmployeeAction in `app/Filament/Resources/AssetResource/Actions/AssignToEmployeeAction.php` as modal action with fields: assignable_type (radio: Karyawan LPK / PT Staff, set polymorphic type), assignable_id (select using relationship query based on assignable_type and entity match), assigned_date (default today), on submit: call `$record->assignTo()` per FR-009
- [ ] T058 [US3] Add validation to AssignToEmployeeAction: cannot assign if asset already assigned (status='Assigned') with error message from FR-010, cannot assign to employee from different entity
- [ ] T059 [US3] Create ReturnAssetAction in `app/Filament/Resources/AssetResource/Actions/ReturnAssetAction.php` as modal action with fields: return_notes (optional textarea), on submit: call `$record->returnFromAssignment()` per FR-011
- [ ] T060 [US3] Add AssignToEmployeeAction and ReturnAssetAction to AssetResource table actions and view page, conditionally visible: AssignToEmployeeAction only if status='Available', ReturnAssetAction only if status='Assigned'

### Filament Relation Manager - Assignment History

- [ ] T061 [US3] Create AssignmentHistoryRelationManager in `app/Filament/Resources/AssetResource/RelationManagers/AssignmentHistoryRelationManager.php` to display asset_assignments table: assignable (polymorphic, show employee name), assigned_by user name, assigned_date, return_date (nullable), return_notes (nullable), duration in days
- [ ] T062 [US3] Register AssignmentHistoryRelationManager in AssetResource::getRelations() array
- [ ] T063 [US3] Configure relation manager table: sort by assigned_date DESC, badge for active assignment (return_date IS NULL with green "Active"), paginate 10 per page, no create/edit/delete actions per FR-019

### Employee-Side View

- [ ] T064 [US3] Add "My Assets" widget to Karyawan LPK dashboard (if exists) showing assets currently assigned to employee via AssetAssignment with return_date IS NULL per FR-012
- [ ] T065 [US3] Add "My Assets" section to User profile page (for PT staff) showing assets currently assigned with asset name, nomor_inventaris, assigned_date, return expected date (optional)

**Checkpoint**: User Story 3 complete - Full employee assignment workflow with history tracking, active/returned status, and employee-side visibility

---

## Phase 7: User Story 5 - Reporting & Statistics (P3)

**Goal**: Enable Pimpinan and Admin to view dashboard statistics (total assets, value, condition/category/assignment breakdowns) and export data to Excel

**Independent Test**: Pimpinan login → navigate to Asset Dashboard → view widgets showing total assets, total value, condition breakdown chart (Baik/Rusak), category breakdown chart, assignment status (Available/Assigned) → filter by entity LPK only → verify statistics update → click "Export to Excel" → download file → verify all visible assets exported with all columns

**Priority**: P3 - Nice to have, implement after core CRUD and assignments

### Dashboard Widgets - Statistics

- [ ] T066 [P] [US5] Create TotalAssetsWidget in `app/Filament/Resources/AssetResource/Widgets/TotalAssetsWidget.php` showing count of assets scoped by user entity with icon (database), link to ListAssets page
- [ ] T067 [P] [US5] Create TotalValueWidget in `app/Filament/Resources/AssetResource/Widgets/TotalValueWidget.php` showing sum of nilai_pembelian for assets scoped by user entity, formatted as IDR currency with icon (currency-dollar)
- [ ] T068 [P] [US5] Create ConditionBreakdownWidget in `app/Filament/Resources/AssetResource/Widgets/ConditionBreakdownWidget.php` as chart widget (pie chart) showing count per AssetCondition with colors (Baik green, Rusak red) per FR-020
- [ ] T069 [P] [US5] Create CategoryBreakdownWidget in `app/Filament/Resources/AssetResource/Widgets/CategoryBreakdownWidget.php` as chart widget (bar chart) showing count per AssetCategory with category colors from enum
- [ ] T070 [P] [US5] Create AssignmentStatusWidget in `app/Filament/Resources/AssetResource/Widgets/AssignmentStatusWidget.php` as chart widget (donut chart) showing count per AssetAssignmentStatus (Available green, Assigned orange)

### Dashboard Page

- [ ] T071 [US5] Create AssetDashboardPage in `app/Filament/Resources/AssetResource/Pages/AssetDashboard.php` with `getHeaderWidgets()` returning all 5 widgets (T066-T070) in responsive grid layout
- [ ] T072 [US5] Register AssetDashboard as page in AssetResource::getPages() with route '/dashboard' and navigation label "Dashboard"
- [ ] T073 [US5] Add entity filter to AssetDashboard widgets (visible only for Pimpinan) allowing filter by PT, LPK, or ALL, widgets should reactively update based on filter selection per FR-022

### Excel Export

- [ ] T074 [US5] Add ExportAction to AssetResource table bulk actions using Filament's built-in export with columns: nomor_inventaris, entity, kategori, nama_barang, jumlah, satuan, kondisi, status_assignment, tahun_pembelian, nilai_pembelian, lokasi, keterangan, created_at per FR-021, FR-022
- [ ] T075 [US5] Configure export filename as `Asset_[Entity]_[Date].xlsx` (e.g., Asset_LPK_2026-02-08.xlsx)
- [ ] T076 [US5] Test export with 500+ dummy assets to verify performance < 5 seconds per SC-006

**Checkpoint**: User Story 5 complete - Full dashboard with statistics widgets and Excel export functionality

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final improvements, testing, documentation, and code quality before merge

### Testing (Feature Tests)

- [ ] T077 [P] Create AssetManagementTest in `tests/Feature/AssetManagementTest.php` with tests: test_admin_lpk_can_create_asset_with_auto_entity(), test_asset_nomor_inventaris_auto_generated(), test_admin_can_search_assets(), test_admin_can_filter_by_category_and_condition(), test_admin_can_update_asset(), test_admin_can_soft_delete_asset(), all using DB::beginTransaction() in setUp() and DB::rollBack() in tearDown() per Laravel Boost guidelines
- [ ] T078 [P] Create AssetEntityIsolationTest in `tests/Feature/AssetEntityIsolationTest.php` with tests: test_admin_lpk_only_sees_lpk_assets(), test_admin_pt_only_sees_pt_assets(), test_admin_lpk_cannot_access_pt_asset_by_url(), test_pimpinan_sees_all_assets(), test_pimpinan_cannot_edit_or_delete_assets()
- [ ] T079 [P] Create AssetConditionTrackingTest in `tests/Feature/AssetConditionTrackingTest.php` with tests: test_condition_update_creates_history_record(), test_condition_history_shows_timeline(), test_warning_badge_for_rusak_over_30_days()
- [ ] T080 [P] Create AssetAssignmentTest in `tests/Feature/AssetAssignmentTest.php` with tests: test_admin_can_assign_asset_to_employee(), test_cannot_assign_already_assigned_asset(), test_asset_return_updates_status(), test_assignment_history_visible(), test_employee_sees_assigned_assets()
- [ ] T081 [P] Create AssetReportingTest in `tests/Feature/AssetReportingTest.php` with tests: test_dashboard_shows_correct_statistics(), test_export_to_excel_works(), test_widgets_scope_by_entity()

### Testing (Unit Tests)

- [ ] T082 [P] Create AssetNumberGeneratorTest in `tests/Unit/AssetNumberGeneratorTest.php` with tests: test_generates_correct_format(), test_sequence_increments(), test_different_categories_have_separate_sequences(), test_concurrent_generation_no_collision() using DB transactions per Laravel Boost guidelines

### Code Quality

- [ ] T083 Run `vendor/bin/pint --dirty` to format all PHP files in app/ and tests/ directories
- [ ] T084 Run `php artisan test --compact` to verify all tests pass with green checkmarks
- [ ] T085 Review all Filament Resource components for consistent Badge colors, icon usage, responsive layout (grid cols)
- [ ] T086 Add PHPDoc blocks to all public methods in Asset, AssetAssignment, AssetConditionHistory models documenting @param, @return, @throws

### Performance Optimization

- [ ] T087 Verify all indexes created in migrations: entity, kategori, kondisi, status_assignment, nomor_inventaris_unique, composite entity+kategori per data-model.md
- [ ] T088 Add eager loading in AssetResource::getEloquentQuery(): `->with(['creator', 'updater', 'currentAssignment.assignable'])->withCount('assignments')` to prevent N+1 queries
- [ ] T089 Test list page performance with 1000+ dummy assets, verify load time < 2 seconds per SC-002

### Documentation

- [ ] T090 [P] Update AssetResource with inline comments explaining entity scoping logic, polymorphic relationships, and authorization checks
- [ ] T091 [P] Add README section in `specs/004-asset-management/README.md` (new file) documenting: feature overview, key components (models, resources, policies), testing instructions, deployment checklist
- [ ] T092 Update quickstart.md with actual artisan command outputs and screenshots (optional) after implementation

### Security Hardening

- [ ] T093 Review AssetPolicy::update() to ensure nomor_inventaris field cannot be edited (should be readonly in form)
- [ ] T094 Review AssetObserver::updating() to confirm entity field immutability enforced at model level
- [ ] T095 Verify all Filament form fields use appropriate validation (TextInput maxLength, Select required, number min/max constraints)

### Final Validation

- [ ] T096 Run quickstart.md step-by-step to verify all setup instructions work correctly for new developer
- [ ] T097 Manually test all user stories (US1-US5) end-to-end in browser against acceptance scenarios from spec.md
- [ ] T098 Verify all success criteria (SC-001 to SC-010) met: performance, audit trail, entity isolation, concurrent users
- [ ] T099 Check all functional requirements (FR-001 to FR-029) implemented and working
- [ ] T100 Final code review: check for TODO comments, debug statements, unused imports, test data in seeders

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - **BLOCKS all user stories**
- **User Stories (Phase 3-7)**: All depend on Foundational phase completion
  - US1 (Phase 3) and US4 (Phase 4) can proceed in parallel after Phase 2 - both P1 MVP
  - US2 (Phase 5) and US3 (Phase 6) can proceed in parallel after US1+US4 complete - both P2
  - US5 (Phase 7) should proceed after US1+US4 complete (needs base CRUD) - P3
- **Polish (Phase 8)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (Phase 3 - P1)**: Can start after Foundational (Phase 2) ✅ No dependencies on other stories
- **User Story 4 (Phase 4 - P1)**: Can start after Foundational (Phase 2) ✅ No dependencies, can run parallel with US1
- **User Story 2 (Phase 5 - P2)**: Depends on US1 (needs Asset model, Filament Resource) ⚠️ Wait for US1 completion
- **User Story 3 (Phase 6 - P2)**: Depends on US1 (needs Asset model, Filament Resource) ⚠️ Wait for US1 completion
- **User Story 5 (Phase 7 - P3)**: Depends on US1+US4 (needs CRUD + scoping for statistics) ⚠️ Wait for MVP

### Within Each User Story

1. **Factories & Seeders** FIRST (demo data for testing)
2. **Form Requests** (validation classes)
3. **Filament Resource** core structure (form, table, pages)
4. **Filament Actions** (modal actions for workflows)
5. **Relation Managers** (for displaying related data)
6. **Policy & Authorization** (security layer)
7. **Widgets** (statistics and dashboards)

### Parallel Opportunities

All tasks marked **[P]** can run in parallel within their phase:

- **Phase 2 (Foundational)**: T004-T006 (enums), T012-T014 (models) can all run parallel
- **US1**: T019-T020 (factories/seeders), T021-T022 (form requests), T029-T030 (scoping) parallel
- **US4**: T036-T039 (policy enhancements) can run parallel
- **US2**: T044-T045 (factory/methods) parallel
- **US3**: T054-T056 (factory/methods) parallel
- **US5**: T066-T070 (all 5 widgets) can run parallel
- **Phase 8**: T077-T082 (all tests) can run parallel

---

## Parallel Example: Foundational Phase

```bash
# Developer A:
T004: Create AssetCategory enum
T005: Create AssetCondition enum
T006: Create AssetAssignmentStatus enum

# Developer B (simultaneously):
T012: Create Asset model
T013: Create AssetAssignment model
T014: Create AssetConditionHistory model

# Then together (sequential, depends on above):
T007-T010: Migrations (requires enums)
T015-T016: Observer (requires Asset model)
T017-T018: Permissions seeder
```

---

## Implementation Strategy

### MVP First (US1 + US4 Only) - Recommended Start

**Goal**: Deployable asset management with entity isolation

1. ✅ Complete Phase 1: Setup (T001-T003)
2. ✅ Complete Phase 2: Foundational (T004-T018) - **CRITICAL PATH**
3. ✅ Complete Phase 3: User Story 1 (T019-T035) - Asset CRUD
4. ✅ Complete Phase 4: User Story 4 (T036-T043) - Entity Isolation
5. **STOP and VALIDATE**: Test US1+US4 independently against acceptance scenarios
6. **Deploy to staging** for user acceptance testing
7. Collect feedback before proceeding to US2, US3, US5

**Result**: ~43 tasks (T001-T043), ~3-4 days for 1 developer, delivers core value

### Incremental Delivery (Recommended Sequence)

**Iteration 1 (MVP)**: Setup + Foundational + US1 + US4 → Deploy
- Delivers: Asset registration, entity isolation, search, filters
- Tests: AssetManagementTest, AssetEntityIsolationTest
- Users can start using system immediately

**Iteration 2 (Condition Tracking)**: + US2 → Deploy
- Adds: Condition history, maintenance tracking, timeline
- Tests: AssetConditionTrackingTest
- Enhances: Asset lifecycle management

**Iteration 3 (Assignments)**: + US3 → Deploy
- Adds: Employee assignments, custody tracking, return workflow
- Tests: AssetAssignmentTest
- Enhances: Accountability and asset utilization tracking

**Iteration 4 (Reporting)**: + US5 → Deploy
- Adds: Dashboard, statistics widgets, Excel export
- Tests: AssetReportingTest
- Enhances: Management visibility and decision making

**Iteration 5 (Polish)**: Phase 8 → Final Deploy
- Adds: Performance optimization, documentation, security hardening
- Completes: All 100 tasks

### Parallel Team Strategy

With 2-3 developers available:

**Setup Sprint** (Both together, 1 day):
- Everyone: Phase 1 + Phase 2 together (foundational setup)

**MVP Sprint** (Parallel, 2-3 days):
- Developer A: Phase 3 (US1 Asset CRUD) - T019-T035
- Developer B: Phase 4 (US4 Entity Isolation) - T036-T043
- Both: Integrate and test together

**Enhancement Sprint 1** (Parallel, 2 days):
- Developer A: Phase 5 (US2 Condition History) - T044-T053
- Developer B: Phase 6 (US3 Assignments) - T054-T065

**Enhancement Sprint 2** (Single, 1-2 days):
- Developer A or B: Phase 7 (US5 Reporting) - T066-T076

**Polish Sprint** (Parallel, 1 day):
- Developer A: Tests T077-T082
- Developer B: Code quality T083-T089
- Both: Final validation T096-T100

**Total Timeline**: ~7-9 days with 2 developers, ~10-14 days with 1 developer

---

## MVP Scope Summary

**Minimum Viable Product** (US1 + US4):

✅ **Included**:
- Asset registration with auto-generated nomor inventaris
- Full CRUD operations (Create, Read, Update, Delete with soft delete)
- Entity-based isolation (Admin LPK sees only LPK, Admin PT sees only PT)
- Search by name, location, nomor inventaris
- Filters by entity, category, condition, assignment status, year
- Statistics header (total assets, total value, condition/assignment breakdown)
- Audit logging (Spatie Activity Log on all changes)
- Permission-based access control (Filament Shield)
- Policy authorization with entity scoping

❌ **Deferred to Later**:
- Condition history timeline (US2)
- Employee assignment workflow (US3)
- Dashboard widgets and charts (US5)
- Excel export (US5)
- Photo uploads (out of scope)
- Bulk import (out of scope)
- Depreciation calculation (out of scope)
- QR code generation (out of scope)

**MVP Delivers**: Core asset inventory management with security, enough to start tracking assets immediately

---

## Success Validation Checklist

Before marking feature complete, verify:

### Functional Requirements (All 29)
- [ ] FR-001 to FR-008: Asset CRUD, search, filtering ✓
- [ ] FR-009 to FR-012: Employee assignment (US3 only)
- [ ] FR-013 to FR-015: Condition updates (US2 only)
- [ ] FR-016 to FR-019: Entity isolation ✓ (MVP required)
- [ ] FR-020 to FR-022: Reporting (US5 only)
- [ ] FR-023 to FR-026: Data validation ✓
- [ ] FR-027 to FR-029: Soft delete & audit ✓

### User Stories (5 total, MVP = 2)
- [ ] US1: Asset Registration ✓ (MVP)
- [ ] US2: Condition Updates (optional P2)
- [ ] US3: Employee Assignment (optional P2)
- [ ] US4: Entity Isolation ✓ (MVP)
- [ ] US5: Reporting (optional P3)

### Success Criteria (10 total, MVP = 5)
- [ ] SC-001: Asset registration < 30s ✓
- [ ] SC-002: List load 1000+ assets < 2s ✓
- [ ] SC-003: 100% entity isolation ✓ (critical)
- [ ] SC-004: Audit trail 100% (US2 enhances this)
- [ ] SC-005: Assignment < 30s (US3 only)
- [ ] SC-006: Export < 5s (US5 only)
- [ ] SC-007: Dashboard < 1s (US5 only)
- [ ] SC-008: 95% usability (validate with users)
- [ ] SC-009: Nomor inventaris unique ✓
- [ ] SC-010: 50 concurrent users ✓

### Code Quality
- [ ] All tests pass green
- [ ] Pint formatting applied
- [ ] No N+1 queries (eager loading verified)
- [ ] Policies enforce entity isolation
- [ ] Factories exist for all models
- [ ] PHPDoc blocks on public methods

---

## Notes

- **[P] marker**: Tasks that touch different files and have no dependencies can run in parallel
- **[Story] marker**: Maps each task to its user story for traceability (US1, US2, US3, US4, US5)
- **Checkpoint after each phase**: Validate story works independently before proceeding
- **MVP recommendation**: Complete US1 + US4 (T001-T043) before other stories for fastest time to value
- **Testing approach**: Use database transactions (setUp: DB::beginTransaction(), tearDown: DB::rollBack()) per Laravel Boost guidelines, NOT RefreshDatabase
- **Filament v4 changes**: Use Filament\Schemas\Components for layouts (Grid, Section, etc.), all actions extend Filament\Actions\Action
- **Entity scoping**: Must be enforced at 3 layers: Global Scope (Eloquent), Policy (authorization), UI (Filament query)
- **Observer pattern**: Used for nomor_inventaris auto-generation and condition history logging
- **Polymorphic relationships**: AssetAssignment uses morphTo for assignable (EmployeeLPK or User)

---

**Total Tasks**: 100  
**MVP Tasks**: 43 (T001-T043) - US1 + US4  
**Optional Enhancements**: 57 tasks (T044-T100) - US2, US3, US5, Polish  
**Estimated Duration**: 3-4 days (MVP), 10-14 days (complete feature) for 1 developer  
**Parallel Efficiency**: 50%+ task reduction with 2 developers using [P] markers

---

**Version**: 1.0  
**Generated by**: `/speckit.tasks` command  
**Last Updated**: February 8, 2026
