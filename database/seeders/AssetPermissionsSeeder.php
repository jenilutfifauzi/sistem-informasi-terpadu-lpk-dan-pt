<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssetPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Asset Permissions
        $permissions = [
            'view_asset',
            'view_any_asset',
            'create_asset',
            'update_asset',
            'delete_asset',
            'restore_asset',
            'force_delete_asset',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign Asset permissions to appropriate roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Admin LPK - Full CRUD on LPK assets
        $adminLPK = Role::where('name', 'Admin LPK')->first();
        if ($adminLPK) {
            $adminLPK->givePermissionTo([
                'view_asset',
                'view_any_asset',
                'create_asset',
                'update_asset',
                'delete_asset',
                'restore_asset',
                'force_delete_asset',
            ]);
        }

        // Admin PT - Full CRUD on PT assets
        $adminPT = Role::where('name', 'Admin PT')->first();
        if ($adminPT) {
            $adminPT->givePermissionTo([
                'view_asset',
                'view_any_asset',
                'create_asset',
                'update_asset',
                'delete_asset',
                'restore_asset',
                'force_delete_asset',
            ]);
        }

        // Keuangan LPK - View only for LPK assets
        $keuanganLPK = Role::where('name', 'Keuangan LPK')->first();
        if ($keuanganLPK) {
            $keuanganLPK->givePermissionTo([
                'view_asset',
                'view_any_asset',
            ]);
        }

        // Keuangan PT - View only for PT assets
        $keuanganPT = Role::where('name', 'Keuangan PT')->first();
        if ($keuanganPT) {
            $keuanganPT->givePermissionTo([
                'view_asset',
                'view_any_asset',
            ]);
        }

        // Pimpinan - Read-only access to all assets (both PT and LPK)
        $pimpinan = Role::where('name', 'Pimpinan')->first();
        if ($pimpinan) {
            $pimpinan->givePermissionTo([
                'view_asset',
                'view_any_asset',
            ]);
        }

        // Super Admin - Full access to all assets
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view_asset',
                'view_any_asset',
                'create_asset',
                'update_asset',
                'delete_asset',
                'restore_asset',
                'force_delete_asset',
            ]);
        }
    }
}
