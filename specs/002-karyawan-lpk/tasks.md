# Tasks: Karyawan LPK Management

**Feature Branch**: `002-karyawan-lpk`  
**Input**: Design documents from `/specs/002-karyawan-lpk/`  
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, quickstart.md ✅

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `- [ ] [ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure. Since Laravel project already exists, verify configuration only.

- [ ] T001 Verify private disk configuration exists in config/filesystems.php
- [ ] T002 Create storage/app/private/certificates/ directory for sertifikat files
- [ ] T003 [P] Verify spatie/laravel-activitylog is installed (from spec 001) or install if missing

**Checkpoint**: Storage infrastructure ready

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core enums, base model, migration that MUST be complete before ANY user story implementation

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T004 [P] Create JabatanLPK enum in app/Enums/JabatanLPK.php with cases: Instruktur, AdminLPK, Staff (implementing HasLabel)
- [ ] T005 [P] Create StatusKepegawaian enum in app/Enums/StatusKepegawaian.php with cases: Aktif, Cuti, Resign (implementing HasLabel)
- [ ] T006 [P] Verify EntityType enum exists in app/Enums/EntityType.php with PT and LPK cases (from spec 001)
- [ ] T007 Create EmployeeLPK model in app/Models/EmployeeLPK.php with SoftDeletes, LogsActivity, boot() event for entity auto-assignment
- [ ] T008 Create migration 2026_01_13_000001_create_karyawan_lpk_table.php with 20 fields, indexes, constraints, foreign keys
- [ ] T009 Create EmployeeLPKFactory in database/factories/EmployeeLPKFactory.php with instruktur() and resign() states
- [ ] T010 [P] Create EmployeeLPKPolicy in app/Policies/EmployeeLPKPolicy.php with viewAny, view, create, update, delete, restore, forceDelete, viewOwn, updateOwn methods
- [ ] T011 [P] Create StoreEmployeeLPKRequest in app/Http/Requests/StoreEmployeeLPKRequest.php with 18 validation rules
- [ ] T012 [P] Create UpdateEmployeeLPKRequest in app/Http/Requests/UpdateEmployeeLPKRequest.php with validation rules (nik not editable)
- [ ] T013 Run migration: php artisan migrate to create karyawan_lpk table
- [ ] T014 Create EmployeeLPKSeeder in database/seeders/EmployeeLPKSeeder.php with 10 sample employees (5 Instruktur, 3 Admin, 2 Staff)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Kelola Data Karyawan LPK (Priority: P1) 🎯 MVP

**Goal**: Admin LPK dapat perform full CRUD pada karyawan LPK dengan validasi uniqueness NIK/email, soft delete, entity isolation

**Independent Test**: 
1. Admin LPK login → akses menu Karyawan LPK → lihat tabel dengan filter Jabatan/Status
2. Tambah karyawan baru → submit → verify tersimpan dengan entity='LPK'
3. Edit karyawan → update nama/alamat → verify perubahan tercatat di audit log
4. Ubah status ke Resign → verify soft delete → filter "Only Trashed" → lihat record
5. Restore karyawan → verify kembali ke status Aktif

### Implementation for User Story 1

- [ ] T015 [P] [US1] Create EmployeeLPKResource in app/Filament/Resources/EmployeeLPKResource.php with form() method defining 3 sections: Personal Info, Employment, Audit
- [ ] T016 [P] [US1] Create ListEmployeesLPK page in app/Filament/Resources/EmployeeLPKResource/Pages/ListEmployeesLPK.php with table columns and TrashedFilter
- [ ] T017 [P] [US1] Create CreateEmployeeLPK page in app/Filament/Resources/EmployeeLPKResource/Pages/CreateEmployeeLPK.php
- [ ] T018 [P] [US1] Create EditEmployeeLPK page in app/Filament/Resources/EmployeeLPKResource/Pages/EditEmployeeLPK.php with NIK field disabled
- [ ] T019 [P] [US1] Create ViewEmployeeLPK page in app/Filament/Resources/EmployeeLPKResource/Pages/ViewEmployeeLPK.php with read-only infolist
- [ ] T020 [US1] Implement form schema in EmployeeLPKResource::form() with Personal Info section (nama_lengkap, nik, email, tanggal_lahir, jenis_kelamin, alamat, telepon)
- [ ] T021 [US1] Implement form schema Employment section (jabatan Select with enum, status Select with enum, tanggal_bergabung DatePicker, entity Hidden field default='LPK')
- [ ] T022 [US1] Implement table() method in EmployeeLPKResource with columns: nama_lengkap, nik, jabatan (badge), status (badge with color), tanggal_bergabung
- [ ] T023 [US1] Add filters to table: SelectFilter for jabatan (using JabatanLPK enum), SelectFilter for status (using StatusKepegawaian enum), TrashedFilter
- [ ] T024 [US1] Add table actions: EditAction, DeleteAction (soft delete with status change to Resign), RestoreAction, ForceDeleteAction (admin only)
- [ ] T025 [US1] Implement getEloquentQuery() in ListEmployeesLPK to scope by entity='LPK'
- [ ] T026 [US1] Configure resource navigation: icon, group "Data Master", sort order, badge count for active employees
- [ ] T027 [US1] Register EmployeeLPKResource in Filament panel provider (should auto-discover)
- [ ] T028 [US1] Add authorization to EmployeeLPKResource::canViewAny() checking user has 'view_any_karyawan_lpk' permission

### Testing for User Story 1

- [ ] T029 [P] [US1] Create EmployeeLPKResourceTest in tests/Feature/Filament/EmployeeLPKResourceTest.php testing CRUD operations via Livewire
- [ ] T030 [P] [US1] Test case: Admin LPK can list all employees, see table with nama/nik/jabatan columns
- [ ] T031 [P] [US1] Test case: Admin LPK can create employee with valid data, entity auto-set to LPK
- [ ] T032 [P] [US1] Test case: Admin LPK cannot create employee with duplicate NIK (validation error)
- [ ] T033 [P] [US1] Test case: Admin LPK cannot create employee with duplicate email (validation error)
- [ ] T034 [P] [US1] Test case: Admin LPK can edit employee, NIK field disabled
- [ ] T035 [P] [US1] Test case: Admin LPK can soft delete employee (status changes to Resign, deleted_at filled)
- [ ] T036 [P] [US1] Test case: Admin LPK can restore soft-deleted employee (status changes to Aktif, deleted_at cleared)
- [ ] T037 [P] [US1] Test case: Filter by jabatan=Instruktur shows only Instruktur employees
- [ ] T038 [P] [US1] Test case: Filter by status=Aktif shows only active employees
- [ ] T039 [P] [US1] Test case: TrashedFilter "Only Trashed" shows only soft-deleted employees
- [ ] T040 [US1] Create EmployeeLPKValidationTest in tests/Feature/Filament/EmployeeLPKValidationTest.php
- [ ] T041 [P] [US1] Test case: NIK must be 16 digits (reject 15 digits, reject 17 digits, reject letters)
- [ ] T042 [P] [US1] Test case: Email must be valid format (reject invalid@, reject @domain.com)
- [ ] T043 [P] [US1] Test case: Tanggal lahir must be before today (reject future date)
- [ ] T044 [P] [US1] Test case: Tanggal bergabung must be >= tanggal lahir (reject date before birth)
- [ ] T045 [US1] Create EmployeeLPKModelTest in tests/Feature/Models/EmployeeLPKModelTest.php
- [ ] T046 [P] [US1] Test case: Entity auto-assigned to LPK on model creation
- [ ] T047 [P] [US1] Test case: SoftDeletes trait works (deleted record has deleted_at)
- [ ] T048 [P] [US1] Test case: LogsActivity trait logs create/update/delete actions
- [ ] T049 [US1] Create EmployeeLPKAuthorizationTest in tests/Feature/Filament/EmployeeLPKAuthorizationTest.php
- [ ] T050 [P] [US1] Test case: Admin LPK role can viewAny, create, update, delete employees
- [ ] T051 [P] [US1] Test case: Pimpinan role can viewAny but cannot create/update/delete
- [ ] T052 [P] [US1] Test case: Instruktur role cannot viewAny employees (access denied)
- [ ] T053 [US1] Run all US1 tests: php artisan test --filter=EmployeeLPK

**Checkpoint**: User Story 1 complete and independently functional. Admin LPK can perform full CRUD with entity isolation, soft delete, audit logging.

---

## Phase 4: User Story 2 - Kelola Honor Karyawan LPK (Priority: P2)

**Goal**: Admin LPK dan Keuangan LPK dapat mencatat honor pokok + honor per jam (untuk Instruktur). Data honor dapat difilter.

**Independent Test**:
1. Admin LPK edit karyawan → lihat section Kompensasi → fill honor_pokok → submit → verify tersimpan
2. Karyawan dengan jabatan=Instruktur → honor_per_jam field visible → fill value → submit
3. Karyawan non-Instruktur → honor_per_jam field hidden
4. Keuangan LPK login → edit karyawan → dapat update honor fields → TIDAK dapat delete
5. Filter "Ada Honor" → tampil hanya karyawan dengan honor_pokok NOT NULL

### Implementation for User Story 2

- [ ] T054 [US2] Add Kompensasi section to EmployeeLPKResource::form() after Employment section
- [ ] T055 [US2] Add honor_pokok TextInput (numeric, min:0, suffix: 'Rupiah') to Kompensasi section
- [ ] T056 [US2] Add honor_per_jam TextInput (numeric, min:0, suffix: 'Rupiah', visible only if jabatan=Instruktur) to Kompensasi section
- [ ] T057 [US2] Implement reactive() on jabatan field to show/hide honor_per_jam based on selection
- [ ] T058 [US2] Add honor_pokok column to table (formatted as Rupiah currency, sortable)
- [ ] T059 [US2] Add SelectFilter "Ada Honor" to table filters (Yes = honor_pokok NOT NULL, No = honor_pokok NULL)
- [ ] T060 [US2] Update EmployeeLPKPolicy::update() to allow Keuangan LPK role to update only honor fields
- [ ] T061 [US2] Add form field authorization: make honor fields editable by Keuangan, other fields read-only for Keuangan

### Testing for User Story 2

- [ ] T062 [P] [US2] Test case: Admin LPK can set honor_pokok for any employee
- [ ] T063 [P] [US2] Test case: honor_per_jam field visible for Instruktur jabatan
- [ ] T064 [P] [US2] Test case: honor_per_jam field hidden for Admin LPK and Staff jabatan
- [ ] T065 [P] [US2] Test case: Changing jabatan from Instruktur to Staff hides honor_per_jam field
- [ ] T066 [P] [US2] Test case: Keuangan LPK can edit honor_pokok and honor_per_jam
- [ ] T067 [P] [US2] Test case: Keuangan LPK cannot edit nama_lengkap, email, or other non-honor fields
- [ ] T068 [P] [US2] Test case: Keuangan LPK cannot delete employee
- [ ] T069 [P] [US2] Test case: Filter "Ada Honor=Yes" shows only employees with honor_pokok NOT NULL
- [ ] T070 [P] [US2] Test case: Filter "Ada Honor=No" shows only employees with honor_pokok NULL
- [ ] T071 [P] [US2] Test case: Honor validation rejects negative values
- [ ] T072 [P] [US2] Test case: Honor validation rejects non-numeric input
- [ ] T073 [US2] Run US2 tests: php artisan test --filter=Honor

**Checkpoint**: User Stories 1 AND 2 independently functional. Honor management complete with role-based access.

---

## Phase 5: User Story 3 - Kelola Sertifikat Instruktur (Priority: P3)

**Goal**: Admin LPK dapat upload sertifikat kompetensi untuk Instruktur. File tersimpan private dengan authorization untuk download.

**Independent Test**:
1. Admin LPK edit Instruktur → lihat section "Sertifikat Kompetensi" → upload PDF 3MB → submit → verify file saved to storage/app/private/certificates/
2. Download link muncul di detail → klik → file downloaded
3. Upload file 6MB → validation error "File maksimal 5MB"
4. Upload file .docx → validation error "Format harus PDF/JPG/PNG"
5. Edit karyawan non-Instruktur → section sertifikat TIDAK tampil
6. Instruktur login → lihat profil sendiri → dapat download sertifikat sendiri
7. Instruktur TIDAK dapat download sertifikat instruktur lain (403 Forbidden)

### Implementation for User Story 3

- [ ] T074 [US3] Add Sertifikat Kompetensi section to EmployeeLPKResource::form() after Kompensasi section, visible only if jabatan=Instruktur
- [ ] T075 [US3] Add sertifikat_path FileUpload field (disk='private', directory='certificates', acceptedFileTypes: pdf/jpg/png, maxSize: 5120 KB)
- [ ] T076 [US3] Configure FileUpload visibility='private' and storeFileNamesIn to save original filename
- [ ] T077 [US3] Add sertifikat_download_url attribute accessor in EmployeeLPK model returning signed URL for private file
- [ ] T078 [US3] Create custom download route in routes/web.php: GET /karyawan-lpk/{employee}/sertifikat/download
- [ ] T079 [US3] Create download controller method checking EmployeeLPKPolicy::downloadSertifikat before serving file
- [ ] T080 [US3] Implement EmployeeLPKPolicy::downloadSertifikat allowing Admin LPK, Pimpinan, and owner Instruktur
- [ ] T081 [US3] Add sertifikat indicator column to table (icon if file exists, null if no file)
- [ ] T082 [US3] Add download link to ViewEmployeeLPK infolist (only if sertifikat_path exists and user authorized)
- [ ] T083 [US3] Update form reactive: when jabatan changes to Instruktur, show sertifikat section; when changes from Instruktur, hide (but preserve file)

### Testing for User Story 3

- [ ] T084 [P] [US3] Test case: Admin LPK can upload PDF sertifikat for Instruktur
- [ ] T085 [P] [US3] Test case: Admin LPK can upload JPG sertifikat for Instruktur
- [ ] T086 [P] [US3] Test case: Admin LPK can upload PNG sertifikat for Instruktur
- [ ] T087 [P] [US3] Test case: Upload 5MB file succeeds (at size limit)
- [ ] T088 [P] [US3] Test case: Upload 5.1MB file fails with validation error
- [ ] T089 [P] [US3] Test case: Upload .docx file fails with validation error "Format harus PDF/JPG/PNG"
- [ ] T090 [P] [US3] Test case: Sertifikat section visible for jabatan=Instruktur
- [ ] T091 [P] [US3] Test case: Sertifikat section hidden for jabatan=Admin LPK
- [ ] T092 [P] [US3] Test case: Sertifikat section hidden for jabatan=Staff
- [ ] T093 [P] [US3] Test case: File saved to storage/app/private/certificates/ with correct naming
- [ ] T094 [P] [US3] Test case: Admin LPK can download any Instruktur's sertifikat
- [ ] T095 [P] [US3] Test case: Pimpinan can download any Instruktur's sertifikat
- [ ] T096 [P] [US3] Test case: Instruktur can download own sertifikat
- [ ] T097 [P] [US3] Test case: Instruktur CANNOT download other Instruktur's sertifikat (403 Forbidden)
- [ ] T098 [P] [US3] Test case: Keuangan LPK CANNOT download any sertifikat (no access)
- [ ] T099 [P] [US3] Test case: Upload new sertifikat replaces old file
- [ ] T100 [P] [US3] Test case: Changing jabatan from Instruktur to Staff hides sertifikat field but preserves file
- [ ] T101 [US3] Run US3 tests: php artisan test --filter=Sertifikat

**Checkpoint**: User Stories 1, 2, AND 3 independently functional. Sertifikat management complete with private storage and authorization.

---

## Phase 6: User Story 4 - Instruktur Lihat Profil Sendiri (Priority: P3)

**Goal**: Instruktur dapat self-service view dan update alamat/telepon sendiri tanpa bantuan Admin LPK.

**Independent Test**:
1. User dengan role Instruktur login → akses menu "Profil Saya" → lihat detail profil sendiri
2. Fields read-only: nama_lengkap, nik, email, jabatan, honor (grayed out)
3. Fields editable: alamat, telepon
4. Update alamat → submit → verify perubahan tersimpan dan audit log tercatat
5. Instruktur coba akses menu "Karyawan LPK" → access denied (403)
6. Instruktur coba download sertifikat sendiri (jika ada) → berhasil download

### Implementation for User Story 4

- [ ] T102 [US4] Create EmployeeLPKProfileResource in app/Filament/Resources/Profile/EmployeeLPKProfileResource.php for self-service access
- [ ] T103 [US4] Create ViewProfile page in app/Filament/Resources/Profile/EmployeeLPKProfileResource/Pages/ViewProfile.php
- [ ] T104 [US4] Create EditProfile page in app/Filament/Resources/Profile/EmployeeLPKProfileResource/Pages/EditProfile.php
- [ ] T105 [US4] Implement form() in EmployeeLPKProfileResource with read-only fields: nama_lengkap, nik, email, jabatan, status, honor_pokok, honor_per_jam
- [ ] T106 [US4] Add editable fields: alamat (Textarea), telepon (TextInput)
- [ ] T107 [US4] Add sertifikat download link (read-only, if exists) to profile view
- [ ] T108 [US4] Override getEloquentQuery() to return only auth()->user()->employeeLPK() (scope to own record)
- [ ] T109 [US4] Implement EmployeeLPKPolicy::viewOwn() allowing Instruktur to view own profile
- [ ] T110 [US4] Implement EmployeeLPKPolicy::updateOwn() allowing Instruktur to update only alamat and telepon
- [ ] T111 [US4] Configure ProfileResource navigation: icon, label "Profil Saya", visible only for Instruktur role
- [ ] T112 [US4] Add relationship from User model to EmployeeLPK: hasOne via email match or custom pivot
- [ ] T113 [US4] Disable create/delete actions in ProfileResource (only view and edit own profile)

### Testing for User Story 4

- [ ] T114 [P] [US4] Test case: Instruktur can access "Profil Saya" menu
- [ ] T115 [P] [US4] Test case: Instruktur sees own profile data (nama, NIK, email, jabatan)
- [ ] T116 [P] [US4] Test case: Read-only fields are disabled: nama_lengkap, nik, email, jabatan, honor
- [ ] T117 [P] [US4] Test case: Editable fields are enabled: alamat, telepon
- [ ] T118 [P] [US4] Test case: Instruktur can update alamat and submit successfully
- [ ] T119 [P] [US4] Test case: Instruktur can update telepon and submit successfully
- [ ] T120 [P] [US4] Test case: Instruktur cannot access "Karyawan LPK" menu (hidden or 403)
- [ ] T121 [P] [US4] Test case: Instruktur cannot view other employees' profiles
- [ ] T122 [P] [US4] Test case: Instruktur can download own sertifikat from profile page
- [ ] T123 [P] [US4] Test case: Instruktur CANNOT edit honor_pokok or honor_per_jam
- [ ] T124 [P] [US4] Test case: Audit log records Instruktur's profile updates
- [ ] T125 [P] [US4] Test case: Admin LPK cannot access "Profil Saya" menu (menu hidden for Admin)
- [ ] T126 [US4] Run US4 tests: php artisan test --filter=Profile

**Checkpoint**: All 4 user stories independently functional. Self-service profile management complete.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories, code quality, and production readiness

- [ ] T127 [P] Run Laravel Pint code formatter: vendor/bin/pint app/Models/EmployeeLPK.php app/Filament/Resources/
- [ ] T128 [P] Add PHPDoc blocks to all EmployeeLPK model methods (relationships, scopes, accessors)
- [ ] T129 [P] Add PHPDoc blocks to EmployeeLPKPolicy methods
- [ ] T130 [P] Verify all Filament form labels are in Bahasa Indonesia
- [ ] T131 [P] Verify all validation error messages are in Bahasa Indonesia
- [ ] T132 Add table search functionality to EmployeeLPKResource (search by nama, NIK, email)
- [ ] T133 Add table sorting to EmployeeLPKResource (sort by nama, jabatan, status, tanggal_bergabung)
- [ ] T134 Configure table default sort: tanggal_bergabung DESC (newest employees first)
- [ ] T135 Add bulk delete action to table (soft delete multiple employees, Admin only)
- [ ] T136 Add export action to table (export to Excel, PDF - Admin and Pimpinan only)
- [ ] T137 [P] Create database indexes verification script checking indexes exist on jabatan, status, entity, deleted_at
- [ ] T138 [P] Verify entity='LPK' constraint prevents manual override (try to create with entity='PT', should fail)
- [ ] T139 [P] Verify soft delete behavior: deleted employees have deleted_at filled and status='Resign'
- [ ] T140 [P] Verify restore behavior: restored employees have deleted_at NULL and status='Aktif'
- [ ] T141 Run full test suite: php artisan test --coverage to ensure all scenarios covered
- [ ] T142 Seed database with realistic data: php artisan db:seed --class=EmployeeLPKSeeder
- [ ] T143 Validate quickstart.md workflows manually:
  - Section 1: Create karyawan LPK as Admin LPK
  - Section 2: View and edit karyawan
  - Section 3: Manage honor as Keuangan LPK
  - Section 4: Self-service profile as Instruktur
  - Section 5: View karyawan as Pimpinan
  - Section 6: Certificate download
- [ ] T144 [P] Add Filament notifications for success/error messages (create, update, delete operations)
- [ ] T145 [P] Configure Filament table pagination (default 25 per page, options: 10, 25, 50, 100)
- [ ] T146 [P] Add table column toggles (allow users to show/hide columns)
- [ ] T147 Performance test: Load 500 employee records, verify table renders in <2 seconds
- [ ] T148 Performance test: Upload 5MB sertifikat file, verify submission completes in <3 seconds
- [ ] T149 Security audit: Verify private files cannot be accessed without authorization (test direct URL access)
- [ ] T150 Security audit: Verify entity isolation (Instruktur cannot see karyawan_pt records)
- [ ] T151 [P] Add README.md section documenting Karyawan LPK feature setup and usage
- [ ] T152 [P] Update .env.example with required FILESYSTEM_DISK_PRIVATE=private configuration
- [ ] T153 Commit all changes with message: "feat(002): Complete Karyawan LPK Management feature with CRUD, honor, sertifikat, self-service"
- [ ] T154 Create pull request from 002-karyawan-lpk to main with quickstart.md validation checklist

**Final Checkpoint**: Feature complete, tested, documented, and ready for code review.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - verify configuration immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational phase - Basic CRUD foundation
- **User Story 2 (Phase 4)**: Depends on Foundational phase - Builds on US1 resource (adds honor fields)
- **User Story 3 (Phase 5)**: Depends on Foundational phase - Builds on US1 resource (adds sertifikat field)
- **User Story 4 (Phase 6)**: Depends on Foundational phase - Independent ProfileResource
- **Polish (Phase 7)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - **No dependencies on other stories** - MVP ready
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - Extends US1 EmployeeLPKResource with honor section
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - Extends US1 EmployeeLPKResource with sertifikat section
- **User Story 4 (P3)**: Can start after Foundational (Phase 2) - **Independent resource**, no dependency on US1/US2/US3

### Within Each User Story

**User Story 1 (Basic CRUD)**:
1. T015-T019 (Pages) can run in parallel
2. T020-T021 (Form sections) - sequential (employment depends on personal info structure)
3. T022-T024 (Table) - sequential
4. T025-T028 (Configuration) - sequential after table
5. T029-T053 (Tests) - most tests can run in parallel after implementation complete

**User Story 2 (Honor Management)**:
1. T054-T057 (Form additions) - sequential
2. T058-T061 (Table and policy) - can run in parallel with form
3. T062-T073 (Tests) - can run in parallel after implementation complete

**User Story 3 (Sertifikat)**:
1. T074-T077 (Form) - sequential
2. T078-T080 (Download route and policy) - sequential
3. T081-T083 (UI enhancements) - can run in parallel with route
4. T084-T101 (Tests) - can run in parallel after implementation complete

**User Story 4 (Self-Service Profile)**:
1. T102-T104 (ProfileResource structure) - sequential
2. T105-T107 (Form) - sequential after structure
3. T108-T113 (Query scope and policy) - can run in parallel
4. T114-T126 (Tests) - can run in parallel after implementation complete

**Polish Phase**:
- T127-T131 (Code quality) - all parallelizable
- T132-T136 (Table enhancements) - sequential
- T137-T140 (Verification) - all parallelizable
- T141-T143 (Testing and validation) - sequential
- T144-T146 (UI polish) - all parallelizable
- T147-T150 (Performance and security) - all parallelizable
- T151-T154 (Documentation and PR) - sequential

### Parallel Opportunities

**Phase 1 (Setup)**: T001, T002, T003 can all run in parallel (3 concurrent tasks)

**Phase 2 (Foundational)**: 
- T004, T005, T006 (Enums) - parallel (3 tasks)
- T010, T011, T012 (Policy and Requests) - parallel (3 tasks)
- After T007-T009 complete: T013, T014 - parallel (2 tasks)

**Phase 3 (User Story 1)**:
- T015, T016, T017, T018, T019 (All pages) - parallel (5 tasks)
- After implementation: T029-T048 (Most tests) - parallel (up to 20 tests)

**Phase 4 (User Story 2)**:
- T058-T061 (Table/Policy) - parallel with T054-T057 (2-3 concurrent tasks)
- T062-T072 (All tests) - parallel (11 tasks)

**Phase 5 (User Story 3)**:
- T081-T083 (UI) - parallel with T078-T080 (3-4 concurrent tasks)
- T084-T100 (All tests) - parallel (17 tasks)

**Phase 6 (User Story 4)**:
- T108-T113 (Scope/Policy/Config) - parallel (6 tasks)
- T114-T125 (All tests) - parallel (12 tasks)

**Phase 7 (Polish)**:
- T127-T131 (Code quality) - parallel (5 tasks)
- T137-T140 (Verification) - parallel (4 tasks)
- T144-T146 (UI polish) - parallel (3 tasks)
- T147-T150 (Audits) - parallel (4 tasks)
- T151-T152 (Docs) - parallel (2 tasks)

**Cross-Phase Parallelization**:
- Once Foundational (Phase 2) completes, User Stories 1, 2, 3, 4 can ALL start in parallel (4 concurrent tracks)
- Example: Developer A works on US1, Developer B works on US2, Developer C works on US3, Developer D works on US4

---

## Parallel Execution Examples

### Optimal Parallel Execution for Team of 4

```bash
# Phase 2 (Foundational) - Day 1
Developer A: T004, T007, T009 (Enums + Model + Factory)
Developer B: T005, T008, T014 (Enums + Migration + Seeder)  
Developer C: T006, T010 (Enum verification + Policy)
Developer D: T011, T012, T013 (Requests + Run migration)

