<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\CTK;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CTKPayment>
 */
class CTKPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $banks = ['BCA', 'Mandiri', 'BRI', 'BNI', 'BTN'];
        $methods = ['Transfer Bank', 'Tunai', 'EDC/Kartu Debit'];

        return [
            'ctk_id' => CTK::factory(),
            'stage_number' => fake()->numberBetween(1, 5),
            'amount' => fake()->randomFloat(2, 500000, 5000000),
            'bank_name' => fake()->randomElement($banks),
            'payment_date' => fake()->optional(0.8)->dateTimeBetween('-60 days', 'now'),
            'payment_method' => fake()->randomElement($methods),
            'payment_status' => fake()->randomElement([PaymentStatus::Pending, PaymentStatus::Lunas]),
            'payment_proof_path' => fake()->optional(0.7)->filePath(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate the payment is complete (Lunas).
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Lunas,
            'payment_date' => fake()->dateTimeBetween('-60 days', 'now'),
            'payment_proof_path' => 'payments/proof-'.fake()->uuid().'.pdf',
        ]);
    }

    /**
     * Indicate the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Pending,
            'payment_date' => null,
            'payment_proof_path' => null,
        ]);
    }

    /**
     * Set the payment for a specific stage.
     */
    public function forStage(int $stage): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_number' => $stage,
        ]);
    }
}
