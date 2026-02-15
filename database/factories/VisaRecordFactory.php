<?php

namespace Database\Factories;

use App\Models\CTK;
use App\Models\VisaRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisaRecord>
 */
class VisaRecordFactory extends Factory
{
    protected $model = VisaRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $applicationDate = fake()->dateTimeBetween('-6 months', '-1 month');
        $issuanceDate = fake()->dateTimeBetween($applicationDate, 'now');

        return [
            'ctk_id' => CTK::factory(),
            'application_status' => fake()->randomElement(['Diajukan', 'Terbit']),
            'application_date' => $applicationDate,
            'visa_number' => 'V'.fake()->unique()->numerify('######'),
            'issuance_date' => $issuanceDate,
            'expiry_date' => fake()->dateTimeBetween($issuanceDate, '+2 years'),
            'issuing_country' => fake()->randomElement(['Japan', 'Taiwan', 'Malaysia', 'Singapore', 'South Korea']),
            'visa_type' => fake()->randomElement(['Work Visa', 'Business Visa', 'Employment Visa']),
            'visa_document_path' => 'visa-documents/visa_'.fake()->uuid().'.pdf',
        ];
    }

    /**
     * Indicate that the visa is issued (Terbit).
     */
    public function terbit(): static
    {
        $applicationDate = fake()->dateTimeBetween('-6 months', '-2 months');
        $issuanceDate = fake()->dateTimeBetween($applicationDate, '-1 month');

        return $this->state(fn (array $attributes) => [
            'application_status' => 'Terbit',
            'application_date' => $applicationDate,
            'issuance_date' => $issuanceDate,
            'expiry_date' => fake()->dateTimeBetween($issuanceDate, '+2 years'),
            'visa_number' => 'V'.fake()->unique()->numerify('######'),
            'issuing_country' => fake()->randomElement(['Japan', 'Taiwan', 'Malaysia']),
            'visa_type' => 'Work Visa',
        ]);
    }

    /**
     * Indicate that the visa is pending (Diajukan).
     */
    public function diajukan(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_status' => 'Diajukan',
            'visa_number' => null,
            'issuance_date' => null,
            'expiry_date' => null,
            'visa_document_path' => null,
        ]);
    }

    /**
     * Indicate that the visa is expiring soon (within 30 days).
     */
    public function expiringSoon(): static
    {
        $applicationDate = fake()->dateTimeBetween('-18 months', '-12 months');
        $issuanceDate = fake()->dateTimeBetween($applicationDate, '-11 months');
        $expiryDate = now()->addDays(fake()->numberBetween(1, 30));

        return $this->state(fn (array $attributes) => [
            'application_status' => 'Terbit',
            'application_date' => $applicationDate,
            'issuance_date' => $issuanceDate,
            'expiry_date' => $expiryDate,
        ]);
    }
}
