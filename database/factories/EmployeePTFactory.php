<?php

namespace Database\Factories;

use App\Enums\DivisiPT;
use App\Enums\EntityType;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeePT;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeePT>
 */
class EmployeePTFactory extends Factory
{
    protected $model = EmployeePT::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal_lahir = $this->faker->dateTimeBetween('-65 years', '-20 years');

        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'nama_lengkap' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tanggal_lahir' => $tanggal_lahir,
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'jabatan' => $this->faker->randomElement(JabatanPT::cases()),
            'divisi' => $this->faker->randomElement(DivisiPT::cases()),
            'status' => StatusKepegawaian::Aktif,
            'jenis_kontrak' => $this->faker->randomElement(JenisKontrak::cases()),
            'tanggal_bergabung' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'gaji_pokok' => null,
            'tunjangan' => null,
            'foto_path' => null,
            'dokumen_path' => null,
            'entity' => EntityType::PT,
        ];
    }

    /**
     * Indicate that the employee is active (Aktif).
     */
    public function aktif(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusKepegawaian::Aktif,
                'deleted_at' => null,
            ];
        });
    }

    /**
     * Indicate that the employee has resigned (soft deleted).
     */
    public function resign(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusKepegawaian::Resign,
                'deleted_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the employee has a dokumen kepegawaian on file.
     */
    public function withDokumen(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'dokumen_path' => 'documents/sample-'.$this->faker->uuid().'.pdf',
            ];
        });
    }
}
