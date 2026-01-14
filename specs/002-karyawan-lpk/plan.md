# Implementation Plan: Karyawan LPK Management

**Branch**: `002-karyawan-lpk` | **Date**: 2026-01-13 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/002-karyawan-lpk/spec.md`

## Summary

Implementasi sistem manajemen karyawan LPK dengan CRUD lengkap untuk Instruktur dan Staff administratif. Fitur ini menyediakan:
- Data master karyawan LPK (personal info, jabatan, status kepegawaian)
- Manajemen honor/kompensasi (honor pokok + honor per jam untuk Instruktur)
- Upload dan penyimpanan sertifikat kompetensi instruktur (PDF/JPG/PNG)
- Role-based access untuk Admin LPK, Keuangan LPK, Instruktur, dan Pimpinan
- Soft delete untuk retention + audit logging
- Entity isolation otomatis (entity='LPK')

Technical approach: Menggunakan Filament EmployeeLPKResource dengan custom form sections (conditional sertifikat field untuk Instruktur), validasi comprehensive via Form Request, Filament's FileUpload component untuk sertifikat dengan private disk storage. Enum untuk Jabatan dan Status. Model events untuk audit trail.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Framework**: Laravel 11.x  
**Primary Dependencies**: 
- filament/filament v4.x (admin panel UI, forms, tables, file upload)
- spatie/laravel-permission v6.x (RBAC - already installed via spec 001)
- spatie/laravel-activitylog v4.x (✅ RESOLVED: Use for audit logging per research.md)
- livewire/livewire v3.x (reactive components untuk conditional fields)

**Storage**: MySQL/MariaDB (karyawan_lpk table, relationship to future pelatihan table)  
**File Storage**: Private disk (storage/app/private/certificates/)  
**Testing**: PHPUnit v10 (Feature tests untuk CRUD + authorization, validation tests)  
**Target Platform**: Web application (server-side rendering via Livewire/Filament)  
**Project Type**: Web (Laravel monolith with Filament admin panel)  

**Performance Goals**: 
- Table rendering <2 seconds untuk 500 records (dengan pagination 25 per page)
- Form submission <3 seconds (including file upload 5MB)
- File upload validation immediate (client-side + server-side)

**Constraints**: 
- MUST use Laravel 10 structure (models in app/Models/, no bootstrap/app.php config)
- MUST use Filament v4 conventions (Schemas\Components for layouts, not Form\Components\Grid)
- Entity field MUST be immutable and default to 'LPK'
- NIK field MUST be read-only after creation (unique identifier)
- File uploads MUST be private (not publicly accessible)
- Soft deletes REQUIRED (align with constitution + spec FR-005)

**Scale/Scope**: 
- Initial: ~30-50 karyawan LPK (20 instruktur + 10-15 staff)
- Growth target: up to 500 karyawan LPK
- 3 jabatan types (Instruktur, Admin LPK, Staff)
- 3 status types (Aktif, Cuti, Resign)
- Estimated 20-30 instruktur akan upload sertifikat (each ~2-5MB)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### ✅ Principle I: Data Integrity & Single Source of Truth
- **Status**: PASS
- **Validation**: NIK dan email sebagai unique identifiers dengan database constraints. Tidak ada duplikasi record (FR-002). EmployeeLPK model menjadi canonical source untuk data karyawan LPK.
- **Implementation**: 
  - Migration: unique index pada email (case-insensitive via LOWER())
  - Migration: unique index pada nik
  - Form Request validation: `unique:karyawan_lpk,email` + `unique:karyawan_lpk,nik`
  - Database constraint enforcement prevents duplicates at DB level

### ✅ Principle II: Multi-Entity Isolation
- **Status**: PASS
- **Validation**: Setiap karyawan LPK MUST memiliki entity='LPK' (FR-006). Field entity immutable dan auto-assigned. Permission checks akan scope data ke LPK-only.
- **Implementation**: 
  - Migration: entity column ENUM('PT','LPK') DEFAULT 'LPK' NOT NULL
  - Model: boot() method auto-sets entity='LPK' on creating event
  - Resource: entity field hidden/disabled di form (tidak editable)
  - Policy: EmployeeLPKPolicy viewAny() scopes query ke entity='LPK' only
  - Future: Global scope untuk entity filtering (Phase 1 via policy checks)

### ✅ Principle III: Role-Based Access Control & Least Privilege
- **Status**: PASS
- **Validation**: 4 roles dengan explicit permissions dari spec (FR-011 to FR-014): Admin LPK (full CRUD), Keuangan LPK (view + edit honor), Instruktur (view own + edit contact), Pimpinan (read-only all).
- **Implementation**: 
  - Shield policies auto-generated untuk EmployeeLPKResource
  - Custom policy methods: viewOwn(), updateOwn() for Instruktur role
  - Filament authorization via `->authorizeUsing()` untuk field-level permissions (honor fields for Keuangan)
  - Resource visibility via `canViewAny()` checks in panel navigation

### ✅ Principle IV: Auditability & Compliance
- **Status**: PASS
- **Validation**: Audit log untuk semua CRUD operations (FR-019). Soft delete untuk data retention (FR-005). File sertifikat private storage (FR-009).
- **Implementation**: 
  - Model uses SoftDeletes trait
  - Model events (created, updated, deleted) trigger audit log via Observer or spatie/activitylog
  - Audit log includes: user_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent
  - File storage: disk='private', path=certificates/{nik}_{timestamp}.{ext}
  - Download authorization via Policy check before serving file

### ✅ Principle V: Incremental Delivery & Simplicity
- **Status**: PASS
- **Validation**: 4 user stories (P1-P3) dapat di-deliver secara incremental. P1 (basic CRUD) adalah MVP. P2 (honor) dan P3 (sertifikat, self-service) optional additions.
- **Implementation**: 
  - P1: EmployeeLPKResource dengan basic CRUD (nama, NIK, jabatan, status, dates) - standalone deliverable
  - P2: Add honor fields to existing resource form - non-breaking addition
  - P3: Add sertifikat FileUpload field (conditional on jabatan) - non-breaking addition
  - P3: Add EmployeeLPK ProfileResource untuk self-service - separate resource, no impact on main resource
  - Tests written per user story for independent validation

### Summary: All Constitution Checks PASS ✅
No violations detected. No complexity justification required. Feature aligns with all 5 core principles. Proceed to Phase 0 research.

**Post-Design Re-check** (after Phase 1 data-model.md completion):
- [✅] **VERIFIED**: Entity column implementation → DB default='LPK' + Model boot() + Form default (defense-in-depth per research.md)
- [✅] **VERIFIED**: Audit logging approach → spatie/laravel-activitylog v4.x (LogsActivity trait on model)
- [✅] **VERIFIED**: File upload authorization → Policy-based gate check before serving private file (custom download route)
- [✅] **VERIFIED**: Soft delete behavior → TrashedFilter::make() in resource, status='Resign' on delete, restores to 'Aktif'

**All Post-Design Checks PASS** ✅ | **Date**: 2026-01-13

## Project Structure

### Documentation (this feature)

```text
specs/002-karyawan-lpk/
├── plan.md              # This file
├── research.md          # Phase 0: Filament v4 file upload, audit logging decision, enum patterns
├── data-model.md        # Phase 1: EmployeeLPK schema, relationships, validations
├── quickstart.md        # Phase 1: How to create karyawan, assign jabatan, upload sertifikat
├── contracts/           # Phase 1: N/A (no API endpoints - Filament only)
│   └── .gitkeep
├── checklists/
│   └── requirements.md  # Validation checklist (already created)
└── spec.md              # Feature specification (already created)
```

### Source Code (repository root)

Laravel monolith following Laravel 10 structure (middleware in app/Http/Middleware/):

```text
app/
├── Models/
│   └── EmployeeLPK.php                 # [CREATE] Karyawan LPK model dengan SoftDeletes, HasFactory
├── Filament/
│   └── Resources/
│       └── EmployeeLPK/
│           ├── EmployeeLPKResource.php     # [CREATE] Main resource dengan forms, table, filters
│           └── Pages/
│               ├── ListEmployeesLPK.php    # [CREATE] Table view dengan filters
│               ├── CreateEmployeeLPK.php   # [CREATE] Create form
│               ├── EditEmployeeLPK.php     # [CREATE] Edit form (conditional sertifikat)
│               └── ViewEmployeeLPK.php     # [CREATE] Detail view
│       └── Profile/
│           └── EmployeeLPKProfileResource.php  # [CREATE] P3: Self-service untuk Instruktur
├── Enums/
│   ├── JabatanLPK.php                  # [CREATE] Enum: Instruktur, AdminLPK, Staff
│   └── StatusKepegawaian.php           # [CREATE] Enum: Aktif, Cuti, Resign
├── Http/
│   └── Requests/
│       ├── StoreEmployeeLPKRequest.php     # [CREATE] Validation rules untuk create
│       └── UpdateEmployeeLPKRequest.php    # [CREATE] Validation rules untuk update
├── Policies/
│   └── EmployeeLPKPolicy.php           # [CREATE] Authorization: viewAny, create, update, delete, viewOwn, updateOwn
├── Observers/
│   └── EmployeeLPKObserver.php         # [CREATE] Audit logging via model events (if custom audit)
└── Providers/
    └── AppServiceProvider.php          # [MODIFY] Register EmployeeLPKObserver if needed

