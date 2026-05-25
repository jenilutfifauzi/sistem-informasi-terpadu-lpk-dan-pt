<?php

namespace Tests\Feature;

use App\Filament\Exports\BukuIndukSiswaExport;
use App\Filament\Resources\BukuIndukSiswaResource\Pages\CreateBukuIndukSiswa;
use App\Filament\Resources\BukuIndukSiswaResource\Pages\ListBukuIndukSiswas;
use App\Filament\Resources\BukuIndukSiswaResource\Pages\ViewBukuIndukSiswa;
use App\Models\BukuIndukSiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BukuIndukSiswaResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Pimpinan', 'guard_name' => 'web']);

        $permissions = [
            'view_any_buku_induk_siswa',
            'view_buku_induk_siswa',
            'create_buku_induk_siswa',
            'update_buku_induk_siswa',
            'delete_buku_induk_siswa',
            'restore_buku_induk_siswa',
            'force_delete_buku_induk_siswa',
            'export_buku_induk_siswa',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create();
        $this->admin->syncRoles('super_admin');
        $this->admin->givePermissionTo($permissions);

        $this->viewer = User::factory()->create();
        $this->viewer->syncRoles('Pimpinan');
        $this->viewer->givePermissionTo(['view_any_buku_induk_siswa', 'view_buku_induk_siswa']);
    }

    public function test_admin_can_create_buku_induk_siswa_with_all_form_sections(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateBukuIndukSiswa::class)
            ->fillForm([
                'nama_lengkap' => 'Kanthi Pramono',
                'nomor_induk' => 'BI-00001',
                'program_pendidikan' => 'LPK Bahasa Jepang',
                'program_bahasa' => 'Bahasa Jepang',
                'nama_panggilan' => 'Kanthi',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1998-08-15',
                'agama' => 'Islam',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Belum Kawin',
                'nama_suami_istri' => null,
                'no_hp_suami_istri' => null,
                'alamat_siswa' => 'Desa Ujungmanik RT.003/RW.003, Kec. Kawunganten, Kab. Cilacap.',
                'no_hp_siswa' => '088226521921',
                'email' => 'kanthi@example.com',
                'alamat_orang_tua' => 'Desa Ujungmanik, Cilacap',
                'no_hp_orang_tua' => '081234567890',
                'golongan_darah' => 'O',
                'penyakit_pernah_diderita' => 'Tidak ada',
                'kelainan_jasmani' => 'Tidak ada',
                'tinggi_badan_cm' => 160,
                'berat_badan_kg' => 52,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('buku_induk_siswa', [
            'nomor_induk' => 'BI-00001',
            'nama_lengkap' => 'Kanthi Pramono',
            'program_bahasa' => 'Bahasa Jepang',
            'golongan_darah' => 'O',
            'tinggi_badan_cm' => 160,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_audit_fields_are_not_mass_assignable(): void
    {
        $model = new BukuIndukSiswa;

        $this->assertNotContains('created_by', $model->getFillable());
        $this->assertNotContains('updated_by', $model->getFillable());
    }

    public function test_admin_can_upload_foto_when_creating_buku_induk_siswa(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin)
            ->test(CreateBukuIndukSiswa::class)
            ->fillForm([
                'foto_path' => UploadedFile::fake()->create('foto-siswa.png', 444, 'image/png'),
                'nama_lengkap' => 'Upload Foto Siswa',
                'nomor_induk' => 'BI-UPLOAD-01',
                'program_pendidikan' => 'LPK Bahasa Jepang',
                'program_bahasa' => 'Bahasa Jepang',
                'jenis_kelamin' => 'Perempuan',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = BukuIndukSiswa::where('nomor_induk', 'BI-UPLOAD-01')->firstOrFail();

        $this->assertNotNull($record->foto_path);
        $this->assertStringStartsWith('buku-induk-siswa/foto/', $record->foto_path);
        Storage::disk('public')->assertExists($record->foto_path);
    }

    public function test_foto_display_uses_public_disk_on_index_and_detail_pages(): void
    {
        $record = BukuIndukSiswa::factory()->create([
            'foto_path' => 'buku-induk-siswa/foto/foto-siswa.jpg',
        ]);

        $listPage = Livewire::actingAs($this->admin)
            ->test(ListBukuIndukSiswas::class)
            ->assertSuccessful();

        $fotoColumn = $listPage->instance()->getTable()->getColumn('foto_path');

        $this->assertSame('public', $fotoColumn->getDiskName());

        $detailPage = Livewire::actingAs($this->admin)
            ->test(ViewBukuIndukSiswa::class, ['record' => $record->getKey()])
            ->assertSuccessful();

        $fotoEntry = $detailPage->instance()->getSchema('infolist')->getComponent('foto_path');

        $this->assertSame('public', $fotoEntry->getDiskName());
    }

    public function test_export_visibility_and_exporter_follow_filtered_dataset(): void
    {
        BukuIndukSiswa::factory()->create(['program_pendidikan' => 'LPK Bahasa Inggris', 'nomor_induk' => 'BI-EXPORT-01']);
        $target = BukuIndukSiswa::factory()->create(['program_pendidikan' => 'LPK Bahasa Jepang', 'nomor_induk' => 'BI-EXPORT-02']);

        Livewire::actingAs($this->admin)
            ->test(ListBukuIndukSiswas::class)
            ->assertSee('Export Excel');

        $export = new BukuIndukSiswaExport(BukuIndukSiswa::query()->where('program_pendidikan', 'LPK Bahasa Jepang'));

        $this->assertCount(1, $export->query()->get());
        $this->assertSame('BI-EXPORT-02', $export->map($target->fresh())[1]);
        $this->assertSame('LPK Bahasa Jepang', $export->map($target->fresh())[2]);
        $this->assertSame('Nomor Induk', $export->headings()[1]);

        activity()
            ->causedBy($this->admin)
            ->withProperties([
                'export_type' => 'buku_induk_siswa',
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
            ->test(ListBukuIndukSiswas::class)
            ->assertDontSee('Export Excel');

        $this->assertFalse($this->viewer->can('export', BukuIndukSiswa::class));
    }

    public function test_required_fields_are_validated_when_creating_record(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateBukuIndukSiswa::class)
            ->fillForm([
                'nama_lengkap' => '',
                'nomor_induk' => '',
                'program_pendidikan' => '',
                'jenis_kelamin' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'nama_lengkap',
                'nomor_induk',
                'program_pendidikan',
                'jenis_kelamin',
            ]);
    }

    public function test_nomor_induk_must_be_unique(): void
    {
        BukuIndukSiswa::factory()->create(['nomor_induk' => 'BI-00002']);

        Livewire::actingAs($this->admin)
            ->test(CreateBukuIndukSiswa::class)
            ->fillForm([
                'nama_lengkap' => 'Duplikat Siswa',
                'nomor_induk' => 'BI-00002',
                'program_pendidikan' => 'LPK Bahasa Jepang',
                'program_bahasa' => 'Bahasa Jepang',
                'jenis_kelamin' => 'Laki-laki',
            ])
            ->call('create')
            ->assertHasFormErrors(['nomor_induk']);
    }

    public function test_list_page_can_search_by_nomor_induk_nama_and_program(): void
    {
        $nomorIndukRecord = BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-54321',
            'nama_lengkap' => 'Alpha Siswa',
            'program_pendidikan' => 'LPK Bahasa Jepang',
            'program_bahasa' => 'Bahasa Jepang',
        ]);
        $namaRecord = BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-12345',
            'nama_lengkap' => 'Beta Program',
            'program_pendidikan' => 'LPK Bahasa Korea',
            'program_bahasa' => 'Bahasa Korea',
        ]);
        $programRecord = BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-77777',
            'nama_lengkap' => 'Gamma User',
            'program_pendidikan' => 'LPK Bahasa Mandarin',
            'program_bahasa' => 'Bahasa Mandarin',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListBukuIndukSiswas::class)
            ->searchTable('BI-54321')
            ->assertCanSeeTableRecords([$nomorIndukRecord])
            ->assertCanNotSeeTableRecords([$namaRecord, $programRecord]);

        Livewire::actingAs($this->admin)
            ->test(ListBukuIndukSiswas::class)
            ->searchTable('Beta Program')
            ->assertCanSeeTableRecords([$namaRecord])
            ->assertCanNotSeeTableRecords([$nomorIndukRecord, $programRecord]);

        Livewire::actingAs($this->admin)
            ->test(ListBukuIndukSiswas::class)
            ->searchTable('Mandarin')
            ->assertCanSeeTableRecords([$programRecord])
            ->assertCanNotSeeTableRecords([$nomorIndukRecord, $namaRecord]);
    }

    public function test_detail_page_displays_buku_induk_sections(): void
    {
        $record = BukuIndukSiswa::factory()->create([
            'nomor_induk' => 'BI-10001',
            'nama_lengkap' => 'Detail Buku Induk',
            'program_pendidikan' => 'LPK Bahasa Jepang',
            'program_bahasa' => 'Bahasa Jepang',
            'golongan_darah' => 'A',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ViewBukuIndukSiswa::class, ['record' => $record->getKey()])
            ->assertSuccessful()
            ->assertSee('Detail Buku Induk')
            ->assertSee('BI-10001')
            ->assertSee('Keterangan Pribadi')
            ->assertSee('Keterangan Tempat Tinggal')
            ->assertSee('Keterangan Kesehatan');
    }
}
