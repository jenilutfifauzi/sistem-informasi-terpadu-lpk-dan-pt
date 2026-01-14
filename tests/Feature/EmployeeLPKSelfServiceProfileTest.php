<?php

namespace Tests\Feature;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeLPKSelfServiceProfileTest extends TestCase
{
    protected User $instruktur;

    protected User $admin;

    protected EmployeeLPK $instrukturEmployee;

    protected EmployeeLPK $otherInstrukturEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->instruktur = User::factory()->create();
        $this->instruktur->syncRoles('instruktur');

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('admin_lpk');

        // Create employee record matching instruktur user
        $this->instrukturEmployee = EmployeeLPK::factory()->create([
            'email' => $this->instruktur->email,
            'jabatan' => JabatanLPK::Instruktur,
        ]);

        // Create another instruktur employee
        $this->otherInstrukturEmployee = EmployeeLPK::factory()->create([
            'jabatan' => JabatanLPK::Instruktur,
        ]);
    }

    public function test_instruktur_can_access_own_profile(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText($this->instrukturEmployee->nama_lengkap);
    }

    public function test_instruktur_can_view_own_personal_information(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText($this->instrukturEmployee->nama_lengkap)
            ->assertSeeText($this->instrukturEmployee->nik)
            ->assertSeeText($this->instrukturEmployee->email);
    }

    public function test_instruktur_cannot_access_other_profile(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->otherInstrukturEmployee->id}");

        $response->assertStatus(403);
    }

    public function test_profile_shows_personal_information_section(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText('Informasi Personal');
    }

    public function test_profile_shows_employment_information_section(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText('Informasi Kepegawaian');
    }

    public function test_profile_shows_compensation_information_section(): void
    {
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText('Kompensasi');
    }

    public function test_instruktur_can_see_sertifikat_section_if_jabatan_matches(): void
    {
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        $this->actingAs($this->admin)->put(
            "/admin/karyawan-lpks/{$this->instrukturEmployee->id}",
            ['sertifikat_path' => $file]
        );

        $this->instrukturEmployee->refresh();

        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText('Sertifikat Kompetensi');
    }

    public function test_instruktur_can_download_own_sertifikat_from_profile(): void
    {
        $file = UploadedFile::fake()->create('sertifikat.pdf', 1024, 'application/pdf');

        $this->actingAs($this->admin)->put(
            "/admin/karyawan-lpks/{$this->instrukturEmployee->id}",
            ['sertifikat_path' => $file]
        );

        $this->instrukturEmployee->refresh();

        $response = $this->actingAs($this->instruktur)
            ->get("/karyawan-lpk/{$this->instrukturEmployee->id}/sertifikat/download");

        $response->assertStatus(200);
    }

    public function test_admin_lpk_can_access_main_resource(): void
    {
        // Admin uses the main EmployeeLPKResource, not the profile resource
        $response = $this->actingAs($this->admin)
            ->get("/admin/karyawan-lpks/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText($this->instrukturEmployee->nama_lengkap);
    }

    public function test_instruktur_cannot_put_to_profile_resource(): void
    {
        // Profile resource only has view page, PUT should return 405
        $response = $this->actingAs($this->instruktur)
            ->put("/admin/profil-saya/{$this->instrukturEmployee->id}", [
                'nama_lengkap' => 'New Name',
            ]);

        $response->assertStatus(405);

        $this->instrukturEmployee->refresh();
        $this->assertNotEquals('New Name', $this->instrukturEmployee->nama_lengkap);
    }

    public function test_profile_shows_honor_information_when_present(): void
    {
        $this->instrukturEmployee->update([
            'honor_pokok' => 3000000,
            'honor_per_jam' => 50000,
        ]);

        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->instrukturEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText('Kompensasi')
            ->assertSeeText('Honor Pokok');
    }

    public function test_pimpinan_can_access_own_profile(): void
    {
        $pimpinan = User::factory()->create();
        $pimpinan->syncRoles('pimpinan');

        $pimpinanEmployee = EmployeeLPK::factory()->create([
            'email' => $pimpinan->email,
            'jabatan' => JabatanLPK::Staff,
        ]);

        $response = $this->actingAs($pimpinan)
            ->get("/admin/profil-saya/{$pimpinanEmployee->id}");

        $response->assertStatus(200)
            ->assertSeeText($pimpinanEmployee->nama_lengkap);
    }

    public function test_pimpinan_profile_does_not_show_sertifikat_section(): void
    {
        $pimpinan = User::factory()->create();
        $pimpinan->syncRoles('pimpinan');

        $pimpinanEmployee = EmployeeLPK::factory()->create([
            'email' => $pimpinan->email,
            'jabatan' => JabatanLPK::Staff,
        ]);

        $response = $this->actingAs($pimpinan)
            ->get("/admin/profil-saya/{$pimpinanEmployee->id}");

        $response->assertStatus(200)
            ->assertDontSeeText('Sertifikat Kompetensi');
    }

    public function test_profile_resource_enforces_email_based_access(): void
    {
        // Instruktur trying to access non-existent resource for other employees should fail
        $response = $this->actingAs($this->instruktur)
            ->get("/admin/profil-saya/{$this->otherInstrukturEmployee->id}");

        $response->assertStatus(403);
    }
}
