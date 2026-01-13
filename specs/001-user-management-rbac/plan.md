# Implementation Plan: User Management & RBAC Foundation

**Branch**: `001-user-management-rbac` | **Date**: 2026-01-13 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-user-management-rbac/spec.md`

## Summary

Implementasi sistem autentikasi dan Role-Based Access Control (RBAC) menggunakan Filament Shield dan Spatie Laravel Permission. Fitur ini menyediakan foundation untuk:
- User authentication dengan 8 predefined roles (Admin LPK, Instruktur, HR PT, Admin PT, Legal PT, Keuangan PT, Keuangan LPK, Pimpinan)
- Multi-entity isolation (PT vs LPK) untuk data segregation
- Permission management untuk resources, pages, dan widgets
- Audit logging untuk semua operasi RBAC
- Soft deletes untuk user retention

Technical approach: Menggunakan Filament Shield v3.x (built on Spatie Permission v6.x) untuk menyediakan UI dan backend RBAC. Custom UserResource dengan tambahan field `entity` (enum: PT/LPK) untuk data isolation sesuai constitution principle II.

## Technical Context

**Language/Version**: PHP 8.4.5  
**Framework**: Laravel 11.x  
**Primary Dependencies**: 
- filament/filament v4.x (admin panel)
- bezhansalleh/filament-shield v3.x (RBAC management)
- spatie/laravel-permission v6.x (backend permissions - auto-installed with Shield)
- livewire/livewire v3.x (reactive components)

**Storage**: MySQL/MariaDB (existing setup)  
**Testing**: PHPUnit v10 (Laravel default - Feature tests required per constitution)  
**Target Platform**: Web application (server-side rendering via Livewire)  
**Project Type**: Web (Laravel monolith with Filament admin panel)  

**Performance Goals**: 
- Login response time < 3 seconds
- Permission check overhead < 50ms per request
- Support 1000 concurrent authenticated users

**Constraints**: 
- MUST use Laravel 10 structure (middleware in app/Http/Middleware/, not bootstrap/app.php)
- NO email verification requirement (fase 1 - users immediately active)
- NO 2FA/OAuth (future enhancement)
- Soft deletes MUST be implemented (90-day retention per spec)

**Scale/Scope**: 
- Initial: ~50-100 users across PT and LPK
- Growth target: up to 1000 users
- 8 predefined roles
- Estimated 30-50 permissions (resources, pages, widgets)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### ✅ Principle I: Data Integrity & Single Source of Truth
- **Status**: PASS
- **Validation**: User model dengan email uniqueness constraint mencegah duplikasi. Spatie permission tables menjadi single source untuk role/permission data.
- **Implementation**: Migration adds unique index on users.email, Shield manages canonical permission records.

### ✅ Principle II: Multi-Entity Isolation
- **Status**: PASS
- **Validation**: Field `entity` (enum: PT/LPK) ditambahkan ke users table. Setiap user MUST di-assign ke satu entitas.
- **Implementation**: Migration adds entity column, UserResource form includes entity field (required), future queries will scope by entity.
- **Note**: Tenancy NOT implemented in phase 1 (foundation only), future features will enforce entity-based data access.

### ✅ Principle III: Role-Based Access Control & Least Privilege
- **Status**: PASS  
- **Validation**: 8 roles dari PRD akan di-seed dengan explicit permissions. Shield enforces permission checks via policies and middleware.
- **Implementation**: RolesAndPermissionsSeeder creates roles with minimal required permissions. Shield policies auto-generated for resources.

### ✅ Principle IV: Auditability & Compliance
- **Status**: PASS
- **Validation**: Audit logging untuk user/role operations (FR-011). Soft deletes implemented untuk user retention.
- **Implementation**: 
  - Laravel's model events (creating, updating, deleting) will trigger audit logs
  - Custom AuditLog model or use spatie/laravel-activitylog (to be decided in research phase)
  - Soft deletes via Illuminate\Database\Eloquent\SoftDeletes trait

### ✅ Principle V: Incremental Delivery & Simplicity
- **Status**: PASS
- **Validation**: User stories P1-P2 dapat di-deliver secara incremental. Menggunakan Shield plugin (simplest effective design) vs custom RBAC.
- **Implementation**: 
  - P1 stories (super admin, roles, users) are MVP foundation
  - P2 story (permission enforcement) validates the system
  - Tests included in acceptance criteria

### Summary: All Constitution Checks PASS ✅
No violations. No complexity justification needed. Proceed to Phase 0 research.

## Project Structure

### Documentation (this feature)

```text
specs/001-user-management-rbac/
├── plan.md              # This file
├── research.md          # Phase 0: Shield setup, audit logging approach
├── data-model.md        # Phase 1: User, Role, Permission schemas
├── quickstart.md        # Phase 1: How to create users, assign roles
├── contracts/           # Phase 1: N/A (no API endpoints in phase 1)
│   └── .gitkeep        
├── checklists/
│   └── requirements.md  # Validation checklist (already created)
└── spec.md              # Feature specification (already created)
```

### Source Code (repository root)

This is a Laravel monolith following Laravel 10 structure:

```text
app/
├── Models/
│   ├── User.php                    # [MODIFY] Add entity field, HasRoles trait, soft deletes
│   └── AuditLog.php                # [CREATE] Audit trail model (if using custom solution)
├── Filament/
│   └── Resources/
│       ├── UserResource.php        # [CREATE] Custom user management with entity field
│       │   ├── Pages/
│       │   │   ├── ListUsers.php
│       │   │   ├── CreateUser.php
│       │   │   ├── EditUser.php
│       │   │   └── ViewUser.php
│       │   └── RelationManagers/   # [OPTIONAL] For user roles
│       └── Shield/                 # [AUTO-GENERATED by Shield]
│           └── RoleResource.php    # Shield's default role resource
├── Enums/
│   └── EntityType.php              # [CREATE] Enum for PT/LPK entity types
├── Policies/
│   ├── UserPolicy.php              # [CREATE] Authorization for UserResource
│   └── RolePolicy.php              # [AUTO-GENERATED by Shield]
└── Providers/
    └── AppServiceProvider.php      # [MODIFY] Register policies if needed

