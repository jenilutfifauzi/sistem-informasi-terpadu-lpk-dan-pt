# Implementation Plan: CTK Edit Stages Separation

**Branch**: `007-ctk-edit-stages-separation` | **Date**: 2026-02-15 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/007-ctk-edit-stages-separation/spec.md`

## Summary

Refactor the Edit CTK form to separate combined sections into individual per-stage sections. Currently stages 3-4, 6-7, and 10-11-13 are grouped together, and stages 8-9 have no form sections. This plan splits `DocumentSection` into `SoalBerkasSection` (stage 3) + `PasporSection` (stage 4, with nomor paspor input), splits `ScreeningSection` into `Screening1Section` (stage 6) + `InterviewUserSection` (stage 7), creates new `IjinDesaSection` (stage 8), `RekomendasiSection` (stage 9), and `WorkingPermitSection` (stage 10), and updates `VisaSection` to only cover stages 11-13. A migration adds `screening_stage` column to `c_t_k_screenings` for reliable stage differentiation.

## Technical Context

**Language/Version**: PHP 8.4.5
**Primary Dependencies**: Laravel 11, Filament v4, Livewire v3
**Storage**: MySQL/MariaDB
**Testing**: PHPUnit 10 via `php artisan test`
**Target Platform**: Web (server-rendered Filament admin panel)
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: N/A (admin panel, low concurrency)
**Constraints**: No data loss during migration; backward-compatible with existing records
**Scale/Scope**: ~15 CTK form sections, 7 new/modified schema classes, 1 migration

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Design Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity & Single Source of Truth | PASS | No duplicate records. Adding `screening_stage` column improves data integrity by replacing fragile LIKE queries. |
| II. Multi-Entity Isolation | PASS | No cross-entity changes. Form sections only affect CTK edit within current entity context. |
| III. RBAC & Least Privilege | PASS | No permission changes. Same edit page, same access controls. |
| IV. Auditability & Compliance | PASS | Activity logging untouched. Screening stage changes tracked via existing audit log. |
| V. Incremental Delivery & Simplicity | PASS | Simplest approach: individual section classes following existing patterns (MCUSection, TrainingSection). No new abstractions. |

### Post-Design Re-Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity | PASS | Data migration backfills `screening_stage` preserving existing logic. No data loss. |
| II. Multi-Entity Isolation | PASS | No cross-entity changes introduced. |
| III. RBAC | PASS | No permission model changes. |
| IV. Auditability | PASS | `screening_stage` added to model (auditable). Stage completion logic improved. |
| V. Simplicity | PASS | Each new section class follows established patterns from MCUSection/TrainingSection. No new patterns introduced. |

**Result**: All gates pass. No violations to justify.

## Project Structure

### Documentation (this feature)

```text
specs/007-ctk-edit-stages-separation/
├── plan.md              # This file
├── research.md          # Phase 0 output — research findings
├── data-model.md        # Phase 1 output — entity changes
├── quickstart.md        # Phase 1 output — setup guide
├── contracts/
│   └── sections.md      # Phase 1 output — section component contracts
├── checklists/
│   └── requirements.md  # Specification quality checklist
└── tasks.md             # Phase 2 output (NOT created by /speckit.plan)
```

### Source Code (affected paths)

```text
app/Filament/Resources/CTKS/
├── Schemas/
│   ├── SoalBerkasSection.php       # NEW (Stage 3)
│   ├── PasporSection.php           # NEW (Stage 4)
│   ├── Screening1Section.php       # NEW (Stage 6)
│   ├── InterviewUserSection.php    # NEW (Stage 7)
│   ├── IjinDesaSection.php         # NEW (Stage 8)
│   ├── RekomendasiSection.php      # NEW (Stage 9)
│   ├── WorkingPermitSection.php    # NEW (Stage 10)
│   ├── VisaSection.php             # MODIFIED (renamed to 11-13)
│   ├── DocumentSection.php         # DELETED (replaced by Stage 3+4)
│   └── ScreeningSection.php        # DELETED (replaced by Stage 6+7)
├── Pages/
│   └── EditCTK.php                 # MODIFIED (updated section order)
└── Actions/
    └── AdvanceStageAction.php      # REVIEW (no changes expected)

app/Models/
├── CTK.php                         # MODIFIED (stage 6/7 completion logic)
└── CTKScreening.php                # MODIFIED (add screening_stage)

database/migrations/
└── XXXX_add_screening_stage_to_c_t_k_screenings_table.php  # NEW

tests/Feature/
├── CTKScreeningTest.php            # MODIFIED (add screening_stage)
└── CTKStageTrackingTest.php        # MODIFIED (update stage 6/7 tests)
```

**Structure Decision**: Standard Laravel monolith structure. New files follow existing patterns in `app/Filament/Resources/CTKS/Schemas/`. Each section is a standalone class with a static `make()` method returning a Filament `Section` component.

## Complexity Tracking

> No violations found. All constitution gates pass. No complexity justification needed.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
