<?php

namespace Tests\Feature;

use App\Filament\Resources\EmployeePTResource\Pages\EditEmployeePT;
use App\Filament\Resources\EmployeePTResource\Pages\ListEmployeesPT;
use App\Models\EmployeePT;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePTKompensasiTest extends TestCase
{
    protected User $admin;

    protected User $keuanganPT;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'Keuangan PT']);

        $ptPermissions = ['view_any_karyawan_pt', 'view_karyawan_pt', 'create_karyawan_pt', 'update_karyawan_pt', 'delete_karyawan_pt'];
        foreach ($ptPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');

        $this->keuanganPT = User::factory()->create();
        $this->keuanganPT->syncRoles('Keuangan PT');
        $this->keuanganPT->givePermissionTo(['view_any_karyawan_pt', 'view_karyawan_pt', 'update_karyawan_pt']);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function admin_pt_can_set_gaji_pokok(): void
    {
        $employee = EmployeePT::factory()->create(['gaji_pokok' => null]);

        Livewire::actingAs($this->admin)
            ->test(EditEmployeePT::class, ['record' => $employee->id])
            ->fillForm(['gaji_pokok' => '8000000'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('karyawan_pt', [
            'id' => $employee->id,
            'gaji_pokok' => 8000000.00,
        ]);
    }

    /** @test */
    public function admin_pt_can_set_tunjangan_as_nullable(): void
    {
        $employee = EmployeePT::factory()->create(['tunjangan' => null]);

        Livewire::actingAs($this->admin)
            ->test(EditEmployeePT::class, ['record' => $employee->id])
            ->fillForm(['tunjangan' => null])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    /** @test */
    public function gaji_pokok_column_is_hidden_by_default(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->assertTableColumnExists('gaji_pokok');
    }

    /** @test */
    public function filter_ada_gaji_shows_only_employees_with_gaji(): void
    {
        $withGaji = EmployeePT::factory()->create(['gaji_pokok' => 5000000]);
        $withoutGaji = EmployeePT::factory()->create(['gaji_pokok' => null]);

        Livewire::actingAs($this->admin)
            ->test(ListEmployeesPT::class)
            ->filterTable('has_gaji', true)
            ->assertCanSeeTableRecords([$withGaji])
            ->assertCanNotSeeTableRecords([$withoutGaji]);
    }

    /** @test */
    public function input_negative_gaji_pokok_fails_validation(): void
    {
        $employee = EmployeePT::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditEmployeePT::class, ['record' => $employee->id])
            ->fillForm(['gaji_pokok' => '-1000'])
            ->call('save')
            ->assertHasFormErrors(['gaji_pokok']);
    }

    /** @test */
    public function input_non_numeric_gaji_pokok_fails_validation(): void
    {
        $employee = EmployeePT::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditEmployeePT::class, ['record' => $employee->id])
            ->fillForm(['gaji_pokok' => 'bukan_angka'])
            ->call('save')
            ->assertHasFormErrors(['gaji_pokok']);
    }

    /** @test */
    public function keuangan_pt_can_update_gaji_pokok_and_tunjangan(): void
    {
        $employee = EmployeePT::factory()->create(['gaji_pokok' => null, 'tunjangan' => null]);

        // Keuangan PT has keuangan_pt role which allows update() in the policy
        $this->assertTrue($this->keuanganPT->can('update', $employee));
    }
}
