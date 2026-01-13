# Research: User Management & RBAC Foundation

**Feature**: 001-user-management-rbac  
**Date**: 2026-01-13  
**Phase**: Phase 0 - Research & Decisions

## Executive Summary

This research phase evaluates technical approaches for implementing authentication, RBAC, and audit logging in the User Management feature. All decisions prioritize simplicity (Constitution Principle V) while ensuring compliance with auditability requirements (Constitution Principle IV).

### Key Decisions Made:

1. **Audit Logging**: Use **spatie/laravel-activitylog** package
2. **Shield Setup**: Non-tenant mode initially (tenancy is future enhancement)
3. **Entity Field**: Use **Enum** class (EntityType::PT, EntityType::LPK)
4. **Permission Naming**: Follow Shield defaults (e.g., `view_user`, `create_user`, `delete_user`)

---

## 1. Audit Logging Approach

### Decision: Use spatie/laravel-activitylog ✅

**Options Evaluated:**

| Approach | Pros | Cons | Verdict |
|----------|------|------|---------|
| **Custom AuditLog Model** | - Full control<br/>- No extra dependency<br/>- Minimal overhead | - More code to maintain<br/>- Need to implement model observers<br/>- Reinventing the wheel | ❌ Rejected |
| **spatie/laravel-activitylog** | - Well-maintained (Spatie)<br/>- Laravel 11 compatible<br/>- Easy Filament integration<br/>- 365-day retention configurable<br/>- Batch logging support | - Additional package dependency<br/>- Slightly more database columns | ✅ **Selected** |
| **Manual Logging via Events** | - Lightweight<br/>- No package needed | - Very manual<br/>- Error-prone<br/>- Inconsistent | ❌ Rejected |

### Rationale

**Constitution Alignment:**
- **Principle IV (Auditability)**: spatie/laravel-activitylog provides comprehensive logging with `who`, `when`, `what`, `why` (via properties field)
- **Principle V (Simplicity)**: Using a proven, documented package is simpler than custom implementation

**Technical Benefits:**
- Automatic model event logging (creating, updating, deleting)
- Support for custom properties (`->withProperties()`)
- Causer tracking (which user performed the action)
- Subject tracking (which model was affected)
- Batch logging (group related changes)
- Built-in cleanup command for retention policy

### Implementation Details

**Installation:**
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
php artisan migrate
```

**Configuration** (`config/activitylog.php`):
```php
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 90, // Per spec: 90-day retention
    'default_log_name' => 'default',
    'default_auth_driver' => null, // Use Laravel's default
    'subject_returns_soft_deleted_models' => true, // Important for soft deletes
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'table_name' => 'activity_log',
];
```

**User Model** (Add logging):
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasRoles, SoftDeletes, LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'entity']) // Don't log password!
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs();
    }
}
```

**Role Model** (Shield handles this, but we can enhance):
```php
// In RoleResource actions, log manually:
activity()
    ->causedBy(auth()->user())
    ->performedOn($role)
    ->withProperties(['permissions' => $role->permissions->pluck('name')])
    ->log('role_permissions_updated');
```

### Testing Strategy

**Feature Test** (`tests/Feature/AuditLogTest.php`):
```php
public function test_user_creation_is_logged()
{
    $user = User::factory()->create([
        'name' => 'Test User',
        'entity' => EntityType::PT,
    ]);
    
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => User::class,
        'subject_id' => $user->id,
        'description' => 'created',
        'causer_id' => auth()->id(),
    ]);
}

public function test_user_update_logs_only_dirty_attributes()
{
    $user = User::factory()->create();
    
    $user->update(['name' => 'Updated Name']);
    
    $activity = Activity::latest()->first();
    $this->assertArrayHasKey('name', $activity->properties['attributes']);
    $this->assertArrayNotHasKey('password', $activity->properties['attributes']);
}
```

---

## 2. Shield Configuration & Setup

### Decision: Non-Tenant Mode (Tenancy = Future Enhancement) ✅

**Setup Commands:**
```bash
# 1. Install Shield
composer require bezhansalleh/filament-shield

# 2. Setup Shield (publishes config, migrations, runs migrations)
php artisan shield:setup

# 3. Install for panel (registers plugin in AdminPanelProvider)
php artisan shield:install admin

# 4. Generate permissions for existing resources
php artisan shield:generate --all
```

### Shield Panel Registration

**File**: `app/Providers/Filament/AdminPanelProvider.php`

