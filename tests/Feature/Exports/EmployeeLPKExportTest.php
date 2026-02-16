<?php

namespace Tests\Feature\Exports;

use App\Filament\Exports\EmployeeLPKExport;
use App\Models\EmployeeLPK;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

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
    public function test_export_class_generates_correct_headings()
    {
        $query = EmployeeLPK::query();
        $export = new EmployeeLPKExport($query);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertContains('Nama Lengkap', $headings);
        $this->assertContains('Email', $headings);
        $this->assertContains('Jabatan', $headings);
        $this->assertContains('Status', $headings);
        $this->assertNotContains('NIK', $headings); // NIK should be excluded per FR-009
    }

    /** @test */
    public function test_export_maps_employee_data_correctly()
    {
        $employee = EmployeeLPK::factory()->create([
            'nama_lengkap' => 'Test Employee',
            'email' => 'test@example.com',
        ]);

        $query = EmployeeLPK::query()->where('id', $employee->id);
        $export = new EmployeeLPKExport($query);

        $mapped = $export->map($employee);

        $this->assertIsArray($mapped);
        $this->assertContains('Test Employee', $mapped);
        $this->assertContains('test@example.com', $mapped);
    }

    /** @test */
    public function test_nik_is_excluded_from_export()
    {
        $employee = EmployeeLPK::factory()->create([
            'nik' => '9999999999999999',
            'nama_lengkap' => 'Test Employee NIK',
        ]);

        $query = EmployeeLPK::query()->where('id', $employee->id);
        $export = new EmployeeLPKExport($query);

        $mapped = $export->map($employee);
        $headings = $export->headings();

        // NIK should not be in mapped data or headings
        $this->assertNotContains('9999999999999999', $mapped);
        $this->assertNotContains('NIK', $headings);
    }

    /** @test */
    public function test_enum_values_are_transformed_to_labels()
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => 'Instruktur',
            'status' => 'Aktif',
        ]);

        $query = EmployeeLPK::query()->where('id', $employee->id);
        $export = new EmployeeLPKExport($query);

        $mapped = $export->map($employee);

        // Check that labels are present (not raw enum values)
        $this->assertContains('Instruktur', $mapped);
        $this->assertContains('Aktif', $mapped);
    }

    /** @test */
    public function test_export_respects_query_filters()
    {
        // Create test data with unique NIKs
        $activeEmployee = EmployeeLPK::factory()->create([
            'status' => 'Aktif',
            'nik' => '1111111111111111',
        ]);
        $resignedEmployee = EmployeeLPK::factory()->create([
            'status' => 'Resign',
            'nik' => '2222222222222222',
        ]);

        // Query only active employees
        $query = EmployeeLPK::query()
            ->where('status', 'Aktif')
            ->whereIn('nik', ['1111111111111111', '2222222222222222']);
        $export = new EmployeeLPKExport($query);

        $result = $export->query()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Aktif', $result->first()->status->value);
    }

    /** @test */
    public function test_export_handles_empty_dataset()
    {
        // Create an empty query
        $query = EmployeeLPK::query()->where('id', 0); // No match
        $export = new EmployeeLPKExport($query);

        $result = $export->query()->get();

        $this->assertCount(0, $result);
        $this->assertIsArray($export->headings());
    }

    /** @test */
    public function test_export_includes_honor_fields()
    {
        $employee = EmployeeLPK::factory()->create([
            'honor_pokok' => 5000000,
            'honor_per_jam' => 150000,
        ]);

        $query = EmployeeLPK::query()->where('id', $employee->id);
        $export = new EmployeeLPKExport($query);

        $mapped = $export->map($employee);
        $headings = $export->headings();

        $this->assertContains('Honor Pokok', $headings);
        $this->assertContains('Honor Per Jam', $headings);

        // Honor values should be present in mapped data (may be string or numeric)
        $mappedString = implode(',', $mapped);
        $this->assertStringContainsString('5000000', $mappedString);
        $this->assertStringContainsString('150000', $mappedString);
    }

    /** @test */
    public function test_export_formats_dates_correctly()
    {
        $employee = EmployeeLPK::factory()->create([
            'tanggal_lahir' => '1990-01-15',
            'tanggal_bergabung' => '2020-03-20',
        ]);

        $query = EmployeeLPK::query()->where('id', $employee->id);
        $export = new EmployeeLPKExport($query);

        $mapped = $export->map($employee);

        $this->assertContains('1990-01-15', $mapped);
        $this->assertContains('2020-03-20', $mapped);
    }
}