database/
├── factories/
│   └── UserFactory.php             # [MODIFY] Add entity field to factory
├── migrations/
│   ├── 2014_10_12_000000_create_users_table.php                    # [EXISTING]
│   ├── 2026_01_13_000001_add_entity_to_users_table.php             # [CREATE]
│   ├── [timestamp]_create_permission_tables.php                     # [AUTO] Spatie/Shield
│   └── [timestamp]_create_audit_logs_table.php                      # [CREATE] If custom audit
└── seeders/
    ├── DatabaseSeeder.php                      # [MODIFY] Call RolesAndPermissionsSeeder
    └── RolesAndPermissionsSeeder.php           # [CREATE] 8 roles + permissions

tests/
└── Feature/
    ├── Auth/
    │   ├── LoginTest.php                       # [CREATE] Test authentication flow
    │   └── PermissionEnforcementTest.php       # [CREATE] Test RBAC enforcement
    ├── Filament/
    │   ├── UserResourceTest.php                # [CREATE] Test user CRUD
    │   └── RoleResourceTest.php                # [CREATE] Test role CRUD
    └── Seeders/
        └── RolesAndPermissionsSeederTest.php   # [CREATE] Validate seeder output

config/
├── filament-shield.php             # [AUTO] Shield config (published)
└── permission.php                  # [AUTO] Spatie config (published)

resources/
└── views/
    └── filament/                   # [OPTIONAL] Custom Shield views if needed
        └── resources/
            └── user-resource/
