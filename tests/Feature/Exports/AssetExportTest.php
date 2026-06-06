<?php

namespace Tests\Feature\Exports;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Filament\Exports\AssetExport;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\EmployeeLPK;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class AssetExportTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        // Create authenticated user
        $this->user = User::factory()->create([
            'email' => 'asset-export-auth-'.uniqid()."@example.test",
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
        $exporter = new AssetExport(Asset::query());
        $headings = $exporter->headings();

        $this->assertEquals([
            'ID',
            'Nomor Inventaris',
            'Nama Barang',
            'Kategori',
            'Jumlah',
            'Satuan',
            'Kondisi',
            'Status Assignment',
            'Assigned To',
            'Assigned Date',
            'Tahun Pembelian',
            'Nilai Pembelian',
            'Lokasi',
            'Deskripsi',
            'Created At',
            'Updated At',
        ], $headings);
    }

    /** @test */
    public function it_maps_asset_data_correctly(): void
    {
        $asset = Asset::factory()->create([
            'nomor_inventaris' => 'INV-001',
            'nama_barang' => 'Laptop Dell',
            'kategori' => AssetCategory::Elektronik,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'kondisi' => AssetCondition::Baik,
            'status_assignment' => AssetAssignmentStatus::Available,
            'tahun_pembelian' => 2024,
            'nilai_pembelian' => 15000000,
            'lokasi' => 'Office Jakarta',
            'deskripsi' => 'Company laptop',
        ]);

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertEquals($asset->id, $mapped[0]);
        $this->assertEquals('INV-001', $mapped[1]);
        $this->assertEquals('Laptop Dell', $mapped[2]);
        $this->assertEquals(AssetCategory::Elektronik->getLabel(), $mapped[3]);
        $this->assertEquals(1, $mapped[4]);
        $this->assertEquals('Unit', $mapped[5]);
        $this->assertEquals(AssetCondition::Baik->getLabel(), $mapped[6]);
        $this->assertEquals(AssetAssignmentStatus::Available->getLabel(), $mapped[7]);
        $this->assertEquals('', $mapped[8]); // No assignment
        $this->assertEquals('', $mapped[9]); // No assignment date
        $this->assertEquals(2024, $mapped[10]);
        $this->assertStringContainsString('15.000.000', $mapped[11]); // Formatted currency
        $this->assertEquals('Office Jakarta', $mapped[12]);
        $this->assertEquals('Company laptop', $mapped[13]);
    }

    /** @test */
    public function it_transforms_enum_values_to_labels(): void
    {
        $asset = Asset::factory()->create([
            'kategori' => AssetCategory::Furniture,
            'kondisi' => AssetCondition::Rusak,
            'status_assignment' => AssetAssignmentStatus::Assigned,
        ]);

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertEquals(AssetCategory::Furniture->getLabel(), $mapped[3]);
        $this->assertEquals(AssetCondition::Rusak->getLabel(), $mapped[6]);
        $this->assertEquals(AssetAssignmentStatus::Assigned->getLabel(), $mapped[7]);
    }

    /** @test */
    public function it_includes_current_assignment_information(): void
    {
        $employee = EmployeeLPK::factory()->create(['nama_lengkap' => 'John Doe']);

        $asset = Asset::factory()->create([
            'kondisi' => AssetCondition::Baik,
            'status_assignment' => AssetAssignmentStatus::Assigned,
        ]);

        $assignment = AssetAssignment::create([
            'asset_id' => $asset->id,
            'assignable_type' => EmployeeLPK::class,
            'assignable_id' => $employee->id,
            'assigned_by' => $this->user->id,
            'assigned_date' => now()->subDays(5),
        ]);

        // Refresh asset with relationship
        $asset->load('currentAssignment.assignable');

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertEquals('John Doe', $mapped[8]); // Assigned To
        $this->assertEquals($assignment->assigned_date->format('Y-m-d'), $mapped[9]); // Assigned Date
    }

    /** @test */
    public function it_handles_assets_without_assignment(): void
    {
        $asset = Asset::factory()->create([
            'kondisi' => AssetCondition::Baik,
            'status_assignment' => AssetAssignmentStatus::Available,
        ]);

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertEquals('', $mapped[8]); // Assigned To should be empty
        $this->assertEquals('', $mapped[9]); // Assigned Date should be empty
    }

    /** @test */
    public function it_respects_query_filters(): void
    {
        $entity = $this->user->entity;

        $elektronikAsset = Asset::factory()->create([
            'entity' => $entity,
            'kategori' => AssetCategory::Elektronik,
            'kondisi' => AssetCondition::Baik,
        ]);
        $furnitureAsset = Asset::factory()->create([
            'entity' => $entity,
            'kategori' => AssetCategory::Furniture,
            'kondisi' => AssetCondition::Baik,
        ]);
        $kendaraanAsset = Asset::factory()->create([
            'entity' => $entity,
            'kategori' => AssetCategory::Kendaraan,
            'kondisi' => AssetCondition::Rusak,
        ]);

        $query = Asset::query()
            ->whereKey([$elektronikAsset->id, $furnitureAsset->id, $kendaraanAsset->id])
            ->where('kategori', AssetCategory::Elektronik);
        $exporter = new AssetExport($query);

        $collection = $exporter->query()->get();

        $this->assertCount(1, $collection);
        foreach ($collection as $asset) {
            $this->assertEquals(AssetCategory::Elektronik, $asset->kategori);
            $this->assertEquals($entity, $asset->entity);
        }
    }

    /** @test */
    public function it_handles_empty_dataset(): void
    {
        $query = Asset::query()->where('id', 0); // No records
        $exporter = new AssetExport($query);

        $collection = $exporter->query()->get();

        $this->assertCount(0, $collection);
    }

    /** @test */
    public function it_formats_currency_values_correctly(): void
    {
        $asset = Asset::factory()->create([
            'kondisi' => AssetCondition::Baik,
            'nilai_pembelian' => 25500000, // 25.5 million
        ]);

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertStringContainsString('25.500.000', $mapped[11]);
        $this->assertStringStartsWith('Rp ', $mapped[11]);
    }

    /** @test */
    public function it_handles_zero_currency_values(): void
    {
        $asset = Asset::factory()->create([
            'kondisi' => AssetCondition::Baik,
            'nilai_pembelian' => 0,
        ]);

        $exporter = new AssetExport(Asset::query());
        $mapped = $exporter->map($asset);

        $this->assertStringContainsString('0', $mapped[11]); // Should format 0 as Rp 0
    }

    /** @test */
    public function it_eager_loads_relationships_to_avoid_n_plus_1(): void
    {
        // Create assets with assignments
        $employee = EmployeeLPK::factory()->create();
        $entity = $this->user->entity;

        $createdAssets = collect();

        for ($i = 0; $i < 3; $i++) {
            $asset = Asset::factory()->create([
                'entity' => $entity,
                'kondisi' => AssetCondition::Baik,
            ]);
            AssetAssignment::create([
                'asset_id' => $asset->id,
                'assignable_type' => EmployeeLPK::class,
                'assignable_id' => $employee->id,
                'assigned_by' => $this->user->id,
                'assigned_date' => now(),
            ]);
            $createdAssets->push($asset->id);
        }

        $exporter = new AssetExport(Asset::query()->whereKey($createdAssets->all()));
        $assets = $exporter->query()->get();

        $this->assertCount(3, $assets);
        foreach ($assets as $asset) {
            $this->assertTrue(
                $asset->relationLoaded('currentAssignment'),
                'currentAssignment relationship should be eager loaded'
            );
        }
    }

    /** @test */
    public function it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new AssetExport(Asset::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    /** @test */
    public function it_provides_styled_header_configuration(): void
    {
        $exporter = new AssetExport(Asset::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    /** @test */
    public function it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new AssetExport(Asset::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    /** @test */
    public function it_defines_readable_column_widths_for_exported_sheet(): void
    {
        $exporter = new AssetExport(Asset::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(20, $columnWidths['B']);
        $this->assertSame(28, $columnWidths['C']);
        $this->assertSame(40, $columnWidths['N']);
    }
}
