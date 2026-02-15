<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all necessary permissions first
        $permissions = [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create super_admin role with all permissions
        $this->createSuperAdminRole();

        // Create 8 application roles
        $this->createAdminLPKRole();
        $this->createInstrukturRole();
        $this->createHRPTRole();
        $this->createAdminPTRole();
        $this->createLegalPTRole();
        $this->createKeuanganPTRole();
        $this->createKeuanganLPKRole();
        $this->createPimpinanRole();

        // Call CTK permissions seeder
        $this->call(CTKPermissionsSeeder::class);

        // Sync super_admin with all permissions again after CTK permissions are created
        $this->syncSuperAdminPermissions();
    }

    /**
     * Sync super_admin role with all permissions
     */
    private function syncSuperAdminPermissions(): void
    {
        $role = Role::where('name', 'super_admin')->first();
        if ($role) {
            $permissions = Permission::where('guard_name', 'web')->get();
            $role->syncPermissions($permissions);
        }
    }

    /**
     * Create super_admin role with all permissions
     */
    private function createSuperAdminRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $permissions = Permission::where('guard_name', 'web')->get();
        $role->syncPermissions($permissions);
    }

    /**
     * Create admin_lpk role with view_user and view_any_user permissions
     */
    private function createAdminLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_lpk', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user', 'view_any_user']);
    }

    /**
     * Create instruktur role with view_user permission only
     */
    private function createInstrukturRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'instruktur', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user']);
    }

    /**
     * Create hr_pt role with view_user, view_any_user, create_user, update_user permissions
     */
    private function createHRPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'hr_pt', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user', 'view_any_user', 'create_user', 'update_user']);
    }

    /**
     * Create admin_pt role with view_user and view_any_user permissions
     */
    private function createAdminPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_pt', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user', 'view_any_user']);
    }

    /**
     * Create legal_pt role with view_user permission only
     */
    private function createLegalPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'legal_pt', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user']);
    }

    /**
     * Create keuangan_pt role with view_user permission only
     */
    private function createKeuanganPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_pt', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user']);
    }

    /**
     * Create keuangan_lpk role with view_user permission only
     */
    private function createKeuanganLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_lpk', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user']);
    }

    /**
     * Create pimpinan role with all view_* permissions
     */
    private function createPimpinanRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        $role->syncPermissions(['view_user', 'view_any_user', 'view_role', 'view_any_role']);
    }
}