```php
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->plugins([
            FilamentShieldPlugin::make()
                ->gridColumns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 3
                ])
                ->sectionColumnSpan(1)
                ->checkboxListColumns([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 4,
                ])
                ->resourceCheckboxListColumns([
                    'default' => 1,
                    'sm' => 2,
                ]),
        ]);
}
```

### Shield Configuration

**File**: `config/filament-shield.php`

Key settings:
```php
return [
    'shield_resource' => [
        'should_register_navigation' => true,
        'slug' => 'shield/roles',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => true,
        'is_globally_searchable' => false,
        'show_model_path' => true,
        'is_scoped_to_tenant' => false, // IMPORTANT: false for phase 1
    ],
    
    'auth_provider_model' => [
        'fqcn' => 'App\\Models\\User',
    ],
    
    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ],
        'page' => 'page',
        'widget' => 'widget',
    ],
];
```

### Permission Naming Convention

Shield auto-generates permissions using this pattern:

**Resources:**
- `view_user` - Can view single user
- `view_any_user` - Can list users
- `create_user` - Can create new user
- `update_user` - Can edit user
- `delete_user` - Can soft delete user
- `force_delete_user` - Can permanently delete user
- `restore_user` - Can restore soft-deleted user

**Pages:**
- `page_Dashboard` - Can access Dashboard page

**Widgets:**
- `widget_StatsOverview` - Can view StatsOverview widget

**Custom UserResource Permission** (if needed):
```php
// In UserResource.php
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class UserResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'assign_roles', // CUSTOM permission for role assignment
        ];
    }
}
```

---

## 3. Entity Field Implementation

### Decision: Use Enum Class ✅

**Options Evaluated:**

| Approach | Pros | Cons | Verdict |
|----------|------|------|---------|
| **String Column** | - Simple migration<br/>- Flexible | - No type safety<br/>- Typo prone<br/>- Manual validation | ❌ Rejected |
| **Boolean (is_pt)** | - Efficient storage | - Not extensible (what if 3rd entity?)<br/>- Less readable | ❌ Rejected |
| **Enum Class** | - Type safety<br/>- Autocomplete in IDE<br/>- Extensible<br/>- Clean code | - Requires PHP 8.1+ (✅ we have 8.4) | ✅ **Selected** |

### Implementation

**File**: `app/Enums/EntityType.php`

```php
<?php

namespace App\Enums;

enum EntityType: string
{
    case PT = 'PT';
    case LPK = 'LPK';
    
    public function label(): string
    {
        return match($this) {
            self::PT => 'PT (Perusahaan Jasa Tenaga Kerja Indonesia)',
            self::LPK => 'LPK (Lembaga Pelatihan Kerja)',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::PT => 'success',
            self::LPK => 'primary',
        };
    }
    
    public static function options(): array
    {
        return [
            self::PT->value => self::PT->label(),
            self::LPK->value => self::LPK->label(),
        ];
    }
}
```

