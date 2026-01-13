<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeeLPK;
use App\Models\User;
use Tests\TestCase;

class EmployeeLPKResourceTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with necessary permissions
        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
    }

    public function test_can_access_employee_list_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/karyawan-lpks');

        $response->assertStatus(200);
    }

    public function test_can_view_employee_list_with_records(): void
    {
        $employees = EmployeeLPK::factory(3)->create();

        $this->actingAs($this->admin)
            ->get('/admin/karyawan-lpks')
            ->assertStatus(200)
            ->assertSeeText('Karyawan LPK');
    }

    public function test_can_access_create_employee_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/karyawan-lpks/create');

        $response->assertStatus(200);
    }

    public function test_can_create_employee(): void
    {
        $employeeData = [
            'nama_lengkap' => 'John Doe',
            'nik' => '1234567890123456',
            'email' => 'john@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $employeeData);

        // Should redirect to list or view page
        $this->assertDatabaseHas('karyawan_lpk', [
            'nama_lengkap' => 'John Doe',
            'nik' => '1234567890123456',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_view_employee_detail(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/karyawan-lpks/{$employee->id}");

        $response->assertStatus(200)
            ->assertSeeText($employee->nama_lengkap)
            ->assertSeeText($employee->nik)
            ->assertSeeText($employee->email);
    }

    public function test_can_edit_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/karyawan-lpks/{$employee->id}/edit");

        $response->assertStatus(200)
            ->assertSeeText($employee->nama_lengkap);
    }

    public function test_can_update_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $updateData = [
            'nama_lengkap' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => StatusKepegawaian::Cuti->value,
        ];

        $this->actingAs($this->admin)->put("/admin/karyawan-lpks/{$employee->id}", $updateData);

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'nama_lengkap' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => StatusKepegawaian::Cuti->value,
        ]);
    }

    public function test_can_delete_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/karyawan-lpks/{$employee->id}");

        $this->assertSoftDeleted('karyawan_lpk', [
            'id' => $employee->id,
        ]);
    }

    public function test_can_restore_deleted_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employee->delete();

        $this->actingAs($this->admin)->post("/admin/karyawan-lpks/{$employee->id}/restore");

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    public function test_table_filters_by_status(): void
    {
        EmployeeLPK::factory(2)->create(['status' => StatusKepegawaian::Aktif]);
        EmployeeLPK::factory(1)->create(['status' => StatusKepegawaian::Cuti]);

        $this->actingAs($this->admin)
            ->get('/admin/karyawan-lpks?tableFilters[status][value]=Aktif')
            ->assertStatus(200);
    }

    public function test_table_filters_by_jabatan(): void
    {
        EmployeeLPK::factory(2)->create(['jabatan' => JabatanLPK::Instruktur]);
        EmployeeLPK::factory(1)->create(['jabatan' => JabatanLPK::AdminLPK]);

        $this->actingAs($this->admin)
            ->get('/admin/karyawan-lpks?tableFilters[jabatan][value]=Instruktur')
            ->assertStatus(200);
    }

    public function test_table_shows_trashed_filter(): void
    {
        EmployeeLPK::factory()->create()->delete();

        $this->actingAs($this->admin)
            ->get('/admin/karyawan-lpks?tableFilters[trashed][value]=with')
            ->assertStatus(200);
    }
}
