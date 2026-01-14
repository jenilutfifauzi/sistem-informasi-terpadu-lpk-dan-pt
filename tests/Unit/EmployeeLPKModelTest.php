<?php

namespace Tests\Unit;

use App\Enums\EntityType;
use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeeLPK;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class EmployeeLPKModelTest extends TestCase
{
    public function test_employee_has_correct_default_entity(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $this->assertEquals(EntityType::LPK->value, $employee->entity);
    }

    public function test_employee_cannot_change_entity_after_creation(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $originalEntity = $employee->entity;

        // Attempt to change entity (should be prevented by boot method)
        $employee->update(['entity' => EntityType::PT->value]);

        // Reload and verify entity remains unchanged
        $employee->refresh();
        $this->assertEquals(EntityType::LPK->value, $employee->entity);
    }

    public function test_employee_has_correct_default_status(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $this->assertEquals(StatusKepegawaian::Aktif->value, $employee->status);
    }

    public function test_employee_soft_deletes_on_delete(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employeeId = $employee->id;

        $employee->delete();

        $this->assertSoftDeleted('karyawan_lpk', ['id' => $employeeId]);
    }

    public function test_soft_deleted_employee_not_retrieved_by_default(): void
    {
        EmployeeLPK::factory(2)->create();
        $deletedEmployee = EmployeeLPK::factory()->create();
        $deletedEmployee->delete();

        $activeCount = EmployeeLPK::count();

        $this->assertEquals(2, $activeCount);
    }

    public function test_soft_deleted_employee_retrieved_with_trashed(): void
    {
        EmployeeLPK::factory(2)->create();
        $deletedEmployee = EmployeeLPK::factory()->create();
        $deletedEmployee->delete();

        $allCount = EmployeeLPK::withTrashed()->count();

        $this->assertEquals(3, $allCount);
    }

    public function test_soft_deleted_employee_can_be_restored(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employee->delete();

        $employee->restore();

        $this->assertNull($employee->deleted_at);
        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    public function test_employee_creation_logs_activity(): void
    {
        Activity::query()->delete(); // Clear activity log

        $employee = EmployeeLPK::factory()->create([
            'nama_lengkap' => 'Test Employee',
        ]);

        $activity = Activity::where('subject_type', EmployeeLPK::class)
            ->where('subject_id', $employee->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
    }

    public function test_employee_update_logs_activity(): void
    {
        $employee = EmployeeLPK::factory()->create();
        Activity::query()->delete(); // Clear activity log

        $employee->update([
            'nama_lengkap' => 'Updated Name',
        ]);

        $activity = Activity::where('subject_type', EmployeeLPK::class)
            ->where('subject_id', $employee->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('updated', $activity->event);
    }

    public function test_employee_deletion_logs_activity(): void
    {
        $employee = EmployeeLPK::factory()->create();
        Activity::query()->delete(); // Clear activity log

        $employee->delete();

        $activity = Activity::where('subject_type', EmployeeLPK::class)
            ->where('subject_id', $employee->id)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals('deleted', $activity->event);
    }

    public function test_employee_has_created_by_relationship(): void
    {
        $creator = User::factory()->create();
        $employee = EmployeeLPK::factory()->create(['created_by' => $creator->id]);

        $this->assertInstanceOf(User::class, $employee->createdBy);
        $this->assertEquals($creator->id, $employee->createdBy->id);
    }

    public function test_employee_has_updated_by_relationship(): void
    {
        $updater = User::factory()->create();
        $employee = EmployeeLPK::factory()->create(['updated_by' => $updater->id]);

        $this->assertInstanceOf(User::class, $employee->updatedBy);
        $this->assertEquals($updater->id, $employee->updatedBy->id);
    }

    public function test_employee_jabatan_enum_cast(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
        ]);

        $this->assertInstanceOf(JabatanLPK::class, $employee->jabatan);
        $this->assertEquals(JabatanLPK::Instruktur, $employee->jabatan);
    }

    public function test_employee_status_enum_cast(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'status' => StatusKepegawaian::Cuti,
        ]);

        $this->assertInstanceOf(StatusKepegawaian::class, $employee->status);
        $this->assertEquals(StatusKepegawaian::Cuti, $employee->status);
    }

    public function test_employee_entity_enum_cast(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $this->assertInstanceOf(EntityType::class, $employee->entity);
        $this->assertEquals(EntityType::LPK, $employee->entity);
    }

    public function test_employee_honor_values_are_decimal_cast(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ]);

        $this->assertIsFloat($employee->honor_pokok) || $this->assertIsInt($employee->honor_pokok);
        $this->assertIsFloat($employee->honor_per_jam) || $this->assertIsInt($employee->honor_per_jam);
    }

    public function test_employee_fillable_fields(): void
    {
        $fillableFields = [
            'nama_lengkap',
            'nik',
            'email',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'nomor_telepon',
            'jabatan',
            'status',
            'tanggal_bergabung',
            'honor_pokok',
            'honor_per_jam',
            'created_by',
            'updated_by',
        ];

        foreach ($fillableFields as $field) {
            $this->assertContains($field, EmployeeLPK::make()->getFillable());
        }
    }

    public function test_employee_protected_attributes(): void
    {
        $protectedAttributes = ['entity'];

        $employee = EmployeeLPK::factory()->create();

        foreach ($protectedAttributes as $attr) {
            $this->assertContains($attr, $employee->getProtected());
        }
    }
}
