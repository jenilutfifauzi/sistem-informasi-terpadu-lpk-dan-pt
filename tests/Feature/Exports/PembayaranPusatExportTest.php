<?php

namespace Tests\Feature\Exports;

use App\Enums\EntityType;
use App\Filament\Exports\PembayaranPusatExport;
use App\Models\CTK;
use App\Models\PembayaranPusat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class PembayaranPusatExportTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->user = User::factory()->create([
            'email' => 'pembayaran-pusat-export-auth-'.uniqid()."@example.test",
            'entity' => EntityType::LPK,
        ]);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_correct_headings(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query());

        $this->assertSame([
            'ID',
            'Entity',
            'Nama CTK',
            'NIK CTK',
            'Tanggal Pembayaran',
            'Nominal',
            'Keterangan',
            'Dibuat Oleh',
            'Dibuat Pada',
            'Diperbarui Pada',
        ], $exporter->headings());
    }

    /** @test */
    public function it_maps_payment_data_correctly(): void
    {
        $ctk = CTK::factory()->create([
            'current_entity' => EntityType::LPK,
            'nama_lengkap' => 'CTK Pembayaran',
            'nik' => 'CTKPAY0001',
        ]);

        $payment = PembayaranPusat::create([
            'entity' => EntityType::LPK,
            'ctk_id' => $ctk->id,
            'tanggal_pembayaran' => '2026-06-07',
            'nominal' => 1500000,
            'keterangan' => 'Transfer tahap pertama',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $payment->load(['ctk', 'creator']);

        $exporter = new PembayaranPusatExport(PembayaranPusat::query()->whereKey($payment->id));
        $mapped = $exporter->map($payment);

        $this->assertSame($payment->id, $mapped[0]);
        $this->assertSame('LPK', $mapped[1]);
        $this->assertSame('CTK Pembayaran', $mapped[2]);
        $this->assertSame('CTKPAY0001', $mapped[3]);
        $this->assertSame('2026-06-07', $mapped[4]);
        $this->assertSame('Rp 1.500.000', $mapped[5]);
        $this->assertSame('Transfer tahap pertama', $mapped[6]);
        $this->assertSame($this->user->name, $mapped[7]);
    }

    /** @test */
    public function it_keeps_zero_nominal_visible_in_export(): void
    {
        $ctk = CTK::factory()->create([
            'current_entity' => EntityType::LPK,
        ]);

        $payment = PembayaranPusat::create([
            'entity' => EntityType::LPK,
            'ctk_id' => $ctk->id,
            'tanggal_pembayaran' => '2026-06-07',
            'nominal' => 0,
            'keterangan' => null,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $payment->load(['ctk', 'creator']);

        $exporter = new PembayaranPusatExport(PembayaranPusat::query()->whereKey($payment->id));
        $mapped = $exporter->map($payment);

        $this->assertSame('Rp 0', $mapped[5]);
    }

    /** @test */
    public function it_respects_query_filters_and_entity_scope(): void
    {
        $ctkLpk = CTK::factory()->create(['current_entity' => EntityType::LPK]);
        $ctkPt = CTK::factory()->create(['current_entity' => EntityType::PT]);

        $lpkPayment = PembayaranPusat::create([
            'entity' => EntityType::LPK,
            'ctk_id' => $ctkLpk->id,
            'tanggal_pembayaran' => '2026-06-07',
            'nominal' => 100000,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        PembayaranPusat::withoutEntityScope()->create([
            'entity' => EntityType::PT,
            'ctk_id' => $ctkPt->id,
            'tanggal_pembayaran' => '2026-06-08',
            'nominal' => 200000,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $exporter = new PembayaranPusatExport(
            PembayaranPusat::query()
                ->whereKey([$lpkPayment->id])
                ->where('entity', EntityType::LPK)
        );

        $result = $exporter->query()->get();

        $this->assertCount(1, $result);
        $this->assertSame($lpkPayment->id, $result->first()->id);
        $this->assertSame(EntityType::LPK, $result->first()->entity);
    }

    /** @test */
    public function it_eager_loads_relationships(): void
    {
        $ctk = CTK::factory()->create(['current_entity' => EntityType::LPK]);
        $payment = PembayaranPusat::create([
            'entity' => EntityType::LPK,
            'ctk_id' => $ctk->id,
            'tanggal_pembayaran' => '2026-06-07',
            'nominal' => 300000,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $exporter = new PembayaranPusatExport(PembayaranPusat::query()->whereKey($payment->id));
        $result = $exporter->query()->get();

        $this->assertTrue($result->first()->relationLoaded('ctk'));
        $this->assertTrue($result->first()->relationLoaded('creator'));
    }

    /** @test */
    public function it_handles_empty_dataset(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query()->whereRaw('1 = 0'));

        $this->assertCount(0, $exporter->query()->get());
        $this->assertIsArray($exporter->headings());
    }

    /** @test */
    public function it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    /** @test */
    public function it_provides_styled_header_configuration(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    /** @test */
    public function it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    /** @test */
    public function it_defines_readable_column_widths_for_exported_sheet(): void
    {
        $exporter = new PembayaranPusatExport(PembayaranPusat::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['C']);
        $this->assertSame(42, $columnWidths['G']);
        $this->assertSame(18, $columnWidths['F']);
    }
}
