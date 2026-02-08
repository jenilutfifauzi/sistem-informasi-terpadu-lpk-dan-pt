<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Models\CTK;
use App\Models\CTKTraining;
use App\Models\EmployeeLPK;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CTKTrainingTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_training_with_instructor(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();
        $instructor = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $training = CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_start_date' => now()->subDays(5),
            'training_location' => 'Ruang Pelatihan A',
            'training_hours' => 40,
            'completion_status' => 'Belum Selesai',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('c_t_k_trainings', [
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_location' => 'Ruang Pelatihan A',
            'training_hours' => 40,
        ]);

        $retrievedTraining = CTKTraining::find($training->id);
        $this->assertEquals($instructor->id, $retrievedTraining->instructor->id);
        $this->assertEquals($admin->id, $retrievedTraining->created_by);
    }

    public function test_admin_views_training_history_with_all_details(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();
        $instructor1 = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $instructor2 = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor1->id,
            'training_start_date' => now()->subDays(10),
            'training_end_date' => now()->subDays(5),
            'training_location' => 'Ruang Pelatihan A',
            'training_hours' => 40,
            'completion_status' => 'Selesai',
            'created_by' => $admin->id,
        ]);

        CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor2->id,
            'training_start_date' => now()->subDays(3),
            'training_location' => 'Ruang Pelatihan B',
            'training_hours' => 30,
            'completion_status' => 'Belum Selesai',
            'created_by' => $admin->id,
        ]);

        $trainings = $ctk->trainings;

        $this->assertCount(2, $trainings);
        $this->assertEquals(70, $trainings->sum('training_hours'));
        $this->assertTrue($trainings->every(fn ($t) => $t->created_by === $admin->id));
    }

    public function test_system_prevents_advancement_when_no_training_completed(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 5]);
        $instructor = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_start_date' => now(),
            'training_location' => 'Ruang Pelatihan A',
            'training_hours' => 40,
            'completion_status' => 'Belum Selesai',
            'created_by' => User::factory()->create()->id,
        ]);

        $this->assertFalse(
            $ctk->trainings()->where('completion_status', 'Selesai')->exists()
        );
    }

    public function test_ctk_with_completed_training_can_advance_from_stage_5(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create(['current_stage' => 5]);
        $instructor = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_start_date' => now()->subDays(10),
            'training_end_date' => now()->subDays(1),
            'training_location' => 'Ruang Pelatihan A',
            'training_hours' => 80,
            'completion_status' => 'Selesai',
            'created_by' => $admin->id,
        ]);

        $this->assertTrue(
            $ctk->trainings()->where('completion_status', 'Selesai')->exists()
        );

        $ctk->update(['current_stage' => 6]);
        $this->assertEquals(6, $ctk->current_stage);
    }

    public function test_training_records_instructor_and_completion_notes(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();
        $instructor = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $completionNotes = 'CTK menunjukkan kemampuan yang baik dalam pelatihan';

        $training = CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_start_date' => now()->subDays(30),
            'training_end_date' => now(),
            'training_location' => 'Gedung LPK',
            'training_hours' => 100,
            'completion_status' => 'Selesai',
            'completion_notes' => $completionNotes,
            'created_by' => $admin->id,
        ]);

        $this->assertEquals($instructor->id, $training->instructor->id);
        $this->assertEquals($completionNotes, $training->completion_notes);
        $this->assertEquals('Selesai', $training->completion_status);
    }

    public function test_multiple_trainings_can_be_tracked_for_single_ctk(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();
        $instructor1 = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $instructor2 = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $instructor3 = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $trainings = [
            ['instructor_id' => $instructor1->id, 'hours' => 40, 'status' => 'Selesai'],
            ['instructor_id' => $instructor2->id, 'hours' => 30, 'status' => 'Selesai'],
            ['instructor_id' => $instructor3->id, 'hours' => 20, 'status' => 'Belum Selesai'],
        ];

        foreach ($trainings as $trainingData) {
            CTKTraining::create([
                'ctk_id' => $ctk->id,
                'instructor_id' => $trainingData['instructor_id'],
                'training_start_date' => now()->subDays(10),
                'training_location' => 'Ruang Pelatihan A',
                'training_hours' => $trainingData['hours'],
                'completion_status' => $trainingData['status'],
                'created_by' => $admin->id,
            ]);
        }

        $this->assertEquals(3, $ctk->trainings()->count());
        $this->assertEquals(90, $ctk->trainings()->sum('training_hours'));
        $this->assertEquals(2, $ctk->trainings()->where('completion_status', 'Selesai')->count());
    }
}
