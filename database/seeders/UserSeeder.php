<?php

namespace Database\Seeders;

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
        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@lpk.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create Admin LPK
        $adminLPK = User::firstOrCreate(
            ['email' => 'admin@lpk.com'],
            [
                'name' => 'Admin LPK',
                'password' => Hash::make('password'),
            ]
        );
        $adminLPK->assignRole('admin_lpk');

        // Create Instruktur
        $instruktur = User::firstOrCreate(
            ['email' => 'instruktur@lpk.com'],
            [
                'name' => 'Instruktur',
                'password' => Hash::make('password'),
            ]
        );
        $instruktur->assignRole('instruktur');

        // Create HR PT
        $hrPT = User::firstOrCreate(
            ['email' => 'hr@pt.com'],
            [
                'name' => 'HR PT',
                'password' => Hash::make('password'),
            ]
        );
        $hrPT->assignRole('hr_pt');

        // Create Admin PT
        $adminPT = User::firstOrCreate(
            ['email' => 'admin@pt.com'],
            [
                'name' => 'Admin PT',
                'password' => Hash::make('password'),
            ]
        );
        $adminPT->assignRole('admin_pt');

        // Create Legal PT
        $legalPT = User::firstOrCreate(
            ['email' => 'legal@pt.com'],
            [
                'name' => 'Legal PT',
                'password' => Hash::make('password'),
            ]
        );
        $legalPT->assignRole('legal_pt');

        // Create Keuangan PT
        $keuanganPT = User::firstOrCreate(
            ['email' => 'keuangan@pt.com'],
            [
                'name' => 'Keuangan PT',
                'password' => Hash::make('password'),
            ]
        );
        $keuanganPT->assignRole('keuangan_pt');

        // Create Keuangan LPK
        $keuanganLPK = User::firstOrCreate(
            ['email' => 'keuangan@lpk.com'],
            [
                'name' => 'Keuangan LPK',
                'password' => Hash::make('password'),
            ]
        );
        $keuanganLPK->assignRole('keuangan_lpk');

        // Create Pimpinan
        $pimpinan = User::firstOrCreate(
            ['email' => 'pimpinan@lpk.com'],
            [
                'name' => 'Pimpinan',
                'password' => Hash::make('password'),
            ]
        );
        $pimpinan->assignRole('pimpinan');
    }
}
