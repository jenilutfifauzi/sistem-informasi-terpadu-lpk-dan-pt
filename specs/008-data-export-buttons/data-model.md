# Data Model: Data Export Functionality

**Feature**: Data Export Buttons  
**Date**: February 16, 2026  
**Status**: Phase 1 Design

## Overview

This feature adds export functionality to existing Filament resources. **No new database entities or migrations are required**. The feature operates on existing models and uses the existing activity log infrastructure for audit trails.

## Existing Entities (No Changes)

The following models are used for export but require **no schema changes**:

### EmployeeLPK
- **Purpose**: LPK employee records
- **Export Fields**: nama_lengkap, email, jabatan, status, tanggal_bergabung, telepon, alamat, etc.
- **Excluded Fields**: NIK (personal identification number per FR-009)
- **Source**: `app/Models/EmployeeLPK.php`

### CTK (Calon Tenaga Kerja)
- **Purpose**: Candidate worker records
- **Export Fields**: nama_lengkap, email, status, current_stage, screening results, MCU status, etc.
- **Excluded Fields**: NIK, passport number, visa number (personal identification)
- **Relationships**: Will include selected relationship data (screening, MCU) in export
- **Source**: `app/Models/CTK.php`

### User
- **Purpose**: System user accounts
- **Export Fields**: name, email, roles, permissions, created_at, updated_at, etc.
- **Excluded Fields**: password (sensitive authentication data)
- **Source**: `app/Models/User.php`

### Asset
- **Purpose**: PT company asset inventory
- **Export Fields**: asset_number, category, name, condition, assignment_status, purchase_date, etc.
- **Excluded Fields**: None specifically, but will follow general security practices
- **Source**: `app/Models/Asset.php`

## Activity Log Integration

Exports use existing Spatie Activity Log infrastructure:

**Log Entry Structure**:
```php
[
    'log_name' => 'default',
    'description' => 'Data exported',
    'subject_type' => 'export',
    'subject_id' => null,
    'causer_type' => 'App\Models\User',
    'causer_id' => <user_id>,
    'properties' => [
        'export_type' => 'employee_lpk|ctk|user|asset',
        'format' => 'csv|excel',
        'record_count' => <integer>,
        'filters_applied' => <array>,
    ],
    'created_at' => <timestamp>,
]
```

**No database changes needed** - existing `activity_log` table supports this structure.

## Export Classes (New - Not Database Entities)

Export classes are PHP classes, not database models:

### EmployeeLPKExport
```php
namespace App\Filament\Exports;

class EmployeeLPKExport implements FromQuery, WithHeadings, WithMapping
{
    // Transforms EmployeeLPK query results to exportable arrays
}
```

### CTKExport
```php
namespace App\Filament\Exports;

class CTKExport implements FromQuery, WithHeadings, WithMapping
{
    // Transforms CTK query results to exportable arrays
}
```

### UserExport
```php
namespace App\Filament\Exports;

class UserExport implements FromQuery, WithHeadings, WithMapping
{
    // Transforms User query results to exportable arrays
}
```

### AssetExport
```php
namespace App\Filament\Exports;

class AssetExport implements FromQuery, WithHeadings, WithMapping
{
    // Transforms Asset query results to exportable arrays
}
```

## Field Mapping Strategy

Each export class defines explicit field mapping to ensure:
1. Sensitive fields are excluded
2. Enum values are transformed to human-readable labels
3. Relationships are included where relevant
4. Date formats are consistent

### Example: EmployeeLPK Field Mapping

```php
public function headings(): array
{
    return [
        'ID',
        'Nama Lengkap',
        'Email',
        'Telepon',
        'Alamat',
        'Tanggal Lahir',
        'Jenis Kelamin',
        'Jabatan',
        'Status',
        'Tanggal Bergabung',
        'Honor Pokok',
    ];
    // Note: NIK excluded per security requirements
}

public function map($employee): array
{
    return [
        $employee->id,
        $employee->nama_lengkap,
        $employee->email,
        $employee->telepon,
        $employee->alamat,
        $employee->tanggal_lahir?->format('Y-m-d'),
        $employee->jenis_kelamin,
        $employee->jabatan?->label ?? $employee->jabatan,
        $employee->status?->label ?? $employee->status,
        $employee->tanggal_bergabung?->format('Y-m-d'),
        $employee->honor_pokok,
    ];
}
```

### Example: User Field Mapping (Excluding Password)

```php
public function headings(): array
{
    return [
        'ID',
        'Name',
        'Email',
        'Email Verified',
        'Roles',
        'Created At',
        'Updated At',
    ];
    // Note: password excluded automatically
}

public function map($user): array
{
    return [
        $user->id,
        $user->name,
        $user->email,
        $user->email_verified_at ? 'Yes' : 'No',
        $user->roles->pluck('name')->implode(', '),
        $user->created_at?->format('Y-m-d H:i:s'),
        $user->updated_at?->format('Y-m-d H:i:s'),
    ];
}
```

## Data Flow

```
User clicks "Export Data" button
    ↓
Filament Action opens modal (format selection)
    ↓
User selects CSV or Excel
    ↓
Action retrieves filtered table query
    ↓
Query passed to appropriate Export class
    ↓
Export class maps query results to arrays
    ↓
Laravel Excel generates file
    ↓
Activity logged to database
    ↓
File sent to browser for download
```

## Security Considerations

### Field Exclusion Rules

| Resource | Excluded Fields | Reason |
|----------|----------------|--------|
| EmployeeLPK | NIK | Personal identification number (KTP equivalent) |
| CTK | NIK, nomor_paspor, visa_number | Personal identification documents |
| User | password | Authentication credential |
| Asset | None | No sensitive personal data |

### Role-Based Access

- Export actions inherit existing resource view permissions
- If user cannot view a resource's list page, they cannot export
- No new permission gates needed - uses existing Filament policy integration

## Performance Considerations

### Query Optimization

Export classes use `FromQuery` interface which:
- Executes query with cursor (memory efficient)
- Chunks results automatically
- Doesn't load entire dataset into memory

### Expected Data Volumes

| Resource | Expected Max Records | Export Time Estimate |
|----------|---------------------|---------------------|
| EmployeeLPK | ~500 | <5 seconds |
| CTK | ~5,000-10,000 | <60 seconds |
| User | ~50-100 | <2 seconds |
| Asset | ~1,000 | <10 seconds |

All estimates well within spec SC-006 (60 second constraint).

## Backward Compatibility

This feature:
- ✅ Adds no database migrations
- ✅ Changes no existing models
- ✅ Modifies only resource files (adding header actions)
- ✅ Requires no data migration or seeding
- ✅ Fully backward compatible with existing data

## Summary

**Database Changes**: None  
**New Migrations**: None  
**New Models**: None  
**Modified Models**: None  
**New Classes**: 5 (4 Export classes + 1 reusable Action)  
**Modified Resources**: 4 (add headerActions)

This is a presentation-layer feature that reads existing data without modifying database structure or existing application models.
