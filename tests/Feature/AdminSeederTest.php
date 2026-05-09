<?php

namespace Tests\Feature;

use App\Enums\EntityType;
use App\Models\Asset;
use App\Models\User;
use Database\Seeders\AssetDemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_seeder_creates_complete_admin_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        $adminLPK = User::where('email', 'admin@lpk.com')->firstOrFail();
        $adminPT = User::where('email', 'admin@pt.com')->firstOrFail();

        $this->assertSame(EntityType::LPK, $adminLPK->entity);
        $this->assertTrue($adminLPK->hasRole('admin_lpk'));
        $this->assertTrue($adminLPK->hasRole('Admin LPK'));

        $this->assertSame(EntityType::PT, $adminPT->entity);
        $this->assertTrue($adminPT->hasRole('admin_pt'));
        $this->assertTrue($adminPT->hasRole('Admin PT'));
    }

    public function test_asset_demo_seeder_uses_seeded_admin_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        $lpkBefore = Asset::withoutGlobalScope('entity')->where('entity', EntityType::LPK)->count();
        $ptBefore = Asset::withoutGlobalScope('entity')->where('entity', EntityType::PT)->count();

        $this->seed(AssetDemoSeeder::class);

        $lpkAfter = Asset::withoutGlobalScope('entity')->where('entity', EntityType::LPK)->count();
        $ptAfter = Asset::withoutGlobalScope('entity')->where('entity', EntityType::PT)->count();

        $this->assertSame($lpkBefore + 25, $lpkAfter);
        $this->assertSame($ptBefore + 25, $ptAfter);
    }
}
