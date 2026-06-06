<?php

namespace Tests\Feature\Exports;

use App\Enums\StatusKepegawaian;
use App\Filament\Exports\EmployeePTExport;
use App\Models\EmployeePT;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    public function test_it_generates_correct_headings(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query());

        $this->assertSame([
            'ID',
            'NIK',
            'Nama Lengkap',
            'Email',
            'Jabatan',
            'Divisi',
            'Status',
            'Jenis Kontrak',
            'Tanggal Bergabung',
            'Gaji Pokok',
            'Tunjangan',
            'Tanggal Dibuat',
        ], $exporter->headings());
    }

    public function test_it_maps_employee_data_correctly(): void
    {
        $employee = EmployeePT::factory()->create([
            'email' => 'employee-pt-test@example.test',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Karyawan PT Test',
            'gaji_pokok' => 7000000,
            'tunjangan' => 1000000,
        ]);

        $exporter = new EmployeePTExport(EmployeePT::query()->whereKey($employee->id));
        $mapped = $exporter->map($employee->fresh());

        $this->assertSame($employee->id, $mapped[0]);
        $this->assertSame('1234567890123456', $mapped[1]);
        $this->assertSame('Karyawan PT Test', $mapped[2]);
        $this->assertSame('employee-pt-test@example.test', $mapped[3]);
        $this->assertSame('Rp 7.000.000', $mapped[9]);
        $this->assertSame('Rp 1.000.000', $mapped[10]);
    }

    public function test_it_keeps_zero_salary_visible_in_export(): void
    {
        $employee = EmployeePT::factory()->create([
            'email' => 'pt-zero-salary@example.test',
            'gaji_pokok' => 0,
            'tunjangan' => 0,
        ]);

        $exporter = new EmployeePTExport(EmployeePT::query()->whereKey($employee->id));
        $mapped = $exporter->map($employee->fresh());

        $this->assertSame('Rp 0', $mapped[9]);
        $this->assertSame('Rp 0', $mapped[10]);
    }

    public function test_it_respects_query_filters(): void
    {
        $included = EmployeePT::factory()->create([
            'email' => 'included-pt@example.test',
            'status' => StatusKepegawaian::Aktif,
        ]);
        EmployeePT::factory()->resign()->create([
            'email' => 'excluded-pt@example.test',
        ]);

        $exporter = new EmployeePTExport(
            EmployeePT::query()->whereKey([$included->id])
        );

        $results = $exporter->query()->get();

        $this->assertCount(1, $results);
        $this->assertSame($included->id, $results->first()->id);
    }

    public function test_it_handles_empty_datasets(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    public function test_it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    public function test_it_provides_styled_header_configuration(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    public function test_it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    public function test_it_defines_readable_column_widths(): void
    {
        $exporter = new EmployeePTExport(EmployeePT::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(20, $columnWidths['B']);
        $this->assertSame(28, $columnWidths['C']);
        $this->assertSame(18, $columnWidths['J']);
    }
}
