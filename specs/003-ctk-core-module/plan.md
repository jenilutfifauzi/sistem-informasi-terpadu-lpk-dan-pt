# Implementation Plan: CTK Core Module

**Branch**: `003-ctk-core-module` | **Date**: January 22, 2026 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/003-ctk-core-module/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

The CTK (Calon Tenaga Kerja) Core Module implements a comprehensive lifecycle management system for prospective workers, tracking them through 15 sequential stages from registration to final departure. This module serves as the **single source of truth** for all CTK data, enforcing strict entity isolation between LPK and PT, maintaining complete audit trails, and implementing data immutability for final stages. The system manages personal information, medical checkup results, multi-stage payments, document uploads (10+ document types), training completion, screening results, visa processing, and final departure tracking. All operations are logged for compliance, with role-based access control determining visibility of CTK records based on current stage and user entity.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Framework**: Laravel 11 (with Laravel 10 structure), Filament v4, Livewire v3  
**Primary Dependencies**: 
- filament/filament v4 (admin panel UI)
- spatie/laravel-permission v6 (RBAC)
- spatie/laravel-activitylog v4 (audit logging)
- intervention/image v3 (image processing for uploads)
- maatwebsite/excel v3 (Excel export)
- barryvdh/laravel-dompdf v2 (PDF export)

**Storage**: MySQL/MariaDB with soft deletes, file storage on local disk (private visibility) for document uploads  
**Testing**: PHPUnit v10 for unit and feature tests, Filament/Livewire testing helpers  
**Target Platform**: Web application (desktop browsers - Chrome, Firefox, Safari)  
**Project Type**: Laravel web application with Filament admin panel  
**Performance Goals**: 
- CTK list page load < 2 seconds for 10,000 records
- Document upload and validation < 3 seconds for 10MB files
- Search/filter results < 2 seconds
- Audit trail query < 1 second for single CTK record

**Constraints**: 
- NIK must be unique across all CTK records
- CTK records in "Terbang" or "OPP" stages are immutable (except admin override)
- Document files limited to PDF, JPG, JPEG, PNG, max 10MB per file
- Entity isolation enforced at query scope level (LPK sees stages 1-5, PT sees stages 6-15)
- All stage transitions must be auditable with user, timestamp, and optional notes
- Soft deletes for data retention (7-year policy)
- File uploads stored with private visibility, accessible only through authenticated routes

**Scale/Scope**: 
- Expected: 500-1000 active CTK records at any time
- Historical: 10,000+ CTK records over 2 years
- Concurrent users: Up to 50 users (mixed LPK and PT entities)
- Document storage: ~5GB per year (average 10 documents per CTK @ 1MB each)
- Audit log entries: ~100 entries per CTK over full lifecycle

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### I. Data Integrity & Single Source of Truth ✅

**Requirement**: CTK records must be canonical single-source entities with auditable state transitions and duplicate prevention.

**Compliance**:
- ✅ CTK model will have unique constraint on `nik` column preventing duplicates
- ✅ All status changes will be recorded in `stage_transitions` table with from/to state
- ✅ Soft deletes enabled for data retention and recovery
- ✅ Single `ctk` table serves as canonical source; related tables (payments, documents, etc.) reference ctk_id

**Validation**: Database migration includes UNIQUE index on `nik`. Factory will prevent duplicate NIK generation. Feature tests will verify duplicate prevention.

---

### II. Multi-Entity Isolation ✅

**Requirement**: Data must be isolated between PT and LPK with explicit, auditable transfer workflow.

**Compliance**:
- ✅ CTK records include `current_entity` field (LPK/PT) indicating data ownership
- ✅ Query scopes will enforce entity filtering based on user's entity and current CTK stage
- ✅ Stage transitions between LPK→PT boundaries (stage 5→6) will log entity handoff in audit trail
- ✅ Policies will prevent cross-entity access: LPK users cannot access CTK in PT stages (6-15), PT users cannot access CTK in LPK stages (1-5)

