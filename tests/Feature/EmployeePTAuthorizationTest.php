<?php

namespace Tests\Feature;

use App\Enums\EntityType;
use App\Filament\Resources\EmployeePTResource\Pages\ListEmployeesPT;
use App\Models\EmployeePT;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePTAuthorizationTest extends TestCase
{
    protected User $superAdmin;

    protected User $adminPT;

    protected User $keuanganPT;

    protected User $pimpinan;

    protected User $adminLPK;

    protected User $keuanganLPK;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'Admin PT']);
        Role::firstOrCreate(['name' => 'Keuangan PT']);
        Role::firstOrCreate(['name' => 'Pimpinan']);
        Role::firstOrCreate(['name' => 'Admin LPK']);
        Role::firstOrCreate(['name' => 'Keuangan LPK']);

        // Create karyawan pt permissions if not present
        $ptPermissions = [
            'view_any_karyawan_pt',
            'view_karyawan_pt',
            'create_karyawan_pt',
            'update_karyawan_pt',
            'delete_karyawan_pt',
            'restore_karyawan_pt',
            'force_delete_karyawan_pt',
        ];
        foreach ($ptPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // super_admin
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->syncRoles('super_admin');

        // Admin PT gets full karyawan pt permissions
        $this->adminPT = User::factory()->create();
        $this->adminPT->syncRoles('Admin PT');
        $this->adminPT->givePermissionTo($ptPermissions);

        // Keuangan PT gets view only + update (kompensasi)
        $this->keuanganPT = User::factory()->create();
        $this->keuanganPT->syncRoles('Keuangan PT');
        $this->keuanganPT->givePermissionTo(['view_any_karyawan_pt', 'view_karyawan_pt', 'update_karyawan_pt']);

        // Pimpinan gets view only
        $this->pimpinan = User::factory()->create();
        $this->pimpinan->syncRoles('Pimpinan');
        $this->pimpinan->givePermissionTo(['view_any_karyawan_pt', 'view_karyawan_pt']);

        // Admin LPK — no karyawan_pt permissions
        $this->adminLPK = User::factory()->create();
        $this->adminLPK->syncRoles('Admin LPK');

        // Keuangan LPK — no karyawan_pt permissions
        $this->keuanganLPK = User::factory()->create();
        $this->keuanganLPK->syncRoles('Keuangan LPK');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function admin_pt_has_full_crud_access(): void
    {
        Livewire::actingAs($this->adminPT)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful();
    }

    /** @test */
    public function admin_lpk_has_no_access_to_karyawan_pt(): void
    {
        $this->assertFalse(
            $this->adminLPK->can('viewAny', EmployeePT::class),
            'Admin LPK should not have access to Karyawan PT'
        );
    }

    /** @test */
    public function keuangan_lpk_has_no_access_to_karyawan_pt(): void
    {
        $this->assertFalse(
            $this->keuanganLPK->can('viewAny', EmployeePT::class),
            'Keuangan LPK should not have access to Karyawan PT'
        );
    }

    /** @test */
    public function keuangan_pt_can_view_employees(): void
    {
        Livewire::actingAs($this->keuanganPT)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful();
    }

    /** @test */
    public function keuangan_pt_cannot_create_employees(): void
    {
        $this->assertFalse(
            $this->keuanganPT->can('create', EmployeePT::class),
            'Keuangan PT should not be able to create Karyawan PT'
        );
    }

    /** @test */
    public function keuangan_pt_can_update_kompensasi_fields(): void
    {
        $employee = EmployeePT::factory()->create();

        // Keuangan PT has update_karyawan_pt permission AND keuangan_pt role
        // The policy update() allows keuangan_pt role
        $this->assertTrue($this->keuanganPT->can('update', $employee));
    }

    /** @test */
    public function pimpinan_can_view_employees_list(): void
    {
        Livewire::actingAs($this->pimpinan)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful();
    }

    /** @test */
    public function super_admin_has_full_access(): void
    {
        EmployeePT::factory()->create();

        Livewire::actingAs($this->superAdmin)
            ->test(ListEmployeesPT::class)
            ->assertSuccessful();
    }

    /** @test */
    public function entity_is_always_pt_never_lpk(): void
    {
        $employee = EmployeePT::factory()->create();
        $this->assertEquals(EntityType::PT, $employee->entity);
        $this->assertNotEquals(EntityType::LPK, $employee->entity);
    }
}
