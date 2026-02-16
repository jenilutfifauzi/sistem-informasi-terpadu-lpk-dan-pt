# Export Actions Contract

**Feature**: Data Export Functionality  
**Date**: February 16, 2026  
**Version**: 1.0

## Overview

This document defines the contracts (interfaces and behaviors) for export functionality across Filament resources. All export implementations must adhere to these contracts to ensure consistency, security, and maintainability.

## 1. Export Class Contract

All export classes MUST implement the following Laravel Excel interfaces:

### Required Interfaces

```php
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
```

### Interface Implementation Contract

```php
namespace App\Filament\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class {Resource}Export implements FromQuery, WithHeadings, WithMapping
{
    /**
     * The query builder instance with filters applied
     *
     * @var Builder
     */
    protected Builder $query;

    /**
     * Constructor receives filtered query from Filament table
     *
     * @param Builder $query Filtered table query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Return the query to be exported
     * MUST return the filtered query without modification
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Define column headings for export
     * MUST be human-readable, localized if applicable
     * MUST NOT include sensitive field names
     *
     * @return array<string>
     */
    public function headings(): array
    {
        // Return array of column headers
    }

    /**
     * Map model instance to exportable array
     * MUST exclude sensitive fields (see Security Contract)
     * MUST transform enums to labels
     * MUST format dates consistently (Y-m-d or Y-m-d H:i:s)
     *
     * @param mixed $model The model instance being exported
     * @return array
     */
    public function map($model): array
    {
        // Return array of values matching headings order
    }
}
```

## 2. Filament Action Contract

Each resource's table MUST add a header action following this contract:

### Action Configuration

```php
use Filament\Actions\Action;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

public static function table(Table $table): Table
{
    return $table
        ->headerActions([
            Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('format')
                        ->label('Export Format')
                        ->options([
                            'csv' => 'CSV (.csv)',
                            'excel' => 'Excel (.xlsx)',
                        ])
                        ->default('excel')
                        ->required()
                        ->helperText('Choose the file format for export'),
                ])
                ->action(function (Table $table, array $data) {
                    // Export logic (see Action Logic Contract)
                }),
        ])
        // ... other table configuration
}
```

### Action Logic Contract

The action handler MUST:
1. Retrieve filtered table query
2. Instantiate appropriate Export class
3. Generate file using Laravel Excel
4. Log export activity
5. Return download response

```php
->action(function (Table $table, array $data) {
    // Step 1: Get filtered query
    $query = $table->getFilteredTableQuery();
    
    // Step 2: Instantiate export class
    $export = new {Resource}Export($query);
    
    // Step 3: Determine writer type based on format
    $writerType = $data['format'] === 'csv' 
        ? \Maatwebsite\Excel\Excel::CSV 
        : \Maatwebsite\Excel\Excel::XLSX;
    
    // Step 4: Log activity (see Logging Contract)
    activity()
        ->causedBy(auth()->user())
        ->withProperties([
            'export_type' => '{resource_name}',
            'format' => $data['format'],
            'record_count' => $query->count(),
        ])
        ->log('Data exported');
    
    // Step 5: Generate and download
    return Excel::download(
        $export,
        '{resource-name}-' . now()->format('Y-m-d') . '.' . $data['format'],
        $writerType
    );
})
```

## 3. Security Contract

All exports MUST adhere to these security requirements:

### Field Exclusion Rules

**MUST EXCLUDE** the following field types:
- Authentication credentials (passwords, tokens, API keys)
- Personal identification numbers (NIK, KTP, passport, visa numbers)
- Any field marked as `$hidden` in model (if using automatic mapping)

### Specific Exclusions by Resource

| Resource | Excluded Fields |
|----------|----------------|
| EmployeeLPK | `nik` |
| CTK | `nik`, `nomor_paspor`, `visa_number` |
| User | `password`, `remember_token` |
| Asset | None (no sensitive personal data) |

### Permission Enforcement

Exports MUST:
- Use existing Filament resource policies
- Only allow export if user can `viewAny` the resource
- Apply same data filters as table view (no bypassing filters)
- Respect soft-delete settings (only export records user can view)

## 4. Logging Contract

All export actions MUST create activity log entries:

### Required Log Properties

```php
activity()
    ->causedBy(auth()->user())          // REQUIRED: Who performed export
    ->withProperties([                   // REQUIRED: Export details
        'export_type' => string,         // Resource name (employee_lpk, ctk, user, asset)
        'format' => string,              // File format (csv, excel)
        'record_count' => int,           // Number of records exported
        'filters_applied' => array|null, // Optional: Active filters at export time
    ])
    ->log('Data exported');              // REQUIRED: Description
```

### Log Entry Specification

- **Description**: Always "Data exported"
- **Caused By**: Authenticated user performing export
- **Subject**: NULL (export action, not related to specific model instance)
- **Properties**: MUST include at minimum: export_type, format, record_count

