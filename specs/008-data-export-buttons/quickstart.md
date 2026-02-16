# Quick Start Guide: Data Export Implementation

**Feature**: Data Export Buttons  
**Date**: February 16, 2026  
**For**: Developers implementing export functionality

## Prerequisites

Before implementing export functionality, ensure:

- [x] You have read [spec.md](spec.md) for business requirements
- [x] You have read [research.md](research.md) for technical approach
- [x] You have reviewed [contracts/export-actions.md](contracts/export-actions.md)
- [x] You have access to a development environment
- [x] You can run `php artisan test` successfully

## Step 1: Install Laravel Excel

### Check if Already Installed

```bash
composer show maatwebsite/excel
```

If not installed or version is < 3.1:

```bash
composer require "maatwebsite/excel:^3.1"
```

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## Step 2: Create Export Directory Structure

```bash
mkdir -p app/Filament/Exports
mkdir -p tests/Feature/Exports
```

## Step 3: Implementation Order (Follow Priorities)

Implement resources in this order per spec priorities:

1. **Karyawan LPK** (P1) - Simplest, establishes pattern
2. **CTK** (P1) - More complex, but still P1
3. **Users** (P2) - Medium priority
4. **Assets** (P3) - Lower priority

## Step 4: Implement One Resource (Example: Karyawan LPK)

### 4a. Create Export Class

Create `app/Filament/Exports/EmployeeLPKExport.php`:

```php
<?php

namespace App\Filament\Exports;

use App\Models\EmployeeLPK;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeLPKExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

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
        // Note: NIK excluded per security requirements (FR-009)
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
}
```

### 4b. Add Export Action to Resource

Edit `app/Filament/Resources/EmployeeLPKResource.php`:

Find the `table()` method and add header action:

```php
use Filament\Actions\Action;
use Filament\Forms;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Exports\EmployeeLPKExport;

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
                    $query = $table->getFilteredTableQuery();
                    
                    // Check for empty dataset
                    if ($query->count() === 0) {
                        Notification::make()
                            ->warning()
                            ->title('No data to export')
                            ->body('No records match the current filters.')
                            ->send();
                        return;
                    }
                    
                    $export = new EmployeeLPKExport($query);
                    
                    $writerType = $data['format'] === 'csv' 
                        ? \Maatwebsite\Excel\Excel::CSV 
                        : \Maatwebsite\Excel\Excel::XLSX;
                    
                    // Log export activity
                    activity()
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'export_type' => 'employee_lpk',
                            'format' => $data['format'],
                            'record_count' => $query->count(),
                        ])
                        ->log('Data exported');
                    
                    return Excel::download(
                        $export,
                        'karyawan-lpk-' . now()->format('Y-m-d') . '.' . $data['format'],
                        $writerType
                    );
                }),
        ])
        ->columns([
            // ... existing columns
        ])
        // ... rest of table configuration
}
```

### 4c. Add Required Imports

Make sure these imports are at the top of the resource file:

```php
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Exports\EmployeeLPKExport;
```

## Step 5: Create Tests

Create `tests/Feature/Exports/EmployeeLPKExportTest.php`:

```php
<?php

namespace Tests\Feature\Exports;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmployeeLPK;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use App\Filament\Resources\EmployeeLPKResource\Pages\ListEmployeeLPKS;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Exports\EmployeeLPKExport;

class EmployeeLPKExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function authenticated_user_can_export_employee_data_as_excel()
    {
        $user = User::factory()->create();
        $employees = EmployeeLPK::factory()->count(5)->create();

        Excel::fake();

        $this->actingAs($user);

        Livewire::test(ListEmployeeLPKS::class)
            ->callTableHeaderAction('export', data: ['format' => 'excel']);

        Excel::assertDownloaded(
            fn ($filename) => str_starts_with($filename, 'karyawan-lpk-'),
            function (EmployeeLPKExport $export) {
                return true;
            }
        );
    }

    /** @test */
    public function export_excludes_sensitive_fields()
    {
        $user = User::factory()->create();
        $employee = EmployeeLPK::factory()->create(['nik' => '1234567890123456']);

        $this->actingAs($user);

        $export = new EmployeeLPKExport(EmployeeLPK::query());
        $headings = $export->headings();

        $this->assertNotContains('NIK', $headings);
        $this->assertNotContains('nik', $headings);
    }

    /** @test */
    public function export_creates_activity_log_entry()
    {
        $user = User::factory()->create();
        EmployeeLPK::factory()->count(3)->create();

        Excel::fake();

        $this->actingAs($user);

        Livewire::test(ListEmployeeLPKS::class)
            ->callTableHeaderAction('export', data: ['format' => 'excel']);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'description' => 'Data exported',
        ]);
    }
}
```