**Validation**: Policy tests will verify entity isolation. Feature tests will verify LPK user cannot view CTK in PT stages and vice versa.

---

### III. Role-Based Access Control & Least Privilege ✅

**Requirement**: RBAC must govern all access with explicit permissions mapped to PRD roles.

**Compliance**:
- ✅ Leverages existing Spatie Permission from Spec 001
- ✅ Permissions: `view_ctk`, `view_any_ctk`, `create_ctk`, `update_ctk`, `delete_ctk`, `restore_ctk`, `force_delete_ctk`
- ✅ Additional permissions: `override_ctk_immutability` (for admin corrections to final-stage CTKs), `view_ctk_audit` (for audit trail access)
- ✅ Filament policies will check permissions AND entity scope before granting access
- ✅ Roles mapping:
  - Admin LPK: create, update, view (LPK stages only)
  - Admin PT: update, view (PT stages only)
  - Legal PT: view, update (documents only, PT stages)
  - Keuangan PT/LPK: view (payment info), update (payment status)
  - Pimpinan: view_any (read-only across all entities)
  - Super Admin: all permissions

**Validation**: Permission seeder will create CTK permissions. Policy tests will verify role-based restrictions.

---

### IV. Auditability & Compliance ✅

**Requirement**: All CTK lifecycle transitions must be logged. Final-stage CTKs must be immutable with documented correction process.

**Compliance**:
- ✅ Spatie ActivityLog will track all CTK model changes (create, update, delete)
- ✅ Separate `stage_transitions` table will log every status change with from_stage, to_stage, user_id, timestamp, notes
- ✅ `ctk_documents` table will log every upload with uploader_id and timestamp
- ✅ Model observer will enforce immutability: CTKs with status 'terbang' or stage >= 14 (OPP) will throw exception on update unless user has `override_ctk_immutability` permission
- ✅ Override actions will be logged with justification in audit trail
- ✅ Export functionality will include complete audit trail for compliance reports

**Validation**: Feature tests will verify immutability enforcement. Observer tests will verify exceptions are thrown. Audit log tests will verify all transitions are recorded.

---

### V. Incremental Delivery & Simplicity ✅

**Requirement**: Favor iterative increments with simplest effective design and test coverage.

**Compliance**:
- ✅ Phase 2 will implement MVP: core CTK CRUD + stage progression (stages 1-5 MCU→Training)
- ✅ Phase 3 will add document management for basic documents (Soal/Berkas, Paspor)
- ✅ Phase 4 will complete remaining stages (6-15 Screening→Terbang) + full document suite
- ✅ Phase 5 will add advanced features (bulk actions, exports, dashboard widgets)
- ✅ Each phase deliverable is independently testable and provides incremental value
- ✅ Simple Filament Resource structure following existing EmployeeLPKResource patterns
- ✅ Factory and tests will be created alongside each model

**Validation**: Each phase will have passing feature tests before proceeding. PR reviews will verify simplicity and test coverage.

---

### Constitution Check Summary

**Status**: ✅ **ALL GATES PASSED**

All five core principles are satisfied by the planned design. No violations requiring justification. The CTK module follows established patterns from Spec 001 (RBAC) and Spec 002 (EmployeeLPK), ensuring consistency and simplicity.

## Project Structure

### Documentation (this feature)

