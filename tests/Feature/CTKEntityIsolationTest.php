<?php

namespace Tests\Feature;

use App\Enums\EntityType;
use App\Models\CTK;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CTKEntityIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'Admin LPK']);
        Role::create(['name' => 'Admin PT']);
        Role::create(['name' => 'Pimpinan']);

        // Seed permissions for viewing and updating CTK
        $this->seedPermissions();
    }

    protected function seedPermissions(): void
    {
        \Spatie\Permission\Models\Permission::create(['name' => 'view_ctk']);
        \Spatie\Permission\Models\Permission::create(['name' => 'view_any_ctk']);
        \Spatie\Permission\Models\Permission::create(['name' => 'update_ctk']);
        \Spatie\Permission\Models\Permission::create(['name' => 'create_ctk']);

        // Give roles the permissions
        Role::findByName('Admin LPK')->givePermissionTo(['view_ctk', 'view_any_ctk', 'update_ctk', 'create_ctk']);
        Role::findByName('Admin PT')->givePermissionTo(['view_ctk', 'view_any_ctk', 'update_ctk', 'create_ctk']);
        Role::findByName('super_admin')->givePermissionTo(['view_ctk', 'view_any_ctk', 'update_ctk', 'create_ctk']);
        Role::findByName('Pimpinan')->givePermissionTo(['view_ctk', 'view_any_ctk']);
    }

    /** @test */
    public function admin_lpk_can_only_see_lpk_stages_1_to_5()
    {
        // Create Admin LPK user
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        // Create CTKs in different stages
        $lpkStage1 = CTK::factory()->create([
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
        ]);
        $lpkStage5 = CTK::factory()->create([
            'current_stage' => 5,
            'current_entity' => EntityType::LPK,
        ]);
        $ptStage6 = CTK::factory()->create([
            'current_stage' => 6,
            'current_entity' => EntityType::PT,
        ]);

        $this->actingAs($adminLPK);

        // Admin LPK should be able to view LPK CTKs
        $this->assertTrue($adminLPK->can('view', $lpkStage1));
        $this->assertTrue($adminLPK->can('view', $lpkStage5));

        // Admin LPK should NOT be able to view PT CTKs
        $this->assertFalse($adminLPK->can('view', $ptStage6));
    }

    /** @test */
    public function admin_pt_can_only_see_pt_stages_6_to_15()
    {
        // Create Admin PT user
        $adminPT = User::factory()->create(['entity' => EntityType::PT]);
        $adminPT->assignRole('Admin PT');

        // Create CTKs in different stages
        $lpkStage5 = CTK::factory()->create([
            'current_stage' => 5,
            'current_entity' => EntityType::LPK,
        ]);
        $ptStage6 = CTK::factory()->create([
            'current_stage' => 6,
            'current_entity' => EntityType::PT,
        ]);
        $ptStage15 = CTK::factory()->create([
            'current_stage' => 15,
            'current_entity' => EntityType::PT,
        ]);

        $this->actingAs($adminPT);

        // Admin PT should NOT be able to view LPK CTKs
        $this->assertFalse($adminPT->can('view', $lpkStage5));

        // Admin PT should be able to view PT CTKs
        $this->assertTrue($adminPT->can('view', $ptStage6));
        $this->assertTrue($adminPT->can('view', $ptStage15));
    }

    /** @test */
    public function super_admin_can_see_all_ctks()
    {
        // Create Super Admin user
        $superAdmin = User::factory()->create(['entity' => EntityType::LPK]);
        $superAdmin->assignRole('super_admin');

        // Create CTKs in different stages/entities
        $lpkStage1 = CTK::factory()->create([
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
        ]);
        $ptStage15 = CTK::factory()->create([
            'current_stage' => 15,
            'current_entity' => EntityType::PT,
        ]);

        $this->actingAs($superAdmin);

        // Super Admin should be able to view all
        $this->assertTrue($superAdmin->can('view', $lpkStage1));
        $this->assertTrue($superAdmin->can('view', $ptStage15));
    }

    /** @test */
    public function entity_filter_applied_in_eloquent_query()
    {
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        // Create multiple CTKs
        $lpkStage1 = CTK::factory()->create(['current_stage' => 1, 'current_entity' => EntityType::LPK]);
        $lpkStage3 = CTK::factory()->create(['current_stage' => 3, 'current_entity' => EntityType::LPK]);
        $lpkStage5 = CTK::factory()->create(['current_stage' => 5, 'current_entity' => EntityType::LPK]);
        $ptStage6 = CTK::factory()->create(['current_stage' => 6, 'current_entity' => EntityType::PT]);
        $ptStage10 = CTK::factory()->create(['current_stage' => 10, 'current_entity' => EntityType::PT]);

        $this->actingAs($adminLPK);

        // Query should only return LPK stages 1-5
        $ctkResource = app(\App\Filament\Resources\CTKS\CTKResource::class);
        $query = $ctkResource->getEloquentQuery();
        $records = $query->pluck('id')->toArray();

        $this->assertContains($lpkStage1->id, $records);
        $this->assertContains($lpkStage3->id, $records);
        $this->assertContains($lpkStage5->id, $records);
        $this->assertNotContains($ptStage6->id, $records);
        $this->assertNotContains($ptStage10->id, $records);
    }

    /** @test */
    public function admin_pt_query_returns_only_pt_stages()
    {
        $adminPT = User::factory()->create(['entity' => EntityType::PT]);
        $adminPT->assignRole('Admin PT');

        // Create multiple CTKs
        $lpkStage1 = CTK::factory()->create(['current_stage' => 1, 'current_entity' => EntityType::LPK]);
        $ptStage6 = CTK::factory()->create(['current_stage' => 6, 'current_entity' => EntityType::PT]);
        $ptStage10 = CTK::factory()->create(['current_stage' => 10, 'current_entity' => EntityType::PT]);
        $ptStage15 = CTK::factory()->create(['current_stage' => 15, 'current_entity' => EntityType::PT]);

        $this->actingAs($adminPT);

        // Query should only return PT stages 6-15
        $ctkResource = app(\App\Filament\Resources\CTKS\CTKResource::class);
        $query = $ctkResource->getEloquentQuery();
        $records = $query->pluck('id')->toArray();

        $this->assertNotContains($lpkStage1->id, $records);
        $this->assertContains($ptStage6->id, $records);
        $this->assertContains($ptStage10->id, $records);
        $this->assertContains($ptStage15->id, $records);
    }
}
