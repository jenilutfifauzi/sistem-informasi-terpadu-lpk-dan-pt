# Implementation Plan: Data Siswa LPK Administration

**Branch**: `001-data-lpks-admin` | **Date**: 2026-05-09 | **Spec**: `/Users/indobuzz/Developer/Local/SIT_LPK/specs/001-data-lpks-admin/spec.md`
**Input**: Feature specification from `/specs/001-data-lpks-admin/spec.md`

## Summary

Deliver a new LPK-only admin data resource for Siswa LPK registration records using the field structure from the existing registration sheet. The implementation will use a dedicated Laravel model and MySQL table, a Filament admin resource for create, list, view, edit, and export flows, policy and Filament Shield permissions for RBAC, and PHPUnit plus Livewire tests for validation, search, uniqueness, export, and audit-sensitive behavior.

## Technical Context

**Language/Version**: PHP 8.4.19  
**Primary Dependencies**: Laravel 11.47, Filament 4.5, Livewire 3.7, Spatie Permission with Filament Shield, Spatie Activitylog, Laravel Pint, PHPUnit 10.5  
**Storage**: MySQL primary database  
**Testing**: PHPUnit feature and unit tests executed via `herd php artisan test --compact`, with Livewire test coverage for Filament pages  
**Target Platform**: Laravel Herd-hosted web admin panel on macOS development, browser-based Filament admin UI  
**Project Type**: Single Laravel web application  
**Performance Goals**: Support admin create flow completion under 3 minutes and record lookup by student number or student name within 10 seconds under normal internal usage  
**Constraints**: LPK data must stay isolated from PT data, student number must remain unique across active and soft-deleted records, email stays optional, birth date cannot be later than enrollment date, soft deletes and activity logging are required for retention and auditability, export capability is required for staff use, and no new package dependency should be introduced  
**Scale/Scope**: One new LPK master-data resource, one new database table with approximately 13 business fields plus audit fields, internal administrative users only

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Initial Gate Review

- **I. Data Integrity & Single Source of Truth**: PASS. The feature introduces one canonical Siswa LPK record per student number, with duplicate prevention handled by application validation and a database uniqueness rule.
- **II. Multi-Entity Isolation**: PASS. The feature is scoped to a dedicated LPK-only dataset and admin surface, with no PT cross-write behavior.
- **III. Role-Based Access Control & Least Privilege**: PASS. Access will be enforced with a dedicated policy and Filament Shield-generated permissions for view, create, update, and export capabilities.
- **IV. Auditability & Compliance**: PASS. The design includes soft deletes, activity logging, creator/updater attribution, and auditable export behavior consistent with repository conventions.
- **V. Incremental Delivery & Simplicity**: PASS. The scope is one CRUD-oriented master-data resource with focused tests and no extra abstraction layers.

### Post-Design Gate Review

- **I. Data Integrity & Single Source of Truth**: PASS. `siswa_lpk` remains the sole store for Siswa LPK registration data, and `nomor_induk` is both searchable and unique.
- **II. Multi-Entity Isolation**: PASS. The data model, UI contract, and quickstart all keep the feature inside LPK administration boundaries.
- **III. Role-Based Access Control & Least Privilege**: PASS. The contract and plan require policy-backed access for every admin interaction.
- **IV. Auditability & Compliance**: PASS. The data model includes soft deletion and audit relationships; quickstart includes tests for duplicate handling, export behavior, and activity logging.
- **V. Incremental Delivery & Simplicity**: PASS. The chosen design avoids separate program master data or transfer workflows until a future spec requires them.

## Project Structure

### Documentation (this feature)

```text
specs/001-data-lpks-admin/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── lpk-students.openapi.yaml
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Filament/
│   ├── Exports/
│   │   └── SiswaLPKExport.php
│   └── Resources/
│       ├── SiswaLPKResource.php
│       └── SiswaLPKResource/
│           └── Pages/
│               ├── CreateSiswaLPK.php
│               ├── EditSiswaLPK.php
│               ├── ListSiswaLPKS.php
│               └── ViewSiswaLPK.php
├── Models/
│   └── SiswaLPK.php
├── Policies/
│   └── SiswaLPKPolicy.php
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php

database/
├── factories/
│   └── SiswaLPKFactory.php
└── migrations/
    └── *_create_siswa_lpk_table.php

tests/
├── Feature/
│   └── SiswaLPKResourceTest.php
└── Unit/
    └── SiswaLPKModelTest.php
```

**Structure Decision**: Use the existing single-project Laravel structure and place the feature in the same Filament, export, model, policy, migration, factory, and PHPUnit directories used by current admin resources. The resource will stay simple by keeping form and table definitions in the main resource class unless implementation complexity grows beyond this feature.

## Complexity Tracking

No constitution violations or complexity justifications are required for this plan.

