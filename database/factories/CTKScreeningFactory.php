<?php

namespace Database\Factories;

use App\Enums\ScreeningStage;
use App\Models\CTK;
use App\Models\CTKScreening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CTKScreening>
 */
class CTKScreeningFactory extends Factory
{
    protected $model = CTKScreening::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ctk_id' => CTK::factory(),
            'interviewer_id' => User::factory(),
            'interview_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'interview_location' => fake()->randomElement([
                'Kantor PT',
                'Online - Zoom',
                'Ruang Interview 1',
                'Ruang Interview 2',
                'Aula Utama',
            ]),
            'screening_stage' => fake()->randomElement([
                ScreeningStage::Screening1,
                ScreeningStage::InterviewUser,
            ]),
            'screening_result' => fake()->randomElement(['Lolos', 'Tidak Lolos']),
            'interview_notes' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the screening passed (Lolos).
     */
    public function lolos(): static
    {
        return $this->state(fn (array $attributes) => [
            'screening_result' => 'Lolos',
            'interview_notes' => 'Kandidat menunjukkan kualitas yang baik dan siap untuk ditempatkan.',
        ]);
    }

    /**
     * Indicate that the screening failed (Tidak Lolos).
     */
    public function tidakLolos(): static
    {
        return $this->state(fn (array $attributes) => [
            'screening_result' => 'Tidak Lolos',
            'interview_notes' => 'Kandidat belum memenuhi kriteria yang dibutuhkan.',
        ]);
    }

    /**
     * Indicate that this is a Screening 1 record.
     */
    public function screening1(): static
    {
        return $this->state(fn (array $attributes) => [
            'screening_stage' => ScreeningStage::Screening1,
            'interview_location' => 'Ruang Interview 1',
        ]);
    }

    /**
     * Indicate that this is an Interview User record.
     */
    public function interviewUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'screening_stage' => ScreeningStage::InterviewUser,
            'interview_location' => 'Ruang Interview User',
        ]);
    }
}
