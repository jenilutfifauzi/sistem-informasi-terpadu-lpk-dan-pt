<?php

namespace Database\Seeders;

use App\Enums\JabatanLPK;
use App\Models\EmployeeLPK;
use Illuminate\Database\Seeder;

class EmployeeLPKSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 Instruktur
        EmployeeLPK::factory()
            ->instruktur()
            ->count(5)
            ->create();

        // Create 3 Admin LPK
        EmployeeLPK::factory()
            ->count(3)
            ->state(function () {
                return [
                    'jabatan' => JabatanLPK::AdminLPK,
                    'honor_per_jam' => null,
                ];
            })
            ->create();

        // Create 2 Staff
        EmployeeLPK::factory()
            ->count(2)
            ->state(function () {
                return [
                    'jabatan' => JabatanLPK::Staff,
                    'honor_per_jam' => null,
                ];
            })
            ->create();
    }
}
