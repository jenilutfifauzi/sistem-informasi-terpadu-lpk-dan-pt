<?php

namespace Tests\Feature\Exports;

use App\Filament\Exports\UserExport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function test_export_class_generates_correct_headings()
    {
        $query = User::query();
        $export = new UserExport($query);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertContains('Name', $headings);
        $this->assertContains('Email', $headings);
        $this->assertContains('Entity', $headings);
        $this->assertContains('Roles', $headings);
        $this->assertNotContains('Password', $headings); // Password should be excluded per FR-009
        $this->assertNotContains('password', $headings);
    }

    /** @test */
    public function test_export_maps_user_data_correctly()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test-user@example.com',
        ]);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());

        $this->assertIsArray($mapped);
        $this->assertContains('Test User', $mapped);
        $this->assertContains('test-user@example.com', $mapped);
    }

    /** @test */
    public function test_password_fields_excluded_from_export()
    {
        $user = User::factory()->create([
            'password' => bcrypt('test-password'),
            'remember_token' => 'test-token',
        ]);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());
        $headings = $export->headings();

        // Password and remember_token should not be in mapped data or headings
        $this->assertNotContains('Password', $headings);
        $this->assertNotContains('Remember Token', $headings);

        // Mapped data should not contain password hash or token
        $mappedString = implode(',', $mapped);
        $this->assertStringNotContainsString('test-token', $mappedString);
    }

    /** @test */
    public function test_export_includes_user_roles()
    {
        $role = Role::create(['name' => 'Test Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());

        // Should contain role name
        $this->assertContains('Test Admin', $mapped);
    }

    /** @test */
    public function test_export_handles_user_with_multiple_roles()
    {
        $role1 = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $role2 = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole([$role1, $role2]);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());

        // Should contain both role names as comma-separated
        $rolesString = implode(',', $mapped);
        $this->assertStringContainsString('Admin', $rolesString);
        $this->assertStringContainsString('Editor', $rolesString);
    }

    /** @test */
    public function test_export_handles_user_without_roles()
    {
        $user = User::factory()->create();

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());

        // Should contain 'No Roles' indicator
        $this->assertContains('No Roles', $mapped);
    }

    /** @test */
    public function test_export_respects_query_filters()
    {
        // Create test data with different entities
        $lpkUser = User::factory()->create([
            'entity' => 'LPK',
            'email' => 'lpk-user@example.com',
        ]);
        $ptUser = User::factory()->create([
            'entity' => 'PT',
            'email' => 'pt-user@example.com',
        ]);

        // Query only LPK users
        $query = User::query()
            ->where('entity', 'LPK')
            ->whereIn('email', ['lpk-user@example.com', 'pt-user@example.com']);
        $export = new UserExport($query);

        $result = $export->query()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('LPK', $result->first()->entity->value);
    }

    /** @test */
    public function test_export_formats_dates_correctly()
    {
        $user = User::factory()->create([
            'created_at' => '2024-01-15 10:30:00',
        ]);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $mapped = $export->map($user->fresh());

        // Should contain formatted date
        $this->assertContains('2024-01-15 10:30:00', $mapped);
    }

    /** @test */
    public function test_export_handles_empty_dataset()
    {
        // Create an empty query
        $query = User::query()->where('id', 0); // No match
        $export = new UserExport($query);

        $result = $export->query()->get();

        $this->assertCount(0, $result);
        $this->assertIsArray($export->headings());
    }

    /** @test */
    public function test_export_eager_loads_roles()
    {
        $role = Role::firstOrCreate(['name' => 'Test Role', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $query = User::query()->where('id', $user->id);
        $export = new UserExport($query);

        $result = $export->query()->get();

        // Roles should be loaded
        $this->assertTrue($result->first()->relationLoaded('roles'));
    }
}
