<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Karyawan LPK Management Feature

The Karyawan LPK Management module provides a comprehensive system for managing employee data specific to LPK (Lembaga Pelatihan Kerja) institutions. This feature includes CRUD operations, honor/compensation management, certificate management, and self-service profile access for instructors.

### Features

**User Story 1: Basic Employee Management (US1)**
- Full CRUD operations for LPK employees
- Employee data includes: personal information (NIK, name, email, birth date, gender, address, phone)
- Employment information (position/jabatan, employment status, join date)
- Entity isolation (automatic assignment to LPK)
- Soft delete with restore capability
- Activity logging for all operations

**User Story 2: Honor/Compensation Management (US2)**
- Record base salary (honor_pokok) and hourly teaching rate (honor_per_jam)
- Hourly rate visible only for Instructor positions
- Filtering by compensation status (with/without honor)
- Role-based access: Keuangan LPK can update honor fields only

**User Story 3: Certificate Management (US3)**
- Upload and store competency certificates (sertifikat kompetensi)
- Private file storage with signed download URLs
- Instructor-specific certificate display
- Download authorization for admin, leadership, and employees (own certificate)

**User Story 4: Self-Service Profile (US4)**
- Instructors can view their own profile
- Edit personal information (address and phone number)
- Download their own certificate
- Email-based access control prevents cross-access

### Role-Based Access Control

- **Admin LPK** (admin_lpk): Full CRUD access, all honor fields, certificate management
- **Finance/Keuangan LPK** (keuangan_lpk): Update honor fields only
- **Leadership/Pimpinan** (pimpinan): View employees, download certificates
- **Instructor/Instruktur** (instruktur): Self-service profile, own certificate download

### Setup Instructions

1. **Database Setup**
   ```bash
   php artisan migrate
   ```

2. **Seed Sample Data**
   ```bash
   php artisan db:seed --class=EmployeeLPKSeeder
   ```

3. **Configure File Storage**
   Ensure `.env` includes:
   ```
   FILESYSTEM_DISK_PRIVATE=private
   ```

4. **Access the Admin Panel**
   - Navigate to `/admin` in your browser
   - Login with admin credentials
   - Access "Data Master > Karyawan LPK" for employee management
   - Instructors access "Akun Saya > Profil Saya" for self-service profile

### Testing

Run the test suite for the Karyawan LPK feature:
```bash
# Honor Management Tests
php artisan test tests/Feature/EmployeeLPKHonorManagementTest.php

# Certificate Management Tests
php artisan test tests/Feature/EmployeeLPKSertifikatManagementTest.php

# All tests
php artisan test
```

### File Structure

```
app/
  Models/
    EmployeeLPK.php              # Employee model with soft deletes & activity logging
  Filament/
    Resources/
      EmployeeLPKResource.php          # Main admin CRUD interface
      EmployeeLPKProfileResource.php   # Self-service profile for employees
  Policies/
    EmployeeLPKPolicy.php        # Authorization policies
  Enums/
    JabatanLPK.php               # Position enum (Instruktur, AdminLPK, Staff)
    StatusKepegawaian.php        # Employment status enum (Aktif, Cuti, Resign)

database/
  migrations/
    2026_01_13_000001_create_karyawan_lpk_table.php
  factories/
    EmployeeLPKFactory.php
  seeders/
    EmployeeLPKSeeder.php

tests/
  Feature/
    EmployeeLPKHonorManagementTest.php     # 12 tests
    EmployeeLPKSertifikatManagementTest.php # 10 tests
```

### For More Information

See [specs/002-karyawan-lpk/](specs/002-karyawan-lpk/) for detailed specifications:
- `spec.md` - Feature requirements and user stories
- `plan.md` - Technical architecture and design decisions
- `quickstart.md` - Step-by-step usage examples
- `data-model.md` - Database schema documentation

