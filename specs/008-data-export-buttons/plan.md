# Implementation Plan: Data Export Functionality

**Branch**: `008-data-export-buttons` | **Date**: February 16, 2026 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/008-data-export-buttons/spec.md`

## Summary

Add data export capability to four Filament Resources (Karyawan LPK, CTK, Users, Asset PT) allowing users to download filtered table data in CSV and Excel formats. Users can select export format and download all visible records based on active filters. All exports are logged for audit compliance and exclude sensitive fields (passwords, personal ID numbers). Implementation uses Filament's built-in Table Actions and Laravel Excel package for data generation.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Primary Dependencies**: Laravel 11, Filament v4, Livewire v3, Laravel Excel (maatwebsite/excel)  
**Storage**: MySQL/MariaDB  
**Testing**: PHPUnit 10 via `php artisan test`  
**Target Platform**: Web (server-rendered Filament admin panel)  
**Project Type**: Web application (Laravel monolith)  
**Performance Goals**: Export up to 10,000 records within 60 seconds  
**Constraints**: Exclude passwords and personal ID numbers (KTP, passport, visa); log all exports for audit  
**Scale/Scope**: 4 Filament Resources, CSV + Excel export formats, activity logging integration

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Design Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity & Single Source of Truth | PASS | Exports read-only operation. No data modification. Exports canonical CTK/Employee/User/Asset records without creating duplicates. |
| II. Multi-Entity Isolation | PASS | Each resource respects existing entity isolation (PT vs LPK). Export actions inherit existing RBAC constraints - users can only export data they can view. |
| III. RBAC & Least Privilege | PASS | No new permissions needed. Export capability follows existing view permissions. If user can see table data, they can export it. |
| IV. Auditability & Compliance | PASS | All exports logged via activity log showing who exported what data when. Sensitive fields (passwords, KTP, passport numbers) excluded from exports per security requirements. |
| V. Incremental Delivery & Simplicity | PASS | Simplest approach: use Filament's built-in HeaderAction on tables with Laravel Excel. Each resource gets same export pattern. MVP-ready: can deploy one resource at a time (P1 first). |

### Post-Design Re-Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity | PASS | No data modifications. FromQuery interface ensures query integrity. Explicit field mapping prevents accidental data corruption. |
| II. Multi-Entity Isolation | PASS | Export actions inherit resource-level isolation. Query filters maintain entity boundaries. No cross-entity data access introduced. |
| III. RBAC | PASS | Export uses existing Filament policy integration. No permission bypass. Activity logging provides full audit trail of who exported what. |
| IV. Auditability | PASS | All exports logged with user, timestamp, record count, and format. Sensitive field exclusion enforced via explicit mapping (NIK, passwords, ID numbers excluded). |
| V. Simplicity | PASS | Standard Filament HeaderAction pattern. Laravel Excel for file generation (industry standard). Each export class follows FromQuery + WithMapping pattern. No custom abstractions. Tests cover all requirements. |

**Result**: All gates pass after design. No constitution violations.

**Result**: All gates pass. No violations to justify.

## Project Structure

### Documentation (this feature)

```text
specs/008-data-export-buttons/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/
│   └── export-actions.md  # Phase 1 output — export action interface contracts
├── checklists/
│   └── requirements.md  # Specification quality checklist
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (affected paths)

```text
app/Filament/Resources/
├── EmployeeLPKResource.php          # MODIFIED (add export header action)
├── CTKS/CTKResource.php             # MODIFIED (add export header action)
├── Users/UserResource.php           # MODIFIED (add export header action)
└── Assets/AssetResource.php         # MODIFIED (add export header action)

app/Filament/Exports/                # NEW directory
├── EmployeeLPKExport.php            # NEW (Excel export for Karyawan LPK)
├── CTKExport.php                    # NEW (Excel export for CTK)
├── UserExport.php                   # NEW (Excel export for Users)
└── AssetExport.php                  # NEW (Excel export for Assets)

app/Filament/Actions/                # NEW directory (or use existing if present)
└── ExportTableAction.php            # NEW (reusable export action with format selection)

tests/Feature/
├── EmployeeLPKExportTest.php        # NEW (test Karyawan LPK export)
├── CTKExportTest.php                # NEW (test CTK export)
├── UserExportTest.php               # NEW (test User export)
└── AssetExportTest.php              # NEW (test Asset export)

composer.json                         # MODIFIED (add maatwebsite/excel if not present)
```

**Structure Decision**: Standard Laravel monolith structure. Export logic follows Filament best practices using HeaderActions on table() method. Each resource gets its own Export class implementing Laravel Excel's `FromCollection` or `FromQuery` interface with headings and field mapping. Reusable ExportTableAction provides format selection (CSV/Excel) UI.

## Complexity Tracking

No violations. All constitution gates pass.
