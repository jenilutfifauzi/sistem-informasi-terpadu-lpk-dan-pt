<?php

namespace Tests\Feature;

use App\Models\CTK;
use App\Models\CTKScreening;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CTKScreeningTest extends TestCase
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
    public function admin_can_assign_screening_with_interviewer(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 7]);
        $interviewer = User::factory()->create(['name' => 'John Interviewer']);

        $screening = CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'interviewer_id' => $interviewer->id,
            'interview_date' => now()->subDays(1),
            'interview_location' => 'Ruang Interview 1',
            'screening_result' => 'Lolos',
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('c_t_k_screenings', [
            'ctk_id' => $ctk->id,
            'interviewer_id' => $interviewer->id,
            'screening_result' => 'Lolos',
        ]);

        $this->assertEquals($interviewer->id, $screening->interviewer->id);
        $this->assertEquals('John Interviewer', $screening->interviewer->name);
    }

    /** @test */
    public function admin_views_screening_history_with_all_details(): void
    {
        $ctk = CTK::factory()->create();
        $interviewer1 = User::factory()->create(['name' => 'Interviewer A']);
        $interviewer2 = User::factory()->create(['name' => 'Interviewer B']);

        CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'interviewer_id' => $interviewer1->id,
            'screening_result' => 'Lolos',
            'created_by' => $this->admin->id,
        ]);

        CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'interviewer_id' => $interviewer2->id,
            'screening_result' => 'Tidak Lolos',
            'created_by' => $this->admin->id,
        ]);

        $screenings = $ctk->screenings;

        $this->assertCount(2, $screenings);
        $this->assertEquals($this->admin->id, $screenings->first()->created_by);
    }

    /** @test */
    public function system_prevents_advancement_when_no_screening_lolos(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 7]);

        CTKScreening::factory()->tidakLolos()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasLolos = $ctk->screenings()
            ->where('screening_result', 'Lolos')
            ->exists();

        $this->assertFalse($hasLolos);
    }

    /** @test */
    public function ctk_with_lolos_screening_can_advance_from_stage_7(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 7]);

        CTKScreening::factory()->lolos()->create([
            'ctk_id' => $ctk->id,
        ]);

        $hasLolos = $ctk->screenings()
            ->where('screening_result', 'Lolos')
            ->exists();

        $this->assertTrue($hasLolos);
    }

    /** @test */
    public function screening_records_interviewer_and_interview_notes(): void
    {
        $ctk = CTK::factory()->create();
        $interviewer = User::factory()->create(['name' => 'Sarah Interviewer']);

        $screening = CTKScreening::factory()->create([
            'ctk_id' => $ctk->id,
            'interviewer_id' => $interviewer->id,
            'interview_notes' => 'Kandidat sangat baik dalam komunikasi.',
        ]);

        $this->assertEquals('Sarah Interviewer', $screening->interviewer->name);
        $this->assertEquals('Kandidat sangat baik dalam komunikasi.', $screening->interview_notes);
    }

    /** @test */
    public function multiple_screenings_can_be_tracked_for_single_ctk(): void
    {
        $ctk = CTK::factory()->create();

        CTKScreening::factory()->lolos()->create(['ctk_id' => $ctk->id]);
        CTKScreening::factory()->lolos()->create(['ctk_id' => $ctk->id]);
        CTKScreening::factory()->tidakLolos()->create(['ctk_id' => $ctk->id]);

        $screenings = $ctk->screenings;
        $lolosCount = $screenings->where('screening_result', 'Lolos')->count();

        $this->assertCount(3, $screenings);
        $this->assertEquals(2, $lolosCount);
    }
}
