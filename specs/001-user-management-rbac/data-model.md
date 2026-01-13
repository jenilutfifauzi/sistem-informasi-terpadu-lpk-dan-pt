# Data Model: User Management & RBAC Foundation

**Feature**: 001-user-management-rbac  
**Date**: 2026-01-13  
**Phase**: Phase 1 - Design & Contracts

## Overview

This document defines the complete data model for the User Management and RBAC foundation. All models follow Laravel Eloquent conventions and align with Spatie Laravel Permission package structure.

---

## Entity Relationship Diagram (ERD)

```
┌──────────────┐         ┌──────────────────┐         ┌────────────────┐
│    users     │────┬────│ model_has_roles  │────┬────│     roles      │
└──────────────┘    │    └──────────────────┘    │    └────────────────┘
                    │                             │             │
                    │    ┌──────────────────────┐ │             │
                    └────│model_has_permissions│ │             │
                         └──────────────────────┘ │             │
                                                  │             │
                         ┌──────────────────────┐ │             │
                         │ role_has_permissions │─┘             │
                         └──────────────────────┘               │
                                    │                           │
                                    └───────────────────────────┘
                                                │
                                        ┌───────────────┐
                                        │  permissions  │
                                        └───────────────┘

┌──────────────┐
│ activity_log │──── references users (causer_id)
└──────────────┘──── references any model polymorphically (subject)
```

---

## Entities

### 1. User

**Purpose**: Represents authenticated users in the system with role-based access and entity assignment.

**Table**: `users`

**Columns**:

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | NOT NULL, UNIQUE | Login email (unique identifier) |
| email_verified_at | timestamp | NULL | Email verification timestamp (unused in phase 1) |
| password | varchar(255) | NOT NULL | Bcrypt hashed password |
| entity | enum('PT','LPK') | NOT NULL | Entity assignment (PT or LPK) |
| remember_token | varchar(100) | NULL | Laravel remember me token |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `users_email_unique` (`email`)
- INDEX `users_deleted_at_index` (`deleted_at`) - for soft delete queries

**Relationships**:
- `belongsToMany(Role)` via `model_has_roles`
- `belongsToMany(Permission)` via `model_has_permissions`
- `morphMany(Activity, 'causer')` - activities caused by this user
- `morphMany(Activity, 'subject')` - activities performed on this user

**Traits**:
- `Illuminate\Database\Eloquent\SoftDeletes`
- `Spatie\Permission\Traits\HasRoles`
- `Spatie\Activitylog\Traits\LogsActivity`
- `Illuminate\Notifications\Notifiable`
- `Laravel\Sanctum\HasApiTokens` (for future API access)

**Casts**:
```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'entity' => EntityType::class, // Cast to Enum
    ];
}
```

**Fillable**:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'entity',
];
```

**Hidden**:
```php
protected $hidden = [
    'password',
    'remember_token',
];
```

**Activity Logging Configuration**:
```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'entity'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->useLogName('user_management');
}
```

**Validation Rules** (via FormRequest):
```php
// CreateUserRequest.php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email', 'max:255'],
        'password' => ['required', 'string', 'min:8', 'max:255'],
        'entity' => ['required', 'in:PT,LPK'],
        'roles' => ['required', 'array', 'min:1'],
        'roles.*' => ['exists:roles,id'],
    ];
}

