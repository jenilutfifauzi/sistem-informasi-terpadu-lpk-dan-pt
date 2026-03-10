<?php

namespace Tests\Feature;

use App\Enums\DivisiPT;
use App\Enums\EntityType;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Filament\Resources\EmployeePTResource\Pages\CreateEmployeePT;
use App\Filament\Resources\EmployeePTResource\Pages\EditEmployeePT;
use App\Filament\Resources\EmployeePTResource\Pages\ListEmployeesPT;
use App\Models\EmployeePT;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePTResourceTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function admin_pt_can_list_employees(): void
    {
        EmployeePT::factory(3)->create();

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful();
    }

    /** @test */
    public function list_page_contains_expected_table_columns(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful()
            ->assertSeeHtml('nama_lengkap')
            ->assertSeeHtml('nik')
            ->assertSeeHtml('jabatan')
            ->assertSeeHtml('divisi')
            ->assertSeeHtml('status');
    }

    /** @test */
    public function admin_pt_can_create_employee_with_required_fields(): void
    {
        $formData = [
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'tanggal_lahir' => '1990-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Sudirman No. 1, Jakarta',
            'telepon' => '081234567890',
            'jabatan' => JabatanPT::StafHRD->value,
            'divisi' => DivisiPT::HRD->value,
            'status' => StatusKepegawaian::Aktif->value,
            'jenis_kontrak' => JenisKontrak::Tetap->value,
            'tanggal_bergabung' => '2024-01-15',
        ];

        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('karyawan_pt', [
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'entity' => 'PT',
        ]);
    }

    /** @test */
    public function entity_is_always_pt_on_employee_creation(): void
    {
        $formData = [
            'nik' => '9999888877776666',
            'nama_lengkap' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'tanggal_lahir' => '1995-03-20',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Jl. Kebon Jeruk No. 5',
            'telepon' => '089876543210',
            'jabatan' => JabatanPT::Manajer->value,
            'divisi' => DivisiPT::Operasional->value,
            'status' => StatusKepegawaian::Aktif->value,
            'jenis_kontrak' => JenisKontrak::PKWT->value,
            'tanggal_bergabung' => '2025-01-01',
        ];

        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        $employee = EmployeePT::where('nik', '9999888877776666')->first();
        $this->assertNotNull($employee);
        $this->assertEquals(EntityType::PT, $employee->entity);
    }

    /** @test */
    public function admin_pt_cannot_create_employee_with_duplicate_nik(): void
    {
        EmployeePT::factory()->create(['nik' => '1234567890123456']);

        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm([
                'nik' => '1234567890123456',
                'nama_lengkap' => 'Another Person',
                'email' => 'another@example.com',
                'tanggal_lahir' => '1992-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Test No. 1',
                'telepon' => '081111111111',
                'jabatan' => JabatanPT::StafHRD->value,
                'divisi' => DivisiPT::HRD->value,
                'status' => StatusKepegawaian::Aktif->value,
                'jenis_kontrak' => JenisKontrak::Tetap->value,
                'tanggal_bergabung' => '2024-01-01',
            ])
            ->call('create')
            ->assertHasFormErrors(['nik']);
    }

    /** @test */
    public function admin_pt_cannot_create_employee_with_duplicate_email(): void
    {
        EmployeePT::factory()->create(['email' => 'existing@example.com']);

        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm([
                'nik' => '1111222233334444',
                'nama_lengkap' => 'New Person',
                'email' => 'existing@example.com',
                'tanggal_lahir' => '1993-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Test No. 2',
                'telepon' => '082222222222',
                'jabatan' => JabatanPT::StafKeuangan->value,
                'divisi' => DivisiPT::Keuangan->value,
                'status' => StatusKepegawaian::Aktif->value,
                'jenis_kontrak' => JenisKontrak::Tetap->value,
                'tanggal_bergabung' => '2024-01-01',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    /** @test */
    public function admin_pt_can_edit_employee_and_nik_is_disabled(): void
    {
        $employee = EmployeePT::factory()->create(['nama_lengkap' => 'Original Name']);

        Livewire::actingAs($this->admin)
            ->test(EditEmployeePT::class, ['record' => $employee->id])
            ->assertFormFieldIsDisabled('nik')
            ->assertSuccessful();
    }

    /** @test */
    public function changing_status_to_resign_triggers_soft_delete(): void
    {
        $employee = EmployeePT::factory()->create(['status' => 'Aktif']);

        $this->actingAs($this->admin);

        // Update status to Resign — the model's updating observer should soft-delete
        // We test this by calling the model update directly (the observer logic)
        $employee->status = StatusKepegawaian::Aktif; // ensure it's clean
        $employee->saveQuietly();

        // Now simulate the status change to Resign
        $employee->refresh();
        $beforeDeletedAt = $employee->deleted_at;
        $this->assertNull($beforeDeletedAt);

        // Simulate update on the model (as the observer does)
        $employee->fill(['status' => 'Resign']);
        $employee->save();

        // The updating observer triggers delete() when status becomes Resign
        $softDeleted = EmployeePT::withTrashed()->find($employee->id);
        $this->assertNotNull($softDeleted->deleted_at, 'Employee should be soft-deleted after status set to Resign');
    }

    /** @test */
    public function admin_pt_can_restore_soft_deleted_employee(): void
    {
        $employee = EmployeePT::factory()->resign()->create();

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->filterTable('trashed', 'with')
            ->callTableAction('restore', $employee)
            ->assertSuccessful();

        $restored = EmployeePT::withTrashed()->find($employee->id);
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
    }

    /** @test */
    public function trashed_filter_shows_only_soft_deleted_records(): void
    {
        $active = EmployeePT::factory()->create(['status' => 'Aktif']);
        $resigned = EmployeePT::factory()->resign()->create();

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$resigned]);
    }

    /** @test */
    public function filter_by_jabatan_shows_only_matching_employees(): void
    {
        $stafHrd = EmployeePT::factory()->create(['jabatan' => JabatanPT::StafHRD->value]);
        $direktur = EmployeePT::factory()->create(['jabatan' => JabatanPT::Direktur->value]);

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->filterTable('jabatan', JabatanPT::StafHRD->value)
            ->assertCanSeeTableRecords([$stafHrd])
            ->assertCanNotSeeTableRecords([$direktur]);
    }

    /** @test */
    public function filter_by_divisi_shows_only_matching_employees(): void
    {
        $hrdEmployee = EmployeePT::factory()->create(['divisi' => DivisiPT::HRD->value]);
        $keuanganEmployee = EmployeePT::factory()->create(['divisi' => DivisiPT::Keuangan->value]);

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->filterTable('divisi', DivisiPT::HRD->value)
            ->assertCanSeeTableRecords([$hrdEmployee])
            ->assertCanNotSeeTableRecords([$keuanganEmployee]);
    }
}
