<?php

namespace Database\Factories;

use App\Enums\EntityType;
use App\Enums\JabatanLPK;
use App\Enums\StatusKepegawaian;
use App\Models\EmployeeLPK;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeLPK>
 */
class EmployeeLPKFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\EmployeeLPK>
     */
    protected $model = EmployeeLPK::class;

    /**
     * Define the model's default state.
     *
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
            'jabatan' => $this->faker->randomElement([
                JabatanLPK::Instruktur,
                JabatanLPK::AdminLPK,
                JabatanLPK::Staff,
            ]),
            'status' => StatusKepegawaian::Aktif,
            'tanggal_bergabung' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'honor_pokok' => $this->faker->randomElement([null, $this->faker->numberBetween(3000000, 10000000)]),
            'honor_per_jam' => null, // Will be set if jabatan is Instruktur
            'sertifikat_path' => null,
            'entity' => EntityType::LPK,
        ];
    }

    /**
     * Indicate that the employee is an instructor
     */
    public function instruktur(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'jabatan' => JabatanLPK::Instruktur,
                'honor_per_jam' => $this->faker->numberBetween(100000, 500000),
            ];
        });
    }

    /**
     * Indicate that the employee is resigned
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
}
