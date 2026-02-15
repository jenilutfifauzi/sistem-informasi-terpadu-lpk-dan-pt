# Implementation Plan: CTK Index Action Buttons

**Branch**: `006-ctk-view-actions` | **Date**: February 15, 2026 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/006-ctk-view-actions/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Add explicit "View" and "Kelola Progress" (Manage Progress) action buttons to the CTK index table (http://localhost:8000/admin/c-t-k-s). The "View" action navigates to CTK detail page, while "Kelola Progress" opens a modal for quick stage updates directly from the index. Action visibility is controlled by role-based authorization matching existing entity/stage access patterns. This reduces clicks for common workflows (viewing details and updating progress) and makes these actions more discoverable to users.

Technical approach leverages Filament 4's table action system with role-based `visible()` callbacks, modal actions for progress updates, and Livewire reactivity for automatic table refresh after updates.

## Technical Context

**Language/Version**: PHP 8.2+ (production: PHP 8.4.5)  
**Primary Dependencies**: Laravel 11.28+, Filament 4.0+, Livewire 3, Spatie Activity Log 4.10  
**Storage**: MySQL/MariaDB (existing CTK table, no schema changes required)  
**Testing**: PHPUnit 10.0+ with database transactions (NOT RefreshDatabase per DATABASE_SAFETY.md)  
**Target Platform**: Web application (Laravel Vite for asset bundling)  
**Project Type**: Web (server-driven UI with Filament/Livewire)  
**Performance Goals**: Action response <2s, table refresh <3s post-update, support 50+ concurrent CTK list users  
**Constraints**: Must respect existing RBAC (Admin LPK stages 1-5, Admin PT stages 6-15, Pimpinan read-only), audit all progress updates, prevent final stage (Terbang) modifications  
**Scale/Scope**: ~15 table rows visible per page, actions on 100s-1000s of CTK records, 8 distinct user roles with different access patterns

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Principle I: Data Integrity & Single Source of Truth
**Status**: ✅ PASS  
**Analysis**: Feature adds UI actions to existing CTK table; does not create duplicates or modify canonical CTK data flow. All updates go through existing CTK model methods preserving single source of truth.

### Principle II: Multi-Entity Isolation
**Status**: ✅ PASS  
**Analysis**: Action visibility respects existing entity isolation via `getEloquentQuery()` scoping (Admin LPK sees LPK stages 1-5, Admin PT sees PT stages 6-15). No cross-entity access introduced.

### Principle III: Role-Based Access Control & Least Privilege
**Status**: ✅ PASS  
**Analysis**: Action buttons use explicit role-based `visible()` callbacks checking user role and CTK entity/stage. "Kelola Progress" hidden for Pimpinan (read-only). Super Admin sees all. Follows least-privilege via existing authorization patterns.

### Principle IV: Auditability & Compliance
**Status**: ✅ PASS  
**Analysis**: Progress updates will create Spatie Activity Log entries (existing pattern in CTK model). Final stages (Terbang) remain immutable per existing business rules. All actions traceable to user + timestamp.

### Principle V: Incremental Delivery & Simplicity
**Status**: ✅ PASS  
**Analysis**: Feature prioritized as P1 (View + Kelola Progress), P2 (Role visibility), P3 (Bulk actions). Each tier independently testable. Uses standard Filament `Action` classes (no custom complexity). PHPUnit tests required per constitution.

**Overall Gate**: ✅ PASSED - No violations. Feature aligns with all constitution principles.

## Project Structure

### Documentation (this feature)

```text
specs/006-ctk-view-actions/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output - Filament action patterns research
├── data-model.md        # Phase 1 output - No new entities, documents action flow
├── quickstart.md        # Phase 1 output - Developer setup & testing guide
├── contracts/           # Phase 1 output - N/A (no external API)
├── checklists/
│   └── requirements.md  # Spec validation checklist
└── spec.md              # Feature specification (input)
```

### Source Code (repository root)

```text
app/
├── Filament/
│   └── Resources/
│       └── CTKS/
│           ├── CTKResource.php                    # Add getEloquentQuery() usage notes
│           ├── Tables/
│           │   └── CTKSTable.php                  # PRIMARY: Add ->actions() with View + Kelola Progress
│           ├── Actions/                           # NEW: Custom table actions
│           │   └── ManageProgressAction.php       # NEW: Kelola Progress modal action
│           └── Pages/
│               ├── ListCTKS.php                   # Existing - may add helper methods
│               └── ViewCTK.php                    # Target page for View action
├── Models/
│   └── CTK.php                                    # Existing - validate stage transition methods exist
└── Policies/
    └── CTKPolicy.php                              # OPTIONAL: Explicit action authorization if needed

tests/
└── Feature/
    ├── CTKTableActionsTest.php                    # NEW: Test action visibility & authorization
    └── CTKProgressManagementTest.php              # NEW: Test Kelola Progress modal & updates
```

**Structure Decision**: Laravel web application with Filament admin panel. Primary changes in `app/Filament/Resources/CTKS/Tables/CTKSTable.php` adding `->actions()` method with role-based visibility. Custom action class `ManageProgressAction` handles progress update modal. No database migrations required (UI-only changes). Tests use database transactions per DATABASE_SAFETY.md (NOT RefreshDatabase).

## Complexity Tracking

> No constitution violations detected. This section intentionally left empty.
