<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PembayaranPusatPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pembayaran Pusat Permissions
        $permissions = [
            'view_pembayaran_pusat',
            'view_any_pembayaran_pusat',
            'create_pembayaran_pusat',
            'update_pembayaran_pusat',
            'delete_pembayaran_pusat',
            'restore_pembayaran_pusat',
            'force_delete_pembayaran_pusat',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign Pembayaran Pusat permissions to appropriate roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Admin LPK - Full CRUD on LPK payments
        $adminLPK = Role::where('name', 'Admin LPK')->first();
        if ($adminLPK) {
            $adminLPK->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
                'create_pembayaran_pusat',
                'update_pembayaran_pusat',
                'delete_pembayaran_pusat',
                'restore_pembayaran_pusat',
                'force_delete_pembayaran_pusat',
            ]);
        }

        // Admin PT - Full CRUD on PT payments
        $adminPT = Role::where('name', 'Admin PT')->first();
        if ($adminPT) {
            $adminPT->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
                'create_pembayaran_pusat',
                'update_pembayaran_pusat',
                'delete_pembayaran_pusat',
                'restore_pembayaran_pusat',
                'force_delete_pembayaran_pusat',
            ]);
        }

        // Keuangan LPK - Full access for LPK payments (financial role)
        $keuanganLPK = Role::where('name', 'Keuangan LPK')->first();
        if ($keuanganLPK) {
            $keuanganLPK->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
                'create_pembayaran_pusat',
                'update_pembayaran_pusat',
                'delete_pembayaran_pusat',
            ]);
        }

        // Keuangan PT - Full access for PT payments (financial role)
        $keuanganPT = Role::where('name', 'Keuangan PT')->first();
        if ($keuanganPT) {
            $keuanganPT->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
                'create_pembayaran_pusat',
                'update_pembayaran_pusat',
                'delete_pembayaran_pusat',
            ]);
        }

        // Pimpinan - Read-only access to all payments (both PT and LPK)
        $pimpinan = Role::where('name', 'Pimpinan')->first();
        if ($pimpinan) {
            $pimpinan->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
            ]);
        }

        // Super Admin - Full access to all payments
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'view_pembayaran_pusat',
                'view_any_pembayaran_pusat',
                'create_pembayaran_pusat',
                'update_pembayaran_pusat',
                'delete_pembayaran_pusat',
                'restore_pembayaran_pusat',
                'force_delete_pembayaran_pusat',
            ]);
        }
    }
}
