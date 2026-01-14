# Karyawan LPK Feature Quickstart

This guide covers the four main user stories for managing employees (Karyawan) in the Sistem Informasi Terpadu LPK.

## Table of Contents

1. [US1: Kelola Data Karyawan LPK (Employee Management)](#us1-kelola-data-karyawan-lpk)
2. [US2: Kelola Honor Karyawan LPK (Honor Management)](#us2-kelola-honor-karyawan-lpk)
3. [US3: Kelola Sertifikat Instruktur (Certificate Management)](#us3-kelola-sertifikat-instruktur)
4. [US4: Self-Service Profile (Employee Profile Access)](#us4-self-service-profile)

---

## US1: Kelola Data Karyawan LPK

### Overview
Complete CRUD system for managing employee data with role-based access control, soft deletes, and audit logging.

### Access Control
- **super_admin**: Full access
- **admin_lpk**: Full access
- **pimpinan_lpk**: View only

### Features
- **Create Employee**: Register new employee with personal, employment, and compensation data
- **View Employees**: List with filters (status, jabatan, entity)
- **Update Employee**: Modify employee information
- **Delete Employee**: Soft delete (data retained, flagged as deleted)
- **Restore Employee**: Recover soft-deleted employees
- **Force Delete**: Permanent data removal (admin only)

### Form Sections
1. **Informasi Personal** (Personal Information)
   - Nama Lengkap (Full Name)
   - NIK (National ID)
   - Email
   - Tanggal Lahir (Birth Date)

2. **Informasi Kepegawaian** (Employment Information)
   - Jabatan (Position) - Instruktur, Staff
   - Status Kepegawaian (Employment Status) - Tetap, Kontrak, Magang
   - Tanggal Bergabung (Join Date)
   - Entity (LPK)

3. **Kompensasi** (Compensation)
   - Honor Pokok (Base Honor) - Keuangan LPK can edit
   - Honor per Jam Mengajar (Hourly Teaching Honor) - Instruktur only, Keuangan LPK can edit

### Example Workflow: Creating an Employee
```
1. Navigate to Admin > Karyawan LPK > Create
2. Fill in personal information (nama, NIK, email, tanggal lahir)
3. Select jabatan (position) - e.g., "Instruktur"
4. Select status kepegawaian - e.g., "Tetap"
5. Set tanggal bergabung (join date)
6. Fill compensation if applicable
7. Click "Create" to save
```

---

## US2: Kelola Honor Karyawan LPK

### Overview
Dedicated honor management system for employee compensation tracking with role-based field visibility and Keuangan LPK access.

### Access Control
- **keuangan_lpk**: Can edit honor fields (honor_pokok, honor_per_jam)
- **admin_lpk**: Can edit honor fields
- **instruktur**: Can view own honor in self-service profile

### Features
- **Honor Fields** appear only for employees with appropriate roles
- **Field Visibility**:
  - honor_pokok: Visible for all roles, editable by admin_lpk and keuangan_lpk
  - honor_per_jam: Visible for Instruktur only, editable by admin_lpk and keuangan_lpk
- **Filtering**: "Ada Honor" filter to show employees with honor set
- **Validation**: Positive numbers only, no negative or invalid values

### Permission Rules
```
Keuangan LPK:
✓ Can view any employee record
✓ Can edit honor_pokok and honor_per_jam
✗ Cannot create/delete employees
✗ Cannot modify other fields

Admin LPK:
✓ Full access including honor fields

Instruktur:
✓ Can view own honor in self-service profile
✓ Can see honor_per_jam if jabatan is Instruktur
```

### Example Workflow: Setting Honor for an Employee
```
1. Login as Keuangan LPK
2. Navigate to Admin > Karyawan LPK
3. Click on an employee record
4. Scroll to "Kompensasi" section
5. Update honor_pokok (e.g., 3,000,000)
6. If Instruktur, update honor_per_jam (e.g., 50,000)
7. Click "Save" to update
8. Changes are logged to activity log
```

---

## US3: Kelola Sertifikat Instruktur

### Overview
File upload and management system for instructor competency certificates with secure private storage and role-based download access.

### Access Control
- **admin_lpk**: Can upload/download any sertifikat
- **pimpinan_lpk**: Can download any sertifikat
- **instruktur**: Can upload/download own sertifikat only

### Features
- **File Upload**
  - Formats: PDF, JPG, PNG
  - Max Size: 5 MB
  - Storage: Private disk (`storage/app/private/certificates/`)
  - Only visible for Instruktur jabatan

- **File Download**
  - Secure route with authorization check
  - Filename sanitized: `Sertifikat_{NamaLengkap}.pdf`
  - Proper content-type headers

- **UI Integration**
  - Sertifikat section in edit form
  - Icon column in table showing certificate status
  - Download button in self-service profile (for Instruktur)

### Example Workflow: Uploading Sertifikat (Instruktur)
```
1. Login as Instruktur
2. Navigate to Admin > Akun Saya > Edit Profile
3. Scroll to "Sertifikat Kompetensi"
4. Click file upload button
5. Select PDF/JPG/PNG file (≤5MB)
6. System saves to private storage
7. File is now available for download
```

### Example Workflow: Downloading Sertifikat (Admin)
```
1. Login as Admin LPK or Pimpinan
2. Navigate to Admin > Karyawan LPK
3. Click on an employee with sertifikat
4. In form, see sertifikat download link OR
5. Use download route: GET /karyawan-lpk/{employee}/sertifikat/download
6. File downloads with sanitized filename
```

---

## US4: Self-Service Profile

### Overview
Employee-only view of their own profile in read-only mode, with convenient access to personal, employment, compensation data, and certificate downloads.

### Access Control
- **All authenticated users**: Can access own profile only
- **Cross-user access**: Returns 403 Forbidden
- **Query Scoping**: Database query filtered by email

### Features
- **Read-Only Display**
  - All fields disabled for editing
  - Shows personal, employment, compensation info
  - No create/update/delete actions

- **Jabatan-Specific Sections**
  - Personal Info: Always visible
  - Employment Info: Always visible
  - Compensation: Always visible
  - Sertifikat: Only visible for Instruktur jabatan

- **Download Action**
  - "Unduh Sertifikat" button in page header
  - Visible only for Instruktur with uploaded certificate
  - Opens in new tab

### Example Workflow: Viewing Own Profile (Instruktur)
```
1. Login as Instruktur user
2. Navigate to Admin > Akun Saya > Profil Saya (shows own profile)
3. See personal information:
   - Nama Lengkap: [Your Name]
   - NIK: [Your ID]
   - Email: [Your Email]
4. See employment information:
   - Jabatan: Instruktur
   - Status: Tetap
   - Tanggal Bergabung: [Date]
5. See compensation:
   - Honor Pokok: 3,000,000
   - Honor per Jam Mengajar: 50,000
6. See sertifikat section:
   - View uploaded certificate
   - Click "Unduh Sertifikat" to download
7. Cannot edit any fields (all disabled)
```

### Security Features
- **Email-based Scoping**: Prevents access to other users' profiles
- **Authorization Checks**: Both at query level and record level
- **Read-Only Forms**: All fields disabled, no PUT route
- **File Authorization**: Downloads checked via policy

---

## Database Schema

### employees_lpk Table
```sql
id                  BIGINT PRIMARY KEY
nama_lengkap        VARCHAR (Employee Name)
nik                 VARCHAR (National ID)
email               VARCHAR UNIQUE (User Email)
tanggal_lahir       DATE
jabatan             ENUM (Instruktur, Staff)
status              ENUM (Tetap, Kontrak, Magang)
tanggal_bergabung   DATE
entity              VARCHAR (LPK Entity)
honor_pokok         DECIMAL (Base Honor)
honor_per_jam       DECIMAL (Hourly Teaching Honor)
sertifikat_path     VARCHAR NULLABLE (Private Storage Path)
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE (Soft Delete)
```

---

## API Routes

| Method | Route | Purpose | Auth |
|--------|-------|---------|------|
| GET | `/admin/karyawan-lpks` | List all employees | admin_lpk |
| GET | `/admin/karyawan-lpks/{id}` | View employee | admin_lpk, pimpinan |
| GET | `/admin/karyawan-lpks/create` | Create form | admin_lpk |
| POST | `/admin/karyawan-lpks` | Store employee | admin_lpk |
| GET | `/admin/karyawan-lpks/{id}/edit` | Edit form | admin_lpk |
| PUT | `/admin/karyawan-lpks/{id}` | Update employee | admin_lpk, keuangan_lpk |
| DELETE | `/admin/karyawan-lpks/{id}` | Delete employee | admin_lpk |
| GET | `/admin/profil-saya/{id}` | View own profile | Any user |
| GET | `/karyawan-lpk/{id}/sertifikat/download` | Download cert | admin_lpk, pimpinan, instruktur (own) |

---

## Testing

All features have comprehensive test coverage:

```bash
# Run all employee-related tests
php artisan test --filter=EmployeeLPK

# Run specific test file
php artisan test tests/Feature/EmployeeLPKResourceTest.php
php artisan test tests/Feature/EmployeeLPKHonorManagementTest.php
php artisan test tests/Feature/EmployeeLPKSertifikatManagementTest.php
php artisan test tests/Feature/EmployeeLPKSelfServiceProfileTest.php
```

---

## Troubleshooting

### Certificate not uploading
- Ensure file size < 5MB
- Verify format is PDF, JPG, or PNG
- Check `storage/app/private/` directory exists

### Cannot edit honor fields
- Verify you have keuangan_lpk role
- Check employee record exists
- Ensure employee is not soft-deleted

### Cannot download certificate
- Verify you have download authorization via policy
- Check certificate file exists in private storage
- Ensure file path is correctly stored in database

### Profile shows 403 error
- Verify you're logged in
- Confirm employee email matches your user email
- Check soft-delete status (use trashed filter)

---

## Related Documentation

- [US1 Implementation Details](../app/Filament/Resources/EmployeeLPKResource.php)
- [US2 Honor Logic](../app/Policies/EmployeeLPKPolicy.php)
- [US3 Certificate Download](../app/Http/Controllers/EmployeeSertifikatController.php)
- [US4 Profile Resource](../app/Filament/Resources/EmployeeLPKProfileResource.php)
- [Database Migrations](../database/migrations/)
- [Test Suites](../tests/Feature/)
