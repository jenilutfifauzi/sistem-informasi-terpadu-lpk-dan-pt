<?php

namespace Database\Factories;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CTK>
 */
class CTKFactory extends Factory
{
    private static $nikSequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = CTKStatus::MCU;
        $stage = 1;
        $entity = EntityType::LPK;

        return [
            'nik' => sprintf('CTK%08d', self::$nikSequence++),
            'nama_lengkap' => fake()->name(),
            'tanggal_lahir' => fake()->dateTimeBetween('-40 years', '-18 years'),
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'alamat' => fake()->address(),
            'no_telepon' => fake()->phoneNumber(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'current_status' => $status,
            'current_stage' => $stage,
            'current_entity' => $entity,
            'opp_status' => 'Belum',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate the CTK is in LPK stages (1-5).
     */
    public function inLPKStages(): static
    {
        $stage = fake()->numberBetween(1, 5);
        $statuses = [
            1 => CTKStatus::MCU,
            2 => CTKStatus::Pembayaran,
            3 => CTKStatus::SoalBerkas,
            4 => CTKStatus::Paspor,
            5 => CTKStatus::BelajarDiLPK,
        ];

        return $this->state(fn (array $attributes) => [
            'current_status' => $statuses[$stage],
            'current_stage' => $stage,
            'current_entity' => EntityType::LPK,
        ]);
    }

    /**
     * Indicate the CTK is in PT stages (6-15).
     */
    public function inPTStages(): static
    {
        $stage = fake()->numberBetween(6, 15);
        $statuses = [
            6 => CTKStatus::Screening1,
            7 => CTKStatus::InterviewUser,
            8 => CTKStatus::IjinDesa,
            9 => CTKStatus::Rekomendasi,
            10 => CTKStatus::WP,
            11 => CTKStatus::ApplyVisa,
            12 => CTKStatus::MedicalFull,
            13 => CTKStatus::Visa,
            14 => CTKStatus::OPP,
            15 => CTKStatus::Terbang,
        ];

        return $this->state(fn (array $attributes) => [
            'current_status' => $statuses[$stage],
            'current_stage' => $stage,
            'current_entity' => EntityType::PT,
        ]);
    }

    /**
     * Indicate the CTK is ready for departure (stage 15).
     */
    public function readyForDeparture(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => CTKStatus::Terbang,
            'current_stage' => 15,
            'current_entity' => EntityType::PT,
        ]);
    }

    /**
     * Indicate the CTK is at MCU stage.
     */
    public function atMCUStage(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
        ]);
    }

    /**
     * Indicate the CTK is at Payment stage.
     */
    public function atPaymentStage(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
        ]);
    }

    /**
     * Indicate the CTK is at Training stage.
     */
    public function atTrainingStage(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_status' => CTKStatus::BelajarDiLPK,
            'current_stage' => 5,
            'current_entity' => EntityType::LPK,
        ]);
    }
}
