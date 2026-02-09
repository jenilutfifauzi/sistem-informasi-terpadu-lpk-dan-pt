<?php

namespace Database\Seeders;

use App\Enums\EntityType;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create admin users for LPK and PT
        $adminLPK = User::where('entity', EntityType::LPK)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Admin LPK');
            })
            ->first();

        $adminPT = User::where('entity', EntityType::PT)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Admin PT');
            })
            ->first();

        // Create 25 LPK assets
        if ($adminLPK) {
            Asset::factory()
                ->count(25)
                ->forLPK()
                ->create([
                    'created_by' => $adminLPK->id,
                ]);

            $this->command->info('✅ Created 25 LPK assets');
        } else {
            $this->command->warn('⚠️  No Admin LPK user found, skipping LPK assets');
        }

        // Create 25 PT assets
        if ($adminPT) {
            Asset::factory()
                ->count(25)
                ->forPT()
                ->create([
                    'created_by' => $adminPT->id,
                ]);

            $this->command->info('✅ Created 25 PT assets');
        } else {
            $this->command->warn('⚠️  No Admin PT user found, skipping PT assets');
        }

        $this->command->info('🎉 Asset demo data seeding completed!');
    }
}
