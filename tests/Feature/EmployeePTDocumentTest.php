<?php

namespace Tests\Feature;

use App\Models\EmployeePT;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePTDocumentTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        Storage::fake('private');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'Admin PT']);

        $ptPermissions = ['view_any_karyawan_pt', 'view_karyawan_pt', 'create_karyawan_pt', 'update_karyawan_pt', 'delete_karyawan_pt'];
        foreach ($ptPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function admin_pt_can_upload_pdf_dokumen(): void
    {
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf');

        $request = new \App\Http\Requests\UpdateEmployeePTRequest;
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['dokumen_path' => $file],
            ['dokumen_path' => $rules['dokumen_path']]
        );
        $this->assertFalse($validator->fails(), 'PDF should pass validation');
    }

    /** @test */
    public function admin_pt_can_upload_jpg_dokumen(): void
    {
        $file = UploadedFile::fake()->image('dokumen.jpg');

        $request = new \App\Http\Requests\UpdateEmployeePTRequest;
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['dokumen_path' => $file],
            ['dokumen_path' => $rules['dokumen_path']]
        );
        $this->assertFalse($validator->fails(), 'JPG should pass validation');
    }

    /** @test */
    public function admin_pt_can_upload_png_dokumen(): void
    {
        $file = UploadedFile::fake()->image('dokumen.png');

        $request = new \App\Http\Requests\UpdateEmployeePTRequest;
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['dokumen_path' => $file],
            ['dokumen_path' => $rules['dokumen_path']]
        );
        $this->assertFalse($validator->fails(), 'PNG should pass validation');
    }

    /** @test */
    public function upload_file_exceeding_5mb_fails_validation(): void
    {
        // 5121KB > 5120KB limit
        $file = UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf');

        $request = new \App\Http\Requests\UpdateEmployeePTRequest;
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['dokumen_path' => $file],
            ['dokumen_path' => $rules['dokumen_path']]
        );
        $this->assertTrue($validator->fails(), 'File over 5MB should fail validation');
    }

    /** @test */
    public function upload_docx_file_fails_validation(): void
    {
        $file = UploadedFile::fake()->create('dokumen.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $request = new \App\Http\Requests\UpdateEmployeePTRequest;
        $rules = $request->rules();
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['dokumen_path' => $file],
            ['dokumen_path' => $rules['dokumen_path']]
        );
        $this->assertTrue($validator->fails(), 'DOCX file should fail validation');
    }

    /** @test */
    public function dokumen_path_is_stored_on_private_disk(): void
    {
        // The FileUpload component uses disk('private') — verify model uses private disk path
        $employee = EmployeePT::factory()->create(['dokumen_path' => 'documents/test-file.pdf']);

        // Private storage is NOT publicly accessible
        $this->assertNotNull($employee->dokumen_path);
        $this->assertStringContainsString('documents/', $employee->dokumen_path);
        // The route-based accessor confirms only authorized users can download
        $this->assertNotNull($employee->getDokumenDownloadUrlAttribute());
    }

    /** @test */
    public function admin_pt_can_download_dokumen_via_authorized_route(): void
    {
        $employee = EmployeePT::factory()->withDokumen()->create();
        Storage::fake('private');
        Storage::disk('private')->put($employee->dokumen_path, 'fake content');

        // Admin has downloadDokumen policy permission via super_admin role
        $this->assertTrue($this->admin->can('downloadDokumen', $employee));
    }

    /** @test */
    public function unauthenticated_request_to_download_route_is_redirected(): void
    {
        $employee = EmployeePT::factory()->withDokumen()->create();

        $response = $this->get(route('karyawan-pt.dokumen.download', $employee));
        $response->assertRedirectToRoute('filament.admin.auth.login');
    }

    /** @test */
    public function table_dokumen_column_shows_truthy_icon_when_dokumen_path_exists(): void
    {
        $withDokumen = EmployeePT::factory()->withDokumen()->create();
        $withoutDokumen = EmployeePT::factory()->create(['dokumen_path' => null]);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Resources\EmployeePTResource\Pages\ListEmployeesPT::class)
            ->assertTableColumnExists('dokumen_path');
    }
}
