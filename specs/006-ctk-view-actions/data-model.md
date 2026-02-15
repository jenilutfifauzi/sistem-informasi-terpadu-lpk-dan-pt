# Phase 1: Data Model - CTK Index Action Buttons

**Feature**: CTK Index Action Buttons  
**Date**: February 15, 2026  
**Purpose**: Document data flow and interactions for table actions (no new database entities)

## Overview

This feature adds UI actions to the existing CTK index table. **No database schema changes are required.** All data interactions use existing CTK model and related tables.

## Existing Data Model

### CTK Entity (Existing)

**Table**: `ctks`  
**Model**: `App\Models\CTK`

**Relevant Fields for Actions**:
- `id` (PK) - CTK identifier
- `nik` - National identification number
- `nama_lengkap` - Full name
- `current_entity` (ENUM: LPK, PT) - Current owning entity
- `current_stage` (INT 1-15) - Current processing stage
- `completed_stages_count` (computed) - Count of completed stages
- `mcu_status` (ENUM) - Medical checkup status (relevant for stage 1)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete timestamp

**Computed Attributes** (used in table display):
- `completion_progress` - Format: "X/15" where X = completed stages
- `completion_percentage` - Format: "Y%" where Y = (X/15) * 100

**Key Relationships**:
- `payments()` - hasMany Payment - Required for stage validation
- `documents()` - hasMany Document - Required for stage validation
- `activityLogs()` - morphMany Activity - Audit trail

### User & Roles (Existing)

**Table**: `users`  
**Model**: `App\Models\User`

**Relevant Fields**:
- `id` (PK)
- `name`
- `email`
- `entity` (ENUM: LPK, PT) - User's entity affiliation

**Roles** (via Spatie Permission):
- `super_admin` - Full access to all CTKs
- `Admin LPK` - LPK stages (1-5)
- `Admin PT` - PT stages (6-15)
- `HR PT` - PT stages (6-15)
- `Legal PT` - PT stages (6-15)
- `Keuangan PT` - PT stages (6-15)
- `Keuangan LPK` - LPK stages (view only)
- `Pimpinan` - All stages (view only, no edits)

### Activity Log (Existing)

**Table**: `activity_log`  
**Package**: Spatie Laravel Activity Log

**Fields**:
- `id` (PK)
- `log_name` - Activity category
- `description` - Human-readable action description
- `subject_type`, `subject_id` - Polymorphic relation to CTK
- `causer_type`, `causer_id` - Polymorphic relation to User
- `properties` (JSON) - Stores old/new values
- `created_at` - When action occurred

## Action Data Flow

### View Action Flow

```
User clicks "View" button
  ↓
Filament ViewAction::make()
  ↓
Navigate to CTKResource::getUrl('view', ['record' => $record])
  ↓
Load ViewCTK page (app/Filament/Resources/CTKS/Pages/ViewCTK.php)
  ↓
Display CTK details (read-only)
```

**Data Read**: CTK record + related payments + related documents  
**Data Write**: None  
**Authorization**: Checked by `getEloquentQuery()` scoping in CTKResource

---

### Kelola Progress Action Flow

```
User clicks "Kelola Progress" button (if visible)
  ↓
Action->visible() callback checks:
  - User role (Admin LPK / Admin PT / etc.)
  - CTK current_entity matches user entity
  - CTK current_stage within user's allowed range
  ↓ (if visible)
Modal opens with form components
  ↓
User fills form:
  - Select new stage
  - Fill stage-specific fields (MCU status, etc.)
  - Add optional notes
  ↓
User submits modal
  ↓
FormRequest validation (automatic)
  ↓
Action->action() callback executes:
  1. Load CTK record
  2. Validate business rules via CTK::canAdvanceToStage()
  3. If invalid → show error notification, abort
  4. If valid:
     a. Update CTK record (current_stage, stage-specific fields)
     b. Log activity to activity_log table
     c. Show success notification
  ↓
Livewire auto-refreshes table
  ↓
Table shows updated stage, progress, status
```

**Data Read**: 
- CTK record (current_stage, current_entity, etc.)
- Related payments (for validation)
- Related documents (for validation)

**Data Write**:
- `ctks` table: Update `current_stage`, stage-specific fields (mcu_status, etc.)
- `activity_log` table: Insert audit log entry

**Authorization**: 
- `visible()` callback: Role + entity + stage range check
- `action()` callback: Re-validate authorization (defense in depth)
- Business rules: `CTK::canAdvanceToStage()` validation

