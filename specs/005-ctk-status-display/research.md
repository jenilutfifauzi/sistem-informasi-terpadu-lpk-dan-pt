# Research: CTK Index Status Display Simplification

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)  
**Date**: February 15, 2026  
**Purpose**: Document technical decisions and research findings for implementing simplified CTK status display

## Research Questions Resolved

### 1. How to determine "Lengkap" vs "Belum Lengkap" status?

**Decision**: Use existing `completed_stages_count` accessor on CTK model

**Rationale**: 
- The CTK model already implements `getCompletedStagesCountAttribute()` that iterates through all 15 stage completion checks
- Logic: `completed_stages_count === 15` → "Lengkap", otherwise "Belum Lengkap"
- No new calculation logic needed, leveraging existing battle-tested code
- Maintains consistency with Progress column which also uses `completed_stages_count`

**Alternatives considered**:
- **Create new database column**: Rejected - adds complexity, requires migration, introduces potential sync issues
- **Check current_stage === 15**: Rejected - stage number doesn't guarantee all previous stages are complete
- **Direct database query in table**: Rejected - inefficient, model already provides this

**Code Reference**:
```php
// app/Models/CTK.php lines 360-373
public function getCompletedStagesCountAttribute(): int
{
    $count = 0;
    for ($i = 1; $i <= 15; $i++) {
        $attribute = "stage{$i}_complete";
        if ($this->$attribute) {
            $count++;
        }
    }
    return $count;
}
```

---

### 2. Best practices for Filament 4 table column display

**Decision**: Use `TextColumn::make()` with `formatStateUsing()` callback for conditional text display

**Rationale**:
- Filament 4 best practice for computed display values
- Supports badge styling for visual distinction
- Sortable via underlying `completed_stages_count` relationship
- Consistent with existing table column patterns in CTKSTable.php

**Pattern to use**:
```php
TextColumn::make('completion_status')
    ->label('Status')
    ->badge()
    ->formatStateUsing(fn ($record) => 
        $record->completed_stages_count === 15 ? 'Lengkap' : 'Belum Lengkap'
    )
    ->color(fn ($record) => 
        $record->completed_stages_count === 15 ? 'success' : 'warning'
    )
    ->sortable(query: function (Builder $query, string $direction): Builder {
        // Sort by completed stages count for logical ordering
        return $query->withCount(...)->orderBy('completed_stages_count', $direction);
    })
```

**Alternatives considered**:
- **Database accessor on model**: Rejected - keeps computation in presentation layer where it belongs
- **Custom column component**: Rejected - overcomplicated for simple text display
- **Remove badge styling**: Rejected - badges improve scanability and visual hierarchy

---

### 3. Impact on existing filters and exports

**Decision**: Remove Tahap filter, verify Status filter behavior, ensure exports reflect new columns

**Rationale**:
- Current filters in CTKSTable.php include `SelectFilter::make('current_stage')` which must be removed
- Status filter needs no changes (filters on different field)
- Export behavior should automatically reflect table column changes (Filament default)
- Progress filter can serve as replacement for granular stage filtering

**Testing requirements**:
- Verify filter panel no longer shows Tahap options
- Test export includes "Lengkap/Belum Lengkap" status
- Confirm no broken filter references in JS/Livewire

---

### 4. Testing strategy for display-layer changes

**Decision**: Create feature test that verifies table rendering with different CTK completion states

**Rationale**:
- Display changes are testable via Livewire table assertions
- Need to verify both "Lengkap" (15/15) and "Belum Lengkap" (<15/15) cases
- Should test absence of Tahap column
- PHPUnit with database transactions (per constitution)

**Test structure**:
```php
// tests/Feature/CTKTableDisplayTest.php
public function test_displays_lengkap_status_when_all_stages_complete()
{
    // Create CTK with 15/15 stages complete
    // Assert table shows "Lengkap"
}

public function test_displays_belum_lengkap_status_when_stages_incomplete()
{
    // Create CTK with < 15 stages complete
    // Assert table shows "Belum Lengkap"
}

public function test_tahap_column_not_displayed()
{
    // Assert table does not contain Tahap column
}

public function test_progress_column_remains_functional()
{
    // Verify Progress shows "X/15 Z%" format
}
```

