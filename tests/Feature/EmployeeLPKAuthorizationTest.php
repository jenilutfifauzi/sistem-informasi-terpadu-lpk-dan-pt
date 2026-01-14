<?php

namespace Tests\Feature;

use App\Models\EmployeeLPK;
use App\Models\User;
use Tests\TestCase;

class EmployeeLPKAuthorizationTest extends TestCase
{
    protected User $superAdmin;

    protected User $admin;

    protected User $pimpinan;

    protected User $instruktur;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->syncRoles('super_admin');

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('admin_lpk');

        $this->pimpinan = User::factory()->create();
        $this->pimpinan->syncRoles('pimpinan_lpk');

        $this->instruktur = User::factory()->create();
        $this->instruktur->syncRoles('instruktur');

        $this->staff = User::factory()->create();
        $this->staff->syncRoles('staff_lpk');
    }

    public function test_super_admin_can_view_any_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->superAdmin)->get("/admin/karyawan-lpks/{$employee->id}");

        $response->assertStatus(200);
    }

    public function test_admin_lpk_can_view_any_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/karyawan-lpks/{$employee->id}");

        $response->assertStatus(200);
    }

    public function test_pimpinan_can_view_any_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->pimpinan)->get("/admin/karyawan-lpks/{$employee->id}");

        $response->assertStatus(200);
    }

    public function test_instruktur_can_only_view_own_profile(): void
    {
        $ownEmployee = EmployeeLPK::factory()->create(['email' => $this->instruktur->email]);
        $otherEmployee = EmployeeLPK::factory()->create();

        // Can view own profile
        $response = $this->actingAs($this->instruktur)->get("/admin/karyawan-lpks/{$ownEmployee->id}");
        $response->assertStatus(200);

        // Cannot view others' profiles
        $response = $this->actingAs($this->instruktur)->get("/admin/karyawan-lpks/{$otherEmployee->id}");
        $response->assertStatus(403);
    }

    public function test_staff_cannot_view_employee_list(): void
    {
        $response = $this->actingAs($this->staff)->get('/admin/karyawan-lpks');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_employee(): void
    {
        $data = [
            'nama_lengkap' => 'New Employee',
            'nik' => '1234567890123456',
            'email' => 'new@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => 'Instruktur',
            'status' => 'Aktif',
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->superAdmin)->post('/admin/karyawan-lpks', $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'email' => 'new@example.com',
        ]);
    }

    public function test_admin_lpk_can_create_employee(): void
    {
        $data = [
            'nama_lengkap' => 'New Employee',
            'nik' => '1234567890123456',
            'email' => 'new@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => 'Instruktur',
            'status' => 'Aktif',
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'email' => 'new@example.com',
        ]);
    }

    public function test_instruktur_cannot_create_employee(): void
    {
        $data = [
            'nama_lengkap' => 'New Employee',
            'nik' => '1234567890123456',
            'email' => 'new@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => 'Instruktur',
            'status' => 'Aktif',
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->instruktur)->post('/admin/karyawan-lpks', $data);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $data = [
            'nama_lengkap' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->superAdmin)->put("/admin/karyawan-lpks/{$employee->id}", $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'nama_lengkap' => 'Updated Name',
        ]);
    }

    public function test_admin_lpk_can_update_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $data = [
            'nama_lengkap' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->admin)->put("/admin/karyawan-lpks/{$employee->id}", $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'nama_lengkap' => 'Updated Name',
        ]);
    }

    public function test_instruktur_can_update_own_profile(): void
    {
        $employee = EmployeeLPK::factory()->create(['email' => $this->instruktur->email]);

        $data = [
            'nama_lengkap' => 'Updated Name',
            'email' => $this->instruktur->email,
        ];

        $response = $this->actingAs($this->instruktur)->put("/admin/karyawan-lpks/{$employee->id}", $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'nama_lengkap' => 'Updated Name',
        ]);
    }

    public function test_instruktur_cannot_update_other_profiles(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $data = [
            'nama_lengkap' => 'Updated Name',
        ];

        $response = $this->actingAs($this->instruktur)->put("/admin/karyawan-lpks/{$employee->id}", $data);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_delete_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->superAdmin)->delete("/admin/karyawan-lpks/{$employee->id}");

        $this->assertSoftDeleted('karyawan_lpk', ['id' => $employee->id]);
    }

    public function test_admin_lpk_can_delete_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/karyawan-lpks/{$employee->id}");

        $this->assertSoftDeleted('karyawan_lpk', ['id' => $employee->id]);
    }

    public function test_instruktur_cannot_delete_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->instruktur)->delete("/admin/karyawan-lpks/{$employee->id}");

        $response->assertStatus(403);
    }

    public function test_super_admin_can_restore_deleted_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->superAdmin)->post("/admin/karyawan-lpks/{$employee->id}/restore");

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_lpk_can_restore_deleted_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->admin)->post("/admin/karyawan-lpks/{$employee->id}/restore");

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    public function test_instruktur_cannot_restore_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->instruktur)->post("/admin/karyawan-lpks/{$employee->id}/restore");

        $response->assertStatus(403);
    }
}