---

### Bulk Kelola Progress Action Flow

```
User selects multiple CTK checkboxes
  ↓
Bulk actions toolbar appears
  ↓
User clicks "Kelola Progress" bulk action
  ↓
Modal opens with simplified form (stage selection only)
  ↓
User submits modal
  ↓
BulkAction->action() callback executes:
  Foreach record in selection:
    1. Validate business rules (canAdvanceToStage)
    2. If valid: update record + log activity
    3. If invalid: skip, increment failure count
  ↓
Show summary notification (X succeeded, Y failed)
  ↓
Livewire auto-refreshes table
```

**Data Read**: Collection of CTK records + related data for validation  
**Data Write**: Multiple CTK updates + multiple activity log entries  
**Authorization**: Same as single action, but applied to each record individually

---

## Stage Validation Rules

### Business Rule Matrix

| Current Stage | Can Advance To | Prerequisites Required |
|---------------|----------------|------------------------|
| 1 (MCU) | 2 (Pembayaran 1) | MCU status = FIT |
| 2 (Pembayaran 1) | 3 (Soal/Berkas) | Payment 1 complete |
| 3 (Soal/Berkas) | 4 (Pembayaran 2) | Documents uploaded |
| 4 (Pembayaran 2) | 5 (Belajar di LPK) | Payment 2 complete |
| 5 (Belajar LPK) | 6 (Screening 1) | Training complete + Entity transfer to PT |
| 6 (Screening 1) | 7 (Interview User) | Screening 1 = Lolos |
| 7 (Interview) | 8 (Ijin Desa) | Interview = Lolos |
| ... | ... | ... |
| 14 (Medical Full) | 15 (Terbang) | Medical full complete + OPP received |
| 15 (Terbang) | IMMUTABLE | Cannot update final stage |

**Validation Implementation**:
```php
// app/Models/CTK.php
public function canAdvanceToStage(int $targetStage): bool
{
    // Cannot edit final stage
    if ($this->current_stage === 15) {
        return false;
    }
    
    // Cannot go backward or skip stages
    if ($targetStage <= $this->current_stage || $targetStage > $this->current_stage + 1) {
        return false;
    }
    
    // Stage-specific validation
    return match ($this->current_stage) {
        1 => $this->mcu_status === MCUStatus::FIT,
        2 => $this->payments()->where('stage', 1)->where('status', 'Lunas')->exists(),
        3 => $this->documents()->where('type', 'soal_berkas')->exists(),
        4 => $this->payments()->where('stage', 2)->where('status', 'Lunas')->exists(),
        5 => $this->training_completed_at !== null,
        // ... etc for all stages
        default => true,
    };
}
```

## Data Integrity Guarantees

### Single Source of Truth (Constitution I)
- ✅ All updates go through CTK model
- ✅ No duplicate records created
- ✅ Stage transitions are atomic (database transaction)

### Multi-Entity Isolation (Constitution II)
- ✅ Action visibility respects entity boundaries
- ✅ Admin LPK cannot update PT stages (UI hidden + server validation)
- ✅ Entity transfers logged in activity_log

### Auditability (Constitution IV)
- ✅ Every stage update logged to activity_log with:
  - `causer_id` - Who made the change
  - `subject_id` - Which CTK was changed
  - `properties` - Old stage → New stage
  - `created_at` - When change occurred
- ✅ Immutable final stage (Terbang) enforced in `canAdvanceToStage()`

### RBAC (Constitution III)
- ✅ Action visibility controlled by role-based `visible()` callback
- ✅ Authorization enforced at multiple layers:
  1. Table query scoping (`getEloquentQuery()`)
  2. Action visibility (`visible()`)
  3. Action execution (re-check in `action()`)
- ✅ Least privilege: Users only see/modify stages within their entity/role scope

## No Schema Changes Required

**Migration Files**: NONE  
**Model Changes**: Add `canAdvanceToStage(int $targetStage): bool` method (business logic, not schema)

**Rationale**: All data needed for actions already exists in CTK table and related tables. This is purely a UI enhancement leveraging existing data model.

## rollback Strategy

If feature needs to be rolled back:
1. Remove `->actions()` from `CTKSTable.php`
2. Delete custom action class (if created)
3. No database rollback needed (no schema changes)
4. Activity logs remain for audit trail (intentionally kept)

**Impact**: Users revert to clicking row for navigation, no direct progress management from index.
