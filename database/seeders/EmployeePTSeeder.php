<?php

namespace Database\Seeders;

use App\Enums\DivisiPT;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Models\EmployeePT;
use Illuminate\Database\Seeder;

class EmployeePTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // One Direktur
        EmployeePT::factory()->create([
            'jabatan' => JabatanPT::Direktur,
            'divisi' => DivisiPT::Manajemen,
            'jenis_kontrak' => JenisKontrak::Tetap,
        ]);

        // One Manajer
        EmployeePT::factory()->create([
            'jabatan' => JabatanPT::Manajer,
            'divisi' => DivisiPT::Operasional,
            'jenis_kontrak' => JenisKontrak::Tetap,
        ]);

        // Two Staf HRD
        EmployeePT::factory()->count(2)->create([
            'jabatan' => JabatanPT::StafHRD,
            'divisi' => DivisiPT::HRD,
        ]);

        // One Staf Keuangan
        EmployeePT::factory()->create([
            'jabatan' => JabatanPT::StafKeuangan,
            'divisi' => DivisiPT::Keuangan,
            'jenis_kontrak' => JenisKontrak::Tetap,
        ]);

        // Two Staf Operasional
        EmployeePT::factory()->count(2)->create([
            'jabatan' => JabatanPT::StafOperasional,
            'divisi' => DivisiPT::Operasional,
        ]);

        // One Staf Administrasi
        EmployeePT::factory()->create([
            'jabatan' => JabatanPT::StafAdministrasi,
            'divisi' => DivisiPT::Administrasi,
        ]);
    }
}
