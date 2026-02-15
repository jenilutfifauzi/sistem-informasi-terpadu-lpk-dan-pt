# Implementation Plan: Employee Asset Management System

**Branch**: `004-asset-management` | **Date**: February 8, 2026 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/004-asset-management/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

This feature enables separate asset inventory management for PT and LPK entities with full CRUD operations, entity-based access control, asset assignment to employees, condition tracking, and reporting capabilities. The implementation follows Laravel 11 + Filament v4 conventions with Eloquent models, policies for authorization, and activity logging for audit trails. Key technical approach includes using EntityType enum for entity isolation, Filament Resources for admin UI, and polymorphic relationships for employee assignments across both karyawan_lpk and users tables.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Primary Dependencies**: Laravel Framework v11, Filament v4, Livewire v3, Spatie Activity Log, Filament Shield  
**Storage**: MySQL/MariaDB (existing database)  
**Testing**: PHPUnit v10 (feature tests + unit tests with database transactions, no RefreshDatabase)  
**Target Platform**: Web application (server-side rendered with Livewire)  
**Project Type**: Web application - Laravel monolith with Filament admin panel  
**Performance Goals**: 
- List page load for 1000+ assets < 2 seconds (SC-002)
- Export 500+ assets to Excel < 5 seconds (SC-006)
- Dashboard statistics load < 1 second (SC-007)
- 50 concurrent users without degradation (SC-010)

**Constraints**: 
- Must follow Laravel 10 structure (not migrated to Laravel 11 streamlined structure)
- Entity isolation MUST be enforced at all levels (eloquent scopes, policies, UI)
- All CRUD operations MUST be audited via Spatie Activity Log
- Soft deletes required for data retention
- No RefreshDatabase in tests - use database transactions only

**Scale/Scope**: 
- Expected 10,000+ asset records across PT and LPK
- 50+ concurrent admin users
- 3 main database tables (assets, asset_assignments, asset_condition_histories)
- 1 Filament Resource with List/Create/Edit/View pages
- 5 user stories (US1-US5) with US1+US4 as MVP minimum

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### ✅ I. Data Integrity & Single Source of Truth

**Status**: PASS - Not applicable for asset management  
**Rationale**: Assets are independent entities, not CTK-related. Each asset record is canonical within its entity (PT or LPK). Uniqueness enforced via nomor_inventaris (unique index).

### ✅ II. Multi-Entity Isolation

**Status**: PASS - Fully compliant  
**Implementation**:
- Asset model will have `entity` field (PT/LPK) from EntityType enum
- Eloquent global scope will filter by user's entity automatically
- Policies will enforce entity-based access (Admin LPK sees only LPK, Admin PT sees only PT)
- Pimpinan role gets read-only access to both entities
- No inter-entity asset transfers in MVP (out of scope)

### ✅ III. Role-Based Access Control & Least Privilege

**Status**: PASS - Fully compliant  
**Implementation**:
- New permissions created: view_asset, view_any_asset, create_asset, update_asset, delete_asset, restore_asset, force_delete_asset
- Permissions assigned per role: Admin LPK (CRUD on LPK assets), Admin PT (CRUD on PT assets), Pimpinan (view_any read-only)
- AssetPolicy enforces permissions + entity scoping
- Filament Resource uses canViewAny, canCreate, canEdit, canDelete authorization gates

### ✅ IV. Auditability & Compliance

**Status**: PASS - Fully compliant  
**Implementation**:
- Spatie Activity Log trait on Asset model logs all changes (FR-028)
- AssetConditionHistory table explicitly tracks condition changes with timestamp, user, old/new values, reason (FR-014)
- created_by and updated_by fields on Asset model
- Soft deletes enabled (FR-027)
- Asset immutability NOT required (not in final/legal stage like CTK)

### ✅ V. Incremental Delivery & Simplicity

**Status**: PASS - Fully compliant  
**MVP Scope**: US1 (Asset Registration) + US4 (Entity Isolation)  
**Incremental**: US2 (Condition History), US3 (Employee Assignment), US5 (Reporting) can be added independently  
**Testing**: Feature tests for each user story, unit tests for helpers (nomor_inventaris generation)  
**Simplicity**: Standard Eloquent patterns, no complex abstractions, use Filament's built-in components

### ✅ Operational Constraints

