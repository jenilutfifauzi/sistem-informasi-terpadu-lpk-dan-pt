# Feature Specification: User Management & RBAC Foundation

**Feature Branch**: `001-user-management-rbac`  
**Created**: 2026-01-13  
**Status**: Draft  
**Input**: Foundation module untuk autentikasi, role-based access control, dan user management menggunakan Filament Shield

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Super Admin Setup & Initial Login (Priority: P1)

Administrator pertama kali dapat membuat akun super admin dan login ke sistem untuk mulai mengelola data.

**Why this priority**: Tanpa ini, tidak ada yang bisa akses sistem sama sekali - ini adalah entry point paling fundamental.

**Independent Test**: Super admin bisa dibuat via command, login berhasil, dan melihat dashboard kosong dengan menu User Management.

**Acceptance Scenarios**:

1. **Given** database kosong, **When** menjalankan `shield:super-admin`, **Then** user super admin terbuat dengan email dan password yang ditentukan
2. **Given** super admin sudah dibuat, **When** login dengan kredensial super admin, **Then** berhasil masuk dan redirect ke dashboard
3. **Given** super admin login, **When** melihat sidebar navigation, **Then** menu "Shield", "Roles", dan "Users" tampil

---

### User Story 2 - Role Management (Priority: P1)

Admin dapat membuat dan mengelola 8 roles sesuai PRD (Admin LPK, Instruktur, HR PT, Admin PT, Legal PT, Keuangan PT, Keuangan LPK, Pimpinan) dengan permissions yang sesuai.

**Why this priority**: Roles adalah fondasi RBAC - semua user harus memiliki role sebelum bisa akses fitur apapun.

**Independent Test**: Admin bisa CRUD roles, assign permissions ke role, dan melihat daftar roles yang sudah dibuat.

**Acceptance Scenarios**:

1. **Given** super admin login, **When** membuka halaman Roles, **Then** melihat list roles (minimal super_admin role default)
2. **Given** di halaman Roles, **When** klik "New Role", **Then** form create role tampil dengan nama dan guard_name
3. **Given** form create role, **When** mengisi nama "Admin LPK" dan memilih permissions, **Then** role tersimpan dengan permissions terpilih
4. **Given** role sudah ada, **When** klik edit role, **Then** bisa mengubah nama dan permissions
5. **Given** role tidak digunakan user manapun, **When** klik delete role, **Then** role terhapus dari sistem

---

### User Story 3 - User Management with Entity Assignment (Priority: P1)

Admin dapat membuat user baru, assign role, dan assign entitas (PT atau LPK) untuk isolasi data sesuai konstitusi.

**Why this priority**: User dengan role dan entitas yang benar adalah prerequisite untuk semua modul berikutnya (CTK, Karyawan, dll).

**Independent Test**: Admin bisa CRUD users, assign role dan entitas, user baru bisa login dengan akses sesuai role-nya.

**Acceptance Scenarios**:

1. **Given** super admin login, **When** membuka halaman Users, **Then** melihat list users (minimal super admin sendiri)
2. **Given** di halaman Users, **When** klik "New User", **Then** form tampil dengan fields: nama, email, password, roles (multi-select), dan entitas (PT/LPK)
3. **Given** form create user, **When** mengisi data lengkap dengan role "Admin LPK" dan entitas "LPK", **Then** user tersimpan dengan role dan entitas yang benar
4. **Given** user baru sudah dibuat, **When** user tersebut login, **Then** berhasil masuk dan hanya melihat menu sesuai role-nya
5. **Given** user sudah ada, **When** admin mengubah role user, **Then** permissions user berubah sesuai role baru
6. **Given** user tidak boleh dihapus, **When** admin soft delete user, **Then** user tidak bisa login tapi data tetap ada (soft delete)

---

### User Story 4 - Permission Enforcement & Access Control (Priority: P2)

Sistem menerapkan permission checks di setiap resource, page, dan widget untuk memastikan users hanya bisa akses fitur sesuai role mereka.

**Why this priority**: Validasi RBAC harus berjalan dengan benar sebelum modul-modul berikutnya dibangun.

**Independent Test**: User dengan role tertentu hanya bisa akses menu/fitur yang di-allow untuk role tersebut, akses lain di-block dengan 403.

