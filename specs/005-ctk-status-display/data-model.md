# Data Model: CTK Index Status Display Simplification

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Research**: [research.md](research.md)  
**Date**: February 15, 2026

## Overview

This feature makes **NO DATA MODEL CHANGES**. It is purely a display-layer modification to the CTK list table presentation. All required data already exists in the CTK model via attribute accessors.

## Existing Entities (Unchanged)

### CTK (Calon Tenaga Kerja)

**Purpose**: Prospective worker records tracking progression through 15-stage recruitment and placement process

**Database Table**: `ctk` (existing, no schema changes)

**Relevant Existing Fields**:

| Field | Type | Purpose | Used By |
|-------|------|---------|---------|
| `id` | integer | Primary key | All queries |
| `nik` | string | National ID number | Display column (unchanged) |
| `nama_lengkap` | string | Full name | Display column (unchanged) |
| `no_telepon` | string | Phone number | Display column (unchanged) |
| `current_status` | enum (CTKStatus) | Current workflow status | **REPLACED in display** |
| `current_stage` | integer (1-15) | Current stage number | **REMOVED from display** |
| `current_entity` | enum (EntityType) | PT or LPK | Display column (unchanged) |
| `created_at` | timestamp | Creation date | Display column (unchanged) |

**Existing Model Accessors** (no changes):

```php
// app/Models/CTK.php

/**
 * Counts how many of the 15 stages are complete
 * Iterates through stage1_complete...stage15_complete accessors
 */
public function getCompletedStagesCountAttribute(): int

/**
 * Returns "X/15" format (e.g., "8/15")
 * Used by Progress display column
 */
public function getCompletionProgressAttribute(): string

/**
 * Returns 0-100 percentage
 * Used by Progress description
 */
public function getCompletionPercentageAttribute(): int

/**
 * Boolean accessors for each stage's completion status
 * stage1_complete, stage2_complete, ..., stage15_complete
 */
public function getStage{N}CompleteAttribute(): bool
```

## Display Logic

### New Status Column Computation

**Presentation Logic** (lives in `app/Filament/Resources/CTKS/Tables/CTKSTable.php`):

```text
IF completed_stages_count === 15 THEN
    Display: "Lengkap"
    Badge Color: success (green)
    Status: Complete
ELSE
    Display: "Belum Lengkap"
    Badge Color: warning (yellow/orange)
    Status: Incomplete
END IF
```

**Data Source**: `$record->completed_stages_count` (existing accessor)

**No new model methods required** - all data already available.

## Table Column Structure

### Before Changes

| Column | Data Source | Display Type | Retained? |
|--------|-------------|--------------|-----------|
| NIK | `nik` | Text | ✅ Yes |
| Nama Lengkap | `nama_lengkap` | Text | ✅ Yes |
| **Status** | `current_status` | Badge (MCU, Pembayaran, etc.) | ⚠️ **CHANGED** |
| **Tahap** | `current_stage` | Badge ("Stage N") | ❌ **REMOVED** |
| Entitas | `current_entity` | Badge (LPK/PT) | ✅ Yes |
| Progress | `completion_progress` + `completion_percentage` | Badge ("X/15 Z%") | ✅ Yes |
| No. Telepon | `no_telepon` | Text | ✅ Yes |
| Dibuat | `created_at` | DateTime | ✅ Yes |

### After Changes

| Column | Data Source | Display Type | Change |
|--------|-------------|--------------|--------|
| NIK | `nik` | Text | Unchanged |
| Nama Lengkap | `nama_lengkap` | Text | Unchanged |
| **Status** | `completed_stages_count` | Badge (Lengkap/Belum Lengkap) | **NEW LOGIC** |
| Entitas | `current_entity` | Badge (LPK/PT) | Unchanged |
| Progress | `completion_progress` + `completion_percentage` | Badge ("X/15 Z%") | Unchanged |
| No. Telepon | `no_telepon` | Text | Unchanged |
| Dibuat | `created_at` | DateTime | Unchanged |

**Total Columns**: 8 → 7 (removed Tahap)

## State Transitions

**No state transition changes.** CTK lifecycle stages remain:

```
Stage 1 (MCU) → Stage 2 (Pembayaran) → ... → Stage 15 (Terbang)
```

Status display is **purely presentational** - does not affect:
- Stage advancement logic
- Workflow gates
- Permission checks
- Audit logs

## Validation Rules

**No validation changes required.** This feature does not modify:
- Form inputs
- Data mutations
- Business rules

## Relationships

**No relationship changes.** CTK model relationships unchanged:

- `mcuRecords()` → HasMany MCURecord
- `payments()` → HasMany CTKPayment
- `documents()` → HasMany CTKDocument
- `trainings()` → HasMany CTKTraining
- `screenings()` → HasMany CTKScreening
- (etc. - all relationships preserved)

## Indexing & Performance

**No database index changes required.**

Existing indexes sufficient for:
- Sorting by `completed_stages_count` (computed in-memory)
- Entity scoping (`current_entity`)
- Date sorting (`created_at`)

**Note**: If sorting performance becomes an issue with large datasets, consider adding computed column for `completed_stages_count` in future optimization (not required for MVP).

## Migration Summary

**Database Migrations**: None required

**Model Changes**: None required

**Seed Data Changes**: None required

**Data Cleanup**: None required

## Conclusion

This is a **zero-migration feature**. All implementation occurs in the presentation layer (`CTKSTable.php`). Existing CTK model provides all necessary data via attribute accessors. No schema changes, no business logic changes, no audit impact.
