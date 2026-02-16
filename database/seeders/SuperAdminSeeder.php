<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating roles and permissions...');

        // Create roles if not exist
        $roles = [
            'super_admin' => 'PT',
            'Pimpinan' => 'PT',
            'Admin PT' => 'PT',
            'Admin LPK' => 'LPK',
            'Keuangan PT' => 'PT',
            'Keuangan LPK' => 'LPK',
        ];

        foreach ($roles as $roleName => $entity) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            $this->command->info("  ✓ Role: {$roleName}");
        }

        $this->command->info('');
        $this->command->info('🔄 Creating permissions...');

        // Create basic permissions if not exist
        $permissions = [
            // User management
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',

            // CTK permissions
            'view_ctk',
            'view_any_ctk',
            'create_ctk',
            'update_ctk',
            'delete_ctk',

            // Asset permissions
            'view_asset',
            'view_any_asset',
            'create_asset',
            'update_asset',
            'delete_asset',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
        $this->command->info('  ✓ Created '.count($permissions).' permissions');

        $this->command->info('');
        $this->command->info('🔄 Assigning permissions to roles...');

        // Assign all permissions to super_admin
        $superAdminRole = Role::findByName('super_admin');
        $superAdminRole->syncPermissions(Permission::all());
        $this->command->info('  ✓ super_admin: all permissions');

        // Pimpinan: read-only access
        $pimpinanRole = Role::findByName('Pimpinan');
        $pimpinanRole->syncPermissions([
            'view_user', 'view_any_user',
            'view_ctk', 'view_any_ctk',
            'view_asset', 'view_any_asset',
        ]);
        $this->command->info('  ✓ Pimpinan: read-only permissions');

        // Admin PT: full access to PT entity
        $adminPTRole = Role::findByName('Admin PT');
        $adminPTRole->syncPermissions([
            'view_user', 'view_any_user', 'create_user', 'update_user',
            'view_ctk', 'view_any_ctk', 'create_ctk', 'update_ctk', 'delete_ctk',
            'view_asset', 'view_any_asset', 'create_asset', 'update_asset', 'delete_asset',
        ]);
        $this->command->info('  ✓ Admin PT: full permissions');

        // Admin LPK: full access to LPK entity
        $adminLPKRole = Role::findByName('Admin LPK');
        $adminLPKRole->syncPermissions([
            'view_user', 'view_any_user', 'create_user', 'update_user',
            'view_ctk', 'view_any_ctk', 'create_ctk', 'update_ctk', 'delete_ctk',
            'view_asset', 'view_any_asset', 'create_asset', 'update_asset', 'delete_asset',
        ]);
        $this->command->info('  ✓ Admin LPK: full permissions');

        // Keuangan: read-only
        $keuanganPTRole = Role::findByName('Keuangan PT');
        $keuanganPTRole->syncPermissions([
            'view_ctk', 'view_any_ctk',
            'view_asset', 'view_any_asset',
        ]);
        $this->command->info('  ✓ Keuangan PT: read-only permissions');

        $keuanganLPKRole = Role::findByName('Keuangan LPK');
        $keuanganLPKRole->syncPermissions([
            'view_ctk', 'view_any_ctk',
            'view_asset', 'view_any_asset',
        ]);
        $this->command->info('  ✓ Keuangan LPK: read-only permissions');

        $this->command->info('');
        $this->command->info('🔄 Creating superadmin user...');

        // Check if superadmin already exists
        $superadminEmail = 'admin@sitlpk.com';
        $existingSuperadmin = User::where('email', $superadminEmail)->first();

        if ($existingSuperadmin) {
            // Update existing superadmin
            $existingSuperadmin->assignRole('super_admin');
            $this->command->warn("  ⚠️  Superadmin already exists: {$existingSuperadmin->email}");
        } else {
            // Create new superadmin
            $superadmin = User::create([
                'name' => 'Super Admin',
                'email' => $superadminEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'entity' => 'PT',
            ]);

            $superadmin->assignRole('super_admin');

            $this->command->info('  ✓ Superadmin created!');
            $this->command->info("     Email: {$superadminEmail}");
            $this->command->warn('     Password: password (⚠️  GANTI SEGERA!)');
        }

        $this->command->info('');
        $this->command->info('✅ SuperAdminSeeder completed!');
        $this->command->info('');
        $this->command->line('Summary:');
        $this->command->line('  - Roles: '.Role::count());
        $this->command->line('  - Permissions: '.Permission::count());
        $this->command->line('  - Total Users: '.User::count());
        $this->command->line('  - Users with roles: '.User::has('roles')->count());
    }
}
