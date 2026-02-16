# Research: Data Export Functionality

**Feature**: Data Export Buttons for Table Resources  
**Date**: February 16, 2026  
**Status**: Phase 0 Complete

## Overview

This document consolidates research findings for implementing data export functionality in Filament v4 resources. The goal is to add CSV and Excel export capabilities to four existing table resources while maintaining security, performance, and audit compliance.

## Research Questions & Findings

### 1. Filament v4 Export Approaches

**Question**: What are the standard approaches for adding export functionality to Filament v4 table resources?

**Finding**: Filament v4 provides multiple approaches for table exports:

1. **Header Actions** - Actions displayed above the table, ideal for "export all visible records"
2. **Bulk Actions** - Actions that operate on selected records only
3. **Custom Actions** - Fully custom export implementations

**Decision**: Use **Header Actions** for this feature because:
- Users want to export all filtered data, not just selected records
- Header actions are more discoverable and align with user expectation of "download data" button
- Simpler implementation than bulk actions for full-dataset exports
- Consistent with Filament admin panel UX patterns

**Implementation Pattern**:
```php
public static function table(Table $table): Table
{
    return $table
        ->headerActions([
            Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('format')
                        ->options([
                            'csv' => 'CSV',
                            'excel' => 'Excel (XLSX)',
                        ])
                        ->default('excel')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Export logic here
                }),
        ])
        // ... rest of table configuration
}
```

---

### 2. Laravel Excel Package

**Question**: Should we use Laravel Excel (maatwebsite/excel) or build custom export logic?

**Finding**: Laravel Excel is the de facto standard for exports in Laravel applications.

**Package**: `maatwebsite/excel` (version 3.1+)
- Official Laravel Excel library
- Supports both CSV and XLSX formats
- Query builder integration (avoids loading all records into memory)
- Automatic header generation
- Compatible with Laravel 11

**Decision**: Use Laravel Excel because:
- Industry standard, well-maintained
- Memory-efficient for large datasets (streaming)
- Built-in support for CSV and Excel formats
- Extensive documentation and community support
- Already handles edge cases (special characters, encoding, etc.)

**Alternatives Considered**:
- **Custom CSV generation** - Rejected: Reinventing the wheel, error-prone for edge cases
- **League/CSV** - Rejected: Only handles CSV, would need separate library for Excel
- **PhpSpreadsheet** (direct) - Rejected: Laravel Excel wraps this with better Laravel integration

---

### 3. Export Class Structure

**Question**: How should export classes be structured for maintainability and reusability?

**Finding**: Laravel Excel supports multiple interfaces. For table exports, two approaches work well:

1. **FromQuery** - Best for large datasets, uses database cursor
2. **FromCollection** - Simpler, but loads all data into memory

**Decision**: Use **FromQuery with WithMapping** pattern:
- Memory efficient for large datasets (up to 10,000 records as per requirements)
- Allows field transformation and selective exclusion
- Supports custom headings
- Query-based, respects table filters automatically

**Implementation Pattern**:
```php
namespace App\Filament\Exports;

use App\Models\EmployeeLPK;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class EmployeeLPKExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Email',
            'Jabatan',
            'Status',
            // ... other fields (excluding sensitive data)
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->nama_lengkap,
            $employee->email,
            $employee->jabatan->label ?? $employee->jabatan,
            $employee->status->label ?? $employee->status,
            // ... transformed fields
        ];
    }
}
```

---

### 4. Sensitive Field Exclusion

**Question**: How should sensitive fields be excluded from exports?

**Finding**: Multiple approaches exist:
1. Exclude in mapping (WithMapping interface)
2. Use $hidden property on models (automatic exclusion with some approaches)
3. Whitelist approach - only export explicitly defined fields

**Decision**: **Explicit mapping with whitelist approach**:
- Map method defines exactly what fields are exported
- No risk of accidentally exporting sensitive data if model changes
- Self-documenting - clear what's exported
- Complies with FR-009 requirement

**Fields to Exclude** (per spec FR-009):
- Password hashes (User model)
- Personal identification numbers: NIK, KTP, passport numbers, visa numbers
- Any fields marked as sensitive in existing models

---

### 5. Export Activity Logging

**Question**: How should export actions be logged for audit compliance?

**Finding**: The application already uses Spatie's Laravel Activity Log package (visible from Filament resources using it).

**Decision**: Log exports using existing activity log infrastructure:
- Log causedBy (user who exported)
- Log subject (export type: EmployeeLPK, CTK, User, Asset)
- Log properties (format, record count, filters applied)
- Log timestamp (automatic)

**Implementation Pattern**:
```php
activity()
    ->causedBy(auth()->user())
    ->withProperties([
        'export_type' => 'employee_lpk',
        'format' => $data['format'],
        'record_count' => $query->count(),
        'filters' => $this->getTableFilters(), // Filament provides this
    ])
    ->log('Data exported');
```

---

### 6. Format Selection UI

