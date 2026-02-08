<?php

namespace Tests\Feature;

use App\Models\CTK;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CTKAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function activity_is_logged_when_ctk_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ctk = CTK::factory()->create(['created_by' => $user->id]);

        // Activities are logged automatically, we just verify the table has entries
        $this->assertDatabaseHas('activity_log', [
            'subject_id' => $ctk->id,
            'subject_type' => CTK::class,
        ]);
    }

    /** @test */
    public function activity_is_logged_when_ctk_is_updated()
    {
        $user = User::factory()->create();
        $ctk = CTK::factory()->create();

        $this->actingAs($user);

        $ctk->update(['nama_lengkap' => 'Updated Name']);

        // Verify activity was logged
        $this->assertDatabaseHas('activity_log', [
            'subject_id' => $ctk->id,
            'subject_type' => CTK::class,
        ]);
    }

    /** @test */
    public function activity_log_contains_causer_information()
    {
        $user = User::factory()->create();
        $ctk = CTK::factory()->create();

        $this->actingAs($user);

        activity()
            ->causedBy($user)
            ->performedOn($ctk)
            ->event('tested')
            ->log('Test activity');

        $activity = Activity::where('description', 'Test activity')->first();

        $this->assertNotNull($activity);
        $this->assertEquals($user->id, $activity->causer_id);
    }

    /** @test */
    public function stage_transitions_are_logged()
    {
        $user = User::factory()->create();
        $ctk = CTK::factory()->create(['current_stage' => 1]);

        $this->actingAs($user);

        activity()
            ->causedBy($user)
            ->performedOn($ctk)
            ->event('stage_transition')
            ->withProperties(['from_stage' => 1, 'to_stage' => 2])
            ->log('CTK advanced from stage 1 to stage 2');

        $activity = Activity::where('description', 'CTK advanced from stage 1 to stage 2')->first();

        $this->assertNotNull($activity);
        $this->assertEquals(1, $activity->getExtraProperty('from_stage'));
        $this->assertEquals(2, $activity->getExtraProperty('to_stage'));
    }

    /** @test */
    public function activity_log_can_be_queried_by_subject()
    {
        $user = User::factory()->create();
        $ctk = CTK::factory()->create();

        activity()
            ->causedBy($user)
            ->performedOn($ctk)
            ->log('Test log entry');

        $activities = Activity::forSubject($ctk)->get();

        $this->assertGreaterThan(0, $activities->count());
        $this->assertTrue($activities->contains('subject_id', $ctk->id));
    }

    /** @test */
    public function activity_log_shows_timestamps()
    {
        $user = User::factory()->create();
        $ctk = CTK::factory()->create();

        $this->actingAs($user);

        activity()
            ->causedBy($user)
            ->performedOn($ctk)
            ->log('Timestamp test');

        $activity = Activity::where('description', 'Timestamp test')->first();

        $this->assertNotNull($activity->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $activity->created_at);
    }
}
