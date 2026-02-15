<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Base test class for CTK actions tests.
 *
 * Uses database transactions instead of RefreshDatabase per DATABASE_SAFETY.md.
 * All database changes are automatically rolled back after each test.
 */
abstract class CTKActionsTestBase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start transaction that will rollback after each test
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        // Automatically rollback all database changes
        DB::rollBack();

        parent::tearDown();
    }

    /**
     * Helper method to authenticate as a user with specific role and entity.
     *
     * @param  string  $role  Role name (e.g., 'Admin LPK', 'Admin PT', 'Pimpinan', 'super_admin')
     * @param  \App\Enums\EntityType|null  $entity  Entity affiliation (LPK or PT)
     */
    protected function actingAsUserWithRole(string $role, ?\App\Enums\EntityType $entity = null): \App\Models\User
    {
        $user = \App\Models\User::factory()->create([
            'entity' => $entity,
        ]);

        // Create role if it doesn't exist
        $roleModel = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

        $user->assignRole($roleModel);

        // Grant necessary CTK permissions
        $permissions = [
            'view_any_ctk',
            'view_ctk',
            'create_ctk',
            'update_ctk',
            'delete_ctk',
            'restore_ctk',
            'force_delete_ctk',
        ];

        foreach ($permissions as $permission) {
            $permissionModel = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

            if (! $roleModel->hasPermissionTo($permissionModel)) {
                $roleModel->givePermissionTo($permissionModel);
            }
        }

        // Refresh user to load permissions
        $user->refresh();

        $this->actingAs($user);

        return $user;
    }

    /**
     * Helper method to create a CTK at a specific stage.
     *
     * @param  int  $stage  Stage number (1-15)
     * @param  \App\Enums\EntityType|null  $entity  Optional entity override
     */
    protected function createCTKAtStage(int $stage, ?\App\Enums\EntityType $entity = null): \App\Models\CTK
    {
        $factory = \App\Models\CTK::factory();

        // Determine entity based on stage if not specified
        if ($entity === null) {
            $entity = $stage <= 5 ? \App\Enums\EntityType::LPK : \App\Enums\EntityType::PT;
        }

        $statusMap = [
            1 => \App\Enums\CTKStatus::MCU,
            2 => \App\Enums\CTKStatus::Pembayaran,
            3 => \App\Enums\CTKStatus::SoalBerkas,
            4 => \App\Enums\CTKStatus::Paspor,
            5 => \App\Enums\CTKStatus::BelajarDiLPK,
            6 => \App\Enums\CTKStatus::Screening1,
            7 => \App\Enums\CTKStatus::InterviewUser,
            8 => \App\Enums\CTKStatus::IjinDesa,
            9 => \App\Enums\CTKStatus::Rekomendasi,
            10 => \App\Enums\CTKStatus::WP,
            11 => \App\Enums\CTKStatus::ApplyVisa,
            12 => \App\Enums\CTKStatus::MedicalFull,
            13 => \App\Enums\CTKStatus::Visa,
            14 => \App\Enums\CTKStatus::OPP,
            15 => \App\Enums\CTKStatus::Terbang,
        ];

        return $factory->create([
            'current_stage' => $stage,
            'current_entity' => $entity,
            'current_status' => $statusMap[$stage] ?? \App\Enums\CTKStatus::MCU,
        ]);
    }
}