**Status**: PASS - All constraints met  
- ✅ PHP 8.4.5 - existing version
- ✅ Laravel 11 conventions with Laravel 10 structure - following existing codebase
- ✅ Filament v4 - using existing admin panel
- ✅ Livewire v3 - existing reactive components
- ✅ MySQL/MariaDB - existing database
- ✅ RBAC via Filament Shield - existing infrastructure
- ✅ Entity isolation - core requirement of this feature
- ✅ Audit logs - Spatie Activity Log already installed
- ✅ Soft deletes - will be implemented
- ✅ Excel export - will use Filament export action (maatwebsite/excel if needed)

### ✅ Development Workflow

**Status**: PASS - Will follow all conventions  
- ✅ Use `php artisan make:*` commands with `--no-interaction`
- ✅ Form Request classes for validation (StoreAssetRequest, UpdateAssetRequest)
- ✅ Eloquent relationships with eager loading
- ✅ Factories for Asset, AssetAssignment, AssetConditionHistory
- ✅ Feature tests before merge (no RefreshDatabase, use transactions)
- ✅ Filament Resource components reused where possible
- ✅ `vendor/bin/pint` before finalization
- ✅ Follow Laravel 10 structure (middleware in app/Http/Middleware/, etc.)
- ✅ Check sibling files for conventions

### Feature Sequencing Compliance

**Status**: COMPLIANT with recommended sequence  
**Position**: Asset Management can proceed, as it depends on:
- ✅ Module 001 (User Management & RBAC) - completed
- ✅ Module 002 (Karyawan LPK) - completed (for employee assignments)

**Note**: Asset Management is independent of CTK core module (003) and can proceed in parallel or later.

### Gate Decision: ✅ PROCEED TO PHASE 0

All constitution principles satisfied. No complexity justifications required. Feature ready for research and design phases.

## Project Structure

### Documentation (this feature)

```text
specs/004-asset-management/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (technology decisions & patterns)
├── data-model.md        # Phase 1 output (database schema & relationships)
├── quickstart.md        # Phase 1 output (setup guide for developers)
├── contracts/           # N/A for this feature (internal Filament Resource, no external API)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root - Laravel 10 structure)

```text
app/
├── Enums/
│   ├── AssetCategory.php          # Elektronik, Furniture, Dokumen, Perlengkapan, Kendaraan, Lainnya
│   ├── AssetCondition.php         # Baik, Rusak
│   └── AssetAssignmentStatus.php  # Assigned, Available
├── Models/
│   ├── Asset.php                  # Main asset model with entity scoping
│   ├── AssetAssignment.php        # Employee-asset assignments
│   └── AssetConditionHistory.php  # Condition change audit trail
├── Policies/
│   └── AssetPolicy.php            # Entity-based authorization
├── Http/
│   └── Requests/
│       ├── StoreAssetRequest.php  # Validation for asset creation
│       └── UpdateAssetRequest.php # Validation for asset updates
├── Filament/
│   └── Resources/
│       ├── AssetResource.php      # Main Filament resource
│       └── AssetResource/
│           ├── Pages/
│           │   ├── ListAssets.php
│           │   ├── CreateAsset.php
│           │   ├── EditAsset.php
│           │   └── ViewAsset.php
│           ├── Actions/          # Custom actions (if needed)
│           └── Widgets/          # Statistics widgets (US5)
└── Helpers/                      # (if needed)
    └── AssetNumberGenerator.php  # Nomor inventaris generation logic

database/
├── migrations/
│   ├── 2026_02_08_000001_create_assets_table.php
│   ├── 2026_02_08_000002_create_asset_assignments_table.php
│   └── 2026_02_08_000003_create_asset_condition_histories_table.php
├── factories/
│   ├── AssetFactory.php
│   ├── AssetAssignmentFactory.php
│   └── AssetConditionHistoryFactory.php
└── seeders/
    ├── AssetPermissionsSeeder.php    # Permissions for asset management
    └── AssetDemoSeeder.php           # Demo data for testing

tests/
├── Feature/
│   ├── AssetManagementTest.php       # US1 CRUD tests
│   ├── AssetConditionTrackingTest.php # US2 condition history tests
│   ├── AssetAssignmentTest.php       # US3 employee assignment tests
│   ├── AssetEntityIsolationTest.php  # US4 entity-based access tests
│   └── AssetReportingTest.php        # US5 statistics and export tests
└── Unit/
    └── AssetNumberGeneratorTest.php  # Nomor inventaris logic tests
