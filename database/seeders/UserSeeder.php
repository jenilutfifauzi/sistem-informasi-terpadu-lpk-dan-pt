<?php

namespace Database\Seeders;

use App\Enums\EntityType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createOrUpdateUser(
            name: 'Super Admin',
            email: 'superadmin@lpk.com',
            entity: EntityType::PT,
            roles: ['super_admin']
        );

        $this->createOrUpdateUser(
            name: 'Admin LPK',
            email: 'admin@lpk.com',
            entity: EntityType::LPK,
            roles: ['admin_lpk', 'Admin LPK']
        );

        $this->createOrUpdateUser(
            name: 'Instruktur',
            email: 'instruktur@lpk.com',
            entity: EntityType::LPK,
            roles: ['instruktur', 'Instruktur']
        );

        $this->createOrUpdateUser(
            name: 'HR PT',
            email: 'hr@pt.com',
            entity: EntityType::PT,
            roles: ['hr_pt', 'HR PT']
        );

        $this->createOrUpdateUser(
            name: 'Admin PT',
            email: 'admin@pt.com',
            entity: EntityType::PT,
            roles: ['admin_pt', 'Admin PT']
        );

        $this->createOrUpdateUser(
            name: 'Legal PT',
            email: 'legal@pt.com',
            entity: EntityType::PT,
            roles: ['legal_pt', 'Legal PT']
        );

        $this->createOrUpdateUser(
            name: 'Keuangan PT',
            email: 'keuangan@pt.com',
            entity: EntityType::PT,
            roles: ['keuangan_pt', 'Keuangan PT']
        );

        $this->createOrUpdateUser(
            name: 'Keuangan LPK',
            email: 'keuangan@lpk.com',
            entity: EntityType::LPK,
            roles: ['keuangan_lpk', 'Keuangan LPK']
        );

        $this->createOrUpdateUser(
            name: 'Pimpinan',
            email: 'pimpinan@lpk.com',
            entity: EntityType::PT,
            roles: ['pimpinan', 'Pimpinan']
        );
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function createOrUpdateUser(string $name, string $email, EntityType $entity, array $roles): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'entity' => $entity,
            ]
        );

        $availableRoles = array_values(array_filter($roles, fn (string $role): bool => $user->getRoleNames()->contains($role) || \Spatie\Permission\Models\Role::where('name', $role)->exists()));

        if ($availableRoles !== []) {
            $user->syncRoles($availableRoles);
        }
    }
}