```text
specs/003-ctk-core-module/
├── plan.md              # This file (/speckit.plan command output)
├── spec.md              # Feature specification (already created)
├── checklists/
│   └── requirements.md  # Quality checklist (already created)
├── research.md          # Phase 0 output (to be created by /speckit.plan)
├── data-model.md        # Phase 1 output (to be created by /speckit.plan)
├── quickstart.md        # Phase 1 output (to be created by /speckit.plan)
├── contracts/           # Phase 1 output (API contracts if needed)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (Laravel application structure)

```text
app/
├── Models/
│   ├── CTK.php                      # Main CTK model
│   ├── MCURecord.php                # Medical checkup results
│   ├── CTKPayment.php               # Payment tracking
│   ├── CTKDocument.php              # Document uploads
│   ├── TrainingRecord.php           # LPK training completion
│   ├── ScreeningResult.php          # Screening & interview results
│   ├── VisaRecord.php               # Visa application & issuance
│   ├── StageTransition.php          # Audit log for stage changes
│   └── CTKNote.php                  # Notes/comments on CTK
│
├── Enums/
│   ├── CTKStatus.php                # 15 stage statuses
│   ├── MCUStatus.php                # FIT/UNFIT/PENDING
│   ├── PaymentStatus.php            # Pending/Lunas
│   ├── DocumentType.php             # Document categories
│   ├── ScreeningStage.php           # Screening1/InterviewUser
│   └── ScreeningResult.php          # Lolos/TidakLolos
│
├── Filament/
│   └── Resources/
│       ├── CTKResource.php          # Main Filament resource
│       └── CTKResource/
│           ├── Pages/
│           │   ├── ListCTKs.php
│           │   ├── CreateCTK.php
│           │   ├── EditCTK.php
│           │   └── ViewCTK.php     # Detailed view with tabs
│           ├── Schemas/
│           │   ├── CTKForm.php      # Form schema
│           │   ├── MCUSection.php   # MCU form section
│           │   ├── PaymentSection.php
│           │   ├── DocumentSection.php
│           │   └── StageProgressSection.php
│           ├── Tables/
│           │   └── CTKTable.php     # Table configuration
│           └── Actions/
│               ├── AdvanceStageAction.php    # Custom action for stage progression
│               ├── UploadDocumentAction.php
│               └── ViewAuditTrailAction.php
│
├── Policies/
│   └── CTKPolicy.php                # Authorization policies
│
├── Observers/
│   └── CTKObserver.php              # Model observer for immutability enforcement
│
└── Http/
    └── Controllers/
        └── CTKDocumentController.php  # Download documents (authenticated)

database/
├── migrations/
│   ├── 2026_01_22_000001_create_ctk_table.php
│   ├── 2026_01_22_000002_create_mcu_records_table.php
│   ├── 2026_01_22_000003_create_ctk_payments_table.php
│   ├── 2026_01_22_000004_create_ctk_documents_table.php
│   ├── 2026_01_22_000005_create_training_records_table.php
│   ├── 2026_01_22_000006_create_screening_results_table.php
│   ├── 2026_01_22_000007_create_visa_records_table.php
│   ├── 2026_01_22_000008_create_stage_transitions_table.php
│   └── 2026_01_22_000009_create_ctk_notes_table.php
│
├── factories/
│   ├── CTKFactory.php
│   ├── MCURecordFactory.php
│   ├── CTKPaymentFactory.php
│   └── [others as needed]
│
└── seeders/
    └── CTKPermissionsSeeder.php     # Add CTK permissions to roles

tests/
├── Feature/
│   ├── CTKManagementTest.php        # CRUD operations
│   ├── CTKStageProgressionTest.php  # Stage advancement logic
│   ├── CTKEntityIsolationTest.php   # Entity-based access control
│   ├── CTKDocumentUploadTest.php    # Document management
│   ├── CTKImmutabilityTest.php      # Final stage immutability
│   └── CTKAuditTrailTest.php        # Audit logging
│
└── Unit/
    ├── CTKModelTest.php             # Model relationships and scopes
    ├── CTKObserverTest.php          # Observer behavior
    └── CTKPolicyTest.php            # Policy authorization rules

storage/
└── app/
    └── private/
        └── ctk-documents/           # Document uploads (private visibility)
            ├── soal-berkas/
            ├── paspor/
            ├── ijin-desa/
            ├── rekomendasi/
            ├── working-permit/
            ├── visa/
            ├── medical-full/
            └── opp/

