<?php

namespace Tests\Feature\Exports;

use App\Enums\EntityType;
use App\Filament\Exports\CTKExport;
use App\Models\CTK;
use App\Models\CTKScreening;
use App\Models\MCURecord;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class CTKExportTest extends TestCase
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
    public function it_generates_correct_headings(): void
    {
        $exporter = new CTKExport(CTK::query());

        $this->assertSame([
            'ID',
            'Nama Lengkap',
            'Email',
            'No. Telepon',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Status Saat Ini',
            'Stage Saat Ini',
            'Entitas Saat Ini',
            'Status Screening',
            'Status MCU',
            'Tanggal Dibuat',
        ], $exporter->headings());
    }

    /** @test */
    public function it_maps_ctk_data_correctly(): void
    {
        $ctk = CTK::factory()->create([
            'nama_lengkap' => 'Test CTK',
            'email' => 'test-ctk@example.com',
            'current_entity' => EntityType::LPK,
        ]);
        $ctk->load(['screenings', 'mcuRecords']);

        $exporter = new CTKExport(CTK::query());
        $mapped = $exporter->map($ctk);

        $this->assertSame($ctk->id, $mapped[0]);
        $this->assertSame('Test CTK', $mapped[1]);
        $this->assertSame('test-ctk@example.com', $mapped[2]);
        $this->assertSame($ctk->no_telepon, $mapped[3]);
        $this->assertSame('LPK', $mapped[9]);
        $this->assertNotEmpty($mapped[12]);
    }

    /** @test */
    public function it_excludes_sensitive_fields_from_export(): void
    {
        $ctk = CTK::factory()->create([
            'nik' => '3333333333333333',
            'paspor_number' => 'A12345678',
            'nama_lengkap' => 'Test Sensitive',
        ]);
        $ctk->load(['screenings', 'mcuRecords']);

        $exporter = new CTKExport(CTK::query()->whereKey($ctk->id));
        $mapped = $exporter->map($ctk);
        $headings = $exporter->headings();

        $this->assertNotContains('3333333333333333', $mapped);
        $this->assertNotContains('A12345678', $mapped);
        $this->assertNotContains('NIK', $headings);
        $this->assertNotContains('Paspor', $headings);
    }

    /** @test */
    public function it_includes_latest_screening_and_mcu_statuses(): void
    {
        $ctk = CTK::factory()->create();

        CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'screening_result' => 'Tidak Lolos',
        ]);

        $latestScreening = CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'screening_result' => 'Lolos',
            'created_at' => now()->addMinute(),
        ]);

        MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => 'UNFIT',
        ]);

        $latestMcu = MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => 'FIT',
            'created_at' => now()->addMinutes(2),
        ]);

        $ctk->load(['screenings', 'mcuRecords']);

        $exporter = new CTKExport(CTK::query());
        $mapped = $exporter->map($ctk);

        $this->assertSame($latestScreening->screening_result, $mapped[10]);
        $this->assertContains($mapped[11], ['FIT', 'Fit']);
        $this->assertNotSame('Belum Ada', $mapped[10]);
        $this->assertNotSame('Belum Ada', $mapped[11]);
    }

    /** @test */
    public function it_handles_ctk_without_screening_or_mcu(): void
    {
        $ctk = CTK::factory()->create();
        $ctk->load(['screenings', 'mcuRecords']);

        $exporter = new CTKExport(CTK::query()->whereKey($ctk->id));
        $mapped = $exporter->map($ctk);

        $this->assertSame('Belum Ada', $mapped[10]);
        $this->assertSame('Belum Ada', $mapped[11]);
    }

    /** @test */
    public function it_respects_query_filters(): void
    {
        $lpk = CTK::factory()->create([
            'current_entity' => EntityType::LPK,
            'nik' => '4444444444444444',
        ]);
        $pt = CTK::factory()->create([
            'current_entity' => EntityType::PT,
            'nik' => '5555555555555555',
        ]);

        $exporter = new CTKExport(
            CTK::query()
                ->whereKey([$lpk->id, $pt->id])
                ->where('current_entity', EntityType::LPK)
        );

        $result = $exporter->query()->get();

        $this->assertCount(1, $result);
        $this->assertSame($lpk->id, $result->first()->id);
        $this->assertSame(EntityType::LPK, $result->first()->current_entity);
    }

    /** @test */
    public function it_formats_dates_correctly(): void
    {
        $ctk = CTK::factory()->create([
            'tanggal_lahir' => '1995-05-20',
        ]);
        $ctk->load(['screenings', 'mcuRecords']);

        $exporter = new CTKExport(CTK::query()->whereKey($ctk->id));
        $mapped = $exporter->map($ctk);

        $this->assertSame('1995-05-20', $mapped[4]);
    }

    /** @test */
    public function it_handles_empty_dataset(): void
    {
        $exporter = new CTKExport(CTK::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    /** @test */
    public function it_eager_loads_relationships(): void
    {
        $ctk = CTK::factory()->create();
        CTKScreening::factory()->create(['ctk_id' => $ctk->id]);
        MCURecord::factory()->create(['ctk_id' => $ctk->id]);

        $exporter = new CTKExport(CTK::query()->whereKey($ctk->id));
        $result = $exporter->query()->get();

        $this->assertTrue($result->first()->relationLoaded('screenings'));
        $this->assertTrue($result->first()->relationLoaded('mcuRecords'));
    }

    /** @test */
    public function it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new CTKExport(CTK::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    /** @test */
    public function it_provides_styled_header_configuration(): void
    {
        $exporter = new CTKExport(CTK::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    /** @test */
    public function it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new CTKExport(CTK::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    /** @test */
    public function it_defines_readable_column_widths_for_exported_sheet(): void
    {
        $exporter = new CTKExport(CTK::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['B']);
        $this->assertSame(40, $columnWidths['G']);
        $this->assertSame(18, $columnWidths['K']);
    }
}