**Migration**: `database/migrations/2026_01_13_000001_add_entity_to_users_table.php`

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('entity', ['PT', 'LPK'])->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('entity');
        });
    }
};
```

**User Model**:

```php
use App\Enums\EntityType;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'entity',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'entity' => EntityType::class, // Auto-cast to Enum
        ];
    }
}
```

**UserResource Form**:

```php
use App\Enums\EntityType;
use Filament\Forms\Components\Select;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            
            TextInput::make('password')
                ->password()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->minLength(8)
                ->maxLength(255),
            
            Select::make('entity')
                ->options(EntityType::options())
                ->enum(EntityType::class)
                ->required()
                ->native(false), // Use Filament's styled select
            
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->required(),
        ]);
}
```

**UserResource Table**:

```php
use Filament\Tables\Columns\TextColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            
            TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->copyable(),
            
            TextColumn::make('entity')
                ->badge()
                ->color(fn (EntityType $state) => $state->color())
                ->formatStateUsing(fn (EntityType $state) => $state->value),
            
            TextColumn::make('roles.name')
                ->badge()
                ->separator(',')
                ->searchable(),
            
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('entity')
                ->options(EntityType::options()),
            
            SelectFilter::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload(),
        ]);
}
```

### Future Query Scoping (Preparation)

**Note**: This will be implemented in future features (CTK, Karyawan, etc.):

```php
// Global scope example (future implementation):
class ScopedByEntity extends Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }
        
        if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('pimpinan')) {
            // Super admin and Pimpinan can see ALL entities
            return;
        }
        
        // Scope to user's entity
        $builder->where('entity', auth()->user()->entity);
    }
}
```

---

## 4. Role Permissions Matrix

### 8 Roles from PRD

Based on PRD Section 4 (Role & Hak Akses), here is the initial permission mapping:

| Role | Entity | Primary Permissions | Resources Access |
|------|--------|---------------------|------------------|
| **Super Admin** | N/A (both) | ALL permissions | ALL resources |
| **Admin LPK** | LPK | CTK (LPK context), Training, Instructors | CTKResource (LPK), TrainingResource, EmployeeLPKResource |
| **Instruktur** | LPK | Attendance, Grading (view/update only) | TrainingResource (limited) |
| **HR PT** | PT | Employees (PT), Payroll | EmployeePTResource |
| **Admin PT** | PT | CTK (PT context), Placement | CTKResource (PT), PlacementResource |
| **Legal PT** | PT | CTK Documents (view/update) | CTKResource (documents only) |
| **Keuangan PT** | PT | Payroll (PT) | PayrollResource (PT) |
| **Keuangan LPK** | LPK | Honorarium (LPK) | PayrollResource (LPK) |
| **Pimpinan** | Both | View All (read-only) | ALL resources (view_any, view only) |

### Permission Seeder Structure

**File**: `database/seeders/RolesAndPermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\EntityType;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions for User resource (Phase 1)
        $userPermissions = [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
        ];
        
        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Create permissions for Role resource (Shield auto-creates, but ensure they exist)
        $rolePermissions = [
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
        ];
        
        foreach ($rolePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Create roles
        $this->createSuperAdminRole();
        $this->createAdminLPKRole();
        $this->createInstrukturRole();
        $this->createHRPTRole();
        $this->createAdminPTRole();
        $this->createLegalPTRole();
        $this->createKeuanganPTRole();
        $this->createKeuanganLPKRole();
        $this->createPimpinanRole();
    }
    
    private function createSuperAdminRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all()); // ALL permissions
    }
    
    private function createAdminLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_lpk', 'guard_name' => 'web']);
        // Phase 1: Minimal permissions (can view users, cannot modify)
        $role->givePermissionTo([
            'view_user',
            'view_any_user',
            // Future phases will add: CTK permissions, Training permissions, etc.
        ]);
    }
    
    private function createInstrukturRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'instruktur', 'guard_name' => 'web']);
        // Phase 1: Very minimal (future: attendance, grading)
        $role->givePermissionTo([
            'view_user', // Can view own profile
        ]);
    }
    
    private function createHRPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'hr_pt', 'guard_name' => 'web']);
        // Phase 1: Can manage users (PT entity only)
        $role->givePermissionTo([
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            // Future: Employee PT permissions
        ]);
    }
    
    private function createAdminPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_pt', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_user',
            'view_any_user',
            // Future: CTK PT, Placement permissions
        ]);
    }
    
    private function createLegalPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'legal_pt', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_user',
            // Future: CTK documents (view/update only)
        ]);
    }
    
    private function createKeuanganPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_pt', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_user',
            // Future: Payroll PT permissions
        ]);
    }
    
    private function createKeuanganLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_lpk', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_user',
            // Future: Payroll LPK permissions
        ]);
    }
    
    private function createPimpinanRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        // View all permissions (read-only)
        $viewPermissions = Permission::where('name', 'like', 'view_%')->get();
        $role->givePermissionTo($viewPermissions);
    }
}
```

**Run Seeder**:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Sample User Seeder (For Testing)

```php
// In RolesAndPermissionsSeeder or separate TestUsersSeeder
private function createSampleUsers(): void
{
    $superAdmin = User::firstOrCreate(
        ['email' => 'super@admin.com'],
        [
            'name' => 'Super Administrator',
            'password' => bcrypt('password'),
            'entity' => EntityType::PT, // Super admin can have any entity
        ]
    );
    $superAdmin->assignRole('super_admin');
    
    $adminLPK = User::firstOrCreate(
        ['email' => 'admin@lpk.com'],
        [
            'name' => 'Admin LPK',
            'password' => bcrypt('password'),
            'entity' => EntityType::LPK,
        ]
    );
    $adminLPK->assignRole('admin_lpk');
    
    $instruktur = User::firstOrCreate(
        ['email' => 'instruktur@lpk.com'],
        [
            'name' => 'Instruktur LPK',
            'password' => bcrypt('password'),
            'entity' => EntityType::LPK,
        ]
    );
    $instruktur->assignRole('instruktur');
    
    // ... create sample users for all 8 roles
}
```

---

## 5. Testing Strategy

### Test Structure

```
tests/
└── Feature/
    ├── Auth/
    │   ├── LoginTest.php                       # Authentication flow
    │   └── PermissionEnforcementTest.php       # RBAC validation
    ├── Filament/
    │   ├── UserResourceTest.php                # User CRUD
    │   └── RoleResourceTest.php                # Role CRUD
    ├── AuditLogTest.php                        # Activity logging
    └── Seeders/
        └── RolesAndPermissionsSeederTest.php   # Seeder validation
