# Quickstart Guide: User Management & RBAC

**Feature**: 001-user-management-rbac  
**Date**: 2026-01-13  
**Audience**: Developers, QA, System Administrators

## Prerequisites

Before starting, ensure you have:
- ✅ Laravel 11.x installed and running
- ✅ PHP 8.4+ with required extensions
- ✅ MySQL/MariaDB database configured
- ✅ Composer installed
- ✅ Node.js and NPM installed (for Vite)
- ✅ Git repository initialized

---

## Installation Steps

### Step 1: Install Dependencies

```bash
# Navigate to project root
cd /path/to/SIT_LPK

# Install Filament Shield (includes Spatie Permission)
composer require bezhansalleh/filament-shield

# Install Spatie Activity Log
composer require spatie/laravel-activitylog
```

**Expected Output**:
```
Package operations: 3 installs, 0 updates, 0 removals
  - Installing spatie/laravel-permission (6.x)
  - Installing spatie/laravel-activitylog (4.x)
  - Installing bezhansalleh/filament-shield (3.x)
```

---

### Step 2: Setup Shield

```bash
# Run Shield setup command (publishes config, migrations, runs migrations)
php artisan shield:setup

# Install Shield for admin panel
php artisan shield:install admin
```

**What this does**:
- Publishes `config/filament-shield.php`
- Publishes Spatie Permission migrations
- Runs migrations (creates `roles`, `permissions`, pivot tables)
- Registers Shield plugin in `AdminPanelProvider`

**Verify**:
```bash
# Check if Shield config exists
ls -la config/filament-shield.php

# Check if migrations ran
php artisan migrate:status | grep permission
```

---

### Step 3: Setup Activity Log

```bash
# Publish activitylog migrations
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

# Publish activitylog config
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"

# Run migrations
php artisan migrate
```

**Verify**:
```bash
# Check if activity_log table exists
php artisan db:table activity_log
```

**Configure** (`config/activitylog.php`):
```php
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 90, // 90-day retention per spec
    'default_log_name' => 'default',
    'subject_returns_soft_deleted_models' => true, // Important!
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'table_name' => 'activity_log',
];
```

---

### Step 4: Create Entity Enum

**File**: `app/Enums/EntityType.php`

```bash
# Create Enums directory
mkdir -p app/Enums

# Create EntityType enum
touch app/Enums/EntityType.php
```

**Content**:
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

---

### Step 5: Add Entity Column to Users Table

```bash
# Create migration
php artisan make:migration add_entity_to_users_table --table=users
```

**Migration Content** (`database/migrations/2026_01_13_000001_add_entity_to_users_table.php`):
```php
<?php

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

```bash
# Run migration
php artisan migrate
```

---

### Step 6: Update User Model

**File**: `app/Models/User.php`

```php
<?php

namespace App\Models;

use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'entity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'entity' => EntityType::class, // Cast to Enum
        ];
    }
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'entity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user_management');
    }
}
```

---

### Step 7: Create Roles and Permissions Seeder

```bash
# Create seeder
php artisan make:seeder RolesAndPermissionsSeeder
```

**File**: `database/seeders/RolesAndPermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions for User resource
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
        
        // Create permissions for Role resource
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
        
        // Create 8 roles
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
        $role->givePermissionTo(Permission::all());
    }
    
    private function createAdminLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_lpk', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user', 'view_any_user']);
    }
    
    private function createInstrukturRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'instruktur', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user']);
    }
    
    private function createHRPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'hr_pt', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
        ]);
    }
    
    private function createAdminPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin_pt', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user', 'view_any_user']);
    }
    
    private function createLegalPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'legal_pt', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user']);
    }
    
    private function createKeuanganPTRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_pt', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user']);
    }
    
    private function createKeuanganLPKRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'keuangan_lpk', 'guard_name' => 'web']);
        $role->givePermissionTo(['view_user']);
    }
    
    private function createPimpinanRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        $viewPermissions = Permission::where('name', 'like', 'view_%')->get();
        $role->givePermissionTo($viewPermissions);
    }
}
```

**Run Seeder**:
```bash
# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**Verify**:
```bash
# Check created roles
php artisan tinker
>>> \Spatie\Permission\Models\Role::pluck('name');
# Should show: super_admin, admin_lpk, instruktur, hr_pt, admin_pt, legal_pt, keuangan_pt, keuangan_lpk, pimpinan
```

---

### Step 8: Create Super Admin User

```bash
# Use Shield's command
php artisan shield:super-admin

# Follow prompts:
# Name: Super Administrator
# Email: super@admin.com
# Password: (enter secure password)
```

**Alternative** (manual via Tinker):
```bash
php artisan tinker

>>> use App\Models\User;
>>> use App\Enums\EntityType;
>>> $user = User::create([
...     'name' => 'Super Administrator',
...     'email' => 'super@admin.com',
...     'password' => bcrypt('password'),
...     'entity' => EntityType::PT,
... ]);
>>> $user->assignRole('super_admin');
>>> exit
```

**Verify**:
```bash
php artisan tinker
>>> User::where('email', 'super@admin.com')->first()->roles->pluck('name');
# Should show: ["super_admin"]
```

---

### Step 9: Create UserResource

```bash
# Generate UserResource with Shield
php artisan make:filament-resource User --generate --soft-deletes
```

**File**: `app/Filament/Resources/UserResource.php`

**Customize Form**:
```php
use App\Enums\EntityType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

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
                ->native(false),
            
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->required(),
        ]);
}
```

**Customize Table**:
```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

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

---

### Step 10: Generate Policies

```bash
# Generate Shield policies for all resources
php artisan shield:generate --all