## 5. File Naming Contract

All exported files MUST follow this naming convention:

### Format

```
{resource-name}-{date}.{extension}
```

### Components

- **resource-name**: Lowercase, hyphenated resource identifier
  - `karyawan-lpk` (not `employee-lpk` or `EmployeeLPK`)
  - `ctk`
  - `users`
  - `assets`
  
- **date**: ISO 8601 date format (`Y-m-d`)
  - Example: `2026-02-16`
  
- **extension**: File format extension
  - `csv` for CSV format
  - `xlsx` for Excel format

### Examples

```
karyawan-lpk-2026-02-16.xlsx
ctk-2026-02-16.csv
users-2026-02-16.xlsx
assets-2026-02-16.xlsx
```

## 6. Data Transformation Contract

Export mapping MUST transform data as follows:

### Enum Values

```php
// REQUIRED: Transform enum to label
$model->jabatan?->label ?? $model->jabatan

// NOT ALLOWED: Raw enum value
$model->jabatan
```

### Date Formatting

```php
// REQUIRED: Consistent date format
$model->created_at?->format('Y-m-d H:i:s')
$model->date_field?->format('Y-m-d')

// NOT ALLOWED: Raw Carbon instance (inconsistent format)
$model->created_at
```

### Boolean Values

```php
// REQUIRED: Human-readable
$model->is_active ? 'Yes' : 'No'
$model->is_active ? 'Active' : 'Inactive'

// NOT ALLOWED: 0/1 or true/false
$model->is_active
```

### Null Handling

```php
// REQUIRED: Use null coalescing to provide defaults
$model->optional_field ?? '-'
$model->optional_field ?? 'N/A'

// ALLOWED: Empty string if appropriate
$model->optional_field ?? ''
```

### Relationship Data

```php
// REQUIRED: Use safe navigation and appropriate format
$model->relationship?->name ?? '-'
$model->roles->pluck('name')->implode(', ')

// NOT ALLOWED: Object references
$model->relationship
```

## 7. Performance Contract

Export implementations MUST meet these performance requirements:

### Query Efficiency

- Use `FromQuery` interface (NOT `FromCollection`)
- Do NOT use `get()` or `all()` on query
- Let Laravel Excel handle chunking automatically

### Memory Constraints

- Exports MUST handle up to 10,000 records
- MUST NOT load entire dataset into memory
- Use database cursor via `FromQuery`

### Timeout Constraints

- Exports MUST complete within 60 seconds for max dataset size
- If approaching timeout, consider:
  - Reducing eager loaded relationships
  - Simplifying field transformations
  - Implementing queued exports (future enhancement)

## 8. Testing Contract

Each export implementation MUST have test coverage for:

### Required Tests

1. **Format Generation Test**
   ```php
   test('exports employee data as excel')
   test('exports employee data as csv')
   ```

2. **Field Exclusion Test**
   ```php
   test('export excludes sensitive fields')
   ```

3. **Filter Respect Test**
   ```php
   test('export respects table filters')
   ```

4. **Activity Logging Test**
   ```php
   test('export creates activity log entry')
   ```

5. **Permission Test**
   ```php
   test('unauthorized users cannot export')
   ```

6. **Empty Dataset Test**
   ```php
   test('export handles empty dataset gracefully')
   ```

### Test Assertions

- File generated successfully
- File is valid CSV/XLSX format
- Header row matches expected columns
- Data rows match filtered records
- Sensitive fields not present in export
- Activity log entry created with correct properties

## 9. Error Handling Contract

Export actions MUST handle these error scenarios:

### No Records to Export

```php
if ($query->count() === 0) {
    Notification::make()
        ->warning()
        ->title('No data to export')
        ->body('No records match the current filters.')
        ->send();
    return;
}
```

### Export Failure

```php
try {
    return Excel::download(/* ... */);
} catch (\Exception $e) {
    Notification::make()
        ->danger()
        ->title('Export failed')
        ->body('An error occurred while generating the export file.')
        ->send();
    
    Log::error('Export failed', [
        'user_id' => auth()->id(),
        'export_type' => '{resource}',
        'error' => $e->getMessage(),
    ]);
}
```

## 10. Maintenance Contract

Export implementations MUST be maintained according to:

### Model Changes

When source models change:
- Review and update `headings()` if new fields added
- Review and update `map()` if field types change
- Update tests to cover new fields
- Ensure no sensitive fields accidentally included

### Backward Compatibility

Export changes MUST:
- Not break existing exports (add fields to end)
- Maintain column order for existing fields
- Keep field names consistent
- Document any breaking changes

## Summary

This contract ensures:
- ✅ Consistent export behavior across all resources
- ✅ Security through explicit field exclusions
- ✅ Auditability through comprehensive logging
- ✅ Performance through query-based exports
- ✅ Maintainability through standard patterns
- ✅ Testability through clear requirements

All export implementations MUST be reviewed against this contract before merge.
