<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeePTPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_any_karyawan_pt',
            'view_karyawan_pt',
            'create_karyawan_pt',
            'update_karyawan_pt',
            'delete_karyawan_pt',
            'restore_karyawan_pt',
            'force_delete_karyawan_pt',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->assignPermissionsToRoles();
    }

    /**
     * Assign karyawan PT permissions to appropriate roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Admin PT — full CRUD
        $adminPT = Role::where('name', 'Admin PT')->first();
        if ($adminPT) {
            $adminPT->givePermissionTo([
                'view_any_karyawan_pt',
                'view_karyawan_pt',
                'create_karyawan_pt',
                'update_karyawan_pt',
                'delete_karyawan_pt',
                'restore_karyawan_pt',
                'force_delete_karyawan_pt',
            ]);
        }

        // Keuangan PT — viewAny + view only (update restricted to kompensasi fields via form visibility)
        $keuanganPT = Role::where('name', 'Keuangan PT')->first();
        if ($keuanganPT) {
            $keuanganPT->givePermissionTo([
                'view_any_karyawan_pt',
                'view_karyawan_pt',
            ]);
        }

        // Pimpinan — viewAny + view only
        $pimpinan = Role::where('name', 'Pimpinan')->first();
        if ($pimpinan) {
            $pimpinan->givePermissionTo([
                'view_any_karyawan_pt',
                'view_karyawan_pt',
            ]);
        }

        // Super Admin — full access
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view_any_karyawan_pt',
                'view_karyawan_pt',
                'create_karyawan_pt',
                'update_karyawan_pt',
                'delete_karyawan_pt',
                'restore_karyawan_pt',
                'force_delete_karyawan_pt',
            ]);
        }
    }
}