---

### 5. Accessibility and localization considerations

**Decision**: Use plain Indonesian text labels without translation keys (matches existing pattern)

**Rationale**:
- Current CTKSTable.php uses hardcoded Indonesian labels: `label('NIK')`, `label('Nama Lengkap')`
- System is monolingual (Indonesian only, per constitution context)
- "Lengkap" and "Belum Lengkap" are clear, unambiguous terms
- No internationalization requirement in PRD

**Alternatives considered**:
- **Add Laravel translation keys**: Rejected - introduces unnecessary complexity for monolingual app
- **Use icons only**: Rejected - reduces clarity for non-technical users
- **Use English labels**: Rejected - inconsistent with existing UI language

---

## Technology Best Practices

### Filament 4 Table Columns

**Key patterns identified from `app/Filament/Resources/CTKS/Tables/CTKSTable.php`:**

1. **Column ordering matters**: Status should appear after "Nama Lengkap" for visual hierarchy
2. **Badge styling conventions**: Use `badge()` for categorical data with color coding
3. **Sortable columns**: Important for filtering large datasets
4. **Toggleable columns**: `toggleable()` for optional fields like phone numbers
5. **Custom sort queries**: Use when sorting logic differs from column value

### Laravel Model Accessors (Already Implemented)

- CTK model uses attribute accessors (`getXXXAttribute()`) for computed properties
- Accessors are cached per model instance (performance-efficient)
- Follow convention: `completion_progress`, `completion_percentage`, `completed_stages_count`

### Testing Display Logic

- Livewire component testing for Filament resources: `Livewire::test(ListCTKS::class)`
- Use `assertCanSeeTableRecords()` and `assertTableColumnExists()`
- Database transactions for cleanup (not RefreshDatabase per constitution)

---

## Dependencies

**No new dependencies required.**

Existing stack sufficient:
- **filament/filament** v4 - Table column components
- **livewire/livewire** v3 - Reactive UI (underlying Filament)
- **phpunit/phpunit** v10 - Testing framework

---

## Performance Considerations

### Current Performance Baseline

- CTK list page loads with entity-scoped query (LPK: stages 1-5, PT: stages 6-15)
- Progress column already calculates `completed_stages_count` per row
- No additional database queries introduced by status change

### Performance Impact Assessment

**✅ No degradation expected**

- Status column uses same `completed_stages_count` already computed for Progress
- Removing Tahap column reduces DOM elements (minor improvement)
- No N+1 queries introduced
- Sorting by completion status uses same underlying data

**Optimization opportunities** (not required for MVP):
- Could add database column for `completed_stages_count` if sorting performance becomes issue
- Could cache completion status in Redis for very large datasets
- Current accessor-based approach appropriate for stated scale

---

## Risk Assessment

### Low Risk Items (No Mitigation Needed)

- ✅ No database schema changes
- ✅ No permission/RBAC changes
- ✅ Simple display-layer modification
- ✅ Existing model logic reused

### Medium Risk Items (Mitigated)

- ⚠️ **Export functionality**: Mitigated by testing exports in feature tests
- ⚠️ **Filter breakage**: Mitigated by explicitly removing Tahap filter code
- ⚠️ **User adaptation**: Mitigated by clear status labels ("Lengkap/Belum Lengkap")

### Zero Risk (Not Applicable)

- Data loss: No data deleted or modified
- Security: No access control changes
- Compliance: No audit log impact

---

## Conclusion

All research questions resolved. No technical blockers identified. Existing CTK model accessors provide all necessary data. Filament 4 patterns well-established in codebase. Ready to proceed to Phase 1: Design & Contracts.

**Key Findings Summary**:
1. Use `completed_stages_count === 15` for "Lengkap" determination
2. Implement via `TextColumn` with `formatStateUsing()` callback
3. Remove Tahap column and associated filter
4. Test via Livewire component assertions
5. No new dependencies or performance concerns
