<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeLPKSertifikatManagementTest extends TestCase
{
    protected User $adminLPK;

    protected User $pimpinan;

    protected User $instruktur;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->adminLPK = User::factory()->create();
        $this->adminLPK->syncRoles('admin_lpk');

        $this->pimpinan = User::factory()->create();
        $this->pimpinan->syncRoles('pimpinan_lpk');

        $this->instruktur = User::factory()->create();
        $this->instruktur->syncRoles('instruktur');
    }

    public function test_admin_lpk_can_upload_pdf_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertNotNull($employee->sertifikat_path);
        Storage::disk('private')->assertExists($employee->sertifikat_path);
    }

    public function test_admin_lpk_can_upload_jpg_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.jpg', 1024, 'image/jpeg');

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertNotNull($employee->sertifikat_path);
        Storage::disk('private')->assertExists($employee->sertifikat_path);
    }

    public function test_admin_lpk_can_upload_png_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.png', 1024, 'image/png');

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertNotNull($employee->sertifikat_path);
        Storage::disk('private')->assertExists($employee->sertifikat_path);
    }

    public function test_upload_5mb_file_succeeds(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        // 5120 KB = 5 MB (the size limit)
        $file = UploadedFile::fake()->create('sertifikat.pdf', 5120, 'application/pdf');

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertNotNull($employee->sertifikat_path);
    }

    public function test_upload_larger_than_5mb_fails(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        // 5121 KB exceeds the 5120 KB limit
        $file = UploadedFile::fake()->create('sertifikat.pdf', 5121, 'application/pdf');

        $response = $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        // Should fail validation
        $employee->refresh();
        $this->assertNull($employee->sertifikat_path);
    }

    public function test_upload_invalid_format_fails(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertNull($employee->sertifikat_path);
    }

    public function test_sertifikat_section_visible_for_instruktur(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertSeeText('Sertifikat Kompetensi');
    }

    public function test_sertifikat_section_hidden_for_admin_lpk(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::AdminLPK]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertDontSeeText('Sertifikat Kompetensi');
    }

    public function test_sertifikat_section_hidden_for_staff(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Staff]);

        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertDontSeeText('Sertifikat Kompetensi');
    }

    public function test_file_saved_to_private_directory(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();
        $this->assertTrue(Storage::disk('private')->exists($employee->sertifikat_path));
        $this->assertStringContainsString('certificates', $employee->sertifikat_path);
    }

    public function test_admin_lpk_can_download_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        // Upload file first
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();

        // Download file
        $response = $this->actingAs($this->adminLPK)
            ->get("/karyawan-lpk/{$employee->id}/sertifikat/download");

        $response->assertStatus(200);
    }

    public function test_pimpinan_can_download_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        // Upload file as admin
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();

        // Download as pimpinan
        $response = $this->actingAs($this->pimpinan)
            ->get("/karyawan-lpk/{$employee->id}/sertifikat/download");

        $response->assertStatus(200);
    }

    public function test_instruktur_can_download_own_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'email' => $this->instruktur->email,
        ]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        // Upload file as admin
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();

        // Download as own sertifikat
        $response = $this->actingAs($this->instruktur)
            ->get("/karyawan-lpk/{$employee->id}/sertifikat/download");

        $response->assertStatus(200);
    }

    public function test_instruktur_cannot_download_other_sertifikat(): void
    {
        $otherInstruktur = User::factory()->create();
        $otherInstruktur->syncRoles('instruktur');

        $employee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
            'email' => $otherInstruktur->email,
        ]);
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        // Upload file as admin
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file]
        );

        $employee->refresh();

        // Try download as different instruktur
        $response = $this->actingAs($this->instruktur)
            ->get("/karyawan-lpk/{$employee->id}/sertifikat/download");

        $response->assertStatus(403);
    }

    public function test_upload_new_sertifikat_replaces_old_file(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        $file1 = UploadedFile::fake()->create('sertifikat1.pdf', 1024, 'application/pdf');
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file1]
        );

        $employee->refresh();
        $oldPath = $employee->sertifikat_path;

        $file2 = UploadedFile::fake()->create('sertifikat2.pdf', 1024, 'application/pdf');
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['sertifikat_path' => $file2]
        );

        $employee->refresh();
        $this->assertNotEquals($oldPath, $employee->sertifikat_path);
    }

    public function test_changing_jabatan_from_instruktur_to_staff_hides_sertifikat(): void
    {
        $employee = EmployeeLPK::factory()->create(['jabatan' => JabatanLPK::Instruktur]);

        // Update jabatan to Staff
        $this->actingAs($this->adminLPK)->put(
            "/admin/karyawan-lpks/{$employee->id}",
            ['jabatan' => JabatanLPK::Staff->value]
        );

        $employee->refresh();
        $this->assertEquals(JabatanLPK::Staff, $employee->jabatan);

        // Verify sertifikat section is hidden
        $this->actingAs($this->adminLPK)
            ->get("/admin/karyawan-lpks/{$employee->id}/edit")
            ->assertStatus(200)
            ->assertDontSeeText('Sertifikat Kompetensi');
    }
}
