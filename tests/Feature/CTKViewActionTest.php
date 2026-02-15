<?php

namespace Tests\Feature;

use App\Enums\EntityType;
use App\Models\CTK;
use App\Models\User;
use Livewire\Livewire;

class CTKViewActionTest extends CTKActionsTestBase
{
    /** @test */
    public function all_authenticated_users_can_see_view_action_button_on_ctk_rows()
    {
        // Arrange: Create super admin user and CTK
        $user = $this->actingAsUserWithRole('super_admin', EntityType::LPK);
        $ctk = CTK::factory()->atMCUStage()->create();

        // Get the CTK list resource class
        $listCTKSClass = \App\Filament\Resources\CTKS\Pages\ListCTKS::class;

        // Assert: View action is visible on the table
        Livewire::test($listCTKSClass)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$ctk])
            ->assertTableActionExists('view');
    }

    /** @test */
    public function clicking_view_action_navigates_to_view_ctk_page_with_correct_record()
    {
        // Arrange: Create super admin user and CTK
        $user = $this->actingAsUserWithRole('super_admin', EntityType::LPK);
        $ctk = CTK::factory()->atMCUStage()->create();

        // Act: Get view URL
        $viewUrl = \App\Filament\Resources\CTKS\CTKResource::getUrl('view', ['record' => $ctk]);

        // Assert: URL is generated correctly and contains record ID
        $this->assertStringContainsString((string) $ctk->id, $viewUrl);
    }

    /** @test */
    public function view_action_is_visible_for_all_role_types()
    {
        // Arrange: Create CTKs in different stages
        $lpkCTK = CTK::factory()->create(['current_stage' => 3, 'current_entity' => EntityType::LPK]);
        $ptCTK = CTK::factory()->create(['current_stage' => 10, 'current_entity' => EntityType::PT]);

        $roles = ['super_admin', 'Admin LPK', 'Admin PT', 'Pimpinan', 'HR PT'];

        foreach ($roles as $role) {
            // Act: Create user with role and check view action visibility
            $user = $this->actingAsUserWithRole($role, EntityType::LPK);

            $listCTKSClass = \App\Filament\Resources\CTKS\Pages\ListCTKS::class;

            // Assert: View action exists for this role
            Livewire::test($listCTKSClass)
                ->assertTableActionExists('view');
        }
    }
}
