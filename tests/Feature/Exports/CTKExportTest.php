<?php

namespace Tests\Feature\Exports;

use App\Filament\Exports\CTKExport;
use App\Models\CTK;
use App\Models\CTKScreening;
use App\Models\MCURecord;
use Illuminate\Support\Facades\DB;
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
    public function test_export_class_generates_correct_headings()
    {
        $query = CTK::query();
        $export = new CTKExport($query);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertContains('Nama Lengkap', $headings);
        $this->assertContains('Email', $headings);
        $this->assertContains('Status Saat Ini', $headings);
        $this->assertContains('Status Screening', $headings);
        $this->assertContains('Status MCU', $headings);
        $this->assertNotContains('NIK', $headings); // NIK should be excluded per FR-009
    }

    /** @test */
    public function test_export_maps_ctk_data_correctly()
    {
        $ctk = CTK::factory()->create([
            'nama_lengkap' => 'Test CTK',
            'email' => 'test-ctk@example.com',
        ]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());

        $this->assertIsArray($mapped);
        $this->assertContains('Test CTK', $mapped);
        $this->assertContains('test-ctk@example.com', $mapped);
    }

    /** @test */
    public function test_sensitive_fields_excluded_from_export()
    {
        $ctk = CTK::factory()->create([
            'nik' => '3333333333333333',
            'paspor_number' => 'A12345678',
            'nama_lengkap' => 'Test Sensitive',
        ]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());
        $headings = $export->headings();

        // Sensitive fields should not be in mapped data or headings
        $this->assertNotContains('3333333333333333', $mapped);
        $this->assertNotContains('A12345678', $mapped);
        $this->assertNotContains('NIK', $headings);
        $this->assertNotContains('Paspor', $headings);
    }

    /** @test */
    public function test_export_includes_screening_status()
    {
        $ctk = CTK::factory()->create();

        // Create a screening record
        CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'screening_result' => 'Lolos',
        ]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());

        // Should contain screening status
        $this->assertContains('Lolos', $mapped);
    }

    /** @test */
    public function test_export_includes_mcu_status()
    {
        $ctk = CTK::factory()->create();

        // Create an MCU record
        MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => 'FIT',
        ]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());

        // Should contain MCU status
        $this->assertContains('FIT', $mapped);
    }

    /** @test */
    public function test_export_handles_ctk_without_screening()
    {
        $ctk = CTK::factory()->create();

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());

        // Should contain 'Belum Ada' for missing screening
        $this->assertContains('Belum Ada', $mapped);
    }

    /** @test */
    public function test_export_respects_query_filters()
    {
        // Create test data with different statuses
        $activeCTK = CTK::factory()->create([
            'current_entity' => 'LPK',
            'nik' => '4444444444444444',
        ]);
        $ptCTK = CTK::factory()->create([
            'current_entity' => 'PT',
            'nik' => '5555555555555555',
        ]);

        // Query only LPK CTKs
        $query = CTK::query()
            ->where('current_entity', 'LPK')
            ->whereIn('nik', ['4444444444444444', '5555555555555555']);
        $export = new CTKExport($query);

        $result = $export->query()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('LPK', $result->first()->current_entity->value);
    }

    /** @test */
    public function test_export_formats_dates_correctly()
    {
        $ctk = CTK::factory()->create([
            'tanggal_lahir' => '1995-05-20',
        ]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $mapped = $export->map($ctk->fresh());

        $this->assertContains('1995-05-20', $mapped);
    }

    /** @test */
    public function test_export_handles_empty_dataset()
    {
        // Create an empty query
        $query = CTK::query()->where('id', 0); // No match
        $export = new CTKExport($query);

        $result = $export->query()->get();

        $this->assertCount(0, $result);
        $this->assertIsArray($export->headings());
    }

    /** @test */
    public function test_export_eager_loads_relationships()
    {
        $ctk = CTK::factory()->create();
        CTKScreening::factory()->create(['ctk_id' => $ctk->id]);
        MCURecord::factory()->create(['ctk_id' => $ctk->id]);

        $query = CTK::query()->where('id', $ctk->id);
        $export = new CTKExport($query);

        $result = $export->query()->get();

        // Relationships should be loaded
        $this->assertTrue($result->first()->relationLoaded('screenings'));
        $this->assertTrue($result->first()->relationLoaded('mcuRecords'));
    }
}
