# PRODUCT REQUIREMENT DOCUMENT (PRD)

## Sistem Informasi Terpadu PT PJTKI & LPK
**Platform:** Web Application  
**Tech Stack:** Laravel 10+, Filament Admin Panel, MySQL/MariaDB, Redis (opsional)

---

## 1. Latar Belakang
PT PJTKI dan LPK membutuhkan satu sistem terintegrasi untuk mengelola **data karyawan** dan **data CTK**, namun tetap memisahkan data berdasarkan **entitas hukum** (PT dan LPK) sesuai regulasi.

Masalah utama saat ini:
- Data ganda
- Sulit audit
- Akses tidak terkontrol
- Proses CTK tidak end-to-end

---

## 2. Tujuan Produk
- Menyediakan **single source of truth** untuk data CTK
- Memisahkan data karyawan PT dan LPK dalam satu sistem
- Mendukung audit, pelaporan, dan kepatuhan regulasi
- Menyediakan dashboard berbasis role menggunakan **Filament**

---

## 3. Ruang Lingkup Sistem

### Termasuk
- Manajemen Karyawan PT
- Manajemen Karyawan LPK
- Manajemen CTK (LPK → PT)
- Manajemen Role & Permission
- Dashboard berbasis role

### Tidak Termasuk (fase ini)
- Mobile App
- Integrasi BPJS / Dukcapil
- Payroll otomatis ke bank

---

## 4. Role & Hak Akses

| Role | Akses Utama |
|---|---|
| Admin LPK | CTK LPK, Pelatihan |
| Instruktur | Absensi, Nilai |
| HR PT | Karyawan PT |
| Admin PT | CTK PT, Penempatan |
| Legal PT | Dokumen CTK |
| Keuangan PT | Gaji PT |
| Keuangan LPK | Honor LPK |
| Pimpinan | View All |

---

## 5. Arsitektur Filament (High Level)

- **Single Filament Panel**
- **Multi-Entity Context (PT / LPK)**
- **Role-Based Navigation & Resource Visibility**

---

## 6. Modul & Fitur

### 6.1 Modul Manajemen User

**Fitur:**
- CRUD User
- Assign Role
- Assign Entitas (PT / LPK)

**Filament Resource:**
- UserResource

**Input Data:**
- Nama
- Email
- Password
- Role
- Entitas

---

### 6.2 Modul Karyawan PT

**Aktor:** HR PT, Admin PT

**Fitur:**
- CRUD Karyawan PT
- Jabatan & Divisi
- Kontrak Kerja
- Gaji & Tunjangan
- Dokumen Kepegawaian

**Filament Resource:**
- EmployeePTResource

**Input Data:**
- Data personal
- Jabatan
- Status kerja
- Gaji
- Dokumen

---

### 6.3 Modul Karyawan LPK

**Aktor:** HR LPK, Admin LPK

**Fitur:**
- CRUD Karyawan LPK
- Instruktur & Staff
- Honor
- Jadwal Mengajar
- Sertifikat

**Filament Resource:**
- EmployeeLPKResource

---

### 6.4 Modul CTK (Core Module)

**Konsep:** Single Source of Truth

**Filament Resource:**
- CTKResource

#### Tahap Status CTK
1. Pendaftaran
2. Pelatihan
3. Lulus LPK / Tidak Lulus
4. Siap Penempatan
5. Medical
6. Dokumen
7. Job Order
8. Siap Berangkat
9. Terbang / Batal

---

### 6.5 Modul Pelatihan (LPK)

**Aktor:** Instruktur, Admin LPK

**Fitur:**
- Absensi CTK
- Nilai
- Catatan Instruktur
- Sertifikat

**Filament Resource:**
- TrainingResource

---

### 6.6 Modul Penempatan (PT)

**Aktor:** Operasional PT, Legal PT

**Fitur:**
- Medical Checkup
- Paspor & Visa
- Interview User
- Asuransi

**Filament Resource:**
- PlacementResource

---

## 7. Dashboard

### 7.1 Dashboard Admin LPK
- Statistik CTK
- Jadwal Pelatihan
- CTK Lulus

### 7.2 Dashboard Admin / HR PT
- Karyawan PT
- CTK Siap Penempatan

### 7.3 Dashboard Instruktur
- Jadwal Mengajar
- Absensi & Nilai

### 7.4 Dashboard Keuangan
- Gaji / Honor

### 7.5 Dashboard Pimpinan
- Statistik Global
- Laporan Gabungan

---

## 8. Non-Functional Requirement

- Role-based access control
- Data isolation per entitas
- Audit log
- Soft delete
- Export PDF / Excel

---

## 9. Keamanan

- Permission via Filament Shield / Spatie
- Data CTK terkunci saat tahap akhir
- Tidak ada edit lintas entitas

---

## 10. Future Enhancement

- Mobile App
- API Integration
- Workflow Approval
- Multi PT / Multi LPK

---

## 11. Success Metric

- Tidak ada data ganda
- Audit mudah
- Proses CTK end-to-end
- User adoption > 90%

---

**Dokumen ini menjadi acuan pengembangan Laravel + Filament.**