```

**Structure Decision**: Laravel 10 monolith structure (not migrated to Laravel 11's streamlined structure). Filament Resources follow standard patterns established in specs 001-003. Asset management is a standalone module with no external API exposure, so contracts/ directory is not applicable. All UI interactions handled through Filament admin panel.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

**N/A** - No constitution violations. All principles pass without exceptions.

## Implementation Phases

### Phase 0: Research & Technical Decisions ✅

**Status**: COMPLETE  
**Output**: [research.md](./research.md)

**Key Decisions Made**:
1. ✅ Entity isolation: PT/LPK immutable after creation (no transfers)
2. ✅ Employee references: Polymorphic MorphTo relationship (supports karyawan_lpk + users)
3. ✅ Nomor inventaris: Application-level sequence with DB locking ([PT/LPK]-[KATEGORI]-[TAHUN]-[SEQUENCE])
4. ✅ Zero quantity handling: Soft delete assets instead of allowing jumlah=0
5. ✅ Duplicate names: Allowed (enforce unique nomor_inventaris only)
6. ✅ OUT OF SCOPE for MVP: Bulk import, photo uploads, depreciation calculation, structured locations, maintenance scheduling, warranty tracking, QR code generation

**Research Complete**: All NEEDS CLARIFICATION items resolved.

---

### Phase 1: Database Schema & Design ✅

**Status**: COMPLETE  
**Outputs**: 
- [data-model.md](./data-model.md) - Complete database schema with 3 tables, relationships, indexes, enums
- [quickstart.md](./quickstart.md) - Developer setup guide with step-by-step instructions

**Database Schema**:
- ✅ `assets` table (16 fields, soft deletes, entity/kategori/kondisi indexes)
- ✅ `asset_assignments` table (polymorphic relationship, tracks employee custody)
- ✅ `asset_condition_histories` table (audit trail for condition changes)
- ✅ 3 Enums: AssetCategory (6 values), AssetCondition (2 values), AssetAssignmentStatus (2 values)
- ✅ Eloquent relationships defined (hasMany, morphTo, belongsTo)
- ✅ Global scopes, model observers, business logic methods documented

**Contracts**: N/A (internal Filament Resource, no external API)

---

### Phase 2: Implementation Tasks

**Status**: PENDING  
**Command**: Run `/speckit.tasks` to generate [tasks.md](./tasks.md)

This will break down implementation into granular tasks by user story:
- **US1 Tasks (T001-T030)**: Asset CRUD operations, validation, nomor inventaris generation
- **US2 Tasks (T031-T045)**: Condition history tracking, timeline display
- **US3 Tasks (T046-T060)**: Employee assignment logic, return workflow
- **US4 Tasks (T061-T070)**: Entity isolation (global scopes, policies, UI filters)
- **US5 Tasks (T071-T085)**: Reporting, statistics widgets, Excel export

Each task includes:
- Exact file paths to create/edit
- Artisan commands to run
- Code snippets to implement
- Test requirements
- Dependencies on other tasks

---

## Post-Plan Actions

### Immediate Next Steps

1. ✅ **Review Generated Documents**: 
   - Read [data-model.md](./data-model.md) to understand schema
   - Read [quickstart.md](./quickstart.md) for setup instructions
   - Read [research.md](./research.md) for technical rationale

2. ⏳ **Generate Task Breakdown**: 
   - Run `/speckit.tasks` command to create [tasks.md](./tasks.md)
   - This generates 80+ granular implementation tasks

3. ⏳ **Begin Implementation**:
   - Follow task order in tasks.md
   - Implement US1 (Asset Registration) + US4 (Entity Isolation) first (MVP)
   - Write tests alongside each task
   - Run `vendor/bin/pint` before commits

4. ⏳ **Testing Milestones**:
   - After US1: Test asset CRUD operations
   - After US4: Test entity isolation (Admin LPK cannot see PT assets)
   - After MVP: Run full test suite (`php artisan test --compact`)

5. ⏳ **Code Review & Merge**:
   - Request team review when MVP complete
   - Address feedback
   - Merge `004-asset-management` → `main`

### Agent Context Updated

✅ GitHub Copilot context file updated with:
- PHP 8.4.5
- Laravel Framework v11, Filament v4, Livewire v3
- Spatie Activity Log, Filament Shield
- MySQL/MariaDB database

This ensures AI coding assistants provide accurate suggestions for this feature.

---

## Success Criteria Checklist

Use this checklist to verify feature completion before merge:

### Functional Requirements (FR)

- [ ] FR-001: Asset CRUD operations functional
- [ ] FR-002: Entity field auto-set from user entity
- [ ] FR-003: Nomor inventaris auto-generated with format `[PT/LPK]-[KATEGORI]-[TAHUN]-[SEQUENCE]`
- [ ] FR-004: All required fields validated (nama, kategori, jumlah >= 1, satuan, kondisi, tahun)
- [ ] FR-005: Optional fields work (deskripsi, lokasi, keterangan, nilai_pembelian)
- [ ] FR-006: Admin LPK lists only LPK assets
- [ ] FR-007: Admin PT lists only PT assets
- [ ] FR-008: Pimpinan lists all assets (PT + LPK)
- [ ] FR-009: Search works (nama_barang, lokasi, nomor_inventaris)
- [ ] FR-010: Filters work (entity, kategori, kondisi, assignment status)
- [ ] FR-011: Sort by any column
- [ ] FR-012: Pagination (default 25 per page)
- [ ] FR-013: Asset detail view shows all information
- [ ] FR-014: Condition history timeline displayed
- [ ] FR-015: Update kondisi creates history record
- [ ] FR-016: Assign asset to employee (LPK or PT staff)
- [ ] FR-017: Only one active assignment per asset
- [ ] FR-018: Return asset functionality works
- [ ] FR-019: Assignment history visible
- [ ] FR-020: Statistics widget shows totals by entity/kategori/kondisi
- [ ] FR-021: Export to Excel works
- [ ] FR-022: Export includes all visible columns
- [ ] FR-023: Nama barang: required, max 255 chars
- [ ] FR-024: Jumlah: required, integer >= 1
- [ ] FR-025: Tahun pembelian: 1900 to current year
- [ ] FR-026: Nilai pembelian: decimal >= 0
- [ ] FR-027: Soft delete works
- [ ] FR-028: Spatie Activity Log captures all changes
- [ ] FR-029: User sees own entity's assets only (except Pimpinan)

### User Stories (US)

- [ ] US1: Admin can register, view, update, search, delete assets
- [ ] US2: Admin can track asset condition changes with timeline
- [ ] US3: Admin can assign/return assets to employees with history
- [ ] US4: Entity isolation enforced (LPK/PT separation)
- [ ] US5: Reporting with statistics and Excel export

### Success Criteria (SC)

- [ ] SC-001: Asset registration < 30 seconds
- [ ] SC-002: List page load (1000+ records) < 2 seconds
- [ ] SC-003: Search results < 1 second
- [ ] SC-004: Assignment creation < 15 seconds
- [ ] SC-005: Condition update with history < 20 seconds
- [ ] SC-006: Excel export (500+ records) < 5 seconds
- [ ] SC-007: Dashboard statistics load < 1 second
- [ ] SC-008: Zero unauthorized entity access attempts
- [ ] SC-009: 100% audit trail coverage
- [ ] SC-010: 50 concurrent users without degradation

### Code Quality

- [ ] All tests pass (`php artisan test`)
- [ ] Code formatted with Pint (`vendor/bin/pint`)
- [ ] No N+1 queries (use eager loading)
- [ ] Policies enforce entity isolation
- [ ] Validation uses Form Request classes
- [ ] Factories created for all models
- [ ] Seeders provide demo data
- [ ] Documentation complete (PHPDoc blocks)

---

## Branch Information

**Branch Name**: `004-asset-management`  
**Base Branch**: `main`  
**Feature Spec**: [specs/004-asset-management/spec.md](./spec.md)  
**Created**: February 8, 2026  
**Status**: Planning Complete, Ready for Implementation

---

## Resources

- **Feature Specification**: [spec.md](./spec.md)
- **Technical Research**: [research.md](./research.md)
- **Database Schema**: [data-model.md](./data-model.md)
- **Setup Guide**: [quickstart.md](./quickstart.md)
- **Task Breakdown**: [tasks.md](./tasks.md) *(generate via `/speckit.tasks`)*
- **Constitution**: [.specify/memory/constitution.md](../../.specify/memory/constitution.md)

---

**Plan Version**: 1.0  
**Last Updated**: February 8, 2026  
**Command Used**: `/speckit.plan`  
**Next Command**: `/speckit.tasks` to generate implementation tasks