config/
└── filesystems.php                     # [VERIFY] Private disk configured (already exists in Laravel)

database/
├── factories/
│   └── EmployeeLPKFactory.php          # [CREATE] Factory untuk testing + seeding
├── migrations/
│   └── 2026_01_13_000001_create_karyawan_lpk_table.php   # [CREATE] Schema dengan semua fields
└── seeders/
    └── EmployeeLPKSeeder.php           # [CREATE] Seed 5-10 sample karyawan untuk testing

storage/
└── app/
    └── private/
        └── certificates/               # [AUTO-CREATED] Storage untuk sertifikat instruktur

tests/
└── Feature/
    ├── Filament/
    │   ├── EmployeeLPKResourceTest.php         # [CREATE] CRUD operations tests
    │   ├── EmployeeLPKAuthorizationTest.php    # [CREATE] Role-based access tests
    │   └── EmployeeLPKValidationTest.php       # [CREATE] Validation rules tests (NIK, email unique)
    └── Models/
        └── EmployeeLPKTest.php                 # [CREATE] Model behavior tests (soft delete, entity auto-assign)
```

**Note**: Filament v4 uses `Filament\Schemas\Components` for layout components (Grid, Section, Fieldset), not `Filament\Forms\Components\Grid`. Check research.md after Phase 0 for exact component namespaces.

## Complexity Tracking

> No complexity justification needed. Constitution Check passed all gates.

## Phases

### Phase 0: Outline & Research

**Status**: ✅ **COMPLETE** | **Date**: 2026-01-13  
**Output**: [research.md](research.md) - All NEEDS CLARIFICATION resolved

**Objective**: Resolve all NEEDS CLARIFICATION items dari Technical Context dan research best practices untuk dependencies.

**Research Tasks** (All Resolved ✅):

1. **Audit Logging Approach** (from spec 001) - ✅ RESOLVED
   - **Decision**: spatie/laravel-activitylog v4.x
   - **Rationale**: Mature package, matches Laravel ecosystem standards
   - **Implementation**: LogsActivity trait on EmployeeLPK model, activity_log table already exists (spec 001)
   - **Documentation**: See research.md Section 1

2. **Filament v4 File Upload Best Practices** - ✅ RESOLVED
   - **Decision**: FileUpload with disk('private') + visibility('private')
   - **Authorization**: Custom download route with Policy gate check
   - **Validation**: acceptedFileTypes(['application/pdf', ...]) + maxSize(5120)
   - **Documentation**: See research.md Section 2

3. **Enum Patterns in Laravel 11** - ✅ RESOLVED
   - **Decision**: PHP 8.1 backed string enums implementing HasLabel
   - **Implementation**: 
     ```php
     enum JabatanLPK: string implements HasLabel {
         case Instruktur = 'Instruktur';
         case AdminLPK = 'Admin LPK';
         case Staff = 'Staff';
         public function getLabel(): string { return $this->value; }
     }
     ```
   - **Filament Integration**: Select::make('jabatan')->options(JabatanLPK::class)
   - **Documentation**: See research.md Section 3

4. **Soft Delete Filter in Filament Tables** - ✅ RESOLVED
   - **Decision**: Use built-in TrashedFilter::make()
   - **Implementation**: Import Filament\Tables\Filters\TrashedFilter
   - **Behavior**: Default query withoutTrashed(), admin can toggle "With/Only Trashed"
   - **Documentation**: See research.md Section 4

5. **Entity Field Auto-Assignment Pattern** - ✅ RESOLVED
   - **Decision**: Three-layer defense (DB default + Model boot + Form default)
   - **Implementation**:
     ```php
     // Migration
     $table->enum('entity', ['PT', 'LPK'])->default('LPK')->nullable(false);
     
     // Model boot()
     static::creating(fn($employee) => $employee->entity = EntityType::LPK);
     
     // Form
     Forms\Components\Hidden::make('entity')->default('LPK');
     ```
   - **Documentation**: See research.md Section 5

**Output**: research.md dengan decisions + code examples untuk setiap topic above.

---

### Phase 1: Design & Contracts

**Status**: ✅ **COMPLETE** | **Date**: 2026-01-13  
**Outputs**: 
- [data-model.md](data-model.md) - Complete database schema, validations, relationships
- [quickstart.md](quickstart.md) - User workflows for all roles
- [contracts/](contracts/) - .gitkeep only (no API endpoints)
- Updated `.github/agents/copilot-instructions.md` with EmployeeLPK context

**Prerequisites**: ✅ research.md complete with no NEEDS CLARIFICATION remaining

**Tasks** (All Complete ✅):

1. **Generate data-model.md** - ✅ COMPLETE
   - ✅ Extracted EmployeeLPK entity with 20 fields
   - ✅ Documented schema:
     - Fields: id, nik (CHAR 16 UNIQUE), nama_lengkap, email (UNIQUE), tanggal_lahir, jenis_kelamin, alamat, telepon, jabatan (enum), status (enum), tanggal_bergabung, honor_pokok (nullable decimal), honor_per_jam (nullable decimal), sertifikat_path (nullable string), entity (enum default 'LPK'), created_by, updated_by, created_at, updated_at, deleted_at
     - Indexes: unique(nik), unique(email), index(jabatan), index(status), index(entity), foreign keys (created_by, updated_by)
     - Constraints: entity NOT NULL DEFAULT 'LPK', nik CHAR(16), email format validation
   - ✅ Documented 3 enums: JabatanLPK, StatusKepegawaian, EntityType (with HasLabel interface)
   - ✅ Validation rules in StoreEmployeeLPKRequest / UpdateEmployeeLPKRequest:
     - 18 field-level rules (email unique, nik digits:16, dates, file upload)
     - 2 cross-field rules (tanggal_bergabung >= tanggal_lahir, honor_per_jam requires jabatan=Instruktur)
   - ✅ Relationships:
     - belongsTo User (created_by, updated_by) - for audit
     - hasMany TrainingSession (future - Pelatihan module, commented)
   - ✅ Model features: SoftDeletes, LogsActivity, boot() for entity auto-assign
   - ✅ Factory with instruktur() and resign() states
   - ✅ Migration with indexes, foreign keys, constraints
   - ✅ Business rules: State transitions (Aktif ↔ Cuti, Aktif → Resign, Resign → Aktif restore)

2. **Generate contracts/** (N/A for this feature) - ✅ COMPLETE
   - ✅ Created `contracts/.gitkeep`
   - ✅ Note: No API endpoints required - All access via Filament admin panel

3. **Generate quickstart.md** - ✅ COMPLETE
   - ✅ Section 1: Creating Karyawan LPK (Admin LPK role)
     - Navigation, form fields, conditional sertifikat field
   - ✅ Section 2: Viewing & Editing Karyawan (filters, search, edit, delete, restore)
   - ✅ Section 3: Managing Honor (Keuangan LPK role)
   - ✅ Section 4: Self-Service Profile (Instruktur role)
   - ✅ Section 5: Viewing Karyawan (Pimpinan role - read-only)
   - ✅ Section 6: Certificate Download Workflow (authorization patterns)
   - ✅ Section 7: Troubleshooting common issues
   - ✅ Section 8: Common Workflows Summary table

4. **Update agent context** - ✅ COMPLETE
   - ✅ Ran: `bash .specify/scripts/bash/update-agent-context.sh copilot`
   - ✅ Added new technology to `.github/agents/copilot-instructions.md`:
     - Language: PHP 8.4.5
     - Database: MySQL/MariaDB (karyawan_lpk table, relationship to future pelatihan table)
   - ✅ Manual additions preserved between AGENT_CONTEXT markers


     - Submit → verify file download link appears
   - Section 3: How Instruktur accesses own profile (P3)
     - Login as Instruktur
     - Navigate to "Profil Saya"
     - View read-only fields (nama, NIK, jabatan, honor)
     - Edit contact info (alamat, telepon)
     - Submit → verify update success

4. **Update agent context** (per constitution workflow)
   - Run: `bash .specify/scripts/bash/update-agent-context.sh copilot`
   - Adds to `.github/copilot-instructions.md`:
     - New model: EmployeeLPK
     - New enums: JabatanLPK, StatusKepegawaian
     - New resource: EmployeeLPKResource
     - Technology note: Filament v4 FileUpload for private file storage
   - Preserves manual additions between markers

**Output**: 
- data-model.md (EmployeeLPK schema + validations)
- contracts/.gitkeep
- quickstart.md (3 sections above)
- Updated .github/copilot-instructions.md

---

### Phase 2: Implementation Planning

**Status**: ✅ **COMPLETE** | **Date**: 2026-01-13  
**Output**: [tasks.md](tasks.md) - Complete task breakdown with 154 tasks organized by user story

**Summary**:
- **Total Tasks**: 154 across 7 phases
- **Phase 1 (Setup)**: 3 tasks - Configuration verification
- **Phase 2 (Foundational)**: 11 tasks - Enums, model, migration, policy, requests (BLOCKS all user stories)
- **Phase 3 (US1 - P1 MVP)**: 39 tasks - Basic CRUD with entity isolation, soft delete, audit logging
- **Phase 4 (US2 - P2)**: 20 tasks - Honor management for payroll
- **Phase 5 (US3 - P3)**: 28 tasks - Sertifikat upload/download with private storage
- **Phase 6 (US4 - P3)**: 25 tasks - Self-service profile for Instruktur
- **Phase 7 (Polish)**: 28 tasks - Code quality, performance, security, documentation

**Parallelization**: 87 tasks marked [P] (56% can run in parallel)

**Execution Estimates**:
- Solo developer (sequential): 6-7 days
- Team of 2 (parallel user stories): 4-5 days  
- Team of 4 (all stories parallel): 3-4 days
- **MVP only (US1)**: 2-3 days solo

**Key Organization**:
- Tasks grouped by user story for independent implementation/testing
- Each user story is a complete, deliverable increment
- US1 can be deployed as MVP immediately after Phase 3
- US2, US3, US4 are non-breaking additions

**Dependencies**:
- Foundational phase (Phase 2) MUST complete before any user story
- User stories 1-4 can proceed in parallel after foundation (if team capacity)
- Each story independently testable per spec acceptance scenarios

**Next Action**: Review tasks.md, assign developers to user stories, begin implementation starting with Setup + Foundational phases.

---

## Key Decisions & Rationale

**1. Why EmployeeLPK separate from User model?**
- User model is for authentication/authorization (login credentials)
- EmployeeLPK is business entity (employee master data)
- Some Instruktur may not have system access (future scenario)
- Separation of concerns: auth vs business logic

**2. Why entity='LPK' auto-assignment vs manual selection?**
- Constitution Principle II requires strict entity isolation
- Manual selection introduces risk of human error (PT karyawan created under LPK)
- Hard-coded default + immutable field enforces isolation
- Future: If PT needs similar, create EmployeePT model (separate entity, no shared table)

**3. Why soft delete instead of status=Resign?**
- Soft delete (deleted_at) is Laravel/Filament convention
- Enables "Trashed" filter in table UI
- Status=Resign is business status (employee resigned but record retained)
- Soft delete is technical mechanism for retention (align with constitution Principle IV)
- Behavior: Status Resign triggers soft delete, restore clears deleted_at + sets Status=Aktif

**4. Why conditional sertifikat field vs separate table?**
- Spec assumption: 1 sertifikat per instruktur (fase 1)
- Single file path column simpler than 1:1 relationship table
- Filament FileUpload component handles single file elegantly
- Future: Migrate to SertifikatInstruktur table if multiple files needed (out of scope per spec)

**5. Why honor_pokok and honor_per_jam in same table?**
- Both are compensation attributes of EmployeeLPK entity
- No complex calculation required (payroll out of scope)
- Nullable columns allow flexibility (some karyawan may not have honor set)
- Keuangan LPK role needs simple view/edit access (no separate table needed)

**6. Why Form Request classes vs inline validation?**
- Constitution: "Always create Form Request classes for validation"
- Reusability: Same rules for create/update with minor differences
- Testability: Request validation can be unit tested separately
- Separation of concerns: Controller/Resource stays clean

---

## Next Steps (After Plan Approval)

1. Review this plan for completeness
2. Execute Phase 0: Run research tasks, generate research.md
3. Execute Phase 1: Generate data-model.md, quickstart.md, update agent context
4. Re-run Constitution Check against Phase 1 design
5. If all gates pass: Run `/speckit.tasks` to generate implementation tasks
6. Review tasks.md and begin implementation (spec 002 iteration 1)

---

**Plan Status**: COMPLETE ✅  
**Ready for Phase 0**: YES  
**Constitution Compliance**: ALL GATES PASS  
**Awaiting**: Review + approval to proceed with research phase