// UpdateUserRequest.php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email,' . $this->user->id, 'max:255'],
        'password' => ['nullable', 'string', 'min:8', 'max:255'],
        'entity' => ['required', 'in:PT,LPK'],
        'roles' => ['required', 'array', 'min:1'],
        'roles.*' => ['exists:roles,id'],
    ];
}
```

---

### 2. Role

**Purpose**: Represents user roles for RBAC. Managed by Spatie Permission package.

**Table**: `roles`

**Columns**:

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | varchar(255) | NOT NULL | Role name (e.g., 'super_admin', 'admin_lpk') |
| guard_name | varchar(255) | NOT NULL | Guard name (default: 'web') |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)

**Relationships**:
- `belongsToMany(Permission)` via `role_has_permissions`
- `belongsToMany(User)` via `model_has_roles`

**Predefined Roles** (from seeder):
1. `super_admin` - Full system access
2. `admin_lpk` - LPK administration
3. `instruktur` - LPK instructor
4. `hr_pt` - PT human resources
5. `admin_pt` - PT administration
6. `legal_pt` - PT legal department
7. `keuangan_pt` - PT finance
8. `keuangan_lpk` - LPK finance
9. `pimpinan` - Leadership (view all)

**Model**: `Spatie\Permission\Models\Role` (Spatie package model)

**Note**: Shield provides a UI (RoleResource) for managing roles and their permissions.

---

### 3. Permission

**Purpose**: Represents granular permissions for resources, pages, and widgets.

**Table**: `permissions`

**Columns**:

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | varchar(255) | NOT NULL | Permission name (e.g., 'view_user', 'create_user') |
| guard_name | varchar(255) | NOT NULL | Guard name (default: 'web') |
| created_at | timestamp | NULL | Record creation timestamp |
| updated_at | timestamp | NULL | Record update timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)

**Relationships**:
- `belongsToMany(Role)` via `role_has_permissions`
- `belongsToMany(User)` via `model_has_permissions` (direct user permissions - rare)

**Permission Naming Convention** (Shield standard):
- **Resource**: `{action}_{resource}` (e.g., `view_user`, `create_user`, `delete_user`)
- **Page**: `page_{PageName}` (e.g., `page_Dashboard`)
- **Widget**: `widget_{WidgetName}` (e.g., `widget_StatsOverview`)

**Phase 1 Permissions** (UserResource):
- `view_user` - View single user record
- `view_any_user` - List users in table
- `create_user` - Create new user
- `update_user` - Edit existing user
- `delete_user` - Soft delete user
- `restore_user` - Restore soft-deleted user
- `force_delete_user` - Permanently delete user
- `replicate_user` - Duplicate user record

**Phase 1 Permissions** (RoleResource - Shield):
- `view_role` - View single role record
- `view_any_role` - List roles in table
- `create_role` - Create new role
- `update_role` - Edit existing role (assign permissions)
- `delete_role` - Delete role

**Model**: `Spatie\Permission\Models\Permission` (Spatie package model)

---

### 4. Activity (Audit Log)

**Purpose**: Tracks all changes to users, roles, and permissions for compliance and auditability.

**Table**: `activity_log`

**Columns**:

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| log_name | varchar(255) | NULL | Log category (e.g., 'user_management') |
| description | text | NOT NULL | Action description (e.g., 'created', 'updated', 'deleted') |
| subject_type | varchar(255) | NULL | Model class that was changed (polymorphic) |
| event | varchar(255) | NULL | Eloquent event (e.g., 'created', 'updated') |
| subject_id | bigint UNSIGNED | NULL | ID of the model that was changed |
| causer_type | varchar(255) | NULL | Model class of the user who performed action |
| causer_id | bigint UNSIGNED | NULL | ID of the user who performed action |
| properties | json | NULL | Before/after values, custom properties |
| batch_uuid | char(36) | NULL | UUID for batch logging |
| created_at | timestamp | NULL | When the activity occurred |
| updated_at | timestamp | NULL | Record update timestamp (rarely used) |

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX `subject` (`subject_type`, `subject_id`)
- INDEX `causer` (`causer_type`, `causer_id`)
- INDEX `activity_log_log_name_index` (`log_name`)
- INDEX `activity_log_batch_uuid_index` (`batch_uuid`)

**Relationships** (polymorphic):
- `morphTo('subject')` - The model that was changed (User, Role, etc.)
- `morphTo('causer')` - The user who performed the action

**Model**: `Spatie\Activitylog\Models\Activity` (Spatie package model)

**Example Log Entry**:
```php
Activity {
    id: 1,
    log_name: 'user_management',
    description: 'updated',
    subject_type: 'App\\Models\\User',
    subject_id: 5,
    causer_type: 'App\\Models\\User',
    causer_id: 1, // Super admin
    event: 'updated',
    properties: {
        attributes: {
            name: 'John Doe Updated',
            email: 'john@example.com',
            entity: 'PT'
        },
        old: {
            name: 'John Doe',
            email: 'john@example.com',
            entity: 'PT'
        }
    },
    created_at: '2026-01-13 10:30:00'
}
```

**Pruning Configuration** (config/activitylog.php):
```php
'delete_records_older_than_days' => 90, // Per spec: 90-day retention
```

---

## Pivot Tables (Spatie Permission)

### model_has_roles

**Purpose**: Many-to-many relationship between users and roles.

**Columns**:
- `role_id` (bigint UNSIGNED, FK to roles.id)
- `model_type` (varchar(255), polymorphic model class)
- `model_id` (bigint UNSIGNED, polymorphic model ID)

**Indexes**:
- PRIMARY KEY (`role_id`, `model_id`, `model_type`)
- INDEX `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`)

### model_has_permissions

**Purpose**: Many-to-many relationship for direct user permissions (rare - typically use roles).

**Columns**:
- `permission_id` (bigint UNSIGNED, FK to permissions.id)
- `model_type` (varchar(255), polymorphic model class)
- `model_id` (bigint UNSIGNED, polymorphic model ID)

**Indexes**:
- PRIMARY KEY (`permission_id`, `model_id`, `model_type`)
- INDEX `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`)

### role_has_permissions

**Purpose**: Many-to-many relationship between roles and permissions.

**Columns**:
- `permission_id` (bigint UNSIGNED, FK to permissions.id)
- `role_id` (bigint UNSIGNED, FK to roles.id)

**Indexes**:
- PRIMARY KEY (`permission_id`, `role_id`)
- FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
- FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE

---

## Enums

### EntityType

**Purpose**: Type-safe representation of PT vs LPK entities.

**File**: `app/Enums/EntityType.php`

**Values**:
```php
enum EntityType: string
{
    case PT = 'PT';
    case LPK = 'LPK';
}
```

**Methods**:
```php
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
```

---

## State Transitions

### User Lifecycle

```
[New User] 
    │
    ↓ (create)
