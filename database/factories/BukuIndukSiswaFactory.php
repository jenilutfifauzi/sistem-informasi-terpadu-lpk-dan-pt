<?php

namespace Database\Factories;

use App\Models\BukuIndukSiswa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BukuIndukSiswa>
 */
class BukuIndukSiswaFactory extends Factory
{
    protected $model = BukuIndukSiswa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalLahir = Carbon::instance($this->faker->dateTimeBetween('-35 years', '-17 years'));

        return [
            'foto_path' => null,
            'nama_lengkap' => $this->faker->name(),
            'nomor_induk' => 'BI-'.str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'program_pendidikan' => $this->faker->randomElement(['LPK Bahasa Inggris', 'LPK Bahasa Jepang', 'LPK Bahasa Mandarin']),
            'program_bahasa' => $this->faker->randomElement(['Bahasa Inggris', 'Bahasa Jepang', 'Bahasa Mandarin']),
            'nama_panggilan' => $this->faker->firstName(),
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $tanggalLahir->toDateString(),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'kewarganegaraan' => 'Indonesia',
            'status_perkawinan' => $this->faker->randomElement(['Belum Kawin', 'Kawin']),
            'nama_suami_istri' => null,
            'no_hp_suami_istri' => null,
            'alamat_siswa' => $this->faker->address(),
            'no_hp_siswa' => $this->faker->numerify('08###########'),
            'email' => $this->faker->optional()->safeEmail(),
            'alamat_orang_tua' => $this->faker->address(),
            'no_hp_orang_tua' => $this->faker->numerify('08###########'),
            'golongan_darah' => $this->faker->randomElement(['A', 'B', 'AB', 'O', 'Tidak Tahu']),
            'penyakit_pernah_diderita' => 'Tidak ada',
            'kelainan_jasmani' => 'Tidak ada',
            'tinggi_badan_cm' => $this->faker->numberBetween(145, 185),
            'berat_badan_kg' => $this->faker->numberBetween(40, 90),
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deleted_at' => now(),
        ]);
    }
}
