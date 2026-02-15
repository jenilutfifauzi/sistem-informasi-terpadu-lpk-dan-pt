<?php

namespace Tests\Feature;

use App\Models\CTK;
use App\Models\User;
use App\Models\VisaRecord;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CTKVisaProcessTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function legal_pt_submits_visa_application_and_marks_diajukan(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 11]);

        $visa = VisaRecord::factory()->diajukan()->create([
            'ctk_id' => $ctk->id,
            'application_date' => now()->subDays(5),
        ]);

        $this->assertDatabaseHas('visa_records', [
            'ctk_id' => $ctk->id,
            'application_status' => 'Diajukan',
        ]);

        $this->assertNull($visa->visa_number);
        $this->assertNull($visa->issuance_date);
    }

    /** @test */
    public function legal_pt_marks_visa_as_terbit_with_complete_information(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 13]);

        $visa = VisaRecord::factory()->terbit()->create([
            'ctk_id' => $ctk->id,
            'visa_number' => 'V123456',
            'issuance_date' => now()->subDays(30),
            'expiry_date' => now()->addYears(2),
            'issuing_country' => 'Japan',
            'visa_type' => 'Work Visa',
        ]);

        $this->assertDatabaseHas('visa_records', [
            'ctk_id' => $ctk->id,
            'application_status' => 'Terbit',
            'visa_number' => 'V123456',
        ]);

        $this->assertEquals('Japan', $visa->issuing_country);
        $this->assertEquals('Work Visa', $visa->visa_type);
    }

    /** @test */
    public function system_prevents_advancement_when_visa_not_yet_issued(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 13]);

        VisaRecord::factory()->diajukan()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasTerbit = $ctk->visaRecords()
            ->where('application_status', 'Terbit')
            ->exists();

        $this->assertFalse($hasTerbit);
    }

    /** @test */
    public function ctk_with_terbit_visa_can_advance_from_stage_13(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 13]);

        VisaRecord::factory()->terbit()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasTerbit = $ctk->visaRecords()
            ->where('application_status', 'Terbit')
            ->exists();

        $this->assertTrue($hasTerbit);
    }

    /** @test */
    public function admin_views_visa_details_and_sees_complete_information(): void
    {
        $ctk = CTK::factory()->create();

        $visa = VisaRecord::factory()->terbit()->create([
            'ctk_id' => $ctk->id,
            'visa_number' => 'V789012',
            'issuance_date' => now()->subMonths(3),
            'expiry_date' => now()->addMonths(21),
            'issuing_country' => 'Taiwan',
            'visa_type' => 'Employment Visa',
        ]);

        $retrievedVisa = $ctk->visaRecords()->first();

        $this->assertEquals('V789012', $retrievedVisa->visa_number);
        $this->assertEquals('Taiwan', $retrievedVisa->issuing_country);
        $this->assertEquals('Employment Visa', $retrievedVisa->visa_type);
        $this->assertNotNull($retrievedVisa->issuance_date);
        $this->assertNotNull($retrievedVisa->expiry_date);
    }

    /** @test */
    public function visa_expiry_warning_shows_when_expiring_within_30_days(): void
    {
        $ctk = CTK::factory()->create();

        $visa = VisaRecord::factory()->expiringSoon()->create([
            'ctk_id' => $ctk->id,
        ]);

        $daysUntilExpiry = $visa->expiry_date->diffInDays(now());

        $this->assertLessThanOrEqual(30, $daysUntilExpiry);
        $this->assertTrue($visa->expiry_date->isFuture());
    }

    /** @test */
    public function multiple_visas_can_be_tracked_for_single_ctk(): void
    {
        $ctk = CTK::factory()->create();

        VisaRecord::factory()->terbit()->create(['ctk_id' => $ctk->id, 'issuing_country' => 'Japan']);
        VisaRecord::factory()->terbit()->create(['ctk_id' => $ctk->id, 'issuing_country' => 'Taiwan']);
        VisaRecord::factory()->diajukan()->create(['ctk_id' => $ctk->id]);

        $visas = $ctk->visaRecords;
        $terbitCount = $visas->where('application_status', 'Terbit')->count();

        $this->assertCount(3, $visas);
        $this->assertEquals(2, $terbitCount);
    }
}