# Phase 3-6 (All User Stories in Parallel) - Day 2-5
Developer A: User Story 1 (T015-T053) - Basic CRUD
Developer B: User Story 2 (T054-T073) - Honor management
Developer C: User Story 3 (T074-T101) - Sertifikat  
Developer D: User Story 4 (T102-T126) - Self-service profile

# Phase 7 (Polish) - Day 6
All developers: Code quality (T127-T131), Testing (T141-T143), Security (T149-T150)
Developer A: Table enhancements (T132-T136)
Developer B: Performance tests (T147-T148)
Developer C: Documentation (T151-T152)
Developer D: PR preparation (T153-T154)
```

### Solo Developer Sequential Execution

```bash
# Day 1: Setup + Foundation
Phase 1: T001-T003 (30 min)
Phase 2: T004-T014 (4 hours)

# Day 2-3: MVP (User Story 1 only)
Phase 3: T015-T028 (Implementation - 6 hours)
Phase 3: T029-T053 (Testing - 4 hours)
✅ Deliverable: Working CRUD with entity isolation

# Day 4: Honor Management (User Story 2)
Phase 4: T054-T061 (Implementation - 3 hours)
Phase 4: T062-T073 (Testing - 2 hours)
✅ Deliverable: Honor management working

# Day 5: Sertifikat (User Story 3)
Phase 5: T074-T083 (Implementation - 4 hours)
Phase 5: T084-T101 (Testing - 3 hours)
✅ Deliverable: Sertifikat upload/download working

