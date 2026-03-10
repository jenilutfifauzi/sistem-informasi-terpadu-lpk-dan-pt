# Implementation Plan: Karyawan PT Management

**Branch**: `009-karyawan-pt-resource` | **Date**: 2026-03-09 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/009-karyawan-pt-resource/spec.md`

## Summary

Implementasi sistem manajemen karyawan PT (PJTKI) dengan CRUD lengkap untuk staf internal: Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, dan Staf Administrasi. Fitur ini menyediakan:
- Data master karyawan PT (personal info, jabatan, divisi, jenis kontrak, status kepegawaian)
- Manajemen gaji dan tunjangan (gaji pokok + tunjangan, toggleable di tabel)
- Upload dokumen kepegawaian (kontrak, SK, KTP — PDF/JPG/PNG, private storage)
- Role-based access untuk Admin PT, Keuangan PT, Pimpinan, dan super_admin
- Soft delete untuk data retention + audit logging via spatie/activitylog
- Entity isolation otomatis (entity='PT')
- Export CSV dengan filter aktif

Technical approach: Mirror pola `EmployeeLPKResource` (spec 002) dengan model baru `EmployeePT` pada tabel `karyawan_pt`, dua enum baru (`JabatanPT`, `DivisiPT`, `JenisKontrak`), kebijakan RBAC via `EmployeePTPolicy`, dan export via `EmployeePTExport`. Tidak ada perubahan pada resource LPK.

## Technical Context

**Language/Version**: PHP 8.4.5
**Framework**: Laravel 11.x (menggunakan struktur Laravel 10 yang ada)
**Primary Dependencies**:
- filament/filament v4.x (admin panel UI, forms, tables, infolist, file upload)
- spatie/laravel-permission v6.x (RBAC — sudah terinstall)
- spatie/laravel-activitylog v4.x (audit logging — sudah terinstall, digunakan EmployeeLPK)
- livewire/livewire v3.x (reactive components untuk conditional fields)
- maatwebsite/excel (export CSV — sudah terinstall, digunakan EmployeeLPKExport)

**Storage**: MySQL/MariaDB (tabel `karyawan_pt` baru, terpisah dari `karyawan_lpk`)
**File Storage**: Private disk (`storage/app/private/documents/`) untuk dokumen kepegawaian
**Testing**: PHPUnit v10 (Feature tests untuk CRUD, authorization, validasi)
**Target Platform**: Web application (server-side rendering via Livewire/Filament)
**Project Type**: Web (Laravel monolith dengan Filament admin panel)

**Performance Goals**:
- Table rendering <2 detik untuk 500 records (pagination 25 per page)
- Form submission <3 detik (termasuk file upload 5MB)

**Constraints**:
- HARUS menggunakan struktur Laravel 10 (models di `app/Models/`, middleware di `app/Http/Middleware/`)
- HARUS menggunakan Filament v4 conventions (`Schemas\Components` untuk layouts, bukan `Filament\Forms\Components\Grid`)
- Field `entity` HARUS immutable dan default ke `'PT'`
- Field `nik` HARUS read-only setelah data disimpan (unique identifier)
- File dokumen HARUS private (tidak dapat diakses publik)
- Soft deletes WAJIB (sesuai constitution + spec FR-008)
- Gunakan `BadgeColumn` untuk kolom jabatan dan status (sesuai pola LPK)
- Navigasi grup HARUS `'Data Master'` (sama dengan karyawan LPK)

**Scale/Scope**:
- Estimasi awal: ~15-25 karyawan PT (staf internal PJTKI)
- Target: hingga 200 karyawan PT
- 6 jabatan (Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, Staf Administrasi)
- 5 divisi (Manajemen, HRD, Keuangan, Operasional, Administrasi)
- 3 jenis kontrak (Tetap, PKWT, Probasi)
- 3 status kepegawaian (Aktif, Cuti, Resign) — pakai StatusKepegawaian enum yang ada

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### ✅ Principle I: Data Integrity & Single Source of Truth
- **Status**: PASS
- **Validation**: NIK dan email sebagai unique identifiers dengan database constraints. Tidak ada duplikasi record (FR-002). `EmployeePT` menjadi canonical source untuk data karyawan PT.
- **Implementation**:
  - Migration: unique index pada `email` dan `nik` di tabel `karyawan_pt`
  - Form Request validation: `unique:karyawan_pt,email` + `unique:karyawan_pt,nik`
  - Database constraint enforcement prevents duplicates at DB level

### ✅ Principle II: Multi-Entity Isolation
- **Status**: PASS
- **Validation**: Setiap karyawan PT HARUS memiliki `entity='PT'` (FR-007). Field entity immutable dan auto-assigned. Tabel `karyawan_pt` terpisah dari `karyawan_lpk`.
- **Implementation**:
  - Migration: `entity ENUM('PT','LPK') DEFAULT 'PT' NOT NULL`
  - Model: `boot()` method auto-sets `entity='PT'` pada creating event
  - Resource: entity field disembunyikan di form (tidak editable oleh user)
  - Policy: `EmployeePTPolicy::viewAny()` scope data ke `entity='PT'`

### ✅ Principle III: Role-Based Access Control & Least Privilege
- **Status**: PASS
- **Validation**: 4 roles dengan explicit permissions: Admin PT (full CRUD), Keuangan PT (view + edit kompensasi), Pimpinan (view + download dokumen), super_admin (full access). Admin LPK dan Keuangan LPK TIDAK mendapat akses.
- **Implementation**:
  - Shield policies auto-generated untuk `EmployeePTResource`
  - Resource visibility via `canViewAny()` checks in panel navigation
  - Kolom gaji tersembunyi default (toggleable) — Keuangan PT dapat melihat via toggle

### ✅ Principle IV: Auditability & Compliance
- **Status**: PASS
- **Validation**: Audit log untuk semua CRUD operations (FR-014). Soft delete untuk data retention (FR-008). File dokumen private storage (FR-010).
- **Implementation**:
  - Model menggunakan `SoftDeletes` trait
  - Model menggunakan `LogsActivity` trait (spatie/activitylog v4) — pola identik dengan EmployeeLPK
  - File storage: `disk='private'`, path=`documents/{nik}_{timestamp}.{ext}`
  - Download authorization via Policy gate check sebelum serve file

### ✅ Principle V: Incremental Delivery & Simplicity
- **Status**: PASS
- **Validation**: 3 user stories (P1-P3) dapat di-deliver secara incremental. P1 (basic CRUD) adalah MVP yang berdiri sendiri.
- **Implementation**:
  - P1: `EmployeePTResource` dengan CRUD dasar (nama, NIK, jabatan, divisi, status, dates) — standalone deliverable
  - P2: Tambahkan field gaji/tunjangan ke form existing — non-breaking addition
  - P3: Tambahkan field upload dokumen kepegawaian — non-breaking addition
  - Tests ditulis per user story untuk validasi independen

### Summary: All Constitution Checks PASS ✅
Tidak ada violations. Tidak diperlukan complexity justification. Fitur selaras dengan semua 5 core principles. Lanjut ke Phase 0 research.

**Post-Design Re-check** (setelah Phase 1 data-model.md selesai):
- [✅] **VERIFIED**: Entity column implementation → DB default='PT' + Model boot() + Form default (defense-in-depth)
- [✅] **VERIFIED**: Audit logging approach → spatie/laravel-activitylog v4.x (`LogsActivity` trait, pola identik dengan EmployeeLPK)
- [✅] **VERIFIED**: File upload authorization → Policy-based gate check sebelum serve private file
- [✅] **VERIFIED**: Soft delete behavior → `TrashedFilter::make()` di resource, status='Resign' saat delete

**All Post-Design Checks PASS** ✅ | **Date**: 2026-03-09

## Project Structure

### Documentation (this feature)

```text
specs/009-karyawan-pt-resource/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (N/A - Filament only, no API endpoints)
│   └── .gitkeep
├── checklists/
│   └── requirements.md  # Validation checklist (already created)
└── spec.md              # Feature specification
```

### Source Code (repository root)

Laravel monolith, mengikuti struktur Laravel 10 yang ada (middleware di `app/Http/Middleware/`):

```text
app/
├── Models/
│   └── EmployeePT.php                          # [CREATE] Model dengan SoftDeletes, LogsActivity, HasFactory
├── Enums/
│   ├── JabatanPT.php                           # [CREATE] Enum: Direktur, Manajer, StafHRD, StafKeuangan, StafOperasional, StafAdministrasi
│   ├── DivisiPT.php                            # [CREATE] Enum: Manajemen, HRD, Keuangan, Operasional, Administrasi
│   └── JenisKontrak.php                        # [CREATE] Enum: Tetap, PKWT, Probasi
├── Filament/
│   ├── Resources/
│   │   └── EmployeePTResource/
│   │       ├── EmployeePTResource.php          # [CREATE] Main resource: form, table, infolist, filters
│   │       └── Pages/
│   │           ├── ListEmployeesPT.php         # [CREATE] Table view
│   │           ├── CreateEmployeePT.php        # [CREATE] Create form
│   │           ├── EditEmployeePT.php          # [CREATE] Edit form
│   │           └── ViewEmployeePT.php          # [CREATE] Detail infolist
│   └── Exports/
│       └── EmployeePTExport.php                # [CREATE] Maatwebsite export CSV
├── Http/
│   └── Requests/
│       ├── StoreEmployeePTRequest.php          # [CREATE] Validation rules untuk create
│       └── UpdateEmployeePTRequest.php         # [CREATE] Validation rules untuk update
└── Policies/
    └── EmployeePTPolicy.php                    # [CREATE] RBAC: viewAny, create, update, delete per role

