<?php

namespace Tests\Feature\Exports;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Filament\Exports\EmployeeLPKExport;
use App\Models\EmployeeLPK;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    public function test_it_generates_correct_headings(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query());

        $this->assertSame([
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
            'Honor Per Jam',
        ], $exporter->headings());
    }

    public function test_it_maps_employee_data_correctly(): void
    {
        $employee = EmployeeLPK::factory()->instruktur()->create([
            'nama_lengkap' => 'Instruktur LPK Test',
            'email' => 'instruktur-lpk-test@example.test',
            'telepon' => '08123456789',
            'alamat' => 'Jl. LPK No. 1',
            'jenis_kelamin' => 'Laki-laki',
            'status' => StatusKepegawaian::Aktif,
            'honor_pokok' => 4500000,
            'honor_per_jam' => 150000,
        ]);

        $exporter = new EmployeeLPKExport(EmployeeLPK::query()->whereKey($employee->id));
        $mapped = $exporter->map($employee->fresh());

        $this->assertSame($employee->id, $mapped[0]);
        $this->assertSame('Instruktur LPK Test', $mapped[1]);
        $this->assertSame('instruktur-lpk-test@example.test', $mapped[2]);
        $this->assertSame('08123456789', $mapped[3]);
        $this->assertSame('Jl. LPK No. 1', $mapped[4]);
        $this->assertSame('Laki-laki', $mapped[6]);
        $this->assertSame(JabatanLPK::Instruktur->getLabel(), $mapped[7]);
        $this->assertSame(StatusKepegawaian::Aktif->getLabel(), $mapped[8]);
        $this->assertSame('Rp 4.500.000', $mapped[10]);
        $this->assertSame('Rp 150.000', $mapped[11]);
    }

    public function test_it_keeps_zero_honor_visible_in_export(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'email' => 'lpk-zero-honor@example.test',
            'honor_pokok' => 0,
            'honor_per_jam' => 0,
        ]);

        $exporter = new EmployeeLPKExport(EmployeeLPK::query()->whereKey($employee->id));
        $mapped = $exporter->map($employee->fresh());

        $this->assertSame('Rp 0', $mapped[10]);
        $this->assertSame('Rp 0', $mapped[11]);
    }

    public function test_it_respects_query_filters(): void
    {
        $included = EmployeeLPK::factory()->create([
            'email' => 'included-lpk@example.test',
            'status' => StatusKepegawaian::Aktif,
        ]);
        EmployeeLPK::factory()->resign()->create([
            'email' => 'excluded-lpk@example.test',
        ]);

        $exporter = new EmployeeLPKExport(
            EmployeeLPK::query()->whereKey([$included->id])
        );

        $results = $exporter->query()->get();

        $this->assertCount(1, $results);
        $this->assertSame($included->id, $results->first()->id);
    }

    public function test_it_handles_empty_datasets(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    public function test_it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    public function test_it_provides_styled_header_configuration(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    public function test_it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    public function test_it_defines_readable_column_widths(): void
    {
        $exporter = new EmployeeLPKExport(EmployeeLPK::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['B']);
        $this->assertSame(40, $columnWidths['E']);
        $this->assertSame(18, $columnWidths['K']);
    }
}