routes/
└── web.php                          # Add CTK document download route
```

**Structure Decision**: 

This follows Laravel 11's structure (keeping Laravel 10 organization with `app/Http/Middleware/` and `app/Providers/`). The CTK module uses:

1. **9 Eloquent models** (CTK + 8 related entities) with proper relationships
2. **6 Enums** for type safety and validation
3. **Single Filament Resource** (CTKResource) with organized sub-structure:
   - Pages for CRUD operations
   - Schemas for form sections (modular, reusable)
   - Tables for list view configuration
   - Actions for custom operations (stage advancement, document upload)
4. **Policy** for authorization (leverages Spatie Permission)
5. **Observer** for enforcing business rules (immutability)
6. **9 Migrations** (one per table)
7. **Feature and Unit tests** for comprehensive coverage
8. **Private file storage** for uploaded documents with authenticated access

This structure follows patterns established in Spec 002 (EmployeeLPK) and aligns with Filament v4 best practices for complex resources with multiple related entities.

## Complexity Tracking

> **Status**: No violations - this section intentionally left minimal

All Constitution principles are satisfied without requiring exceptions. The design follows established patterns from existing modules (Spec 001, 002) and uses standard Laravel/Filament features without additional architectural complexity.

---

## Phase 0: Research & Discovery

**Objective**: Resolve technical unknowns and document best practices for CTK implementation.

### Research Tasks

1. **File Upload Best Practices in Filament v4**
   - Research: How to implement file upload in Filament v4 with private visibility and authenticated download
   - Research: File validation (types, sizes) in Filament schemas
   - Research: File storage organization (subdirectories per document type)
   - Output: Document upload strategy with code examples

2. **Filament Resource Tab Management**
   - Research: How to organize CTK detail view with multiple tabs (Personal Info, MCU, Payments, Documents, Training, Screening, Visa, Audit Trail)
   - Research: Filament Tabs component for ViewCTK page
   - Research: Lazy loading tab content for performance
   - Output: Tab structure documentation with examples

3. **Stage Progression Workflow**
   - Research: Filament custom actions for stage advancement with validation
   - Research: How to implement "Advance to Next Stage" button with gate checks (MCU status, payment completion, document requirements)
   - Research: Modal confirmation dialogs for stage transitions
   - Output: Stage advancement pattern documentation

4. **Entity Scoping in Filament**
   - Research: How to apply query scopes in Filament Resource getEloquentQuery() method
   - Research: Scoping based on user entity and CTK current stage
   - Research: Handling Pimpinan role (view all entities)
   - Output: Entity scoping implementation pattern

5. **Audit Trail Display in Filament**
   - Research: How to display Spatie ActivityLog in Filament infolist or table
   - Research: Custom infolist entries for stage transitions
   - Research: Timeline component for visual stage history
   - Output: Audit trail display strategy

6. **Model Observer for Immutability**
   - Research: Laravel model observers for enforcing business rules
   - Research: How to prevent updates to models in certain states
   - Research: Exception handling and user feedback for immutability violations
   - Output: Observer implementation pattern

7. **Bulk Actions in Filament Tables**
   - Research: Filament bulk actions for multiple CTK selection
   - Research: Bulk document request notification
   - Research: Bulk status updates with validation
   - Output: Bulk action patterns

8. **Export Functionality**
   - Research: Filament export actions with maatwebsite/excel
   - Research: PDF export with dompdf including audit trail
   - Research: Export templates and formatting
   - Output: Export implementation strategy

### Research Output

**Deliverable**: `research.md` file documenting:
- Decision made for each research area
- Rationale (why this approach chosen)
- Alternatives considered
- Code examples from Filament v4 docs
- Performance considerations
- Security implications (especially for file uploads)

---

## Phase 1: Data Model & Contracts

**Objective**: Design database schema, define entity relationships, and create API contracts (if needed).

### Data Model Design

**Deliverable**: `data-model.md` file containing:

1. **Entity Relationship Diagram** (textual description)
   - 9 main entities: CTK, MCURecord, CTKPayment, CTKDocument, TrainingRecord, ScreeningResult, VisaRecord, StageTransition, CTKNote
   - Relationships: All entities belong to CTK (1:many), StageTransition logs each status change, CTKDocument tracks uploads

2. **Table Schemas** (column definitions without SQL)
   - **ctk**: id, nik (unique), nama_lengkap, tanggal_lahir, jenis_kelamin, alamat, no_telepon, email, current_status (enum), current_stage (int 1-15), current_entity (LPK/PT), timestamps, soft_deletes, created_by, updated_by
   - **mcu_records**: id, ctk_id (FK), status (FIT/UNFIT/PENDING), examination_date, clinic_name, examiner_name, notes, timestamps, created_by
   - **ctk_payments**: id, ctk_id (FK), stage_number (1-5), amount, bank_name, payment_date, payment_method, payment_status (Pending/Lunas), payment_proof_path, timestamps, created_by
   - **ctk_documents**: id, ctk_id (FK), document_type (enum), filename, file_path, file_size, mime_type, upload_timestamp, uploader_id
   - **training_records**: id, ctk_id (FK), instructor_id (FK to employee_lpk), start_date, completion_date, training_status, training_location, training_hours, completion_notes, timestamps
   - **screening_results**: id, ctk_id (FK), stage_name (Screening1/InterviewUser), result (Lolos/TidakLolos), screening_date, screener_id (FK to users), notes, timestamps
   - **visa_records**: id, ctk_id (FK), application_status, application_date, visa_number, issuance_date, expiry_date, issuing_country, visa_type, visa_document_path, timestamps
   - **stage_transitions**: id, ctk_id (FK), from_stage, to_stage, transition_timestamp, user_id (FK), transition_reason, approval_id (nullable), immutable (after creation)
   - **ctk_notes**: id, ctk_id (FK), note_text, note_category (Umum/Peringatan/Penting), author_id (FK to users), timestamps

3. **Indexes & Constraints**
   - UNIQUE index on ctk.nik
   - Indexes on foreign keys
   - Indexes on current_status, current_stage, current_entity for filtering
   - Indexes on timestamps for sorting

4. **Scopes & Query Helpers**
   - Entity scope (filterByUserEntity)
   - Status scopes (byStage, inLPKStages, inPTStages)
   - Search scopes (searchByName, searchByNIK)

5. **Validation Rules**
   - NIK: required, unique, digits:16
   - Email: nullable, email format
   - File uploads: mimes:pdf,jpg,jpeg,png|max:10240 (10MB)
   - Stage progression: current_stage + 1 only (sequential)
   - MCU result required before advancing from stage 1

### Contracts (if needed)

**Deliverable**: `contracts/` directory

- **Not Required**: This is an admin panel (Filament), no external API exposure in this phase
- If future API needed: OpenAPI spec for CTK CRUD endpoints would go here

### Quickstart Guide

**Deliverable**: `quickstart.md` file containing:

1. **Developer Setup** (5-minute quick start)
   ```bash
   # After checking out branch 003-ctk-core-module
   composer install
   php artisan migrate
   php artisan db:seed --class=CTKPermissionsSeeder
   php artisan test tests/Feature/CTKManagementTest.php
   ```

2. **Database Setup**
   - Run migrations to create 9 tables
   - Seed CTK permissions into existing roles
   - Create sample CTK records with factory (optional for dev)

3. **Testing CTK Module**
   - Login as Admin LPK
   - Navigate to CTK Resource
   - Create new CTK record
   - Advance through stages 1-5
   - Verify entity isolation (LPK user cannot see PT stages)

4. **Key Files to Review**
   - `app/Models/CTK.php` - Main model with relationships
   - `app/Filament/Resources/CTKResource.php` - Filament resource
   - `app/Policies/CTKPolicy.php` - Authorization rules
   - `database/migrations/*_create_ctk_*` - Database schema

5. **Common Tasks**
   - Add new document type: Update DocumentType enum, add to upload form
   - Add new stage: Update CTKStatus enum, add stage validation logic
   - Modify permissions: Update CTKPolicy and CTKPermissionsSeeder

---

## Phase 2: Implementation Planning

**Objective**: Break down implementation into concrete, testable tasks.

**Note**: This phase outputs `tasks.md` which is created by the `/speckit.tasks` command, NOT by `/speckit.plan`.

The tasks will be organized into:

1. **Phase 2A: Foundation (MVP - Stages 1-5)**
   - Create enums (CTKStatus, MCUStatus, PaymentStatus, DocumentType, ScreeningStage, ScreeningResult)
   - Create migrations for all 9 tables
   - Create models with relationships (CTK, MCURecord, CTKPayment, CTKDocument, TrainingRecord, ScreeningResult, VisaRecord, StageTransition, CTKNote)
   - Create factories for test data
   - Create CTKPolicy with entity scoping
   - Create CTKObserver for immutability enforcement
   - Create CTKPermissionsSeeder
   - Create basic Filament CTKResource (CRUD only)
   - Feature tests for CTK CRUD operations
   - Feature tests for entity isolation

2. **Phase 2B: Stage Progression (Stages 1-5)**
   - Implement MCU record management
   - Implement payment tracking (5 stages)
   - Implement basic document upload (Soal/Berkas, Paspor)
   - Implement training record tracking
   - Create AdvanceStageAction for stage progression with gates
   - Feature tests for stage advancement logic
   - Feature tests for gate validations

3. **Phase 2C: Complete Lifecycle (Stages 6-15)**
   - Implement screening results (Screening 1, Interview User)
   - Implement remaining document types (Ijin Desa, Rekomendasi, WP, Visa, Medical Full, OPP)
   - Implement visa application & issuance tracking
   - Implement stage transitions for stages 6-15
   - Feature tests for PT stages
   - Feature tests for final stage immutability

4. **Phase 2D: Audit & Compliance**
   - Implement stage transition logging
   - Implement audit trail display in ViewCTK page (timeline/infolist)
   - Implement activity log configuration
   - Create ViewAuditTrailAction
   - Feature tests for audit logging
   - Unit tests for CTKObserver

5. **Phase 2E: Advanced Features**
   - Implement bulk actions (bulk status update, bulk document request)
   - Implement export actions (PDF, Excel with audit trail)
   - Implement CTK notes/comments
   - Implement document download controller (authenticated)
   - Implement visual progress indicator
   - Implement advanced filters (by stage, by document status, by payment status)
   - Feature tests for exports
   - Feature tests for bulk actions

6. **Phase 2F: Polish & Optimization**
   - Run `vendor/bin/pint` for code formatting
   - Optimize queries (eager loading, indexes)
   - Add loading states for file uploads
   - Add validation messages for all forms
   - Add success/error notifications
   - Add tooltips and help text
   - Performance testing (10k+ records)
   - Final integration tests

---

## Post-Phase 1 Constitution Re-Check

*Required after data model design is complete*

**Status**: To be completed after `data-model.md` is generated

- [ ] Verify NIK uniqueness constraint exists in migration
- [ ] Verify soft deletes enabled on CTK model
- [ ] Verify stage_transitions table structure supports audit requirements
- [ ] Verify entity scoping enforced in model and policy
- [ ] Verify immutability logic documented in observer design
- [ ] Verify all relationships follow single source of truth principle

---

## Success Criteria Mapping

| Success Criteria from Spec | Implementation Approach | Validation Method |
|----------------------------|-------------------------|-------------------|
| SC-001: CTK operations < 30s | Optimized queries, indexes on search fields | Performance test with 10k records |
| SC-002: Prevent duplicate NIK 100% | UNIQUE constraint + validation | Unit test + feature test |
| SC-003: 100% audit trail accuracy | ActivityLog + StageTransition table | Feature test verifying all changes logged |
| SC-004: Search/filter < 2s for 10k records | Database indexes, query optimization | Performance test with seeded data |
| SC-005: 100% entity isolation enforcement | Query scopes + policy checks | Feature test with LPK/PT users |
| SC-006: Required document validation 100% | Stage gate logic in AdvanceStageAction | Feature test attempting invalid advancement |
| SC-007: 100% stage gate enforcement | Gate checks in AdvanceStageAction | Feature test matrix of all gate scenarios |
| SC-008: Dashboard stats update < 5s | Efficient aggregate queries, caching if needed | Performance test with real-time updates |
| SC-009: Final stage immutability enforced | CTKObserver throwing exception | Unit test + feature test |
| SC-010: Export < 10s for 1000 records | Optimized export queries, chunking | Performance test with export actions |
| SC-011: Concurrent updates without corruption | Database transactions, optimistic locking | Concurrent user simulation test |
| SC-012: Document upload validation < 3s | File validation in form, stream processing | Feature test with various file types/sizes |

---

## Dependencies

**External Dependencies** (Already satisfied):
- ✅ Spec 001: User Management & RBAC (provides Spatie Permission, roles, EntityType enum, User model)
- ✅ Spec 002: Karyawan LPK (provides EmployeeLPK model for instructor assignment in training_records)

**New Package Dependencies** (Need to verify/install):
- ❓ intervention/image v3 - Check if already installed, otherwise add to composer.json
- ❓ maatwebsite/excel v3 - Check if already installed for exports
- ❓ barryvdh/laravel-dompdf v2 - Check if already installed for PDF exports

**Installation Commands** (if packages not present):
```bash
composer require intervention/image
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

---

## Risks & Mitigations

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| File storage fills disk | High | Medium | Implement file size limits (10MB), document retention policy, monitor storage usage |
| Performance degradation with 10k+ records | Medium | Medium | Database indexes on search fields, eager loading, query optimization, pagination |
| Complexity of 15-stage workflow | Medium | Low | Incremental implementation (phases 2A→2C), comprehensive tests for each stage |
| Immutability logic too strict, blocks legitimate corrections | Low | Medium | Provide override permission with audit trail, clear error messages explaining why blocked |
| Entity scoping bugs allowing cross-entity access | High | Low | Comprehensive policy tests, code review focus on scopes, manual QA testing |
| Document upload failures due to file size/type | Low | High | Clear validation messages, file type detection, size checks before upload starts |
| Audit log table grows too large | Medium | Medium | Implement retention policy (keep recent + critical), archive old logs, monitor table size |
| Stage transition logic complexity | Medium | Medium | Well-documented gate checks, unit tests for each gate, comprehensive feature tests |

---

## Next Steps

1. ✅ **Complete**: Feature specification (spec.md) and requirements checklist
2. ✅ **Complete**: Implementation plan (plan.md) with technical context and constitution check
3. 🔄 **In Progress**: Generate research.md (Phase 0) - `/speckit.plan` will create this
4. ⏳ **Pending**: Generate data-model.md (Phase 1) - `/speckit.plan` will create this
5. ⏳ **Pending**: Generate quickstart.md (Phase 1) - `/speckit.plan` will create this
6. ⏳ **Pending**: Re-check constitution after data model design
7. ⏳ **Pending**: Generate tasks.md (Phase 2) - Use `/speckit.tasks` command
8. ⏳ **Pending**: Begin implementation following task order

**Command to continue**: The `/speckit.plan` command should now proceed to Phase 0 (research.md generation) based on the 8 research tasks defined above.
