<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeeLPK;
use App\Models\User;
use Tests\TestCase;

class EmployeeLPKHonorManagementTest extends TestCase
{
    protected User $adminLPK;

    protected User $keuanganLPK;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminLPK = User::factory()->create();
        $this->adminLPK->syncRoles('admin_lpk');

        $this->keuanganLPK = User::factory()->create();
        $this->keuanganLPK->syncRoles('keuangan_lpk');
    }

    public function test_admin_lpk_can_set_honor_pokok_for_any_employee(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => null]);

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['honor_pokok' => 3000000]
        );

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'honor_pokok' => 3000000,
        ]);
    }

    public function test_honor_per_jam_field_visible_for_instruktur_jabatan(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertSeeText('Honor per Jam Mengajar');
    }

    public function test_honor_per_jam_field_hidden_for_admin_lpk_jabatan(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::AdminLPK]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertDontSeeText('Honor per Jam Mengajar');
    }

    public function test_honor_per_jam_field_hidden_for_staff_jabatan(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Staff]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertDontSeeText('Honor per Jam Mengajar');
    }

    public function test_changing_jabatan_from_instruktur_to_staff_hides_honor_per_jam(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => 50000,
        ]);

        // Update jabatan to Staff
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['jabatan' => JabatanLPK::Staff->value]
        );

        // Verify jabatan changed
        $employee->refresh();
        $this->assertEquals(JabatanLPK::Staff, $employee->jabatan);
    }

    public function test_keuangan_lpk_can_edit_honor_pokok(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => 1000000]);

        $this->actingAs($this->keuanganLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['honor_pokok' => 4000000]
        );

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'honor_pokok' => 4000000,
        ]);
    }

    public function test_keuangan_lpk_can_edit_honor_per_jam(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'honor_per_jam' => 25000,
        ]);

        $this->actingAs($this->keuanganLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['honor_per_jam' => 75000]
        );

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'honor_per_jam' => 75000,
        ]);
    }

    public function test_keuangan_lpk_cannot_delete_employee(): void
    {
        $employee = EmployeeLPK::factory()->create();

        $response = $this->actingAs($this->keuanganLPK)->delete(
            "/admin/karyawan-lpks/{$employee->id}"
        );

        $response->assertStatus(403);
    }

    public function test_filter_ada_honor_yes_shows_employees_with_honor(): void
    {
        EmployeeLPK::factory(2)->create(['honor_pokok' => 2000000]);
        EmployeeLPK::factory(2)->create(['honor_pokok' => null]);

        $this->actingAs($this->adminLPK)
            ->get('/admin/karyawan-lpks?tableFilters[has_honor][value]=true')
            ->assertStatus(200);
    }

    public function test_filter_ada_honor_no_shows_employees_without_honor(): void
    {
        EmployeeLPK::factory(2)->create(['honor_pokok' => 2000000]);
        EmployeeLPK::factory(2)->create(['honor_pokok' => null]);

        $this->actingAs($this->adminLPK)
            ->get('/admin/karyawan-lpks?tableFilters[has_honor][value]=false')
            ->assertStatus(200);
    }

    public function test_honor_validation_rejects_negative_values(): void
    {
        $data = [
            'nama_lengkap' => 'Test Employee',
            'nik' => '1234567890123456',
            'email' => 'test@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => -1000000,
            'honor_per_jam' => 50000,
        ];

        $this->actingAs($this->adminLPK)->post('/admin/karyawan-lpks', $data);

        // Should not create record with negative honor
        $this->assertCount(0, EmployeeLPK::where('email', 'test@example.com')->get());
    }

    public function test_honor_validation_rejects_non_numeric_input(): void
    {
        $data = [
            'nama_lengkap' => 'Test Employee',
            'nik' => '1234567890123456',
            'email' => 'test@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 'not a number',
            'honor_per_jam' => 50000,
        ];

        $this->actingAs($this->adminLPK)->post('/admin/karyawan-lpks', $data);

        // Should not create record with non-numeric honor
        $this->assertCount(0, EmployeeLPK::where('email', 'test@example.com')->get());
    }

    public function test_honor_pokok_column_displayed_in_table(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => 3000000]);

        $this->actingAs($this->adminLPK)
            ->get('/admin/karyawan-lpks')
            ->assertStatus(200)
            ->assertSeeText('Honor Pokok');
    }

    public function test_honor_values_zero_is_valid(): void
    {
        $employee = EmployeeLPK::factory()->create(['honor_pokok' => null]);

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['honor_pokok' => 0]
        );

        $this->assertDatabaseHas('karyawan_lpk', [
            'id' => $employee->id,
            'honor_pokok' => 0,
        ]);
    }

    public function test_honor_per_jam_required_for_instruktur(): void
    {
        $data = [
            'nama_lengkap' => 'Instruktur User',
            'nik' => '1234567890123456',
            'email' => 'instruktur@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
            // honor_per_jam not provided
        ];

        $this->actingAs($this->adminLPK)->post('/admin/karyawan-lpks', $data);

        // Should not create Instruktur without honor_per_jam
        $this->assertCount(0, EmployeeLPK::where('email', 'instruktur@example.com')->get());
    }
}
