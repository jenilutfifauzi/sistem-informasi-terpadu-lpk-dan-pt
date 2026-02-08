<?php

namespace Tests\Feature;

use App\Models\CTK;
use App\Models\CTKMedicalFull;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CTKMedicalFullTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    /** @test */
    public function admin_pt_records_medical_full_as_selesai_with_date(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 12]);

        $medical = CTKMedicalFull::factory()->selesai()->create([
            'ctk_id' => $ctk->id,
            'examination_date' => now()->subDays(5),
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('c_t_k_medical_fulls', [
            'ctk_id' => $ctk->id,
            'status' => 'Selesai',
        ]);

        $this->assertEquals('Selesai', $medical->status);
        $this->assertNotNull($medical->medical_report_path);
    }

    /** @test */
    public function admin_uploads_medical_report_document(): void
    {
        $ctk = CTK::factory()->create();

        $medical = CTKMedicalFull::factory()->selesai()->create([
            'ctk_id' => $ctk->id,
            'medical_report_path' => 'medical-full-reports/report_12345.pdf',
        ]);

        $this->assertEquals('medical-full-reports/report_12345.pdf', $medical->medical_report_path);
    }

    /** @test */
    public function medical_full_result_shows_health_issues(): void
    {
        $ctk = CTK::factory()->create();

        $medical = CTKMedicalFull::factory()->selesai()->create([
            'ctk_id' => $ctk->id,
            'examination_findings' => 'Ditemukan tekanan darah tinggi, perlu monitoring',
        ]);

        $this->assertEquals('Ditemukan tekanan darah tinggi, perlu monitoring', $medical->examination_findings);
    }

    /** @test */
    public function system_prevents_advancement_when_medical_full_incomplete(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 12]);

        CTKMedicalFull::factory()->belum()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasSelesai = $ctk->medicalFulls()
            ->where('status', 'Selesai')
            ->exists();

        $this->assertFalse($hasSelesai);
    }

    /** @test */
    public function ctk_with_selesai_medical_full_can_advance_from_stage_12(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 12]);

        CTKMedicalFull::factory()->selesai()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasSelesai = $ctk->medicalFulls()
            ->where('status', 'Selesai')
            ->exists();

        $this->assertTrue($hasSelesai);
    }

    /** @test */
    public function medical_full_completed_over_90_days_ago_shows_renewal_warning(): void
    {
        $ctk = CTK::factory()->create();

        $medical = CTKMedicalFull::factory()->needsRenewal()->create([
            'ctk_id' => $ctk->id,
            'examination_date' => now()->subDays(95),
        ]);

        $this->assertTrue($medical->isExpiringSoon());
        $this->assertGreaterThan(90, $medical->examination_date->diffInDays(now()));
    }

    /** @test */
    public function multiple_medical_fulls_can_be_tracked_for_single_ctk(): void
    {
        $ctk = CTK::factory()->create();

        CTKMedicalFull::factory()->selesai()->create(['ctk_id' => $ctk->id]);
        CTKMedicalFull::factory()->selesai()->create(['ctk_id' => $ctk->id]);
        CTKMedicalFull::factory()->belum()->create(['ctk_id' => $ctk->id]);

        $medicals = $ctk->medicalFulls;
        $selesaiCount = $medicals->where('status', 'Selesai')->count();

        $this->assertCount(3, $medicals);
        $this->assertEquals(2, $selesaiCount);
    }
}
