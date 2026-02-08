<?php

namespace Database\Factories;

use App\Models\CTK;
use App\Models\CTKMedicalFull;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CTKMedicalFull>
 */
class CTKMedicalFullFactory extends Factory
{
    protected $model = CTKMedicalFull::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ctk_id' => CTK::factory(),
            'status' => fake()->randomElement(['Belum', 'Selesai']),
            'examination_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'medical_report_path' => 'medical-full-reports/report_'.fake()->uuid().'.pdf',
            'examination_findings' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the medical examination is completed (Selesai).
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Selesai',
            'examination_date' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'medical_report_path' => 'medical-full-reports/report_'.fake()->uuid().'.pdf',
            'examination_findings' => 'Hasil pemeriksaan menunjukkan kondisi kesehatan baik dan layak untuk bekerja di luar negeri.',
        ]);
    }

    /**
     * Indicate that the medical examination is not yet completed (Belum).
     */
    public function belum(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Belum',
            'medical_report_path' => null,
            'examination_findings' => null,
        ]);
    }

    /**
     * Indicate that the medical examination needs renewal (>90 days old).
     */
    public function needsRenewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Selesai',
            'examination_date' => now()->subDays(95), // >90 days old
            'medical_report_path' => 'medical-full-reports/report_'.fake()->uuid().'.pdf',
            'examination_findings' => 'Pemeriksaan perlu diperpanjang karena sudah lebih dari 90 hari.',
        ]);
    }
}