```

**Structure Decision**: 
Using Laravel 10 structure (NOT Laravel 11 streamlined structure - per constitution operational constraints). Filament resources live in `app/Filament/Resources/`, policies in `app/Policies/`, middleware in `app/Http/Middleware/`. This maintains consistency with existing codebase structure.

Shield will auto-generate its Role and Permission resources. We will create custom UserResource to add the `entity` field requirement.

## Complexity Tracking

**No violations detected.** All design decisions align with constitution principles:

- Using Shield plugin = Simplicity (Principle V) - no custom RBAC implementation
- Entity field on User model = Multi-Entity Isolation (Principle II) - direct implementation
- Soft deletes = Auditability (Principle IV) - Laravel standard feature
- Spatie permissions = Industry standard, well-tested, documented

✅ **No complexity justification required.**

## Phase 0: Research & Decisions

### Research Topics

1. **Audit Logging Approach**
   - Decision needed: Custom AuditLog model vs spatie/laravel-activitylog package
   - Criteria: Simplicity, constitution compliance (Principle IV), future extensibility
   - Output: Decision documented in research.md

2. **Shield Setup & Configuration**
   - Research Shield v3.x setup for Laravel 11
   - Understand permission naming conventions
   - Configure Shield for non-tenant setup (tenancy is future enhancement)
   - Output: Setup steps documented in research.md

3. **Entity Isolation Strategy**
   - Decision: Enum vs string for entity field
   - Future consideration: How to scope queries by entity (preparation for future features)
   - Output: Entity field implementation approach in research.md

4. **User Seeder Requirements**
   - Define sample users for each of 8 roles (for testing)
   - Define base permissions per role
   - Output: Seeder structure in research.md

### Research Deliverables

File: `specs/001-user-management-rbac/research.md`

Expected sections:
- **Audit Logging**: Decision between custom vs spatie/laravel-activitylog
- **Shield Configuration**: Setup steps, config values, panel registration
- **Entity Field Implementation**: Enum class structure, validation rules
- **Role Permissions Matrix**: Table mapping 8 roles to initial permissions
- **Testing Strategy**: Feature test structure for RBAC validation

## Phase 1: Design & Contracts

### Data Model

File: `specs/001-user-management-rbac/data-model.md`

**Entities:**
1. **User** (modify existing)
   - Add: entity (enum/string), deleted_at (soft delete)
   - Relationships: belongsToMany(Role), belongsToMany(Permission)
   
2. **Role** (Spatie - managed by Shield)
   - 8 predefined roles from seeder
   
3. **Permission** (Spatie - managed by Shield)
   - Auto-generated by Shield for resources
   
4. **AuditLog** (custom or package)
   - If custom: user_id, action, model_type, model_id, changes, ip_address, user_agent, created_at

### API Contracts

**N/A for Phase 1** - This is admin panel only, no API endpoints needed.

Future: If API access needed, will use Laravel Sanctum (already in project).

### Quickstart Guide

File: `specs/001-user-management-rbac/quickstart.md`

**Contents:**
- How to create super admin: `php artisan shield:super-admin`
- How to seed roles: `php artisan db:seed --class=RolesAndPermissionsSeeder`
- How to create user via Filament UI
- How to assign roles to user
- How to login and verify permissions
- How to check user's entity assignment
- How to run tests: `php artisan test --filter=UserResource`

## Phase 2: Implementation Tasks

**Note:** Detailed task breakdown will be created using `/speckit.tasks` command after Phase 0 and Phase 1 are complete.

### High-Level Task Groups

**Foundation Setup (Blocking)**
- Install Shield plugin
- Run Shield setup command
- Publish and configure Shield
- Create entity enum

**Database Schema**
- Migration: add entity to users table
- Migration: create audit_logs table (if custom)
- Update UserFactory with entity field

**Models & Seeders**
- Update User model (HasRoles trait, entity field, soft deletes)
- Create AuditLog model (if custom)
- Create EntityType enum
- Create RolesAndPermissionsSeeder (8 roles)

**Filament Resources**
- Create UserResource with entity field
- Customize Shield RoleResource if needed
- Generate policies for UserResource

**Testing**
- Feature test: Super admin creation
- Feature test: User CRUD operations
- Feature test: Role assignment
- Feature test: Permission enforcement
- Feature test: Entity field validation

**Documentation & Cleanup**
- Run vendor/bin/pint for code style
- Update quickstart.md with actual steps
- Verify all constitution checks still pass

## Dependencies & Execution Order

### External Dependencies
- bezhansalleh/filament-shield (Composer package) - MUST be installed first
- Decision from research phase on audit logging approach

### Execution Phases
1. **Phase 0 Research** (must complete first)
   - Research audit logging
   - Research Shield configuration
   - Document decisions in research.md

2. **Phase 1 Design** (depends on Phase 0)
   - Create data-model.md based on research decisions
   - Create quickstart.md skeleton

3. **Phase 2 Implementation** (depends on Phase 1)
   - Install dependencies
   - Run migrations
   - Create seeders
   - Implement resources
   - Write tests
   - Verify constitution compliance

### Task Dependencies Within Implementation
- Shield installation BLOCKS all other tasks
- Migrations BLOCK model modifications
- Models BLOCK resource creation
- Resources BLOCK testing
- All features BLOCK final validation

## Validation & Acceptance

### Pre-Merge Checklist
- [ ] All constitution checks still PASS
- [ ] Super admin can be created via command
- [ ] 8 roles seeded successfully
- [ ] UserResource includes entity field (required)
- [ ] User can login and see role-appropriate menus
- [ ] Permission enforcement blocks unauthorized access
- [ ] Soft delete works for users
- [ ] Audit logs capture user/role changes
- [ ] All feature tests pass
- [ ] Code formatted with vendor/bin/pint
- [ ] Quickstart.md validated against actual implementation

### Success Metrics (from spec.md)
- [ ] SC-001: Super admin created in < 30 seconds
- [ ] SC-002: Login response time < 3 seconds
- [ ] SC-003: 8 roles with permissions created in < 5 minutes
- [ ] SC-004: 100% resource access protected
- [ ] SC-005: 0% unauthorized access bypass rate
- [ ] SC-006: Soft delete functional (data retained)
- [ ] SC-007: 100% RBAC operations logged

## Notes

### Technology Decisions
- **Shield vs Custom RBAC**: Shield chosen for simplicity (Principle V)
- **Entity Field Type**: To be decided in research phase (Enum vs String)
- **Audit Logging**: To be decided in research phase (Custom vs Package)

### Risk Mitigation
- Shield is well-maintained and documented - low risk
- Laravel 10 structure compatibility verified
- Constitution compliance validated upfront

### Future Considerations
- Multi-tenancy (future enhancement) - Shield supports it
- API access (future) - will use Sanctum
- 2FA (future) - Shield has ecosystem plugins for this
- Advanced audit (future) - can enhance audit logging incrementally