```

### Key Test Scenarios

**LoginTest.php**:
```php
public function test_super_admin_can_login()
{
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    
    $response = $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $response->assertRedirect('/admin');
    $this->assertAuthenticatedAs($user);
}

public function test_login_response_time_under_3_seconds()
{
    $user = User::factory()->create();
    
    $startTime = microtime(true);
    
    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    $this->assertLessThan(3, $duration, 'Login took longer than 3 seconds');
}
```

**PermissionEnforcementTest.php**:
```php
public function test_instruktur_cannot_access_user_resource()
{
    $user = User::factory()->create(['entity' => EntityType::LPK]);
    $user->assignRole('instruktur');
    
    $this->actingAs($user);
    
    // Try to access user list
    $response = $this->get('/admin/users');
    $response->assertForbidden();
}

public function test_pimpinan_can_view_all_resources()
{
    $user = User::factory()->create();
    $user->assignRole('pimpinan');
    
    $this->actingAs($user);
    
    $response = $this->get('/admin/users');
    $response->assertSuccessful();
    
    // Future: test other resources as well
}
```

**UserResourceTest.php** (using Livewire testing):
```php
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;

public function test_super_admin_can_create_user()
{
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $this->actingAs($admin);
    
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'entity' => EntityType::PT->value,
            'roles' => [Role::where('name', 'hr_pt')->first()->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('users', [
        'email' => 'newuser@test.com',
        'entity' => 'PT',
    ]);
}
```

---

## 6. Decision Summary & Next Steps

### Decisions Finalized ✅

| Decision Point | Choice | Rationale |
|----------------|--------|-----------|
| Audit Logging | spatie/laravel-activitylog | Simplicity + features + Laravel 11 compatible |
| Shield Setup | Non-tenant mode | Tenancy is future enhancement (keeps MVP simple) |
| Entity Field | Enum (EntityType) | Type safety, extensible, clean code |
| Permission Naming | Shield defaults | Consistent, predictable, documented |
| Sample Users | Seeder with all 8 roles | Enables immediate testing |

### Implementation Sequence

1. **Install packages**: Shield, activitylog
2. **Run setup commands**: `shield:setup`, `shield:install admin`
3. **Create migration**: Add `entity` column to users table
4. **Create EntityType enum**: PT/LPK values
5. **Update User model**: Add HasRoles trait, entity cast, LogsActivity
6. **Create seeder**: RolesAndPermissionsSeeder with 8 roles
7. **Create UserResource**: Custom resource with entity field
8. **Generate policies**: `php artisan shield:generate --all`
9. **Write tests**: Authentication, RBAC, audit logging
10. **Validate**: Run tests, create super admin, test login

### Open Questions (Deferred to Implementation)

- **Q**: Should entity field be editable after user creation?
  - **A**: Decide during implementation - likely YES for super_admin, NO for others
  
- **Q**: Can a user have roles from different entities?
  - **A**: NO - user belongs to ONE entity, roles must align with that entity
  
- **Q**: How to handle "System" actions (e.g., scheduled tasks)?
  - **A**: activitylog supports null causer (system actions) - document in quickstart

### Constitution Compliance Verification

- ✅ **Principle I**: User email uniqueness enforced
- ✅ **Principle II**: Entity field mandatory, enum-enforced
- ✅ **Principle III**: 8 roles seeded, Shield RBAC active
- ✅ **Principle IV**: activitylog logs all changes, 90-day retention
- ✅ **Principle V**: Using packages (Shield, activitylog) = simplest approach

---

## Appendix: Useful Commands Reference

### Shield Commands
```bash
# Create super admin
php artisan shield:super-admin

# Generate permissions for all resources
php artisan shield:generate --all

# Generate permissions for specific resource
php artisan shield:generate --resource=UserResource

# Publish Shield config
php artisan vendor:publish --tag=filament-shield-config
```

### Activity Log Commands
```bash
# Clean old logs (older than config days)
php artisan activitylog:clean

# Clean logs older than specific days
php artisan activitylog:clean --days=30
```

### Database Commands
```bash
# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Fresh migration with seeding (DESTRUCTIVE - dev only)
php artisan migrate:fresh --seed
```

### Testing Commands
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=UserResourceTest

# Run tests with coverage
php artisan test --coverage
```

---

**Research Phase Complete** ✅  
**Next Phase**: Create data-model.md (Phase 1)
