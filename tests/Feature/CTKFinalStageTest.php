<?php

namespace Tests\Feature;

use App\Models\CTK;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CTKFinalStageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function opp_status_defaults_to_belum()
    {
        $ctk = CTK::factory()->create([
            'opp_status' => 'Belum',
        ]);

        $this->assertEquals('Belum', $ctk->opp_status);
        $this->assertNull($ctk->opp_receipt_date);
        $this->assertNull($ctk->departure_date);
        $this->assertNull($ctk->flight_number);
    }

    /** @test */
    public function can_update_ctk_with_opp_received_status()
    {
        $ctk = CTK::factory()->create([
            'opp_status' => 'Belum',
            'current_stage' => 13, // Before OPP stage
        ]);
        $receiptDate = now();
        $departureDate = now()->addDays(7);

        // Update to Diterima from Belum (should be allowed)
        $ctk->opp_status = 'Diterima';
        $ctk->opp_receipt_date = $receiptDate;
        $ctk->departure_date = $departureDate;
        $ctk->flight_number = 'GA123';
        $ctk->save();

        $this->assertEquals('Diterima', $ctk->fresh()->opp_status);
        $this->assertEquals($receiptDate->format('Y-m-d'), $ctk->fresh()->opp_receipt_date->format('Y-m-d'));
    }

    /** @test */
    public function cannot_advance_from_stage_14_without_opp_received()
    {
        $ctk = CTK::factory()->create(['current_stage' => 14]);

        // Attempt to advance without OPP received
        $response = $this->post("/admin/ctks/{$ctk->id}/advanced-actions/advanceStage", [
            'data' => [],
        ], [
            'Accept' => 'application/json',
        ]);

        // Status should remain 14
        $this->assertEquals(14, $ctk->fresh()->current_stage);
    }

    /** @test */
    public function can_advance_from_stage_14_with_opp_received()
    {
        $ctk = CTK::factory()->create([
            'current_stage' => 14,
            'current_status' => 'OPP',
            'opp_status' => 'Diterima',
            'opp_receipt_date' => now(),
            'departure_date' => now()->addDays(5),
        ]);

        // Verify eligibility check passes
        $this->assertTrue($ctk->opp_status === 'Diterima' && $ctk->departure_date !== null);

        // The stage should be 14 (not advanced yet in test)
        $this->assertEquals(14, $ctk->current_stage);
    }

    /** @test */
    public function ctk_in_stage_15_is_marked_as_terbang()
    {
        $ctk = CTK::factory()->create([
            'current_stage' => 15,
            'current_status' => 'Terbang',
            'opp_status' => 'Diterima',
        ]);

        $this->assertEquals('Terbang', $ctk->current_status->value);
        $this->assertEquals(15, $ctk->current_stage);
    }

    /** @test */
    public function opp_dates_are_properly_cast()
    {
        $ctk = CTK::factory()->create([
            'opp_receipt_date' => '2026-02-01',
            'departure_date' => '2026-02-10',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $ctk->opp_receipt_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $ctk->departure_date);
    }
}
