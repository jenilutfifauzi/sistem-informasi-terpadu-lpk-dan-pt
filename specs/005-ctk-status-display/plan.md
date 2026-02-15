# Implementation Plan: CTK Index Status Display Simplification

**Branch**: `005-ctk-status-display` | **Date**: February 15, 2026 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/005-ctk-status-display/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Simplify the CTK list table by changing the Status column to display completion status ("Lengkap" when all 15 stages complete, "Belum Lengkap" otherwise), removing the redundant Tahap (Stage) column, and retaining the Progress column for detailed tracking. This is a pure display-layer change to the Filament table configuration with no data model modifications required.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Primary Dependencies**: Laravel 11, Filament 4, Livewire 3  
**Storage**: MySQL/MariaDB (existing CTK table, no schema changes)  
**Testing**: PHPUnit 10 with database transactions (not RefreshDatabase)  
**Target Platform**: Web application (server-side rendered with Livewire)
**Project Type**: Web application (Laravel backend + Filament admin panel)  
**Performance Goals**: No degradation from current page load times (~500ms for table render)  
**Constraints**: Must maintain existing RBAC filtering, must work with entity-scoped queries (LPK stages 1-5, PT stages 6-15)  
**Scale/Scope**: Affects single table view (CTKSTable.php), estimated ~30-50 lines of code change

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Core Principles Assessment

**I. Data Integrity & Single Source of Truth**
- ✅ **PASS**: No changes to CTK data model or database schema
- ✅ **PASS**: Completion status derived from existing `completed_stages_count` accessor
- ✅ **PASS**: No impact on canonical CTK records or state transitions

**II. Multi-Entity Isolation**
- ✅ **PASS**: No changes to entity scoping or access control logic
- ✅ **PASS**: Existing `getEloquentQuery()` filtering remains unchanged
- ✅ **PASS**: PT/LPK data separation unaffected

**III. Role-Based Access Control & Least Privilege**
- ✅ **PASS**: No permission changes required
- ✅ **PASS**: All existing RBAC rules maintained (Admin LPK, HR PT, etc.)
- ✅ **PASS**: Table display changes do not affect authorization

**IV. Auditability & Compliance**
- ✅ **PASS**: No changes to audit logging
- ✅ **PASS**: No impact on CTK lifecycle immutability rules
- ✅ **PASS**: Export functionality to be verified in testing

**V. Incremental Delivery & Simplicity**
- ✅ **PASS**: Minimal change scope (~30-50 lines in single file)
- ✅ **PASS**: Simple display-layer modification
- ✅ **PASS**: Independently testable and deployable
- ✅ **PASS**: Tests will accompany changes per principle

### Operational Constraints

- ✅ Technology stack alignment: PHP 8.4.5, Laravel 11, Filament 4
- ✅ No new dependencies required
- ✅ Follows Filament table column conventions
- ✅ Compatible with existing Livewire reactive UI

### Development Workflow Compliance

- ✅ Will use existing Filament components (TextColumn, badge helpers)
- ✅ Will follow sibling file conventions (CTKSTable.php structure)
- ✅ Will run `vendor/bin/pint` before finalization
- ✅ Will create PHPUnit tests using database transactions
- ✅ No Artisan scaffolding required (editing existing file)

### Gate Status: ✅ ALL GATES PASSED

No constitutional violations. Proceeding to Phase 0.

## Project Structure

### Documentation (this feature)

```text
specs/005-ctk-status-display/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Filament/
│   └── Resources/
│       └── CTKS/
│           ├── CTKResource.php           # Unchanged (resource definition)
│           └── Tables/
│               └── CTKSTable.php         # PRIMARY EDIT - table column configuration
├── Models/
│   └── CTK.php                           # Unchanged (accessors already exist)
└── Enums/
    └── CTKStatus.php                     # Unchanged (existing enum)

tests/
└── Feature/
    └── CTKTableDisplayTest.php           # NEW - test status display logic
```

**Structure Decision**: Laravel web application with Filament admin panel. Changes isolated to single table configuration class (`CTKSTable.php`). The existing CTK model already provides all necessary accessors (`completed_stages_count`, `completion_percentage`) via `getCompletedStagesCountAttribute()` method that iterates through `stage1_complete` through `stage15_complete` boolean accessors.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

**No violations identified.** All constitution gates passed. This feature requires no complexity justification.
