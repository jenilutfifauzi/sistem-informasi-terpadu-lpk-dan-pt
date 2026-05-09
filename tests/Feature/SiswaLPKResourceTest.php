<?php

namespace Tests\Feature;

use App\Filament\Exports\SiswaLPKExport;
use App\Filament\Resources\SiswaLPKResource\Pages\CreateSiswaLPK;
use App\Filament\Resources\SiswaLPKResource\Pages\EditSiswaLPK;
use App\Filament\Resources\SiswaLPKResource\Pages\ListSiswaLPKS;
use App\Filament\Resources\SiswaLPKResource\Pages\ViewSiswaLPK;
use App\Models\SiswaLPK;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiswaLPKResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin LPK', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Pimpinan', 'guard_name' => 'web']);

        $permissions = [
            'view_any_siswa_lpk',
            'view_siswa_lpk',
            'create_siswa_lpk',
            'update_siswa_lpk',
            'delete_siswa_lpk',
            'restore_siswa_lpk',
            'force_delete_siswa_lpk',
            'export_siswa_lpk',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
        $this->admin->givePermissionTo($permissions);

        $this->viewer = User::factory()->create();
        $this->viewer->syncRoles('Pimpinan');
        $this->viewer->givePermissionTo(['view_any_siswa_lpk', 'view_siswa_lpk']);
    }

    public function test_admin_can_create_siswa_lpk_with_email(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSiswaLPK::class)
            ->fillForm([
                'nomor_urut' => 1,
                'nomor_induk' => '00001',
                'nama_siswa' => 'Kanthi Pramono',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'pendidikan_terakhir' => 'SMA',
                'tanggal_masuk' => '2024-01-10',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1989-08-15',
                'alamat' => 'Desa Ujungmanik RT.003/RW.003, Kec. Kawunganten, Kab. Cilacap.',
                'no_hp' => '088226521921',
                'email' => 'udinkanthi12@gmail.com',
                'program_pendidikan' => 'Bahasa Inggris',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('siswa_lpk', [
            'nomor_induk' => '00001',
            'nama_siswa' => 'Kanthi Pramono',
            'email' => 'udinkanthi12@gmail.com',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_create_siswa_lpk_without_email(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSiswaLPK::class)
            ->fillForm([
                'nomor_urut' => 2,
                'nomor_induk' => '00002',
                'nama_siswa' => 'Eko Riyanto',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'pendidikan_terakhir' => 'SMP',
                'tanggal_masuk' => '2024-01-11',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1991-09-26',
                'alamat' => 'Dusun Ciputri, Rt.001/RW.006, Desa Cimrutu, Patimuan, Kab. Cilacap.',
                'no_hp' => '0882006746356',
                'email' => null,
                'program_pendidikan' => 'Bahasa Inggris',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('siswa_lpk', [
            'nomor_induk' => '00002',
            'nama_siswa' => 'Eko Riyanto',
            'email' => null,
        ]);
    }

    public function test_required_fields_are_validated_when_creating_record(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSiswaLPK::class)
            ->fillForm([
                'nomor_induk' => '',
                'nama_siswa' => '',
                'jenis_kelamin' => null,
                'tanggal_masuk' => null,
                'tempat_lahir' => '',
                'tanggal_lahir' => null,
                'alamat' => '',
                'no_hp' => '',
                'program_pendidikan' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'nomor_induk',
                'nama_siswa',
                'jenis_kelamin',
                'tanggal_masuk',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'no_hp',
                'program_pendidikan',
            ]);
    }

    public function test_create_form_shows_birth_source_guidance(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSiswaLPK::class)
            ->assertSee('Jika catatan sumber menggabungkan tempat lahir dan tanggal lahir, pisahkan nilainya ke field masing-masing sebelum menyimpan.');
    }

    public function test_birth_date_must_not_be_after_enrollment_date(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSiswaLPK::class)
            ->fillForm([
                'nomor_induk' => '00003',
                'nama_siswa' => 'Tanggal Salah',
                'jenis_kelamin' => 'P',
                'agama' => 'Kristen',
                'pendidikan_terakhir' => 'SMA',
                'tanggal_masuk' => '2024-01-01',
                'tempat_lahir' => 'Purwokerto',
                'tanggal_lahir' => '2024-02-01',
                'alamat' => 'Alamat uji',
                'no_hp' => '081234567890',
                'program_pendidikan' => 'Bahasa Jepang',
            ])
            ->call('create')
            ->assertHasFormErrors(['tanggal_lahir']);
    }

    public function test_list_page_can_search_by_nomor_induk_nama_and_program(): void
    {
        $nomorIndukRecord = SiswaLPK::factory()->create([
            'nomor_induk' => '54321',
            'nama_siswa' => 'Alpha Siswa',
            'program_pendidikan' => 'Bahasa Inggris',
        ]);
        $namaRecord = SiswaLPK::factory()->create([
            'nomor_induk' => '12345',
            'nama_siswa' => 'Beta Program',
            'program_pendidikan' => 'Bahasa Jepang',
        ]);
        $programRecord = SiswaLPK::factory()->create([
            'nomor_induk' => '77777',
            'nama_siswa' => 'Gamma User',
            'program_pendidikan' => 'Bahasa Mandarin',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListSiswaLPKS::class)
            ->searchTable('54321')
            ->assertCanSeeTableRecords([$nomorIndukRecord])
            ->assertCanNotSeeTableRecords([$namaRecord, $programRecord]);

        Livewire::actingAs($this->admin)
            ->test(ListSiswaLPKS::class)
            ->searchTable('Beta Program')
            ->assertCanSeeTableRecords([$namaRecord])
            ->assertCanNotSeeTableRecords([$nomorIndukRecord, $programRecord]);

        Livewire::actingAs($this->admin)
            ->test(ListSiswaLPKS::class)
            ->searchTable('Bahasa Mandarin')
            ->assertCanSeeTableRecords([$programRecord])
            ->assertCanNotSeeTableRecords([$nomorIndukRecord, $namaRecord]);
    }

    public function test_list_page_can_filter_by_program_pendidikan(): void
    {
        $inggris = SiswaLPK::factory()->create(['program_pendidikan' => 'Bahasa Inggris']);
        $jepang = SiswaLPK::factory()->create(['program_pendidikan' => 'Bahasa Jepang']);

        Livewire::actingAs($this->admin)
            ->test(ListSiswaLPKS::class)
            ->filterTable('program_pendidikan', 'Bahasa Inggris')
            ->assertCanSeeTableRecords([$inggris])
            ->assertCanNotSeeTableRecords([$jepang]);
    }

    public function test_detail_page_displays_student_data(): void
    {
        $record = SiswaLPK::factory()->create([
            'nomor_induk' => '10001',
            'nama_siswa' => 'Detail Siswa',
            'program_pendidikan' => 'Bahasa Jepang',
            'email' => null,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ViewSiswaLPK::class, ['record' => $record->getKey()])
            ->assertSuccessful()
            ->assertSee('Detail Siswa')
            ->assertSee('10001')
            ->assertSee('Bahasa Jepang')
            ->assertSee('Tidak ada email');
    }

    public function test_admin_can_edit_siswa_lpk_record(): void
    {
        $record = SiswaLPK::factory()->create([
            'nomor_induk' => '10002',
            'nama_siswa' => 'Nama Awal',
            'program_pendidikan' => 'Bahasa Inggris',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditSiswaLPK::class, ['record' => $record->getKey()])
            ->fillForm([
                'nomor_urut' => $record->nomor_urut,
                'nomor_induk' => '10002',
                'nama_siswa' => 'Nama Diperbarui',
                'jenis_kelamin' => $record->jenis_kelamin,
                'agama' => $record->agama,
                'pendidikan_terakhir' => $record->pendidikan_terakhir,
                'tanggal_masuk' => $record->tanggal_masuk?->toDateString(),
                'tempat_lahir' => $record->tempat_lahir,
                'tanggal_lahir' => $record->tanggal_lahir?->toDateString(),
                'alamat' => $record->alamat,
                'no_hp' => $record->no_hp,
                'email' => 'baru@example.com',
                'program_pendidikan' => 'Bahasa Mandarin',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('siswa_lpk', [
            'id' => $record->id,
            'nama_siswa' => 'Nama Diperbarui',
            'email' => 'baru@example.com',
            'program_pendidikan' => 'Bahasa Mandarin',
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_duplicate_nomor_induk_is_rejected_on_update(): void
    {
        SiswaLPK::factory()->create(['nomor_induk' => '20001']);
        $second = SiswaLPK::factory()->create(['nomor_induk' => '20002']);

        Livewire::actingAs($this->admin)
            ->test(EditSiswaLPK::class, ['record' => $second->getKey()])
            ->fillForm([
                'nomor_urut' => $second->nomor_urut,
                'nomor_induk' => '20001',
                'nama_siswa' => $second->nama_siswa,
                'jenis_kelamin' => $second->jenis_kelamin,
                'agama' => $second->agama,
                'pendidikan_terakhir' => $second->pendidikan_terakhir,
                'tanggal_masuk' => $second->tanggal_masuk?->toDateString(),
                'tempat_lahir' => $second->tempat_lahir,
                'tanggal_lahir' => $second->tanggal_lahir?->toDateString(),
                'alamat' => $second->alamat,
                'no_hp' => $second->no_hp,
                'email' => $second->email,
                'program_pendidikan' => $second->program_pendidikan,
            ])
            ->call('save')
            ->assertHasFormErrors(['nomor_induk']);
    }

    public function test_export_visibility_and_exporter_follow_filtered_dataset(): void
    {
        SiswaLPK::factory()->create(['program_pendidikan' => 'Bahasa Inggris', 'nomor_induk' => '30001']);
        $target = SiswaLPK::factory()->create(['program_pendidikan' => 'Bahasa Jepang', 'nomor_induk' => '30002']);

        Livewire::actingAs($this->admin)
            ->test(ListSiswaLPKS::class)
            ->assertSee('Export Excel');

        $export = new SiswaLPKExport(SiswaLPK::query()->where('program_pendidikan', 'Bahasa Jepang'));

        $this->assertCount(1, $export->query()->get());
        $this->assertSame('30002', $export->map($target->fresh())[1]);
        $this->assertSame('Bahasa Jepang', $export->map($target->fresh())[6]);
        $this->assertSame('Nomor Induk', $export->headings()[1]);

        activity()
            ->causedBy($this->admin)
            ->withProperties([
                'export_type' => 'siswa_lpk',
                'format' => 'xlsx',
                'record_count' => 1,
            ])
            ->log('Data exported');

        $this->assertDatabaseHas(Activity::class, [
            'description' => 'Data exported',
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_user_without_export_permission_cannot_export(): void
    {
        Livewire::actingAs($this->viewer)
            ->test(ListSiswaLPKS::class)
            ->assertDontSee('Export Excel');

        $this->assertFalse($this->viewer->can('export', SiswaLPK::class));
    }
}
