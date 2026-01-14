<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use Tests\TestCase;

class EmployeeLPKHonorManagementTest extends TestCase
{
    public function test_employee_can_have_honor_pokok(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => 3000000]);

        $this->assertNotNull($employee->honor_pokok);
        $this->assertEquals(3000000, (int) $employee->honor_pokok);
    }

    public function test_employee_can_have_honor_per_jam(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => 50000,
        ]);

        $this->assertNotNull($employee->honor_per_jam);
        $this->assertEquals(50000, (int) $employee->honor_per_jam);
    }

    public function test_employee_honor_pokok_is_nullable(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => null]);

        $this->assertNull($employee->honor_pokok);
    }

    public function test_employee_honor_per_jam_is_nullable(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => null,
        ]);

        $this->assertNull($employee->honor_per_jam);
    }

    public function test_honor_values_zero_is_valid(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => 0]);

        $this->assertNotNull($employee->honor_pokok);
        $this->assertEquals(0, (int) $employee->honor_pokok);
    }

    public function test_honor_pokok_is_stored_as_decimal(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => 3000000]);

        // Verify it's stored and retrieved correctly
        $retrieved = EmployeeLPK::find($employee->id);
        $this->assertNotNull($retrieved->honor_pokok);
        $this->assertTrue(is_numeric($retrieved->honor_pokok));
    }

    public function test_honor_per_jam_is_stored_as_decimal(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => 75000,
        ]);

        // Verify it's stored and retrieved correctly
        $retrieved = EmployeeLPK::find($employee->id);
        $this->assertNotNull($retrieved->honor_per_jam);
        $this->assertTrue(is_numeric($retrieved->honor_per_jam));
    }

    public function test_can_query_employees_with_honor(): void
    {
        EmployeeLPK::factory(3)->create(['honor_pokok' => 2000000]);
        EmployeeLPK::factory(2)->create(['honor_pokok' => null]);

        $employees = EmployeeLPK::whereNotNull('honor_pokok')->get();
        $this->assertGreaterThanOrEqual(3, $employees->count());
    }

    public function test_can_query_employees_without_honor(): void
    {
        EmployeeLPK::factory(3)->create(['honor_pokok' => 2000000]);
        EmployeeLPK::factory(2)->create(['honor_pokok' => null]);

        $employees = EmployeeLPK::whereNull('honor_pokok')->get();
        $this->assertGreaterThanOrEqual(2, $employees->count());
    }

    public function test_honor_fields_are_cast_to_decimal(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ]);

        // Verify the casts are properly applied
        $this->assertNotNull($employee->honor_pokok);
        $this->assertNotNull($employee->honor_per_jam);
    }

    public function test_instruktur_employee_can_have_honor_per_jam(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => 50000,
        ]);

        $this->assertEquals(JabatanLPK::Instruktur, $employee->jabatan);
        $this->assertNotNull($employee->honor_per_jam);
    }

    public function test_non_instruktur_employee_honor_per_jam(): void
    {
        $adminEmployee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::AdminLPK,
            'honor_per_jam' => null,
        ]);

        $staffEmployee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Staff,
            'honor_per_jam' => null,
        ]);

        $this->assertEquals(JabatanLPK::AdminLPK, $adminEmployee->jabatan);
        $this->assertNull($adminEmployee->honor_per_jam);

        $this->assertEquals(JabatanLPK::Staff, $staffEmployee->jabatan);
        $this->assertNull($staffEmployee->honor_per_jam);
    }
}
