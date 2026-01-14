<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeeLPK;
use App\Models\User;
use Tests\TestCase;

class EmployeeLPKValidationTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
    }

    public function test_nama_lengkap_is_required(): void
    {
        $data = [
            'nik' => '1234567890123456',
            'email' => 'test@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record without nama_lengkap
        $this->assertCount(0, EmployeeLPK::where('email', 'test@example.com')->get());
    }

    public function test_nik_must_be_16_characters(): void
    {
        $data = [
            'nama_lengkap' => 'John Doe',
            'nik' => '123456789',  // Only 9 characters
            'email' => 'test@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with invalid NIK
        $this->assertCount(0, EmployeeLPK::where('email', 'test@example.com')->get());
    }

    public function test_nik_must_be_unique(): void
    {
        $existingNik = '1234567890123456';
        EmployeeLPK::factory()->create(['nik' => $existingNik]);

        $data = [
            'nama_lengkap' => 'Jane Doe',
            'nik' => $existingNik,
            'email' => 'jane@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => '456 Oak St',
            'nomor_telepon' => '081234567891',
            'jabatan' => JabatanLPK::AdminLPK->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 2500000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with duplicate NIK
        $this->assertCount(1, EmployeeLPK::where('nik', $existingNik)->get());
    }

    public function test_email_must_be_valid(): void
    {
        $data = [
            'nama_lengkap' => 'John Doe',
            'nik' => '1234567890123456',
            'email' => 'invalid-email',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 3000000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with invalid email
        $this->assertCount(0, EmployeeLPK::where('nik', '1234567890123456')->get());
    }

    public function test_email_must_be_unique(): void
    {
        $existingEmail = 'existing@example.com';
        EmployeeLPK::factory()->create(['email' => $existingEmail]);

        $data = [
            'nama_lengkap' => 'Jane Doe',
            'nik' => '1234567890123456',
            'email' => $existingEmail,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => '456 Oak St',
            'nomor_telepon' => '081234567891',
            'jabatan' => JabatanLPK::AdminLPK->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 2500000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with duplicate email
        $this->assertCount(1, EmployeeLPK::where('email', $existingEmail)->get());
    }

    public function test_tanggal_bergabung_cannot_be_before_tanggal_lahir(): void
    {
        $data = [
            'nama_lengkap' => 'John Doe',
            'nik' => '1234567890123456',
            'email' => 'john@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::Instruktur->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '1985-01-01',  // Before birth date
            'honor_pokok' => 3000000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with invalid date sequence
        $this->assertCount(0, EmployeeLPK::where('email', 'john@example.com')->get());
    }

    public function test_honor_per_jam_only_required_for_instruktur(): void
    {
        // Admin without honor_per_jam should be OK
        $data = [
            'nama_lengkap' => 'Admin User',
            'nik' => '1234567890123456',
            'email' => 'admin@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => '123 Main St',
            'nomor_telepon' => '081234567890',
            'jabatan' => JabatanLPK::AdminLPK->value,
            'status' => StatusKepegawaian::Aktif->value,
            'tanggal_bergabung' => '2024-01-01',
            'honor_pokok' => 2500000,
            // honor_per_jam not provided
        ];

        $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        $this->assertDatabaseHas('karyawan_lpk', [
            'email' => 'admin@example.com',
            'jabatan' => JabatanLPK::AdminLPK->value,
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

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create Instruktur without honor_per_jam
        $this->assertCount(0, EmployeeLPK::where('email', 'instruktur@example.com')->get());
    }

    public function test_honor_values_must_be_numeric(): void
    {
        $data = [
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
            'honor_pokok' => 'not a number',
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with invalid honor values
        $this->assertCount(0, EmployeeLPK::where('email', 'john@example.com')->get());
    }

    public function test_honor_values_must_be_non_negative(): void
    {
        $data = [
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
            'honor_pokok' => -1000000,
            'honor_per_jam' => 50000,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/karyawan-lpks', $data);

        // Should not create record with negative honor
        $this->assertCount(0, EmployeeLPK::where('email', 'john@example.com')->get());
    }
}
