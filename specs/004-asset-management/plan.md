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

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