**Question**: Should users select format before clicking export, or during export?

**Finding**: Best practices suggest prompting during action execution:
- Reduces UI clutter (one button instead of two)
- Modal provides clear context for the choice
- Follows Filament action pattern conventions

**Decision**: Single "Export Data" button that opens modal with format selection:
- Radio buttons or Select for format choice (CSV vs Excel)
- Default to Excel (most feature-rich, expected by business users)
- Form validation ensures format is selected

---

### 7. Handling Filtered Data

**Question**: How do we ensure exports respect table filters, search, and sorting?

**Finding**: Filament table resources maintain query state that can be accessed in actions.

**Decision**: Pass current table query to export class:
```php
->action(function (Table $table, array $data) {
    $query = $table->getFilteredTableQuery(); // Gets query with all filters applied
    return Excel::download(
        new EmployeeLPKExport($query),
        'karyawan-lpk.' . $data['format'],
        // ... format mapping
    );
})
```

This ensures:
- Search terms are respected
- Filters are applied
- Sorting is maintained (if needed)
- Only visible records are exported

---

### 8. Performance Optimization

**Question**: How do we handle exports of 10,000+ records without timeout?

**Finding**: Laravel Excel provides multiple optimization strategies:
1. **Chunk processing** - Process records in batches
2. **Queue exports** - Run exports in background
3. **Streaming** - Generate file on-the-fly without full memory load

**Decision**: For MVP (Phase 1), use **synchronous exports with chunking**:
- Acceptable for datasets up to 10,000 records (per spec SC-006: <60 seconds)
- Simpler implementation (no queue setup)
- Immediate download (better UX for admin panel)
- Laravel Excel's FromQuery already chunks automatically

**Future Enhancement** (if needed):
- If exports take >30 seconds, upgrade to queued exports with email notification
- Requires Queue configuration and notification system (out of scope for MVP)

---

### 9. File Naming Convention

**Question**: What naming convention should be used for exported files?

**Finding**: Best practices for export filenames:
- Include resource type for clarity
- Include date for versioning
- Use lowercase with hyphens
- Include extension

**Decision**: Format: `{resource}-{date}.{extension}`

Examples:
- `karyawan-lpk-2026-02-16.xlsx`
- `ctk-2026-02-16.csv`
- `users-2026-02-16.xlsx`
- `assets-2026-02-16.csv`

Rationale:
- Date helps users track when export was taken
- Resource name clear even if file moved from downloads
- Lowercase with hyphens follows Laravel/web conventions

---

### 10. Testing Strategy

**Question**: What test coverage is needed for export functionality?

**Finding**: Critical test scenarios based on spec requirements:

**Required Tests** (per user stories):
1. Export generates file in correct format
2. Export includes all filtered records (not all DB records)
3. Export excludes sensitive fields
4. Export logs activity properly
5. Export respects RBAC (users can only export data they can view)
6. Export handles empty datasets gracefully
7. Export handles large datasets (performance test with 1000+ records)

**Decision**: Create feature tests for each resource:
- Test CSV export generates valid CSV
- Test Excel export generates valid XLSX
- Test filtered exports only include filtered records
- Test sensitive field exclusion
- Test activity logging
- Test permissions (use existing test users with different roles)

---

## Technology Stack Summary

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| Export Package | maatwebsite/excel | 3.1+ | Generate CSV/Excel files |
| Export Interface | FromQuery + WithMapping | - | Memory-efficient query-based exports |
| Action Type | Filament HeaderAction | - | Table-level export button |
| Format Selection | Filament Action Form | - | User chooses CSV or Excel |
| Logging | Spatie Activity Log | existing | Export audit trail |
| File Storage | Direct browser download | - | No server storage needed |
| Testing | PHPUnit Feature Tests | 10.x | Export functionality validation |

## Implementation Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Large exports timeout | High | Use FromQuery (chunked), set longer timeout for export route if needed |
| Sensitive data leaked | Critical | Explicit field mapping, exclude sensitive fields by default, code review |
| Missing Laravel Excel | Medium | Check composer.json, install if needed, document in quickstart |
| Export breaks with model changes | Medium | Use explicit field mapping (not toArray()), add tests |
| Excel format issues on Mac/Windows | Low | Laravel Excel handles cross-platform compatibility |
| Memory exhaustion | Medium | FromQuery interface uses database cursor, doesn't load all records |

## Recommended Implementation Order

Based on spec priorities and dependencies:

**Phase 1 - P1 Resources** (Most critical):
1. Karyawan LPK (EmployeeLPKResource) - Simpler model, fewer relationships
2. CTK (CTKResource) - More complex, multiple relationships

**Phase 2 - P2/P3 Resources**:
3. Users (UserResource) - Medium priority, simple model
4. Assets (AssetResource) - Lower priority, simple model

**Rationale**: Implement P1 resources first to deliver value quickly. Patterns established with Karyawan LPK can be replicated for other resources.

## Open Questions

None - all clarifications from spec resolved.
