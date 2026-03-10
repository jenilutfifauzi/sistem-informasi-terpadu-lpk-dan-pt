# Tasks: Karyawan PT Management

**Feature Branch**: `009-karyawan-pt-resource`
**Input**: Design documents from `/specs/009-karyawan-pt-resource/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, quickstart.md ✅, contracts/ ✅ (N/A)

**Organization**: Tasks grouped by user story to enable independent implementation and testing.

## Format: `- [ ] [ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Exact file paths included in all task descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify existing infrastructure is ready. Laravel project already exists — verify and create only what's missing.

- [X] T001 Verify private disk configuration in config/filesystems.php supports private storage for dokumen kepegawaian
- [X] T002 Create storage/app/private/documents/ directory for dokumen kepegawaian files
- [X] T003 [P] Verify spatie/laravel-activitylog is available (already used by EmployeeLPK — confirm activity_log table exists)

**Checkpoint**: Storage infrastructure ready

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Enums, model, migration, factory, policy, and form requests that MUST exist before any user story work begins.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [X] T004 [P] Create JabatanPT enum in app/Enums/JabatanPT.php with cases: Direktur, Manajer, StafHRD, StafKeuangan, StafOperasional, StafAdministrasi (implementing HasLabel)
- [X] T005 [P] Create DivisiPT enum in app/Enums/DivisiPT.php with cases: Manajemen, HRD, Keuangan, Operasional, Administrasi (implementing HasLabel)
- [X] T006 [P] Create JenisKontrak enum in app/Enums/JenisKontrak.php with cases: Tetap, PKWT, Probasi (implementing HasLabel)
- [X] T007 Create EmployeePT model in app/Models/EmployeePT.php with SoftDeletes, LogsActivity, HasFactory; boot() auto-assigning entity='PT', created_by/updated_by via auth()->id() (NOT in $fillable); casts() for JabatanPT/DivisiPT/JenisKontrak/StatusKepegawaian; updating observer that calls $employee->delete() when status transitions to StatusKepegawaian::Resign (triggers soft delete from form status change)
- [X] T008 Create migration database/migrations/2026_03_09_000001_create_karyawan_pt_table.php with all fields: personal info, jabatan, divisi, status, jenis_kontrak, tanggal_bergabung, gaji_pokok, tunjangan, foto_path, dokumen_path, entity (default='PT'), created_by, updated_by, softDeletes, indexes, foreign keys
- [X] T009 Create EmployeePTFactory in database/factories/EmployeePTFactory.php with aktif() and resign() and withDokumen() states
- [X] T010 [P] Create EmployeePTPolicy in app/Policies/EmployeePTPolicy.php — Admin PT (full CRUD: viewAny, view, create, update, delete, restore, forceDelete), Keuangan PT (viewAny + view + updateKompensasi only — cannot create/delete), Pimpinan (viewAny + view + downloadDokumen only), super_admin (full). Admin LPK / Keuangan LPK denied.
- [X] T011 [P] Create StoreEmployeePTRequest in app/Http/Requests/StoreEmployeePTRequest.php with validation rules: nik (size:16, unique:karyawan_pt), email (unique:karyawan_pt), jabatan (Rule::enum), divisi (Rule::enum), jenis_kontrak (Rule::enum), gaji_pokok (nullable, numeric, min:0), dokumen_path (nullable, mimes, max:5120), etc.
- [X] T012 [P] Create UpdateEmployeePTRequest in app/Http/Requests/UpdateEmployeePTRequest.php with validation rules (nik & tanggal_bergabung NOT present — disabled fields not submitted)
- [X] T013 Run migration: php artisan migrate to create karyawan_pt table and verify with php artisan db:show --table=karyawan_pt
- [X] T014 Create EmployeePTSeeder in database/seeders/EmployeePTSeeder.php with 8 sample karyawan PT across all jabatan types

**Checkpoint**: Foundation ready — user story phases can now begin.

---

## Phase 3: User Story 1 - Kelola Data Karyawan PT (Priority: P1) 🎯 MVP

**Goal**: Admin PT dapat perform full CRUD pada karyawan PT — lihat tabel, tambah, edit, view detail, soft delete, restore — dengan validasi uniqueness NIK/email, entity isolation entity='PT', audit log, dan RBAC.

**Independent Test**:
1. Admin PT login → akses menu "Karyawan PT" di grup "Data Master" sidebar → lihat tabel dengan kolom Foto/Nama/NIK/Jabatan/Divisi/Status/Tanggal Bergabung
2. Tambah karyawan baru → isi semua field wajib → submit → verify tersimpan dengan entity='PT' di DB
3. Edit karyawan → NIK dan Tanggal Bergabung berstatus disabled → update nama/alamat → verify audit log tercatat
4. Ubah status ke Resign → verify soft delete (deleted_at filled, tidak muncul di list default)
5. Aktifkan filter "Tampilkan Data Resign" → lihat record resign → restore → verify kembali aktif
6. Admin LPK login → menu "Karyawan PT" TIDAK muncul di sidebar

### Implementation for User Story 1

- [X] T015 [P] [US1] Create EmployeePTResource in app/Filament/Resources/EmployeePTResource.php with model, slug='karyawan-pts', navigationGroup='Data Master', navigationSort=2, recordTitleAttribute='nama_lengkap', getModelLabel/getPluralModelLabel Bahasa Indonesia
- [X] T016 [P] [US1] Create ListEmployeesPT page in app/Filament/Resources/EmployeePTResource/Pages/ListEmployeesPT.php
- [X] T017 [P] [US1] Create CreateEmployeePT page in app/Filament/Resources/EmployeePTResource/Pages/CreateEmployeePT.php
- [X] T018 [P] [US1] Create EditEmployeePT page in app/Filament/Resources/EmployeePTResource/Pages/EditEmployeePT.php
- [X] T019 [P] [US1] Create ViewEmployeePT page in app/Filament/Resources/EmployeePTResource/Pages/ViewEmployeePT.php
- [X] T020 [US1] Implement form() in EmployeePTResource: Section "Informasi Personal" (nama_lengkap, nik [disabled on edit], email, tanggal_lahir, jenis_kelamin Select, alamat Textarea, telepon, foto_path FileUpload disk='public' dir='karyawan-pt-photos' max=2048)
- [X] T021 [US1] Implement form() Section "Informasi Kepegawaian" (jabatan Select options JabatanPT::class, divisi Select options DivisiPT::class, status Select options StatusKepegawaian::class default='Aktif', jenis_kontrak Select options JenisKontrak::class, tanggal_bergabung DatePicker [disabled on edit], entity Hidden default='PT')
- [X] T022 [US1] Implement table() method in EmployeePTResource with columns: foto_path (ImageColumn, circular, public disk), nama_lengkap (searchable, sortable), nik (searchable), jabatan (BadgeColumn with colors map), divisi (TextColumn, sortable), status (BadgeColumn with Aktif=success/Cuti=warning/Resign=danger), tanggal_bergabung (date, sortable), dokumen_path (IconColumn boolean), email (toggleable hidden by default)
- [X] T023 [US1] Add filters to table: SelectFilter jabatan (JabatanPT::class), SelectFilter divisi (DivisiPT::class), SelectFilter status (StatusKepegawaian::class), SelectFilter jenis_kontrak (JenisKontrak::class), TrashedFilter label='Tampilkan Data Resign'
- [X] T024 [US1] Add table actions: ViewAction, EditAction, DeleteAction (soft delete), RestoreAction, ForceDeleteAction
- [X] T025 [US1] Add table bulkActions: DeleteBulkAction, RestoreBulkAction
- [X] T026 [US1] Implement infolist() in EmployeePTResource: Section "Informasi Personal" (ImageEntry foto, TextEntry nama/nik/email/tanggal_lahir/jenis_kelamin/alamat/telepon), Section "Informasi Kepegawaian" (jabatan/divisi/status/jenis_kontrak/tanggal_bergabung)
- [X] T027 [US1] Configure EmployeePTResource::getPages() to return all 4 page routes (index/create/view/edit)
- [X] T028 [US1] Implement EmployeePTResource::getEloquentQuery() scoped to entity='PT' and using withTrashed() support for TrashedFilter
- [X] T029 [US1] Configure authorization: EmployeePTResource::canViewAny() checks 'view_any_karyawan_pt' permission via EmployeePTPolicy

### Testing for User Story 1

- [X] T030 [P] [US1] Create EmployeePTResourceTest in tests/Feature/EmployeePTResourceTest.php — Admin PT can list employees, see expected table columns
- [X] T031 [P] [US1] Test: Admin PT can create employee with all required fields, entity auto-set to 'PT'
- [X] T032 [P] [US1] Test: Admin PT cannot create employee with duplicate NIK (validation fails)
- [X] T033 [P] [US1] Test: Admin PT cannot create employee with duplicate email — test exact duplicate AND case-insensitive duplicate ('User@Email.com' conflicts with 'user@email.com') (validation fails both)
- [X] T034 [P] [US1] Test: Admin PT can edit employee, NIK field is disabled (not modifiable)
- [X] T035 [P] [US1] Test: Admin PT changes employee status to 'Resign' via edit form → model updating observer triggers soft delete (deleted_at set, record hidden from default list) — tests the status-driven trigger, not only the standalone DeleteAction
- [X] T036 [P] [US1] Test: Admin PT can restore soft-deleted employee via RestoreAction — verify restored employee has status='Aktif' and deleted_at=null
- [X] T037 [P] [US1] Test: TrashedFilter shows only soft-deleted records when toggled
- [X] T038 [P] [US1] Test: Filter by jabatan shows only matching employees
- [X] T039 [P] [US1] Test: Filter by divisi shows only matching employees
- [X] T040 [P] [US1] Create EmployeePTValidationTest in tests/Feature/EmployeePTValidationTest.php — NIK must be 16 digits only
- [X] T041 [P] [US1] Test: NIK with 15 chars rejected with validation error
- [X] T042 [P] [US1] Test: NIK with 17 chars rejected with validation error
- [X] T043 [P] [US1] Test: NIK containing letters rejected
- [X] T044 [P] [US1] Test: Email with invalid format rejected
- [X] T045 [P] [US1] Test: Tanggal lahir in future rejected
- [X] T046 [P] [US1] Create EmployeePTAuthorizationTest in tests/Feature/EmployeePTAuthorizationTest.php — Admin PT role has full CRUD access
- [X] T047 [P] [US1] Test: Keuangan PT can viewAny and view employees and can update kompensasi fields (gaji_pokok/tunjangan) but cannot create, delete, or update non-kompensasi fields (expect 403)
- [X] T048 [P] [US1] Test: Admin LPK has NO access to Karyawan PT (403 on viewAny)
- [X] T049 [P] [US1] Test: Keuangan LPK has NO access to Karyawan PT (403 on viewAny)
- [X] T050 [P] [US1] Test: Entity is always 'PT' on EmployeePT creation, never 'LPK'
- [X] T051 [US1] Run all US1 tests: php artisan test --compact --filter=EmployeePT

**Checkpoint**: User Story 1 complete and independently functional. Admin PT can perform full CRUD with entity isolation, soft delete, audit logging, and RBAC enforced.

---

## Phase 4: User Story 2 - Kelola Gaji & Tunjangan Karyawan PT (Priority: P2)

**Goal**: Admin PT dan Keuangan PT dapat mencatat gaji_pokok dan tunjangan. Kolom gaji tersembunyi default (toggleable). Filter "Ada Gaji" tersedia. Validasi input numerik.

**Independent Test**:
1. Admin PT edit karyawan → lihat section "Kompensasi" → isi gaji_pokok=8000000 → submit → verify tersimpan di DB
2. Isi tunjangan (opsional) → submit → verify tersimpan
3. Filter "Ada Gaji" → tampil hanya karyawan dengan gaji_pokok NOT NULL
4. Aktifkan kolom Gaji Pokok di tabel → tampil nilai formatted sebagai Rupiah (IDR)
5. Input gaji negatif → validasi error "Gaji harus berupa angka positif"

### Implementation for User Story 2

- [X] T052 [US2] Add Section "Kompensasi" to EmployeePTResource::form() after Kepegawaian section: gaji_pokok TextInput (numeric, min:0, prefix='Rp ', suffix='/ bulan'), tunjangan TextInput (numeric, min:0, prefix='Rp ', nullable)
- [X] T053 [US2] Add gaji_pokok column to table (TextColumn, money('IDR', locale:'id'), sortable, toggleable isToggledHiddenByDefault=true)
- [X] T054 [US2] Add tunjangan column to table (TextColumn, money('IDR'), toggleable hidden by default)
- [X] T055 [US2] Add Filter::make('has_gaji') to table filters — toggle query whereNotNull('gaji_pokok')
- [X] T056 [US2] Add Kompensasi section to infolist() in EmployeePTResource: TextEntry gaji_pokok (money IDR), TextEntry tunjangan (money IDR, hidden if null)

### Testing for User Story 2

- [X] T057 [P] [US2] Create EmployeePTKompensasiTest in tests/Feature/EmployeePTKompensasiTest.php — Admin PT can set gaji_pokok
- [X] T058 [P] [US2] Test: Admin PT can set tunjangan (nullable, no error if empty)
- [X] T059 [P] [US2] Test: Gaji_pokok toggleable column is hidden by default in table
- [X] T060 [P] [US2] Test: Filter 'Ada Gaji' shows only employees with gaji_pokok NOT NULL
- [X] T061 [P] [US2] Test: Input negative gaji_pokok fails validation
- [X] T062 [P] [US2] Test: Input non-numeric gaji_pokok fails validation
- [X] T063 [P] [US2] Test: Keuangan PT can update gaji_pokok and tunjangan on an EmployeePT record (authorized via updateKompensasi policy)
- [X] T064 [US2] Run US2 tests: php artisan test --compact --filter=Kompensasi

**Checkpoint**: User Stories 1 AND 2 independently functional. Gaji management complete.

---

## Phase 5: User Story 3 - Upload Dokumen Kepegawaian (Priority: P3)

**Goal**: Admin PT dapat upload dokumen kepegawaian (PDF/JPG/PNG max 5MB) ke profil karyawan. File disimpan private. Indikator "Tersedia" tampil di tabel. File tidak dapat diakses publik.

**Independent Test**:
1. Admin PT edit karyawan → lihat section "Dokumen Kepegawaian" → upload PDF 3MB → submit → verify file di storage/app/private/documents/
2. Upload file 6MB → validation error "File maksimal 5MB"
3. Upload file .docx → validation error "Format harus PDF/JPG/PNG"
4. Kolom Dokumen di tabel → ikon ✓ jika ada file, ikon ✗ jika tidak ada
5. Unauthenticated user coba akses URL file langsung → 403 Forbidden

### Implementation for User Story 3

- [X] T065 [US3] Add Section "Dokumen Kepegawaian" to EmployeePTResource::form(): dokumen_path FileUpload (disk='private', directory='documents', visibility='private', acceptedFileTypes=['application/pdf','image/jpeg','image/png'], maxSize=5120, preserveFilenames)
- [X] T066 [US3] Add dokumen_path attribute accessor getDokumenDownloadUrlAttribute() to EmployeePT model returning route URL if file exists
- [X] T067 [US3] Create download route in routes/web.php: GET /karyawan-pt/{employee}/dokumen/download (name: 'karyawan-pt.dokumen.download', middleware: auth)
- [X] T068 [US3] Create download controller action (inline or dedicated) that checks EmployeePTPolicy before streaming private file via Storage::download()
- [X] T069 [US3] Implement EmployeePTPolicy::downloadDokumen() allowing Admin PT, Keuangan PT, Pimpinan, and super_admin
- [X] T070 [US3] Add dokumen download link to ViewEmployeePT infolist Section "Dokumen Kepegawaian" (only if dokumen_path exists and user is authorized)

### Testing for User Story 3

- [X] T071 [P] [US3] Create EmployeePTDocumentTest in tests/Feature/EmployeePTDocumentTest.php — Admin PT can upload PDF dokumen
- [X] T072 [P] [US3] Test: Admin PT can upload JPG dokumen
- [X] T073 [P] [US3] Test: Admin PT can upload PNG dokumen
- [X] T074 [P] [US3] Test: Upload file exceeding 5MB fails validation
- [X] T075 [P] [US3] Test: Upload file with unsupported extension (.docx) fails validation
- [X] T076 [P] [US3] Test: dokumen_path stored in private disk (not publicly accessible)
- [X] T077 [P] [US3] Test: Admin PT can download dokumen via authorized route
- [X] T078 [P] [US3] Test: Unauthenticated request to download route returns 403/redirect
- [X] T079 [P] [US3] Test: Table dokumen column shows truthy icon when dokumen_path exists
- [X] T080 [US3] Run US3 tests: php artisan test --compact --filter=Document

**Checkpoint**: User Stories 1, 2, AND 3 independently functional. Document upload complete.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Goal**: Export CSV, Pint formatting, seeder, final test run.

- [X] T081 Create EmployeePTExport in app/Filament/Exports/EmployeePTExport.php implementing Maatwebsite export: columns (id, nik, nama_lengkap, email, jabatan, divisi, status, jenis_kontrak, tanggal_bergabung, gaji_pokok, tunjangan, created_at)
- [X] T082 Add export headerAction to EmployeePTResource table — Action::make('export') label='Export CSV', icon='heroicon-o-arrow-down-tray', color='success', calls EmployeePTExport with filtered query, logs activity
- [X] T083 [P] Create EmployeePTSeeder in database/seeders/EmployeePTSeeder.php seeding 8 sample karyawan PT (at least one per jabatan)
- [X] T084 [P] Create tests/Feature/Exports/EmployeePTExportTest.php — export returns CSV file with correct headers and data rows
- [X] T085 Run vendor/bin/pint --dirty to fix code style across all new/modified files
- [X] T086 Run full feature test suite for this feature: php artisan test --compact --filter=EmployeePT
- [ ] T087 Verify admin panel: login as Admin PT, confirm "Karyawan PT" appears in sidebar under "Data Master"
- [ ] T088 Verify entity isolation: confirm EmployeeLPKResource still works correctly (no regressions)

---

## Dependencies (User Story Completion Order)

```
Phase 1 (Setup)
    ↓
Phase 2 (Foundation: Enums → Model → Migration → Factory → Policy → Requests → Seeder)
    ↓
Phase 3 (US1: CRUD core) ← MVP — can stop here
    ↓
Phase 4 (US2: Kompensasi) ← non-breaking addition to existing resource
    ↓
Phase 5 (US3: Dokumen) ← non-breaking addition to existing resource
    ↓
Phase 6 (Polish: Export + Pint + Seeder + Regression check)
```

**Parallel opportunities within US1**: T015–T019 (page files) can be created simultaneously. T030–T050 (tests) can be written simultaneously once implementation tasks T020–T029 are done.

**Parallel opportunities within Foundation**: T004/T005/T006 (Enums) can be created simultaneously. T010/T011/T012 (Policy + Requests) can be created simultaneously after T007 (model) exists.

---

## Implementation Strategy

**MVP** (minimum shippable feature): Complete Phases 1–3 only (Tasks T001–T051).

- This delivers a fully functional Karyawan PT menu that mirrors Karyawan LPK with CRUD, entity isolation, RBAC, audit logging, and soft delete.
- P2 (Kompensasi) and P3 (Dokumen) are independent additions that can be delivered in subsequent commits without breaking P1.

**Suggested commit sequence**:
1. `feat: add JabatanPT, DivisiPT, JenisKontrak enums` (T004–T006)
2. `feat: add EmployeePT model and migration` (T007–T008, T013)
3. `feat: add EmployeePTResource with CRUD — US1 MVP` (T009–T029)
4. `test: add EmployeePT feature tests — US1` (T030–T051)
5. `feat: add kompensasi fields to EmployeePTResource — US2` (T052–T056)
6. `test: add kompensasi tests — US2` (T057–T064)
7. `feat: add dokumen kepegawaian upload — US3` (T065–T070)
8. `test: add document upload tests — US3` (T071–T080)
9. `feat: add export CSV + seeder + polish` (T081–T088)
