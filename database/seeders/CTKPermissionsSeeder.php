<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CTKPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // CTK Permissions
        $permissions = [
            'view_ctk',
            'view_any_ctk',
            'create_ctk',
            'update_ctk',
            'delete_ctk',
            'restore_ctk',
            'force_delete_ctk',
            'override_ctk_immutability',
            'view_ctk_audit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign CTK permissions to appropriate roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Admin LPK - Can create, view, and update CTK in LPK stages (1-5)
        $adminLPK = Role::where('name', 'Admin LPK')->first();
        if ($adminLPK) {
            $adminLPK->givePermissionTo([
                'view_ctk',
                'view_any_ctk',
                'create_ctk',
                'update_ctk',
                'view_ctk_audit',
            ]);
        }

        // Admin PT - Can view and update CTK in PT stages (6-15)
        $adminPT = Role::where('name', 'Admin PT')->first();
        if ($adminPT) {
            $adminPT->givePermissionTo([
                'view_ctk',
                'view_any_ctk',
                'update_ctk',
                'view_ctk_audit',
            ]);
        }

        // Pimpinan - Read-only access to all CTK
        $pimpinan = Role::where('name', 'Pimpinan')->first();
        if ($pimpinan) {
            $pimpinan->givePermissionTo([
                'view_ctk',
                'view_any_ctk',
                'view_ctk_audit',
            ]);
        }

        // Super Admin - Full access to all CTK operations
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view_ctk',
                'view_any_ctk',
                'create_ctk',
                'update_ctk',
                'delete_ctk',
                'restore_ctk',
                'force_delete_ctk',
                'override_ctk_immutability',
                'view_ctk_audit',
            ]);
        }
    }
}
