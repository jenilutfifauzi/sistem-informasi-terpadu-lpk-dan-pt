<?php

namespace Database\Factories;

use App\Models\SiswaLPK;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiswaLPK>
 */
class SiswaLPKFactory extends Factory
{
    protected $model = SiswaLPK::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalLahir = Carbon::instance($this->faker->dateTimeBetween('-35 years', '-17 years'));
        $tanggalMasuk = Carbon::instance($this->faker->dateTimeBetween($tanggalLahir, 'now'));

        return [
            'nomor_urut' => $this->faker->optional()->numberBetween(1, 999),
            'nomor_induk' => str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'nama_siswa' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'pendidikan_terakhir' => $this->faker->randomElement(['SD', 'SMP', 'SMA', 'SMK', 'D1', 'D2', 'D3', 'S1']),
            'tanggal_masuk' => $tanggalMasuk->toDateString(),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $tanggalLahir->toDateString(),
            'alamat' => $this->faker->address(),
            'no_hp' => $this->faker->numerify('08###########'),
            'email' => $this->faker->optional()->safeEmail(),
            'program_pendidikan' => $this->faker->randomElement(['Bahasa Inggris', 'Bahasa Jepang', 'Bahasa Mandarin']),
        ];
    }

    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => null,
        ]);
    }

    public function withSequenceNumber(int $nomorUrut): static
    {
        return $this->state(fn (array $attributes): array => [
            'nomor_urut' => $nomorUrut,
        ]);
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deleted_at' => now(),
        ]);
    }
}
