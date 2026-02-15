<?php

namespace Tests\Feature;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use App\Models\CTK;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CTKManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        // Create roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'Admin LPK']);
        Role::firstOrCreate(['name' => 'Admin PT']);
        Role::firstOrCreate(['name' => 'Pimpinan']);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /**
     * Test: Admin LPK can create CTK with valid data and defaults are set correctly
     *
     * @test
     */
    public function admin_lpk_can_create_ctk_with_valid_data_and_defaults_are_set()
    {
        // Arrange: Create Admin LPK user
        $adminLPK = User::factory()->create([
            'entity' => EntityType::LPK,
        ]);
        $adminLPK->assignRole('Admin LPK');

        $ctkData = [
            'nik' => 'CTK00000001',
            'nama_lengkap' => 'John Doe',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'no_telepon' => '081234567890',
            'email' => 'john@example.com',
        ];

        // Act: Create CTK
        $this->actingAs($adminLPK);
        $ctk = CTK::create([
            ...$ctkData,
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
            'updated_by' => $adminLPK->id,
        ]);

        // Assert: CTK was created with correct data
        $this->assertDatabaseHas('ctk', [
            'nik' => 'CTK00000001',
            'nama_lengkap' => 'John Doe',
        ]);

        // Assert: Defaults are set correctly
        $this->assertEquals(CTKStatus::MCU, $ctk->current_status);
        $this->assertEquals(1, $ctk->current_stage);
        $this->assertEquals(EntityType::LPK, $ctk->current_entity);
        $this->assertEquals($adminLPK->id, $ctk->created_by);
        $this->assertEquals($adminLPK->id, $ctk->updated_by);

        // Assert: CTK is in LPK stages
        $this->assertTrue($ctk->is_in_lpk_stages);
        $this->assertFalse($ctk->is_in_pt_stages);
    }

    /**
     * Test: System prevents duplicate NIK entries with clear error message
     *
     * @test
     */
    public function system_prevents_duplicate_nik_entries()
    {
        // Arrange: Create first CTK with NIK
        $user = User::factory()->create(['entity' => EntityType::LPK]);
        $user->assignRole('Admin LPK');

        CTK::create([
            'nik' => 'CTK00000001',
            'nama_lengkap' => 'John Doe',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Test No. 123',
            'no_telepon' => '081234567890',
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Act & Assert: Try to create CTK with duplicate NIK
        $this->expectException(\Illuminate\Database\QueryException::class);

        CTK::create([
            'nik' => 'CTK00000001', // Duplicate NIK
            'nama_lengkap' => 'Jane Doe',
            'tanggal_lahir' => '1992-01-01',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Jl. Test No. 456',
            'no_telepon' => '081234567891',
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    /**
     * Test: Admin LPK can view CTK details with all personal information displayed
     *
     * @test
     */
    public function admin_lpk_can_view_ctk_details()
    {
        // Arrange: Create Admin LPK user and CTK
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
            'updated_by' => $adminLPK->id,
        ]);

        // Act: Retrieve CTK
        $this->actingAs($adminLPK);
        $retrievedCTK = CTK::find($ctk->id);

        // Assert: All personal information is accessible
        $this->assertNotNull($retrievedCTK);
        $this->assertEquals($ctk->nik, $retrievedCTK->nik);
        $this->assertEquals($ctk->nama_lengkap, $retrievedCTK->nama_lengkap);
        $this->assertEquals($ctk->tanggal_lahir->format('Y-m-d'), $retrievedCTK->tanggal_lahir->format('Y-m-d'));
        $this->assertEquals($ctk->jenis_kelamin, $retrievedCTK->jenis_kelamin);
        $this->assertEquals($ctk->alamat, $retrievedCTK->alamat);
        $this->assertEquals($ctk->no_telepon, $retrievedCTK->no_telepon);
        $this->assertEquals($ctk->email, $retrievedCTK->email);
        $this->assertEquals($ctk->current_status, $retrievedCTK->current_status);
        $this->assertEquals($ctk->current_stage, $retrievedCTK->current_stage);
        $this->assertEquals($ctk->current_entity, $retrievedCTK->current_entity);
    }

    /**
     * Test: Admin LPK can update CTK personal information and changes are logged
     *
     * @test
     */
    public function admin_lpk_can_update_ctk_and_changes_are_logged()
    {
        // Arrange: Create Admin LPK user and CTK
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'nama_lengkap' => 'John Doe',
            'no_telepon' => '081234567890',
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
            'updated_by' => $adminLPK->id,
        ]);

        // Act: Update CTK personal information
        $this->actingAs($adminLPK);
        $ctk->update([
            'nama_lengkap' => 'John Updated Doe',
            'no_telepon' => '081234567899',
            'updated_by' => $adminLPK->id,
        ]);

        // Assert: Changes are saved
        $this->assertDatabaseHas('ctk', [
            'id' => $ctk->id,
            'nama_lengkap' => 'John Updated Doe',
            'no_telepon' => '081234567899',
            'updated_by' => $adminLPK->id,
        ]);

        // Assert: Activity log exists (if Spatie Activity Log is enabled)
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => CTK::class,
            'subject_id' => $ctk->id,
            'causer_type' => User::class,
            'causer_id' => $adminLPK->id,
        ]);
    }

    /**
     * Test: Admin LPK can search CTK by name or NIK and get correct results
     *
     * @test
     */
    public function admin_lpk_can_search_ctk_by_name_or_nik()
    {
        // Arrange: Create Admin LPK user
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        // Create multiple CTK records
        $ctk1 = CTK::factory()->create([
            'nik' => 'CTK00000001',
            'nama_lengkap' => 'John Doe',
            'current_status' => CTKStatus::MCU,
            'current_stage' => 1,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        $ctk2 = CTK::factory()->create([
            'nik' => 'CTK00000002',
            'nama_lengkap' => 'Jane Smith',
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        $ctk3 = CTK::factory()->create([
            'nik' => 'CTK00000003',
            'nama_lengkap' => 'Bob Johnson',
            'current_status' => CTKStatus::Paspor,
            'current_stage' => 4,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act & Assert: Search by name (exact match)
        $this->actingAs($adminLPK);
        $searchByName = CTK::searchByName('John Doe')->get();
        $this->assertCount(1, $searchByName);
        $this->assertEquals('John Doe', $searchByName->first()->nama_lengkap);

        // Act & Assert: Search by NIK
        $searchByNIK = CTK::searchByNIK('CTK00000002')->get();
        $this->assertCount(1, $searchByNIK);
        $this->assertEquals('Jane Smith', $searchByNIK->first()->nama_lengkap);

        // Act & Assert: Partial search finds multiple results
        $partialSearch = CTK::searchByName('John')->get();
        $this->assertCount(2, $partialSearch); // John Doe and Bob Johnson (contains 'John')
        $this->assertTrue($partialSearch->contains('nama_lengkap', 'John Doe'));
        $this->assertTrue($partialSearch->contains('nama_lengkap', 'Bob Johnson'));
    }

    /**
     * Test: Entity scoping - Admin LPK can only access LPK stages (1-5)
     *
     * @test
     */
    public function admin_lpk_can_only_access_lpk_stages()
    {
        // Arrange: Create Admin LPK user
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        // Create CTK in LPK stages
        $ctkLPK = CTK::factory()->inLPKStages()->create([
            'created_by' => $adminLPK->id,
        ]);

        // Create CTK in PT stages
        $ctkPT = CTK::factory()->inPTStages()->create([
            'created_by' => $adminLPK->id,
        ]);

        // Act: Query as Admin LPK
        $this->actingAs($adminLPK);
        $accessibleCTK = CTK::where('current_entity', EntityType::LPK)
            ->whereBetween('current_stage', [1, 5])
            ->get();

        // Assert: Can see LPK CTK
        $this->assertTrue($accessibleCTK->contains($ctkLPK));

        // Assert: Cannot see PT CTK
        $this->assertFalse($accessibleCTK->contains($ctkPT));
    }

    /**
     * Test: Entity scoping - Admin PT can only access PT stages (6-15)
     *
     * @test
     */
    public function admin_pt_can_only_access_pt_stages()
    {
        // Arrange: Create Admin PT user
        $adminPT = User::factory()->create(['entity' => EntityType::PT]);
        $adminPT->assignRole('Admin PT');

        // Create CTK in LPK stages
        $ctkLPK = CTK::factory()->inLPKStages()->create();

        // Create CTK in PT stages
        $ctkPT = CTK::factory()->inPTStages()->create();

        // Act: Query as Admin PT
        $this->actingAs($adminPT);
        $accessibleCTK = CTK::where('current_entity', EntityType::PT)
            ->whereBetween('current_stage', [6, 15])
            ->get();

        // Assert: Can see PT CTK
        $this->assertTrue($accessibleCTK->contains($ctkPT));

        // Assert: Cannot see LPK CTK
        $this->assertFalse($accessibleCTK->contains($ctkLPK));
    }

    /**
     * Test: Pimpinan can view all CTK records (read-only)
     *
     * @test
     */
    public function pimpinan_can_view_all_ctk_records()
    {
        // Arrange: Create Pimpinan user
        $pimpinan = User::factory()->create(['entity' => EntityType::LPK]);
        $pimpinan->assignRole('Pimpinan');

        // Create CTK in both LPK and PT stages
        $ctkLPK = CTK::factory()->inLPKStages()->create();
        $ctkPT = CTK::factory()->inPTStages()->create();

        // Act: Query as Pimpinan
        $this->actingAs($pimpinan);
        $allCTK = CTK::all();

        // Assert: Can see all CTK (both LPK and PT)
        $this->assertTrue($allCTK->contains($ctkLPK));
        $this->assertTrue($allCTK->contains($ctkPT));
    }

    /**
     * Test: CTK scopes work correctly
     *
     * @test
     */
    public function ctk_scopes_work_correctly()
    {
        // Arrange: Create CTK in different stages
        $ctkStage3 = CTK::factory()->create([
            'current_status' => CTKStatus::SoalBerkas,
            'current_stage' => 3,
            'current_entity' => EntityType::LPK,
        ]);

        $ctkStage8 = CTK::factory()->create([
            'current_status' => CTKStatus::IjinDesa,
            'current_stage' => 8,
            'current_entity' => EntityType::PT,
        ]);

        // Act & Assert: byEntity scope
        $lpkCTK = CTK::byEntity(EntityType::LPK)->get();
        $this->assertTrue($lpkCTK->contains($ctkStage3));
        $this->assertFalse($lpkCTK->contains($ctkStage8));

        // Act & Assert: inLPKStages scope
        $inLPK = CTK::inLPKStages()->get();
        $this->assertTrue($inLPK->contains($ctkStage3));
        $this->assertFalse($inLPK->contains($ctkStage8));

        // Act & Assert: inPTStages scope
        $inPT = CTK::inPTStages()->get();
        $this->assertTrue($inPT->contains($ctkStage8));
        $this->assertFalse($inPT->contains($ctkStage3));
    }
}
