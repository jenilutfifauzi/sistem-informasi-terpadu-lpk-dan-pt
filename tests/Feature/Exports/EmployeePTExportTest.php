<?php

namespace Tests\Feature\Exports;

use App\Enums\DivisiPT;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Filament\Exports\EmployeePTExport;
use App\Models\EmployeePT;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeePTExportTest extends TestCase
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
    public function export_class_generates_correct_headings(): void
    {
        $query = EmployeePT::query();
        $export = new EmployeePTExport($query);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertContains('NIK', $headings);
        $this->assertContains('Nama Lengkap', $headings);
        $this->assertContains('Email', $headings);
        $this->assertContains('Jabatan', $headings);
        $this->assertContains('Divisi', $headings);
        $this->assertContains('Status', $headings);
        $this->assertContains('Jenis Kontrak', $headings);
        $this->assertContains('Tanggal Bergabung', $headings);
        $this->assertContains('Gaji Pokok', $headings);
        $this->assertContains('Tunjangan', $headings);
    }

    /** @test */
    public function export_maps_employee_data_correctly(): void
    {
        $employee = EmployeePT::factory()->create([
            'nama_lengkap' => 'Export Test Employee',
            'email' => 'export@example.com',
            'jabatan' => JabatanPT::StafHRD,
            'divisi' => DivisiPT::HRD,
            'status' => StatusKepegawaian::Aktif,
            'jenis_kontrak' => JenisKontrak::Tetap,
        ]);

        $query = EmployeePT::query()->where('id', $employee->id);
        $export = new EmployeePTExport($query);

        $mapped = $export->map($employee);

        $this->assertIsArray($mapped);
        $this->assertContains('Export Test Employee', $mapped);
        $this->assertContains('export@example.com', $mapped);
    }

    /** @test */
    public function export_enum_values_are_transformed_to_labels(): void
    {
        $employee = EmployeePT::factory()->create([
            'jabatan' => JabatanPT::StafHRD,
            'status' => StatusKepegawaian::Aktif,
        ]);

        $query = EmployeePT::query()->where('id', $employee->id);
        $export = new EmployeePTExport($query);

        $mapped = $export->map($employee);

        // Enum labels should be present, not raw values
        $this->assertContains('Staf HRD', $mapped);
        $this->assertContains('Aktif', $mapped);
    }

    /** @test */
    public function export_returns_correct_number_of_rows(): void
    {
        $existingCount = EmployeePT::query()->count();
        EmployeePT::factory(5)->create();

        $query = EmployeePT::query();
        $export = new EmployeePTExport($query);

        $this->assertEquals($existingCount + 5, $export->query()->count());
    }
}
