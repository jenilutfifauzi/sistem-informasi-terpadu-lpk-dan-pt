<?php

namespace Tests\Feature;

use App\Enums\DivisiPT;
use App\Enums\JabatanPT;
use App\Enums\JenisKontrak;
use App\Enums\StatusKepegawaian;
use App\Filament\Resources\EmployeePTResource\Pages\CreateEmployeePT;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePTValidationTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin']);
        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /**
     * Returns valid base form data for creating an employee PT.
     *
     * @return array<string, mixed>
     */
    private function validFormData(array $overrides = []): array
    {
        return array_merge([
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Test Employee',
            'email' => 'test@example.com',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Test No. 1',
            'telepon' => '081234567890',
            'jabatan' => JabatanPT::StafHRD->value,
            'divisi' => DivisiPT::HRD->value,
            'status' => StatusKepegawaian::Aktif->value,
            'jenis_kontrak' => JenisKontrak::Tetap->value,
            'tanggal_bergabung' => '2024-01-01',
        ], $overrides);
    }

    /** @test */
    public function nik_must_be_exactly_16_digits(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['nik' => '1234567890123456']))
            ->call('create')
            ->assertHasNoFormErrors(['nik']);
    }

    /** @test */
    public function nik_with_15_chars_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['nik' => '123456789012345']))
            ->call('create')
            ->assertHasFormErrors(['nik']);
    }

    /** @test */
    public function nik_with_17_chars_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['nik' => '12345678901234567']))
            ->call('create')
            ->assertHasFormErrors(['nik']);
    }

    /** @test */
    public function nik_containing_letters_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['nik' => 'ABCD567890123456']))
            ->call('create')
            ->assertHasFormErrors(['nik']);
    }

    /** @test */
    public function email_with_invalid_format_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['email' => 'not-an-email']))
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    /** @test */
    public function tanggal_lahir_in_future_is_rejected(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEmployeePT::class)
            ->fillForm($this->validFormData(['tanggal_lahir' => now()->addYear()->format('Y-m-d')]))
            ->call('create')
            ->assertHasFormErrors(['tanggal_lahir']);
    }
}
