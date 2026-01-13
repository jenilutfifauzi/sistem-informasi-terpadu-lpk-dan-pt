# Tasks: User Management & RBAC Foundation

**Input**: Design documents from `/specs/001-user-management-rbac/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

---

## Format: `- [ ] [ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and package installation

- [ ] T001 Install Filament Shield package via `composer require bezhansalleh/filament-shield`
- [ ] T002 Install Spatie Activity Log package via `composer require spatie/laravel-activitylog`
- [ ] T003 Run Shield setup command `php artisan shield:setup --no-interaction`
- [ ] T004 Install Shield for admin panel via `php artisan shield:install admin --no-interaction`
- [ ] T005 Publish activitylog migrations via `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`
- [ ] T006 Publish activitylog config via `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Database Schema

- [ ] T007 [P] Create EntityType enum in `app/Enums/EntityType.php` with PT and LPK cases, `label()`, `color()`, and `options()` methods
- [ ] T008 [P] Create migration to add entity column to users table in `database/migrations/2026_01_13_000001_add_entity_to_users_table.php`
- [ ] T009 Run all migrations via `php artisan migrate` to create Spatie permission tables and activity_log table

### User Model Configuration

- [ ] T010 Update User model in `app/Models/User.php` to add HasRoles trait from Spatie
- [ ] T011 Add SoftDeletes trait to User model in `app/Models/User.php`
- [ ] T012 Add LogsActivity trait to User model in `app/Models/User.php`
- [ ] T013 Add entity field to fillable array in User model in `app/Models/User.php`
- [ ] T014 Add entity cast to EntityType enum in casts() method in `app/Models/User.php`
- [ ] T015 Implement getActivitylogOptions() method in User model in `app/Models/User.php` to log only name, email, entity fields

### Activity Log Configuration

- [ ] T016 Configure activitylog retention to 90 days in `config/activitylog.php`
- [ ] T017 Set subject_returns_soft_deleted_models to true in `config/activitylog.php`

### Factory Updates

- [ ] T018 Update UserFactory in `database/factories/UserFactory.php` to include entity field with random PT or LPK value

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Super Admin Setup & Initial Login (Priority: P1) 🎯 MVP

**Goal**: Administrator pertama kali dapat membuat akun super admin dan login ke sistem untuk mulai mengelola data.

**Independent Test**: Super admin bisa dibuat via command, login berhasil, dan melihat dashboard kosong dengan menu User Management.

### Implementation for User Story 1

- [x] T019 [US1] Create super admin user via `php artisan shield:super-admin --no-interaction` with predefined credentials (for testing)
- [x] T020 [US1] Verify Shield plugin is registered in `app/Providers/Filament/AdminPanelProvider.php` (auto-done by shield:install)
- [x] T021 [US1] Configure Shield panel settings in `config/filament-shield.php` to set is_scoped_to_tenant = false
- [x] T022 [US1] Test super admin can login via browser at `/admin/login`
- [x] T023 [US1] Verify Shield menu items (Roles) appear in sidebar for super admin

**Checkpoint**: At this point, super admin can login and see Shield's default Role resource

---

## Phase 4: User Story 2 - Role Management (Priority: P1)

**Goal**: Admin dapat membuat dan mengelola 8 roles sesuai PRD dengan permissions yang sesuai.

**Independent Test**: Admin bisa CRUD roles, assign permissions ke role, dan melihat daftar roles yang sudah dibuat.

### Implementation for User Story 2

- [x] T024 [P] [US2] Create RolesAndPermissionsSeeder in `database/seeders/RolesAndPermissionsSeeder.php`
- [x] T025 [US2] Implement createSuperAdminRole() method in RolesAndPermissionsSeeder to create super_admin role with all permissions
- [x] T026 [P] [US2] Implement createAdminLPKRole() method in RolesAndPermissionsSeeder with view_user, view_any_user permissions
- [x] T027 [P] [US2] Implement createInstrukturRole() method in RolesAndPermissionsSeeder with view_user permission only
- [x] T028 [P] [US2] Implement createHRPTRole() method in RolesAndPermissionsSeeder with view_user, view_any_user, create_user, update_user permissions
- [x] T029 [P] [US2] Implement createAdminPTRole() method in RolesAndPermissionsSeeder with view_user, view_any_user permissions
- [x] T030 [P] [US2] Implement createLegalPTRole() method in RolesAndPermissionsSeeder with view_user permission only
- [x] T031 [P] [US2] Implement createKeuanganPTRole() method in RolesAndPermissionsSeeder with view_user permission only
- [x] T032 [P] [US2] Implement createKeuanganLPKRole() method in RolesAndPermissionsSeeder with view_user permission only
- [x] T033 [P] [US2] Implement createPimpinanRole() method in RolesAndPermissionsSeeder with all view_* permissions
- [x] T034 [US2] Register RolesAndPermissionsSeeder in `database/seeders/DatabaseSeeder.php`
- [x] T035 [US2] Run RolesAndPermissionsSeeder via `php artisan db:seed --class=RolesAndPermissionsSeeder`
- [x] T036 [US2] Verify 8 roles created in database via tinker or Shield UI
- [ ] T037 [US2] Test editing a role's permissions via Shield Role Resource UI
- [ ] T038 [US2] Test creating a new custom role via Shield Role Resource UI

**Checkpoint**: At this point, all 8 roles are seeded and can be managed via Shield UI

---

## Phase 5: User Story 3 - User Management with Entity Assignment (Priority: P1)

**Goal**: Admin dapat membuat user baru, assign role, dan assign entitas (PT atau LPK) untuk isolasi data.

**Independent Test**: Admin bisa CRUD users, assign role dan entitas, user baru bisa login dengan akses sesuai role-nya.

### Implementation for User Story 3

- [ ] T039 [US3] Generate UserResource via `php artisan make:filament-resource User --generate --soft-deletes --no-interaction`
- [ ] T040 [US3] Customize UserResource form in `app/Filament/Resources/UserResource.php` to add name field (TextInput, required, max 255)
- [ ] T041 [US3] Add email field to UserResource form in `app/Filament/Resources/UserResource.php` (TextInput, email, required, unique, max 255)
- [ ] T042 [US3] Add password field to UserResource form in `app/Filament/Resources/UserResource.php` (TextInput, password, required on create, min 8, dehydrated when filled)
- [ ] T043 [US3] Add entity field to UserResource form in `app/Filament/Resources/UserResource.php` (Select, options from EntityType::options(), required, not native)
- [ ] T044 [US3] Add roles relationship field to UserResource form in `app/Filament/Resources/UserResource.php` (Select, multiple, relationship to roles, preload, searchable, required)
- [ ] T045 [US3] Customize UserResource table in `app/Filament/Resources/UserResource.php` to add name column (searchable, sortable)
- [ ] T046 [US3] Add email column to UserResource table in `app/Filament/Resources/UserResource.php` (searchable, sortable, copyable)
- [ ] T047 [US3] Add entity column to UserResource table in `app/Filament/Resources/UserResource.php` (badge, color from enum, display value)
- [ ] T048 [US3] Add roles column to UserResource table in `app/Filament/Resources/UserResource.php` (badge, separator, searchable)
- [ ] T049 [US3] Add created_at column to UserResource table in `app/Filament/Resources/UserResource.php` (dateTime, sortable, hidden by default)
- [ ] T050 [US3] Add entity filter to UserResource table in `app/Filament/Resources/UserResource.php` (SelectFilter, options from EntityType)
- [ ] T051 [US3] Add roles filter to UserResource table in `app/Filament/Resources/UserResource.php` (SelectFilter, relationship, multiple, preload)
- [ ] T052 [US3] Generate UserPolicy via `php artisan shield:generate --resource=UserResource --no-interaction`
- [ ] T053 [US3] Test creating new user via UserResource UI with all fields (name, email, password, entity, roles)
- [ ] T054 [US3] Test editing existing user via UserResource UI to change roles
- [ ] T055 [US3] Test soft deleting user via UserResource UI and verify user cannot login
- [ ] T056 [US3] Test restoring soft-deleted user via UserResource UI and verify user can login again
- [ ] T057 [US3] Verify activity log captures user creation in activity_log table
- [ ] T058 [US3] Verify activity log captures user update in activity_log table (only dirty fields)

**Checkpoint**: At this point, User Story 3 should be fully functional - admin can CRUD users with entity and roles

---

## Phase 6: User Story 4 - Permission Enforcement & Access Control (Priority: P2)

**Goal**: Sistem menerapkan permission checks di setiap resource untuk memastikan users hanya bisa akses fitur sesuai role mereka.

**Independent Test**: User dengan role tertentu hanya bisa akses menu/fitur yang di-allow untuk role tersebut, akses lain di-block dengan 403.

### Implementation for User Story 4

- [ ] T059 [P] [US4] Generate permissions for all resources via `php artisan shield:generate --all --no-interaction`
- [ ] T060 [US4] Create test user with instruktur role via UserResource UI
- [ ] T061 [US4] Login as instruktur user and verify Users menu does NOT appear in sidebar
- [ ] T062 [US4] Login as instruktur user and manually access `/admin/shield/users` URL to verify 403 Forbidden error
- [ ] T063 [US4] Create test user with pimpinan role via UserResource UI
- [ ] T064 [US4] Login as pimpinan user and verify all view_* permissions work (can see all menus)
- [ ] T065 [US4] Create test user with admin_lpk role via UserResource UI
- [ ] T066 [US4] Login as admin_lpk user and verify only view access (cannot create/edit/delete users)
- [ ] T067 [US4] Login as admin_lpk user and try to click "New User" button to verify it's hidden or disabled
- [ ] T068 [US4] Verify Shield's role resource shows correct permission checkboxes for each role

**Checkpoint**: All user stories should now be independently functional - RBAC is fully enforced

---

## Phase 7: Testing & Validation

**Purpose**: Comprehensive testing to ensure all features work correctly

### Feature Tests

- [ ] T069 [P] Create LoginTest in `tests/Feature/Auth/LoginTest.php` to test super admin can login successfully
- [ ] T070 [P] Add test_super_admin_can_access_dashboard() method to LoginTest
- [ ] T071 [P] Add test_invalid_credentials_fail() method to LoginTest
- [ ] T072 [P] Add test_deleted_user_cannot_login() method to LoginTest (soft delete scenario)
- [ ] T073 [P] Create PermissionEnforcementTest in `tests/Feature/Auth/PermissionEnforcementTest.php`
- [ ] T074 [P] Add test_instruktur_cannot_access_users_resource() method to PermissionEnforcementTest
- [ ] T075 [P] Add test_pimpinan_can_view_all_resources() method to PermissionEnforcementTest
- [ ] T076 [P] Add test_admin_lpk_has_view_only_access() method to PermissionEnforcementTest
- [ ] T077 [P] Create UserResourceTest in `tests/Feature/Filament/UserResourceTest.php`
- [ ] T078 [P] Add test_super_admin_can_create_user() method to UserResourceTest using Livewire::test()
- [ ] T079 [P] Add test_super_admin_can_edit_user() method to UserResourceTest
- [ ] T080 [P] Add test_super_admin_can_delete_user() method to UserResourceTest (soft delete)
- [ ] T081 [P] Add test_super_admin_can_restore_user() method to UserResourceTest
- [ ] T082 [P] Add test_user_creation_requires_all_fields() method to UserResourceTest (validation)
- [ ] T083 [P] Add test_email_must_be_unique() method to UserResourceTest (validation)
- [ ] T084 [P] Create RoleResourceTest in `tests/Feature/Filament/RoleResourceTest.php`
- [ ] T085 [P] Add test_super_admin_can_view_roles() method to RoleResourceTest
- [ ] T086 [P] Add test_super_admin_can_edit_role_permissions() method to RoleResourceTest
- [ ] T087 [P] Add test_super_admin_can_create_custom_role() method to RoleResourceTest
- [ ] T088 [P] Create AuditLogTest in `tests/Feature/AuditLogTest.php`
- [ ] T089 [P] Add test_user_creation_is_logged() method to AuditLogTest
- [ ] T090 [P] Add test_user_update_logs_only_dirty_attributes() method to AuditLogTest
- [ ] T091 [P] Add test_user_deletion_is_logged() method to AuditLogTest
- [ ] T092 [P] Create RolesAndPermissionsSeederTest in `tests/Feature/Seeders/RolesAndPermissionsSeederTest.php`
- [ ] T093 [P] Add test_seeder_creates_8_roles() method to RolesAndPermissionsSeederTest
- [ ] T094 [P] Add test_super_admin_has_all_permissions() method to RolesAndPermissionsSeederTest
- [ ] T095 [P] Add test_pimpinan_has_all_view_permissions() method to RolesAndPermissionsSeederTest
- [ ] T096 [P] Add test_instruktur_has_minimal_permissions() method to RolesAndPermissionsSeederTest

### Test Execution

- [ ] T097 Run all tests via `php artisan test --compact` and ensure all pass
- [ ] T098 Run specific auth tests via `php artisan test --compact tests/Feature/Auth/`
- [ ] T099 Run specific Filament tests via `php artisan test --compact tests/Feature/Filament/`
- [ ] T100 Fix any failing tests until all tests pass

**Checkpoint**: All tests passing - feature is validated and working correctly

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Code quality, performance, and final validation

- [ ] T101 [P] Run Laravel Pint to format all code via `vendor/bin/pint --dirty`
- [ ] T102 [P] Review and optimize N+1 queries in UserResource table (eager load roles)
- [ ] T103 [P] Add rate limiting to login route in `routes/web.php` (6 attempts per minute)
- [ ] T104 [P] Verify password hashing is using bcrypt (check User model casts)
- [ ] T105 [P] Test login response time is < 3 seconds (measure with browser dev tools)
- [ ] T106 [P] Verify soft-deleted users are retained for 90 days (check migration and model)
- [ ] T107 [P] Test activity log cleanup command `php artisan activitylog:clean` works correctly
- [ ] T108 [P] Verify all Shield navigation items appear correctly in sidebar
- [ ] T109 [P] Test creating sample users for each of 8 roles (for manual testing)
- [ ] T110 [P] Verify EntityType enum badge colors display correctly in table
- [ ] T111 Run complete quickstart.md validation from start to finish
- [ ] T112 Update README.md with setup instructions (link to quickstart.md)
- [ ] T113 Create git commit for completed feature with detailed commit message
- [ ] T114 Review all constitution principles compliance (all should still PASS)

**Checkpoint**: Feature complete, tested, formatted, and ready for production

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational phase completion
- **User Story 2 (Phase 4)**: Depends on Foundational phase completion (can run parallel with US1)
- **User Story 3 (Phase 5)**: Depends on Foundational phase + User Story 2 (needs roles to be seeded)
- **User Story 4 (Phase 6)**: Depends on User Story 1, 2, 3 completion (validates entire RBAC system)
- **Testing (Phase 7)**: Depends on all user stories being complete
- **Polish (Phase 8)**: Depends on all tests passing

### User Story Dependencies

```
Setup (Phase 1)
    ↓
Foundational (Phase 2) ← BLOCKS everything below
    ↓
    ├─→ User Story 1 (Phase 3) ─┐
    │                            ├─→ User Story 4 (Phase 6)
    └─→ User Story 2 (Phase 4) ─┤       ↓
            ↓                    │   Testing (Phase 7)
        User Story 3 (Phase 5) ─┘       ↓
                                    Polish (Phase 8)
```

- **User Story 1 (Super Admin Setup)**: Can start after Foundational - No dependencies on other stories
- **User Story 2 (Role Management)**: Can start after Foundational - Can run parallel with US1
- **User Story 3 (User Management)**: Depends on US2 (needs roles to exist) - Cannot run parallel with US2
- **User Story 4 (Permission Enforcement)**: Depends on US1, US2, US3 - Must run last

### Within Each User Story

- Tasks marked [P] can run in parallel (different files)
- Sequential tasks must complete in order (dependencies within story)
- Tests can run in parallel once implementation is complete

### Parallel Opportunities

**Setup Phase (All parallel)**:
- T001, T002 can run together (different composer packages)
- T005, T006 can run together (different publish tags)

**Foundational Phase**:
- T007 (EntityType enum) can run parallel with T008 (migration)
- T010-T015 (User model updates) must run sequentially (same file)

**User Story 2 (Roles)**:
- T026-T033 (individual role creation methods) can run in parallel (separate methods in same file)

**User Story 3 (Users)**:
- T040-T044 (form fields) can be implemented in parallel (different form components)
- T045-T049 (table columns) can be implemented in parallel (different table columns)
- T050-T051 (filters) can be implemented in parallel

**User Story 4 (Permissions)**:
- T060-T068 (manual testing) can run in parallel if multiple testers available

**Testing Phase (ALL tests parallel)**:
- All test files (T069-T096) can be created in parallel (different files)
- Tests run via `php artisan test` executes in parallel by default

**Polish Phase (Most parallel)**:
- T101-T110 can all run in parallel (different concerns)

---

## Parallel Execution Examples

### Example 1: Foundational Phase Tasks

```bash
# Terminal 1: Create enum
touch app/Enums/EntityType.php
# ... implement EntityType enum

# Terminal 2: Create migration (runs in parallel)
php artisan make:migration add_entity_to_users_table --table=users
# ... implement migration

# Then both complete before moving to T009
```

### Example 2: User Story 3 Form Fields

```bash
# All form fields can be added simultaneously in UserResource.php
# Developer 1: Add name field (T040)
# Developer 2: Add email field (T041)
# Developer 3: Add password field (T042)
# Developer 4: Add entity field (T043)
# Developer 5: Add roles field (T044)

# Using multi_replace_string_in_file or manual edits
```

### Example 3: Testing Phase

```bash
# Create all test files in parallel (different developers or same developer)
# Terminal 1
touch tests/Feature/Auth/LoginTest.php
# ... implement LoginTest

# Terminal 2
touch tests/Feature/Auth/PermissionEnforcementTest.php
# ... implement PermissionEnforcementTest

# Terminal 3
touch tests/Feature/Filament/UserResourceTest.php
# ... implement UserResourceTest

# Terminal 4
touch tests/Feature/Filament/RoleResourceTest.php
# ... implement RoleResourceTest

# All run together
php artisan test --compact
```

### Example 4: Complete Feature Implementation Timeline

**Recommended Execution Order** (single developer):

1. **Day 1**: Setup + Foundational (T001-T018) - ~2 hours
2. **Day 2**: User Story 1 + User Story 2 in parallel (T019-T038) - ~4 hours
3. **Day 3**: User Story 3 (T039-T058) - ~6 hours
4. **Day 4**: User Story 4 + Testing start (T059-T080) - ~6 hours
5. **Day 5**: Complete Testing + Polish (T081-T114) - ~4 hours

**Total Estimated Time**: 22 hours (3-5 working days)

**With Team (3 developers)**:
- Developer 1: User Story 1 + User Story 4
- Developer 2: User Story 2 + Testing (Roles)
- Developer 3: User Story 3 + Testing (Users)

**Total Estimated Time**: 8-10 hours (1-2 working days)

---

## Success Metrics

Upon completion, all tasks should result in:

- ✅ **114 tasks completed** across 8 phases
- ✅ **4 user stories delivered** (3 P1, 1 P2)
- ✅ **8 roles seeded** with appropriate permissions
- ✅ **Entity isolation** implemented via EntityType enum
- ✅ **Audit logging** working for all user/role operations
- ✅ **Soft deletes** implemented and tested
- ✅ **100% permission enforcement** (no unauthorized access)
- ✅ **All tests passing** (28 test methods across 5 test files)
- ✅ **Login time < 3 seconds** verified
- ✅ **Code formatted** with Laravel Pint
- ✅ **Constitution compliance** validated (all 5 principles PASS)

---

## Implementation Strategy

### MVP Scope (User Story 1 only)

If you need to deliver the absolute minimum viable product:

**MVP Tasks**: T001-T023 (Setup + Foundational + User Story 1)
- Result: Super admin can login and see Shield UI
- Time: ~4-6 hours
- Deliverable: Working authentication with super admin access

### Recommended MVP Scope (User Stories 1-3)

**Recommended Tasks**: T001-T058 (Setup + Foundational + US1 + US2 + US3)
- Result: Full user and role management with entity isolation
- Time: ~16-20 hours
- Deliverable: Complete RBAC foundation ready for next features

### Full Feature Scope (All User Stories + Tests)

**All Tasks**: T001-T114
- Result: Production-ready RBAC with comprehensive tests and polish
- Time: ~22-28 hours
- Deliverable: Fully tested and documented authentication system

---

## Notes

- Tasks marked [P] can be executed in parallel for efficiency
- Tests (Phase 7) can be skipped for rapid prototyping but MUST be completed before production
- All Shield commands include `--no-interaction` flag for automation
- Run `vendor/bin/pint --dirty` before each commit (T101)
- Refer to quickstart.md for detailed implementation examples
- Constitution compliance checks are embedded throughout (all principles PASS)

---

**Tasks Ready for Implementation** ✅  
**Total Tasks**: 114  
**Estimated Completion**: 3-5 days (single developer) or 1-2 days (3-person team)  
**Next Step**: Begin with Phase 1 (Setup) tasks T001-T006