database/
├── factories/
│   └── EmployeePTFactory.php                   # [CREATE] Factory untuk testing + seeding
├── migrations/
│   └── 2026_03_09_000001_create_karyawan_pt_table.php  # [CREATE] Schema lengkap
└── seeders/
    └── EmployeePTSeeder.php                    # [CREATE] 5-10 sample karyawan PT

tests/
└── Feature/
    ├── EmployeePTResourceTest.php              # [CREATE] CRUD operations + infolist
    ├── EmployeePTAuthorizationTest.php         # [CREATE] RBAC tests (Admin PT vs Keuangan PT vs Admin LPK)
    ├── EmployeePTValidationTest.php            # [CREATE] Unique NIK/email, format validasi
    ├── EmployeePTKompensasiTest.php            # [CREATE] Gaji/tunjangan field tests
    ├── EmployeePTDocumentTest.php              # [CREATE] File upload dokumen kepegawaian
    └── Exports/
        └── EmployeePTExportTest.php            # [CREATE] Export CSV test
```

**Structure Decision**: Single web project, Laravel monolith. Struktur mengikuti pola `EmployeeLPKResource` yang sudah ada secara konsisten. File upload menggunakan private disk yang sudah dikonfigurasi (sama dengan sertifikat LPK).

## Complexity Tracking

> No complexity justification needed. Constitution Check passed all gates.

## Phases

### Phase 0: Outline & Research

**Status**: ✅ **COMPLETE** | **Date**: 2026-03-09
**Output**: [research.md](research.md) — lihat detail di file tersebut

**Semua keputusan teknis telah resolved berdasarkan implementasi EmployeeLPK yang sudah ada:**

1. **Enum Patterns** — ✅ RESOLVED
   - **Decision**: PHP 8.1 backed string enums implementing `HasLabel` (identik dengan `JabatanLPK`, `StatusKepegawaian`)

2. **Export CSV** — ✅ RESOLVED
   - **Decision**: Maatwebsite\Excel, pola identik dengan `EmployeeLPKExport`

3. **Audit Logging** — ✅ RESOLVED
   - **Decision**: `spatie/laravel-activitylog` v4.x via `LogsActivity` trait, pola identik dengan `EmployeeLPK`

4. **File Upload (Dokumen Kepegawaian)** — ✅ RESOLVED
   - **Decision**: `FileUpload` dengan `disk('private')`, acceptedFileTypes PDF/JPG/PNG, max 5MB
   - Private storage dengan download via authorized route (pola identik dengan sertifikat LPK)

5. **Soft Delete Filter** — ✅ RESOLVED
   - **Decision**: `TrashedFilter::make()` bawaan Filament (identik dengan EmployeeLPKResource)

6. **Entity Auto-Assignment** — ✅ RESOLVED
   - **Decision**: Three-layer defense: DB default + Model `boot()` + Form hidden field
   - Ganti `LPK` → `PT`, `EntityType::PT`

7. **RBAC Scoping** — ✅ RESOLVED
   - **Decision**: `EmployeePTPolicy` dengan roles: `super_admin` (full), `Admin PT` (full CRUD), `Keuangan PT` (view + edit kompensasi), `Pimpinan` (view + downloadDokumen)
   - Admin LPK / Keuangan LPK tidak mendapat akses ke resource ini

8. **Jabatan PT Enum Values** — ✅ RESOLVED
   - **Decision**: Direktur, Manajer, StafHRD (label: "Staf HRD"), StafKeuangan, StafOperasional, StafAdministrasi
   - Mengikuti struktur umum PJTKI sesuai PRD section 6.2

9. **Nama Field Kompensasi** — ✅ RESOLVED
   - **Decision**: `gaji_pokok` + `tunjangan` (bukan `honor_pokok`/`honor_per_jam` yang spesifik untuk LPK)

10. **Navigasi Resource** — ✅ RESOLVED
    - **Decision**: Grup `'Data Master'`, sort setelah Karyawan LPK (`navigationSort = 2`), slug `karyawan-pts`

### Phase 1: Design & Contracts

**Status**: ✅ **COMPLETE** | **Date**: 2026-03-09
**Outputs**: [data-model.md](data-model.md), [contracts/](contracts/), [quickstart.md](quickstart.md)

Lihat file masing-masing untuk detail lengkap.