# Or just for UserResource
php artisan shield:generate --resource=UserResource
```

**Verify**:
```bash
# Check if UserPolicy was created
ls -la app/Policies/UserPolicy.php
```

---

## Usage Examples

### Creating a New User (via Filament UI)

1. **Login as Super Admin**:
   - Navigate to `http://localhost/admin/login`
   - Email: `super@admin.com`
   - Password: (your password)

2. **Navigate to Users**:
   - Click "Users" in sidebar
   - Click "New User" button

3. **Fill Form**:
   - Name: `Admin LPK Test`
   - Email: `admin.lpk@test.com`
   - Password: `password123`
   - Entity: `LPK (Lembaga Pelatihan Kerja)`
   - Roles: Select `admin_lpk`

4. **Save**:
   - Click "Create"
   - User created successfully!

5. **Verify Activity Log** (via Tinker):
```bash
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::latest()->first();
# Should show 'created' activity for new user
```

---

### Assigning Roles to Users

**Via Filament**:
1. Navigate to Users resource
2. Click "Edit" on a user
3. Select roles in "Roles" multi-select
4. Click "Save"

**Via Tinker**:
```bash
php artisan tinker

>>> $user = User::find(2);
>>> $user->assignRole('admin_lpk');
>>> $user->assignRole(['admin_lpk', 'instruktur']); // Multiple roles
>>> $user->roles->pluck('name'); // Check assigned roles
```

---

### Managing Roles & Permissions (via Shield UI)

1. **Navigate to Roles**:
   - Login as super admin
   - Click "Shield" → "Roles" in sidebar

2. **Edit a Role**:
   - Click "Edit" on any role (e.g., `admin_lpk`)
   - Check/uncheck permissions in the grid
   - Click "Save"

3. **Create a New Role**:
   - Click "New Role"
   - Name: `custom_role`
   - Select permissions
   - Save

---

### Checking Permissions (in Code)

```php
// In a controller or Livewire component
if (auth()->user()->can('create_user')) {
    // User has permission to create users
}

if (auth()->user()->hasRole('super_admin')) {
    // User is a super admin
}

if (auth()->user()->hasAnyRole(['super_admin', 'pimpinan'])) {
    // User is either super admin or pimpinan
}
```

---

### Viewing Activity Logs

**Via Tinker**:
```bash
php artisan tinker

# Latest 10 activities
>>> \Spatie\Activitylog\Models\Activity::latest()->take(10)->get();

# Activities for a specific user
>>> $user = User::find(1);
>>> $user->actions()->get(); // Activities CAUSED by this user
>>> \Spatie\Activitylog\Models\Activity::forSubject($user)->get(); // Activities ON this user

# Activities in last 24 hours
>>> \Spatie\Activitylog\Models\Activity::where('created_at', '>=', now()->subDay())->get();
```

**Future**: Create an ActivityLogResource in Filament for UI-based viewing.

---

## Testing

### Run Feature Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Filament/UserResourceTest.php

# Run with filter
php artisan test --filter=test_super_admin_can_create_user
```

### Manual Testing Checklist

- [ ] Super admin can login
- [ ] Super admin can access Users resource
- [ ] Super admin can access Roles resource
- [ ] Super admin can create new user with entity and roles
- [ ] Created user can login successfully
- [ ] User with role "instruktur" CANNOT access Users resource (403 error)
- [ ] User with role "pimpinan" CAN view Users resource (read-only)
- [ ] Soft deleting a user works (user can't login, data retained)
- [ ] Restoring a soft-deleted user works
- [ ] Activity log captures user creation/update/deletion
- [ ] Changing user roles reflects immediately in permissions

---

## Troubleshooting

### Issue: "Class 'Spatie\Permission\Models\Role' not found"

**Solution**:
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

### Issue: "Permission denied" when accessing resource

**Solution**:
```bash
# Re-generate permissions
php artisan shield:generate --all

# Check user's permissions
php artisan tinker
>>> auth()->user()->getAllPermissions()->pluck('name');
```

### Issue: Activity log not recording changes

**Solution**:
```php
// Ensure User model has LogsActivity trait and getActivitylogOptions() method
// Check if ACTIVITY_LOGGER_ENABLED is true in .env
```

### Issue: Entity field not showing in form

**Solution**:
```bash
# Ensure migration ran
php artisan migrate:status | grep add_entity_to_users

# Check if EntityType enum exists
ls -la app/Enums/EntityType.php

# Clear views cache
php artisan view:clear
```

---

## Cleanup Commands

```bash
# Clear all caches
php artisan optimize:clear

# Format code with Laravel Pint
vendor/bin/pint

# Clean old activity logs (older than 90 days)
php artisan activitylog:clean
```

---

## Next Steps

After completing this quickstart:

1. ✅ **Run tests** to ensure everything works
2. ✅ **Create sample users** for each of the 8 roles (for testing)
3. ✅ **Test permission enforcement** by logging in as different roles
4. ✅ **Review activity logs** to ensure audit trail is working
5. ⏭️ **Move to next feature**: CTK Registration (Feature #002)

---

## Reference Commands

```bash
# Create super admin
php artisan shield:super-admin

# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Generate permissions for all resources
php artisan shield:generate --all

# Clean old activity logs
php artisan activitylog:clean

# Run tests
php artisan test --filter=UserResource

# Check roles
php artisan tinker
>>> \Spatie\Permission\Models\Role::with('permissions')->get();
```

---

**Quickstart Complete** ✅  
**Implementation Ready**: All Phase 0 and Phase 1 artifacts complete. Ready for `/speckit.tasks` to generate detailed task breakdown.