# Day 6: Self-Service (User Story 4)
Phase 6: T102-T113 (Implementation - 3 hours)
Phase 6: T114-T126 (Testing - 2 hours)
✅ Deliverable: Instruktur self-service working

# Day 7: Polish
Phase 7: T127-T154 (6 hours)
✅ Deliverable: Production-ready feature
```

---

## Implementation Strategy

### MVP-First Approach (Recommended)

**Week 1 Goal**: Deliver User Story 1 only (Basic CRUD)
- Complete Phase 1, 2, 3 (T001-T053)
- Result: Admin LPK can manage karyawan LPK with entity isolation, soft delete, audit logging
- Deployable to production immediately

**Week 2 Goal**: Add User Story 2 (Honor Management)
- Complete Phase 4 (T054-T073)
- Result: Honor tracking for payroll
- Non-breaking addition to existing functionality

**Week 3 Goal**: Add User Story 3 + 4 (Sertifikat + Self-Service)
- Complete Phase 5 + 6 (T074-T126)
- Result: Full feature set complete
- Instruktur compliance tracking + self-service

**Week 4 Goal**: Polish and Production Hardening
- Complete Phase 7 (T127-T154)
- Result: Performance optimized, security audited, documented

### Test-Driven Development (TDD) Option

If TDD is preferred:
1. Write tests FIRST for each user story (T029-T053, T062-T073, T084-T101, T114-T126)
2. Ensure all tests FAIL (red phase)
3. Implement features to make tests pass (green phase)
4. Refactor for code quality (refactor phase)

This approach adds ~20% time but increases code quality and reduces bugs.

---

## Task Summary

- **Total Tasks**: 154
- **Phase 1 (Setup)**: 3 tasks
- **Phase 2 (Foundational)**: 11 tasks - **CRITICAL PATH**
- **Phase 3 (User Story 1 - P1 MVP)**: 39 tasks (14 implementation + 25 testing)
- **Phase 4 (User Story 2 - P2)**: 20 tasks (9 implementation + 11 testing)
- **Phase 5 (User Story 3 - P3)**: 28 tasks (10 implementation + 18 testing)
- **Phase 6 (User Story 4 - P3)**: 25 tasks (12 implementation + 13 testing)
- **Phase 7 (Polish)**: 28 tasks

**Parallelizable Tasks**: 87 tasks marked [P] (56% can run in parallel)

**Estimated Effort**:
- Solo developer (sequential): 6-7 days
- Team of 2 (parallel user stories): 4-5 days
- Team of 4 (all stories parallel): 3-4 days

**MVP Delivery** (User Story 1 only): 2-3 days solo, 1-2 days with team

---

## Next Steps

1. Review task breakdown with team
2. Assign developers to user stories (if parallel execution)
3. Start with Phase 1 + Phase 2 (everyone together on foundation)
4. Once Phase 2 complete, split into parallel tracks per user story
5. Merge user stories incrementally (US1 first, then US2, etc.)
6. Complete Polish phase together
7. Code review and merge to main

**Ready to implement!** 🚀
