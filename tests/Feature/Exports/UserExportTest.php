<?php

namespace Tests\Feature\Exports;

use App\Enums\EntityType;
use App\Filament\Exports\UserExport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->user = User::factory()->create([
            'email' => 'user-export-auth-'.uniqid()."@example.test",
        ]);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_correct_headings(): void
    {
        $exporter = new UserExport(User::query());

        $this->assertSame([
            'ID',
            'Name',
            'Email',
            'Entity',
            'Roles',
            'Created At',
            'Updated At',
        ], $exporter->headings());
    }

    /** @test */
    public function it_maps_user_data_correctly(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_pt', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'name' => 'Admin PT',
            'email' => 'adminpt@example.com',
            'entity' => EntityType::PT,
        ]);
        $user->assignRole($role);
        $user->load('roles');

        $exporter = new UserExport(User::query());
        $mapped = $exporter->map($user);

        $this->assertSame($user->id, $mapped[0]);
        $this->assertSame('Admin PT', $mapped[1]);
        $this->assertSame('adminpt@example.com', $mapped[2]);
        $this->assertSame('PT', $mapped[3]);
        $this->assertSame('admin_pt', $mapped[4]);
        $this->assertNotEmpty($mapped[5]);
        $this->assertNotEmpty($mapped[6]);
    }

    /** @test */
    public function it_falls_back_to_no_roles_label_when_user_has_no_roles(): void
    {
        $user = User::factory()->create();
        $user->load('roles');

        $exporter = new UserExport(User::query());
        $mapped = $exporter->map($user);

        $this->assertSame('No Roles', $mapped[4]);
    }

    /** @test */
    public function it_respects_query_filters(): void
    {
        User::factory()->create(['entity' => EntityType::LPK]);
        User::factory()->create(['entity' => EntityType::PT]);

        $exporter = new UserExport(User::query()->where('entity', EntityType::LPK));
        $users = $exporter->query()->get();

        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertSame(EntityType::LPK, $user->entity);
        }
    }

    /** @test */
    public function it_eager_loads_roles_to_avoid_n_plus_1(): void
    {
        $exporter = new UserExport(User::query());
        $users = $exporter->query()->limit(3)->get();

        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertTrue($user->relationLoaded('roles'));
        }
    }

    /** @test */
    public function it_implements_xlsx_styling_contracts(): void
    {
        $exporter = new UserExport(User::query());

        $this->assertInstanceOf(ShouldAutoSize::class, $exporter);
        $this->assertInstanceOf(WithColumnWidths::class, $exporter);
        $this->assertInstanceOf(WithEvents::class, $exporter);
        $this->assertInstanceOf(WithStyles::class, $exporter);
    }

    /** @test */
    public function it_provides_styled_header_configuration(): void
    {
        $exporter = new UserExport(User::query());
        $styles = $exporter->styles(new Worksheet());

        $this->assertArrayHasKey(1, $styles);
        $this->assertTrue($styles[1]['font']['bold']);
        $this->assertSame('FFFFFF', $styles[1]['font']['color']['rgb']);
        $this->assertSame('1D4ED8', $styles[1]['fill']['startColor']['rgb']);
    }

    /** @test */
    public function it_registers_after_sheet_event_for_table_formatting(): void
    {
        $exporter = new UserExport(User::query());
        $events = $exporter->registerEvents();

        $this->assertArrayHasKey(AfterSheet::class, $events);
        $this->assertIsCallable($events[AfterSheet::class]);
    }

    /** @test */
    public function it_defines_readable_column_widths_for_exported_sheet(): void
    {
        $exporter = new UserExport(User::query());
        $columnWidths = $exporter->columnWidths();

        $this->assertSame(28, $columnWidths['B']);
        $this->assertSame(34, $columnWidths['C']);
        $this->assertSame(26, $columnWidths['E']);
    }
}