**Acceptance Scenarios**:

1. **Given** user role "Instruktur" login, **When** mencoba akses menu Users, **Then** menu tidak tampil di sidebar
2. **Given** user role "Instruktur" login, **When** langsung akses URL `/admin/shield/users` via browser, **Then** mendapat error 403 Forbidden
3. **Given** user role "Pimpinan" login, **When** melihat dashboard, **Then** semua menu tampil (view all access)
4. **Given** user role "Admin LPK" login, **When** melihat sidebar, **Then** hanya menu yang relevan untuk LPK yang tampil

---

### Edge Cases

- Apa yang terjadi jika user mencoba login dengan email yang belum diverifikasi? (Skip email verification di fase ini - langsung aktif)
- Bagaimana jika admin mencoba delete role yang sedang digunakan oleh user? (Prevent delete dengan validasi)
- Bagaimana jika user lupa password? (Gunakan fitur password reset bawaan Laravel/Filament)
- Bagaimana jika user memiliki multiple roles? (Izinkan - shield/spatie support multiple roles per user)
- Bagaimana jika super admin terhapus? (Prevent delete super admin, atau minimal harus ada 1 super admin)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST dapat membuat super admin via Artisan command (`shield:super-admin`)
- **FR-002**: Sistem MUST menyediakan halaman login dengan email dan password authentication
- **FR-003**: Sistem MUST menggunakan Filament Shield plugin untuk RBAC management
- **FR-004**: Sistem MUST menyediakan Role Resource untuk CRUD roles dan assign permissions
- **FR-005**: Sistem MUST menyediakan User Resource untuk CRUD users dengan fields: name, email, password, roles, entity
- **FR-006**: Sistem MUST memiliki 8 predefined roles: Admin LPK, Instruktur, HR PT, Admin PT, Legal PT, Keuangan PT, Keuangan LPK, Pimpinan
- **FR-007**: Sistem MUST enforce permission checks pada semua resources, pages, dan widgets
- **FR-008**: User MUST dapat memiliki multiple roles (Shield/Spatie support)
- **FR-009**: User MUST di-assign ke entity (PT atau LPK) untuk data isolation
- **FR-010**: Sistem MUST menggunakan soft deletes untuk User model
- **FR-011**: Sistem MUST log semua create/update/delete operations pada users dan roles (audit log)
- **FR-012**: Password MUST di-hash menggunakan bcrypt (Laravel default)
- **FR-013**: Sistem MUST redirect user ke dashboard sesuai role setelah login

### Key Entities

- **User**: Represents authenticated users
  - `id` (bigint, PK)
  - `name` (string)
  - `email` (string, unique)
  - `password` (string, hashed)
  - `entity` (enum: 'PT', 'LPK')
  - `email_verified_at` (timestamp, nullable)
  - `remember_token` (string, nullable)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - soft delete)
  - Relationships: `belongsToMany(Role)`, `belongsToMany(Permission)`

- **Role**: Represents user roles (managed by Spatie Permission)
  - `id` (bigint, PK)
  - `name` (string)
  - `guard_name` (string, default: 'web')
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - Relationships: `belongsToMany(Permission)`, `belongsToMany(User)`

- **Permission**: Represents granular permissions (managed by Spatie Permission)
  - `id` (bigint, PK)
  - `name` (string)
  - `guard_name` (string, default: 'web')
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - Relationships: `belongsToMany(Role)`, `belongsToMany(User)`

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Super admin dapat dibuat dalam < 30 detik menggunakan Artisan command
- **SC-002**: User dapat login dan redirect ke dashboard dalam < 3 detik
- **SC-003**: Admin dapat membuat 8 roles dengan permissions dalam < 5 menit
- **SC-004**: 100% resource access diproteksi dengan permission checks (no unauthorized access)
- **SC-005**: User dengan role tertentu TIDAK dapat akses resource yang tidak sesuai permission-nya (0% bypass rate)
- **SC-006**: Soft delete berfungsi - user yang di-delete tidak bisa login tapi data tetap ada di database
- **SC-007**: Audit log mencatat 100% operasi create/update/delete pada users dan roles

## Technical Requirements

### Packages Required