## Step 6: Run Tests

```bash
# Run just the new export tests
php artisan test --filter=EmployeeLPKExport

# Run all tests to ensure no regressions
php artisan test --compact
```

## Step 7: Manual Testing

1. **Login to application**: Navigate to Karyawan LPK list page
2. **Verify button visible**: Look for "Export Data" button in table header
3. **Click export**: Should open modal with format selection
4. **Select format**: Choose CSV or Excel
5. **Verify download**: File should download with correct name format
6. **Verify content**: Open file, check:
   - Headers are present and readable
   - Data matches table
   - NIK field is NOT present
   - Enum values show labels (not raw values)
7. **Test with filters**: Apply table filters, verify export only includes filtered records
8. **Check activity log**: Navigate to activity log (if UI available) and verify export logged

## Step 8: Code Formatting

Before committing:

```bash
vendor/bin/pint
```

## Step 9: Repeat for Other Resources

Repeat Steps 4-8 for:
- CTK (CTKExport, CTKResource)
- Users (UserExport, UserResource)
- Assets (AssetExport, AssetResource)

Use the same pattern, adjusting:
- Class names
- Field mappings (see [contracts/export-actions.md](contracts/export-actions.md))
- Excluded sensitive fields
- Test factories

## Common Issues & Solutions

### Issue: "Class 'Excel' not found"

**Solution**: Add import: `use Maatwebsite\Excel\Facades\Excel;`

### Issue: "Call to undefined method getFilteredTableQuery()"

**Solution**: Ensure using latest Filament v4. The method exists in Filament's Table class.

### Issue: "Memory exhausted when exporting large dataset"

**Solution**: Ensure using `FromQuery` interface, not `FromCollection`. FromQuery uses database cursors.

### Issue: "Export includes all records, not filtered ones"

**Solution**: Make sure you're using `$table->getFilteredTableQuery()`, not `Model::query()`.

### Issue: "Enum values showing as objects or empty"

**Solution**: Use `$model->enum_field?->label ?? $model->enum_field` in map() method.

### Issue: "Tests fail with 'Excel not downloaded'"

**Solution**: Add `Excel::fake()` before the test action, and use `Excel::assertDownloaded()`.

## Tips for Success

1. **Start simple**: Implement Karyawan LPK first (simplest model)
2. **Test frequently**: Run tests after each change
3. **Follow contract**: Reference [contracts/export-actions.md](contracts/export-actions.md) for all requirements
4. **Review security**: Double-check excluded fields match FR-009
5. **Check activity logs**: Verify logging works in database after each export
6. **Test with real data**: Use seeded data to test exports with variety of field values

## Next Steps

After completing export implementation:

1. Update AGENTS.md if new patterns established
2. Document any deviations from this guide
3. Create PR following repo conventions
4. Request code review focusing on security (field exclusions)

## Reference Files

- **Specification**: [spec.md](spec.md)
- **Research**: [research.md](research.md)
- **Data Model**: [data-model.md](data-model.md)
- **Contracts**: [contracts/export-actions.md](contracts/export-actions.md)
- **Laravel Excel Docs**: https://docs.laravel-excel.com/
- **Filament Actions Docs**: https://filamentphp.com/docs/4.x/actions/overview

## Help & Support

If stuck:
1. Review error messages carefully
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database for activity logs
4. Re-read contracts and research docs
5. Test in isolation (create minimal test case)

Good luck! 🚀
