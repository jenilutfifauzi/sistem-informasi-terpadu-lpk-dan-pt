<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeLPKSertifikatManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_instruktur_can_have_sertifikat_path(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_1.pdf',
        ]);

        $this->assertNotNull($employee->sertifikat_path);
        $this->assertEquals('certificates/instruktur_1.pdf', $employee->sertifikat_path);
    }

    public function test_sertifikat_path_is_nullable(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => null,
        ]);

        $this->assertNull($employee->sertifikat_path);
    }

    public function test_non_instruktur_can_have_null_sertifikat(): void
    {
        $adminEmployee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::AdminLPK,
            'sertifikat_path' => null,
        ]);

        $staffEmployee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Staff,
            'sertifikat_path' => null,
        ]);

        $this->assertNull($adminEmployee->sertifikat_path);
        $this->assertNull($staffEmployee->sertifikat_path);
    }

    public function test_sertifikat_download_url_accessor_exists(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_1.pdf',
        ]);

        // Test that the accessor exists and returns a value
        $downloadUrl = $employee->sertifikat_download_url;
        $this->assertNotNull($downloadUrl);
        $this->assertStringContainsString('/karyawan-lpk/', $downloadUrl);
    }

    public function test_sertifikat_download_url_null_when_no_path(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => null,
        ]);

        $downloadUrl = $employee->sertifikat_download_url;
        $this->assertNull($downloadUrl);
    }

    public function test_can_query_employees_with_sertifikat(): void
    {
        EmployeeLPK::factory(2)->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_1.pdf',
        ]);
        EmployeeLPK::factory(2)->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => null,
        ]);

        $employees = EmployeeLPK::whereNotNull('sertifikat_path')->get();
        $this->assertGreaterThanOrEqual(2, $employees->count());
    }

    public function test_can_query_employees_without_sertifikat(): void
    {
        EmployeeLPK::factory(2)->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_1.pdf',
        ]);
        EmployeeLPK::factory(2)->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => null,
        ]);

        $employees = EmployeeLPK::whereNull('sertifikat_path')->get();
        $this->assertGreaterThanOrEqual(2, $employees->count());
    }

    public function test_sertifikat_path_persists_on_update(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/original.pdf',
        ]);

        $employee->update(['sertifikat_path' => 'certificates/updated.pdf']);
        $employee->refresh();

        $this->assertEquals('certificates/updated.pdf', $employee->sertifikat_path);
    }

    public function test_sertifikat_path_can_be_cleared(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/original.pdf',
        ]);

        $employee->update(['sertifikat_path' => null]);
        $employee->refresh();

        $this->assertNull($employee->sertifikat_path);
    }

    public function test_multiple_instruktur_can_have_different_sertifikats(): void
    {
        $instruktur1 = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_1.pdf',
        ]);

        $instruktur2 = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'sertifikat_path' => 'certificates/instruktur_2.pdf',
        ]);

        $this->assertNotEquals($instruktur1->sertifikat_path, $instruktur2->sertifikat_path);
    }
}