1. **bezhansalleh/filament-shield** (v3.x for Filament v3)
   - Purpose: RBAC management untuk Filament
   - Installation: `composer require bezhansalleh/filament-shield`
   - Setup: `php artisan shield:setup` then `php artisan shield:install admin`

2. **spatie/laravel-permission** (v6.x - dependency of Shield)
   - Purpose: Backend permission system
   - Auto-installed with Shield

### Database Migrations Needed

1. Add `entity` column to `users` table
2. Spatie permissions tables (auto-created by Shield):
   - `roles`
   - `permissions`
   - `model_has_permissions`
   - `model_has_roles`
   - `role_has_permissions`

### Filament Resources Needed

1. **UserResource** (custom - extends Shield)
   - Additional field: `entity` (select/radio PT or LPK)
   - Form: name, email, password, roles (relationship), entity
   - Table: columns for name, email, roles (badge), entity (badge), created_at
   - Filters: by role, by entity
   - Actions: view, edit, delete (soft)

2. **RoleResource** (provided by Shield)
   - Already includes permission management UI
   - Customize if needed

### Configuration

1. Update `User` model:
   - Add `HasRoles` trait from Spatie
   - Add `entity` to fillable
   - Add `entity` cast to enum or string
   - Configure soft deletes

2. Create `EntityEnum` for PT/LPK values

3. Configure Shield:
   - Set auth provider model to `App\Models\User`
   - Enable audit logging if available
   - Configure permission naming conventions

### Seeder Required

Create `RolesAndPermissionsSeeder`:
- Create 8 roles from PRD
- Assign appropriate permissions to each role
- Create sample users for each role (for testing)

## Security Considerations

- **Authentication**: Use Laravel Sanctum (already included in Laravel)
- **Password Policy**: Minimum 8 characters (can be enhanced later)
- **Session Management**: Use database sessions for better security
- **CSRF Protection**: Enabled by default in Laravel
- **XSS Protection**: Filament handles escaping automatically
- **SQL Injection**: Use Eloquent ORM (parameterized queries)
- **Rate Limiting**: Apply to login route (Laravel's built-in rate limiter)

## Non-Functional Requirements

- **Performance**: Login response time < 3 seconds
- **Scalability**: Support up to 1000 concurrent users
- **Availability**: 99.9% uptime
- **Data Retention**: Soft-deleted users retained for 90 days before permanent deletion
- **Audit Trail**: All RBAC changes must be logged with timestamp and actor
- **Browser Support**: Modern browsers (Chrome, Firefox, Safari, Edge - latest 2 versions)

## Out of Scope (Future Enhancements)

- Two-factor authentication (2FA)
- Email verification requirement
- Password expiry policy
- Login history tracking
- IP whitelisting
- OAuth/SSO integration
- Mobile app authentication
- API token management (for API access)

## Dependencies

- Laravel 11.x
- Filament v3.2+
- PHP 8.1+
- MySQL/MariaDB
- Composer
- NPM/Vite (for asset compilation)

## Acceptance Testing Checklist

- [ ] Super admin dapat dibuat via `php artisan shield:super-admin`
- [ ] Super admin dapat login dengan kredensial yang benar
- [ ] Super admin dapat melihat Role Resource dan User Resource
- [ ] Admin dapat create/read/update/delete roles
- [ ] Admin dapat assign permissions ke role
- [ ] Admin dapat create/read/update/delete users
- [ ] Admin dapat assign roles dan entity ke user
- [ ] User baru dapat login dengan kredensial yang dibuat
- [ ] User dengan role "Instruktur" TIDAK bisa akses menu Users
- [ ] User dengan role "Pimpinan" BISA akses semua menu
- [ ] Soft delete user berfungsi (user tidak bisa login, data masih ada)
- [ ] Password di-hash dengan benar (tidak plain text di database)
- [ ] Session logout berfungsi dengan benar
- [ ] 8 roles sesuai PRD sudah di-seed ke database

## Notes

- Gunakan Laravel Boost untuk search dokumentasi Filament Shield saat implementasi
- Ikuti konstitusi: Multi-Entity Isolation (Principle II) - pastikan entity field ada dan ter-enforce
- Ikuti konstitusi: Auditability & Compliance (Principle IV) - log semua operasi RBAC
- Run `vendor/bin/pint` sebelum commit untuk code formatting
- Buat factory dan seeder untuk User model untuk testing
