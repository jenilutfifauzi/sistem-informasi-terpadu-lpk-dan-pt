# Implementation Plan: Pembayaran ke Pusat

**Branch**: `001-ctk-pembayaran-pusat` | **Date**: 2026-04-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-ctk-pembayaran-pusat/spec.md`

## Summary

Fitur pencatatan pembayaran dari LPK/PT ke kantor pusat per CTK. Implementasi menggunakan pattern yang sudah ada di Asset management - tabel baru `pembayaran_pusat`, Filament Resource dengan entity isolation, full CRUD, dan activity logging.

## Technical Context

**Language/Version**: PHP 8.4.x  
**Primary Dependencies**: Laravel 10+, Filament v4, Livewire v3, Spatie Activity Log  
**Storage**: MySQL/MariaDB, File storage (public disk) untuk bukti transfer  
**Testing**: PHPUnit, Pest (existing patterns)  
**Target Platform**: Web application (server-rendered with Livewire)  
**Project Type**: Web application - Laravel Filament admin panel  
**Performance Goals**: Standard web app expectations (<3s page load)  
**Constraints**: Entity isolation (LPK/PT), RBAC, audit logging  
**Scale/Scope**: ~100s of payments per month, single admin panel

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity & Single Source of Truth | ✅ PASS | PembayaranPusat references canonical CTK record via foreign key |
| II. Multi-Entity Isolation | ✅ PASS | Entity column + global scope pattern (same as Asset) |
| III. Role-Based Access Control | ✅ PASS | Uses existing RBAC, Pimpinan sees all entities |
| IV. Auditability & Compliance | ✅ PASS | LogsActivity trait, soft deletes, created_by tracking |
| V. Incremental Delivery & Simplicity | ✅ PASS | Follows existing Asset/CTKPayment patterns exactly |

## Project Structure

### Documentation (this feature)

```text
specs/001-ctk-pembayaran-pusat/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (N/A - no external API)
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
app/
├── Models/
│   └── PembayaranPusat.php           # New model
├── Enums/
│   └── (use existing Entity enum)
└── Filament/
    └── Resources/
        └── PembayaranPusat/          # New resource directory
            ├── PembayaranPusatResource.php
            ├── Pages/
            │   ├── ListPembayaranPusat.php
            │   ├── CreatePembayaranPusat.php
            │   ├── EditPembayaranPusat.php
            │   └── ViewPembayaranPusat.php
            ├── Schemas/
            │   └── PembayaranPusatForm.php
            ├── Tables/
            │   └── PembayaranPusatTable.php
            └── Widgets/
                └── PembayaranPusatStatsOverview.php

database/
└── migrations/
    └── 2026_04_05_000001_create_pembayaran_pusat_table.php

tests/
└── Feature/
    └── PembayaranPusatTest.php
```

**Structure Decision**: Follows existing Asset resource pattern with Pages/Schemas/Tables/Widgets organization.

## Complexity Tracking

> No constitution violations - feature follows existing patterns exactly.
