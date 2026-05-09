<?php

namespace Tests\Unit;

use App\Models\SiswaLPK;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SiswaLPKModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_model_sets_creator_and_updater_relationships(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $record = SiswaLPK::factory()->create();

        $this->assertSame($user->id, $record->created_by);
        $this->assertSame($user->id, $record->updated_by);
        $this->assertInstanceOf(User::class, $record->creator);
        $this->assertInstanceOf(User::class, $record->updater);
    }

    public function test_database_enforces_unique_nomor_induk(): void
    {
        SiswaLPK::factory()->create(['nomor_induk' => '40001']);

        $this->expectException(QueryException::class);

        SiswaLPK::factory()->create(['nomor_induk' => '40001']);
    }

    public function test_soft_deleted_nomor_induk_remains_reserved(): void
    {
        $record = SiswaLPK::factory()->create(['nomor_induk' => '50001']);
        $record->delete();

        $this->expectException(QueryException::class);

        SiswaLPK::factory()->create(['nomor_induk' => '50001']);
    }

    public function test_model_soft_deletes_records(): void
    {
        $record = SiswaLPK::factory()->create();
        $record->delete();

        $this->assertSoftDeleted('siswa_lpk', ['id' => $record->id]);
    }

    public function test_activity_log_records_create_update_and_delete_events(): void
    {
        Activity::query()->delete();

        $user = User::factory()->create();
        $this->actingAs($user);

        $record = SiswaLPK::factory()->create(['nama_siswa' => 'Log Test']);
        $record->update(['nama_siswa' => 'Log Test Updated']);
        $record->delete();

        $events = Activity::query()
            ->where('subject_type', SiswaLPK::class)
            ->where('subject_id', $record->id)
            ->pluck('event')
            ->all();

        $this->assertContains('created', $events);
        $this->assertContains('updated', $events);
        $this->assertContains('deleted', $events);
    }
}
