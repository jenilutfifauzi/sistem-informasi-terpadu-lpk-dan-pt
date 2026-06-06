<?php

namespace Tests\Feature\Exports;

use App\Filament\Exports\BukuIndukSiswaExport;
use App\Models\BukuIndukSiswa;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class BukuIndukSiswaExportTest extends TestCase
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
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query());

        $this->assertSame([
            'Nama Lengkap',
            'Nomor Induk',
            'Program Pendidikan',
            'Program Bahasa',
            'Nama Panggilan',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Kewarganegaraan',
            'Status Perkawinan',
            'Nama Suami / Istri',
            'No. HP Suami / Istri',
            'Alamat Siswa',
            'No. HP Siswa',
            'Email',
            'Alamat Orang Tua',
            'No. HP Orang Tua',
            'Golongan Darah',
            'Penyakit Pernah Diderita',
            'Kelainan Jasmani',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
        ], $exporter->headings());
    }

    public function test_it_maps_student_data_correctly(): void
    {
        $student = BukuIndukSiswa::factory()->create([
            'nama_lengkap' => 'Buku Induk Export Test',
            'nomor_induk' => 'BI-00123',
            'email' => 'buku-induk-export@example.test',
            'alamat_siswa' => 'Jl. Buku Induk No. 1',
            'alamat_orang_tua' => 'Jl. Orang Tua No. 2',
            'tinggi_badan_cm' => 168,
            'berat_badan_kg' => 58,
        ]);

        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query()->whereKey($student->id));
        $mapped = $exporter->map($student->fresh());

        $this->assertSame('Buku Induk Export Test', $mapped[0]);
        $this->assertSame('BI-00123', $mapped[1]);
        $this->assertSame('buku-induk-export@example.test', $mapped[15]);
        $this->assertSame('Jl. Buku Induk No. 1', $mapped[13]);
        $this->assertSame('Jl. Orang Tua No. 2', $mapped[16]);
        $this->assertSame(168, $mapped[21]);
        $this->assertSame(58, $mapped[22]);
    }

    public function test_it_respects_query_filters(): void
    {
        $included = BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-INCLUDED',
            'email' => 'included-buku@example.test',
        ]);
        BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-EXCLUDED',
            'email' => 'excluded-buku@example.test',
        ]);

        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query()->whereKey([$included->id]));
        $results = $exporter->query()->get();

        $this->assertCount(1, $results);
        $this->assertSame($included->id, $results->first()->id);
    }

    public function test_it_handles_empty_datasets(): void
    {
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    public function test_it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    public function test_it_provides_styled_header_configuration(): void
    {
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    public function test_it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    public function test_it_defines_readable_column_widths(): void
    {
        $exporter = new BukuIndukSiswaExport(BukuIndukSiswa::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['A']);
        $this->assertSame(40, $columnWidths['N']);
        $this->assertSame(16, $columnWidths['V']);
        $this->assertSame(16, $columnWidths['W']);
    }
}
