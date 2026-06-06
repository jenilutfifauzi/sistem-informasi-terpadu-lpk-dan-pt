<?php

namespace Tests\Feature\Exports;

use App\Filament\Exports\SiswaLPKExport;
use App\Models\SiswaLPK;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class SiswaLPKExportTest extends TestCase
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
        $exporter = new SiswaLPKExport(SiswaLPK::query());

        $this->assertSame([
            'No. Urut',
            'Nomor Induk',
            'Nama Siswa',
            'Jenis Kelamin',
            'No. HP',
            'Email',
            'Program Pendidikan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Tanggal Masuk',
            'Alamat',
            'Agama',
            'Pendidikan Terakhir',
        ], $exporter->headings());
    }

    public function test_it_maps_student_data_correctly(): void
    {
        $student = SiswaLPK::factory()->create([
            'nomor_urut' => 7,
            'nomor_induk' => '00007',
            'nama_siswa' => 'Siswa LPK Export Test',
            'email' => 'siswa-lpk-export@example.test',
            'program_pendidikan' => 'Bahasa Jepang',
            'alamat' => 'Jl. Siswa LPK No. 7',
        ]);

        $exporter = new SiswaLPKExport(SiswaLPK::query()->whereKey($student->id));
        $mapped = $exporter->map($student->fresh());

        $this->assertSame(7, $mapped[0]);
        $this->assertSame('00007', $mapped[1]);
        $this->assertSame('Siswa LPK Export Test', $mapped[2]);
        $this->assertSame('siswa-lpk-export@example.test', $mapped[5]);
        $this->assertSame('Bahasa Jepang', $mapped[6]);
        $this->assertSame('Jl. Siswa LPK No. 7', $mapped[10]);
    }

    public function test_it_respects_query_filters(): void
    {
        $included = SiswaLPK::factory()->create([
            'nomor_induk' => '11111',
            'email' => 'included-siswa-lpk@example.test',
        ]);
        SiswaLPK::factory()->create([
            'nomor_induk' => '22222',
            'email' => 'excluded-siswa-lpk@example.test',
        ]);

        $exporter = new SiswaLPKExport(SiswaLPK::query()->whereKey([$included->id]));
        $results = $exporter->query()->get();

        $this->assertCount(1, $results);
        $this->assertSame($included->id, $results->first()->id);
    }

    public function test_it_handles_empty_datasets(): void
    {
        $exporter = new SiswaLPKExport(SiswaLPK::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    public function test_it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new SiswaLPKExport(SiswaLPK::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    public function test_it_provides_styled_header_configuration(): void
    {
        $exporter = new SiswaLPKExport(SiswaLPK::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    public function test_it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new SiswaLPKExport(SiswaLPK::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    public function test_it_defines_readable_column_widths(): void
    {
        $exporter = new SiswaLPKExport(SiswaLPK::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['C']);
        $this->assertSame(40, $columnWidths['K']);
        $this->assertSame(20, $columnWidths['M']);
    }
}