[Active User] ←─────┐
    │               │
    ↓ (soft delete) │ (restore)
[Deleted User] ─────┘
    │
    ↓ (force delete after 90 days)
[Permanently Deleted]
```

**States**:
1. **Active**: `deleted_at` is NULL - user can login
2. **Soft Deleted**: `deleted_at` is NOT NULL - user cannot login, data retained
3. **Force Deleted**: Record removed from database - permanent (admin only)

**Transitions**:
- Active → Soft Deleted: User or admin soft deletes
- Soft Deleted → Active: Admin restores user
- Soft Deleted → Force Deleted: Admin force deletes (rare) OR automated cleanup after 90 days

---

## Data Integrity Rules

### Constraints

1. **Email Uniqueness**: 
   - Unique constraint on `users.email`
   - Validation enforced at form level

2. **Entity Requirement**:
   - `entity` column is NOT NULL
   - Must be 'PT' or 'LPK' (enum constraint)
   - Validated at form and database level

3. **Role Assignment**:
   - Every user MUST have at least one role
   - Enforced via validation rule: `'roles' => ['required', 'array', 'min:1']`

4. **Password Security**:
   - Minimum 8 characters
   - Bcrypt hashed (Laravel default)
   - Never logged in activity_log

5. **Soft Delete Protection**:
   - Super admin cannot be soft deleted (policy check)
   - System requires at least 1 super admin exists

6. **Role Deletion Protection**:
   - Cannot delete role if assigned to users
   - Enforced via validation before delete

### Cascade Rules

**On Role Delete**:
- Permissions detached from role (via `role_has_permissions` cascade delete)
- Users keep their other roles, but lose this role

**On User Soft Delete**:
- Roles retained (can be restored)
- Activity log retained (causer references preserved)
- Sessions invalidated

**On User Force Delete**:
- Roles detached
- Activity log `causer_id` becomes dangling reference (but preserved for audit)

---

## Indexing Strategy

### Performance Considerations

1. **Email Lookups** (login):
   - UNIQUE index on `users.email` - already optimal

2. **Permission Checks**:
   - Spatie caches permissions in application cache
   - Pivot table indexes on model_id and model_type

3. **Soft Delete Queries**:
   - Index on `users.deleted_at` for `withTrashed()` and `onlyTrashed()` queries

4. **Activity Log Queries**:
   - Composite index on `(subject_type, subject_id)` for finding all activities on a model
   - Index on `causer_id` for finding all activities by a user
   - Index on `created_at` for time-based queries (latest activities)

---

## Sample Data

### Super Admin User
```php
User {
    id: 1,
    name: 'Super Administrator',
    email: 'super@admin.com',
    entity: EntityType::PT,
    created_at: '2026-01-13 00:00:00',
    roles: [
        Role { name: 'super_admin' }
    ]
}
```

### Admin LPK User
```php
User {
    id: 2,
    name: 'Admin LPK',
    email: 'admin@lpk.com',
    entity: EntityType::LPK,
    created_at: '2026-01-13 00:00:00',
    roles: [
        Role { name: 'admin_lpk' }
    ]
}
```

---

## Migration Files Required

1. **2014_10_12_000000_create_users_table.php** (EXISTING - Laravel default)
2. **2026_01_13_000001_add_entity_to_users_table.php** (NEW - add entity column)
3. **[timestamp]_create_permission_tables.php** (AUTO - Spatie Permission via Shield)
4. **[timestamp]_create_activity_log_table.php** (AUTO - Spatie Activitylog)

---

## Validation Summary

### User Model Validation

| Field | Rules | Error Messages |
|-------|-------|----------------|
| name | required, string, max:255 | "Nama wajib diisi", "Nama maksimal 255 karakter" |
| email | required, email, unique, max:255 | "Email wajib diisi", "Email sudah terdaftar" |
| password | required (create), min:8 | "Password minimal 8 karakter" |
| entity | required, enum(PT,LPK) | "Entitas wajib dipilih" |
| roles | required, array, min:1 | "Minimal satu role harus dipilih" |

### Role Model Validation (Shield handles)

| Field | Rules |
|-------|-------|
| name | required, string, unique(name,guard_name) |
| guard_name | required, default:'web' |
| permissions | array (optional) |

---

## Future Enhancements (Out of Scope - Phase 1)

- **Multi-tenancy**: Add `team_id` column to users and roles (for multi-PT/LPK support)
- **Two-Factor Auth**: Add `two_factor_secret`, `two_factor_recovery_codes` columns
- **Email Verification**: Enforce `email_verified_at` requirement
- **Password Expiry**: Add `password_changed_at` column, enforce 90-day rotation
- **Login History**: Create `user_logins` table to track IP, device, timestamp
- **API Tokens**: Utilize `personal_access_tokens` table (Sanctum - already in Laravel)

---

**Data Model Phase Complete** ✅  
**Next**: Create quickstart.md (Phase 1 final artifact)
