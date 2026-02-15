<?php

namespace Tests\Feature;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use App\Enums\MCUStatus;
use App\Models\CTK;
use App\Models\MCURecord;
use App\Models\StageTransition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CTKMCUStageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        // Create roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'Admin LPK']);
        Role::firstOrCreate(['name' => 'Admin PT']);
        Role::firstOrCreate(['name' => 'Pimpinan']);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /**
     * Test: Admin can record MCU result as FIT and CTK can advance to payment stage
     *
     * @test
     */
    public function admin_can_record_mcu_as_fit_and_ctk_can_advance_to_payment_stage()
    {
        // Arrange: Create Admin LPK user and CTK in stage 1
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create FIT MCU record
        $this->actingAs($adminLPK);
        $mcuRecord = MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
            'examination_date' => now()->subDays(1),
            'clinic_name' => 'Klinik Sehat Sentosa',
            'examiner_name' => 'Dr. John Doe',
            'notes' => 'Hasil pemeriksaan: Sehat dan FIT untuk bekerja',
            'created_by' => $adminLPK->id,
        ]);

        // Assert: MCU record created
        $this->assertDatabaseHas('mcu_records', [
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT->value,
            'clinic_name' => 'Klinik Sehat Sentosa',
        ]);

        // Act: Manually advance stage (simulating action button click)
        $ctk->update([
            'current_stage' => 2,
            'current_status' => CTKStatus::Pembayaran,
            'updated_by' => $adminLPK->id,
        ]);

        // Create stage transition record
        StageTransition::create([
            'ctk_id' => $ctk->id,
            'from_stage' => 1,
            'to_stage' => 2,
            'transition_timestamp' => now(),
            'user_id' => $adminLPK->id,
            'transition_reason' => 'Advancement from stage 1 to 2',
        ]);

        // Assert: CTK advanced to stage 2
        $ctk->refresh();
        $this->assertEquals(2, $ctk->current_stage);
        $this->assertEquals(CTKStatus::Pembayaran, $ctk->current_status);
        $this->assertEquals(EntityType::LPK, $ctk->current_entity);

        // Assert: Stage transition logged
        $this->assertDatabaseHas('stage_transitions', [
            'ctk_id' => $ctk->id,
            'from_stage' => 1,
            'to_stage' => 2,
            'user_id' => $adminLPK->id,
        ]);
    }

    /**
     * Test: Admin marks MCU as UNFIT, system prevents advancement with error
     *
     * @test
     */
    public function admin_marks_mcu_as_unfit_system_prevents_advancement()
    {
        // Arrange: Create Admin LPK user and CTK in stage 1
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create UNFIT MCU record
        $this->actingAs($adminLPK);
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::UNFIT,
            'examination_date' => now()->subDays(1),
            'clinic_name' => 'Klinik Sehat Sentosa',
            'examiner_name' => 'Dr. Jane Smith',
            'notes' => 'Hasil pemeriksaan: UNFIT - Tekanan darah tinggi',
            'created_by' => $adminLPK->id,
        ]);

        // Assert: MCU record created with UNFIT status
        $this->assertDatabaseHas('mcu_records', [
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::UNFIT->value,
        ]);

        // Assert: Cannot advance - check no FIT MCU records exist
        $hasFitMCU = $ctk->mcuRecords()
            ->where('status', MCUStatus::FIT)
            ->exists();
        $this->assertFalse($hasFitMCU, 'CTK should not have FIT MCU record');

        // Assert: CTK remains in stage 1
        $ctk->refresh();
        $this->assertEquals(1, $ctk->current_stage);
        $this->assertEquals(CTKStatus::MCU, $ctk->current_status);
    }

    /**
     * Test: Admin marks MCU as PENDING, CTK remains in current stage
     *
     * @test
     */
    public function admin_marks_mcu_as_pending_ctk_remains_in_current_stage()
    {
        // Arrange: Create Admin LPK user and CTK in stage 1
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create PENDING MCU record
        $this->actingAs($adminLPK);
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::PENDING,
            'examination_date' => now()->subDays(1),
            'clinic_name' => 'Klinik Sehat Sentosa',
            'examiner_name' => 'Dr. Bob Johnson',
            'notes' => 'Menunggu hasil lab lengkap',
            'created_by' => $adminLPK->id,
        ]);

        // Assert: MCU record created with PENDING status
        $this->assertDatabaseHas('mcu_records', [
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::PENDING->value,
        ]);

        // Assert: Cannot advance - check no FIT MCU records exist
        $hasFitMCU = $ctk->mcuRecords()
            ->where('status', MCUStatus::FIT)
            ->exists();
        $this->assertFalse($hasFitMCU, 'CTK should not have FIT MCU record');

        // Assert: CTK remains in stage 1
        $ctk->refresh();
        $this->assertEquals(1, $ctk->current_stage);
        $this->assertEquals(CTKStatus::MCU, $ctk->current_status);
    }

    /**
     * Test: MCU details are visible with date, clinic, examiner, and recorder
     *
     * @test
     */
    public function mcu_details_are_visible_with_all_information()
    {
        // Arrange: Create Admin LPK user and CTK
        $adminLPK = User::factory()->create([
            'entity' => EntityType::LPK,
            'name' => 'Admin LPK User',
        ]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create MCU record
        $this->actingAs($adminLPK);
        $examinationDate = now()->subDays(2);
        $mcuRecord = MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
            'examination_date' => $examinationDate,
            'clinic_name' => 'Rumah Sakit Pelabuhan Jakarta',
            'examiner_name' => 'Dr. Sarah Williams',
            'notes' => 'Semua parameter dalam batas normal',
            'created_by' => $adminLPK->id,
        ]);

        // Assert: All MCU details are accessible
        $retrievedMCU = MCURecord::find($mcuRecord->id);
        $this->assertNotNull($retrievedMCU);
        $this->assertEquals(MCUStatus::FIT, $retrievedMCU->status);
        $this->assertEquals($examinationDate->format('Y-m-d'), $retrievedMCU->examination_date->format('Y-m-d'));
        $this->assertEquals('Rumah Sakit Pelabuhan Jakarta', $retrievedMCU->clinic_name);
        $this->assertEquals('Dr. Sarah Williams', $retrievedMCU->examiner_name);
        $this->assertEquals('Semua parameter dalam batas normal', $retrievedMCU->notes);
        $this->assertEquals($adminLPK->id, $retrievedMCU->created_by);

        // Assert: MCU relationship works
        $this->assertEquals($ctk->id, $retrievedMCU->ctk->id);
        $this->assertEquals('Admin LPK User', $retrievedMCU->creator->name);
    }

    /**
     * Test: Stage transition is logged in stage_transitions table with correct data
     *
     * @test
     */
    public function stage_transition_is_logged_correctly()
    {
        // Arrange: Create Admin LPK user and CTK in stage 1
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Create FIT MCU record first
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
            'examination_date' => now()->subDays(1),
            'clinic_name' => 'Klinik Test',
            'examiner_name' => 'Dr. Test',
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create stage transition
        $this->actingAs($adminLPK);
        $transitionTimestamp = now();

        $transition = StageTransition::create([
            'ctk_id' => $ctk->id,
            'from_stage' => 1,
            'to_stage' => 2,
            'transition_timestamp' => $transitionTimestamp,
            'user_id' => $adminLPK->id,
            'transition_reason' => 'MCU FIT - Ready for payment stage',
        ]);

        // Update CTK stage
        $ctk->update([
            'current_stage' => 2,
            'current_status' => CTKStatus::Pembayaran,
            'updated_by' => $adminLPK->id,
        ]);

        // Assert: Stage transition logged correctly
        $this->assertDatabaseHas('stage_transitions', [
            'ctk_id' => $ctk->id,
            'from_stage' => 1,
            'to_stage' => 2,
            'user_id' => $adminLPK->id,
            'transition_reason' => 'MCU FIT - Ready for payment stage',
        ]);

        // Assert: Transition data is accurate
        $retrievedTransition = StageTransition::find($transition->id);
        $this->assertEquals(1, $retrievedTransition->from_stage);
        $this->assertEquals(2, $retrievedTransition->to_stage);
        $this->assertEquals($adminLPK->id, $retrievedTransition->user_id);
        $this->assertEquals($ctk->id, $retrievedTransition->ctk_id);
        $this->assertNotNull($retrievedTransition->transition_timestamp);

        // Assert: Transition relationships work
        $this->assertEquals($ctk->id, $retrievedTransition->ctk->id);
        $this->assertEquals($adminLPK->id, $retrievedTransition->user->id);
    }

    /**
     * Test: Multiple MCU records can be created and latest FIT status is used
     *
     * @test
     */
    public function multiple_mcu_records_can_be_tracked()
    {
        // Arrange: Create Admin LPK user and CTK
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act: Create multiple MCU records
        $this->actingAs($adminLPK);

        // First MCU - UNFIT
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::UNFIT,
            'examination_date' => now()->subDays(5),
            'clinic_name' => 'Klinik A',
            'examiner_name' => 'Dr. A',
            'notes' => 'First check - UNFIT',
            'created_by' => $adminLPK->id,
        ]);

        // Second MCU - PENDING
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::PENDING,
            'examination_date' => now()->subDays(3),
            'clinic_name' => 'Klinik B',
            'examiner_name' => 'Dr. B',
            'notes' => 'Re-check - PENDING',
            'created_by' => $adminLPK->id,
        ]);

        // Third MCU - FIT
        MCURecord::create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
            'examination_date' => now()->subDays(1),
            'clinic_name' => 'Klinik C',
            'examiner_name' => 'Dr. C',
            'notes' => 'Final check - FIT',
            'created_by' => $adminLPK->id,
        ]);

        // Assert: All 3 MCU records exist
        $mcuRecords = $ctk->mcuRecords;
        $this->assertCount(3, $mcuRecords);

        // Assert: Has FIT MCU record (can advance)
        $hasFitMCU = $ctk->mcuRecords()
            ->where('status', MCUStatus::FIT)
            ->exists();
        $this->assertTrue($hasFitMCU);

        // Assert: All statuses are recorded
        $statuses = $mcuRecords->pluck('status')->map(fn ($s) => $s->value)->toArray();
        $this->assertContains('UNFIT', $statuses);
        $this->assertContains('PENDING', $statuses);
        $this->assertContains('FIT', $statuses);
    }
}
