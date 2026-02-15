<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CTKTraining>
 */
class CTKTrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ctk_id' => \App\Models\CTK::factory(),
            'instructor_id' => \App\Models\EmployeeLPK::factory(),
            'training_start_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'training_end_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'training_location' => $this->faker->randomElement(['Ruang Pelatihan A', 'Ruang Pelatihan B', 'Gedung LPK Lantai 2']),
            'training_hours' => $this->faker->numberBetween(20, 200),
            'completion_status' => $this->faker->randomElement(['Belum Selesai', 'Selesai']),
            'completion_notes' => $this->faker->optional()->sentence(),
            'created_by' => \App\Models\User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_status' => 'Selesai',
            'training_end_date' => now()->subDays(1),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_status' => 'Belum Selesai',
            'training_end_date' => null,
        ]);
    }
}
