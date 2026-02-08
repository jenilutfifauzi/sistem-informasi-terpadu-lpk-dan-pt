<?php

namespace Database\Factories;

use App\Enums\MCUStatus;
use App\Models\CTK;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MCURecord>
 */
class MCURecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clinics = [
            'Klinik Medika Utama',
            'RS Mitra Keluarga',
            'Prodia Medical Center',
            'Klinik Kimia Farma',
            'RS Columbia Asia',
        ];

        return [
            'ctk_id' => CTK::factory(),
            'status' => fake()->randomElement([MCUStatus::FIT, MCUStatus::UNFIT, MCUStatus::PENDING]),
            'examination_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'clinic_name' => fake()->randomElement($clinics),
            'examiner_name' => 'Dr. '.fake()->name(),
            'notes' => fake()->optional(0.6)->sentence(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate the MCU result is FIT.
     */
    public function fit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MCUStatus::FIT,
            'notes' => 'Sehat jasmani dan rohani. Layak untuk bekerja di luar negeri.',
        ]);
    }

    /**
     * Indicate the MCU result is UNFIT.
     */
    public function unfit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MCUStatus::UNFIT,
            'notes' => fake()->randomElement([
                'Tekanan darah tinggi. Perlu pengobatan.',
                'Ditemukan masalah kesehatan. Konsultasi dokter diperlukan.',
                'Tidak memenuhi standar kesehatan untuk bekerja di luar negeri.',
            ]),
        ]);
    }

    /**
     * Indicate the MCU result is PENDING.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MCUStatus::PENDING,
            'notes' => 'Menunggu hasil lab. Follow-up dalam 3 hari kerja.',
        ]);
    }
}
