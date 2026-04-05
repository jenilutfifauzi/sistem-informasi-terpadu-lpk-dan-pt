# Feature Specification: Pembayaran ke Pusat (CTK Payment to Central)

**Feature Branch**: `001-ctk-pembayaran-pusat`  
**Created**: 2026-04-05  
**Status**: Draft  
**Input**: User description: "PEMBAYARAN KE PUSAT - Per CTK (UserId, TGL, JUMLAH/NOMINAL, UPLOAD BUKTI TF) posisi di bawah ASET"

## Overview

Fitur ini memungkinkan pencatatan dan pelacakan pembayaran dari LPK/PT ke Pusat (kantor pusat) untuk setiap CTK (Calon Tenaga Kerja). Setiap pembayaran mencatat siapa yang membuat entri, tanggal pembayaran, nominal yang dibayarkan, dan bukti transfer. Menu ini ditempatkan di bawah menu ASET pada navigasi.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Mencatat Pembayaran ke Pusat (Priority: P1)

Sebagai admin LPK/PT, saya ingin mencatat pembayaran yang dilakukan ke pusat untuk setiap CTK, agar ada bukti dan rekam jejak pembayaran yang jelas.

**Why this priority**: Ini adalah fungsi utama fitur - tanpa kemampuan mencatat pembayaran, fitur ini tidak memiliki nilai.

**Independent Test**: Dapat diuji dengan membuat entri pembayaran baru untuk CTK tertentu, mengisi semua field yang diperlukan (tanggal, nominal, bukti transfer), dan memverifikasi data tersimpan dengan benar.

**Acceptance Scenarios**:

1. **Given** admin sudah login dan berada di halaman Pembayaran ke Pusat, **When** admin mengklik tombol "Buat Baru" dan mengisi form dengan CTK, tanggal, nominal, dan upload bukti transfer, **Then** pembayaran tersimpan dengan status pending dan user yang membuat tercatat otomatis.

2. **Given** admin sedang membuat pembayaran baru, **When** admin tidak memilih CTK atau tidak mengisi nominal, **Then** sistem menampilkan pesan error validasi dan tidak menyimpan data.

3. **Given** admin upload bukti transfer, **When** file yang diupload bukan format gambar (JPG/PNG) atau PDF, **Then** sistem menolak file dan menampilkan pesan format tidak didukung.

---

### User Story 2 - Melihat Daftar Pembayaran per CTK (Priority: P1)

Sebagai admin LPK/PT, saya ingin melihat daftar semua pembayaran yang sudah dilakukan untuk setiap CTK, agar dapat memantau status pembayaran.

**Why this priority**: Melihat data yang sudah dicatat sama pentingnya dengan mencatat data baru - keduanya adalah fungsi inti.

**Independent Test**: Dapat diuji dengan mengakses halaman daftar pembayaran dan memverifikasi semua kolom (CTK, tanggal, nominal, status, bukti) ditampilkan dengan benar.

**Acceptance Scenarios**:

1. **Given** admin berada di halaman daftar Pembayaran ke Pusat, **When** halaman dimuat, **Then** sistem menampilkan tabel dengan kolom: nama CTK, tanggal pembayaran, nominal, status pembayaran, dan preview bukti transfer.

2. **Given** admin berada di halaman daftar, **When** admin mengklik baris pembayaran tertentu, **Then** sistem menampilkan detail lengkap pembayaran tersebut termasuk siapa yang mencatat.

---

### User Story 3 - Memfilter dan Mencari Pembayaran (Priority: P2)

Sebagai admin LPK/PT, saya ingin dapat memfilter dan mencari pembayaran berdasarkan CTK, tanggal, atau status, agar dapat menemukan informasi dengan cepat.

**Why this priority**: Meningkatkan efisiensi penggunaan fitur, tapi fitur tetap berfungsi tanpa filter.

**Independent Test**: Dapat diuji dengan menggunakan filter tanggal untuk menampilkan pembayaran dalam rentang tertentu dan memverifikasi hasil sesuai kriteria.

**Acceptance Scenarios**:

1. **Given** admin berada di halaman daftar pembayaran, **When** admin memasukkan nama CTK di kolom pencarian, **Then** sistem menampilkan hanya pembayaran untuk CTK yang sesuai.

2. **Given** admin berada di halaman daftar pembayaran, **When** admin memilih filter tanggal dari-sampai, **Then** sistem menampilkan pembayaran dalam rentang tanggal tersebut.

---

### User Story 4 - Melihat Ringkasan Total Pembayaran (Priority: P2)

Sebagai admin LPK/PT, saya ingin melihat ringkasan total pembayaran per CTK atau keseluruhan, agar dapat memantau cashflow ke pusat.

**Why this priority**: Fitur pelengkap yang meningkatkan insight tanpa mengubah fungsi inti.

**Independent Test**: Dapat diuji dengan memverifikasi widget/summary menampilkan total yang sesuai dengan penjumlahan semua entri pembayaran.

**Acceptance Scenarios**:

1. **Given** admin berada di halaman daftar pembayaran, **When** halaman dimuat, **Then** sistem menampilkan ringkasan: total pembayaran bulan ini, jumlah transaksi, dan rata-rata per CTK.

---

### Edge Cases

- Apa yang terjadi jika CTK dihapus setelah ada pembayaran tercatat? Pembayaran tetap tersimpan dengan referensi ke CTK yang sudah dihapus (soft reference).
- Bagaimana jika file bukti transfer rusak atau tidak dapat dibuka? Sistem menampilkan placeholder "Bukti tidak tersedia" dan admin dapat mengupload ulang.
- Apa yang terjadi jika admin mencoba membuat pembayaran dengan nominal 0 atau negatif? Sistem menolak dengan validasi "Nominal harus lebih dari 0".
- Bagaimana menangani pembayaran duplikat? Sistem memperbolehkan multiple pembayaran untuk CTK yang sama (pembayaran bertahap).

## Requirements *(mandatory)*

### Functional Requirements

**Data Entry**
- **FR-001**: Sistem HARUS menyediakan form untuk mencatat pembayaran dengan field: CTK (pilihan dari daftar), tanggal pembayaran, nominal (currency IDR), dan upload bukti transfer.
- **FR-002**: Sistem HARUS otomatis mencatat user yang membuat entri pembayaran (created_by).
- **FR-003**: Sistem HARUS memvalidasi nominal pembayaran lebih dari 0.
- **FR-004**: Sistem HARUS menerima file bukti transfer dalam format JPG, PNG, atau PDF dengan ukuran maksimal 10MB.
- **FR-005**: Sistem HARUS memvalidasi tanggal pembayaran tidak lebih dari hari ini.

**Data Display**
- **FR-006**: Sistem HARUS menampilkan daftar pembayaran dalam bentuk tabel dengan kolom: nama CTK, tanggal, nominal (format currency), user pembuat, dan thumbnail/link bukti transfer.
- **FR-007**: Sistem HARUS menampilkan halaman detail pembayaran yang menunjukkan semua informasi termasuk preview bukti transfer.
- **FR-008**: Sistem HARUS memungkinkan preview/download bukti transfer dari halaman daftar dan detail.

**Navigation & Access**
- **FR-009**: Menu "Pembayaran ke Pusat" HARUS ditempatkan di navigasi setelah menu ASET.
- **FR-010**: Sistem HARUS menerapkan isolasi entity - admin LPK hanya melihat pembayaran CTK LPK, admin PT hanya melihat pembayaran CTK PT.
- **FR-010a**: Role Pimpinan HARUS dapat melihat semua pembayaran dari kedua entity (LPK dan PT).

**Filtering & Search**
- **FR-011**: Sistem HARUS menyediakan pencarian berdasarkan nama CTK.
- **FR-012**: Sistem HARUS menyediakan filter berdasarkan rentang tanggal.

**Summary**
- **FR-013**: Sistem HARUS menampilkan ringkasan total pembayaran (total nominal, jumlah transaksi).

### Key Entities

- **PembayaranPusat (Central Payment)**: Entitas baru dengan tabel dedicated `pembayaran_pusat`. Mencatat pembayaran ke pusat per CTK. Atribut utama: entity (LPK/PT), referensi ke CTK, tanggal pembayaran, nominal dalam IDR, file bukti transfer, user pembuat. Relasi: belongs to CTK, belongs to User (created_by). Kolom `entity` disimpan redundant untuk optimasi query filtering.

- **CTK (existing)**: Calon Tenaga Kerja yang sudah ada di sistem. Relasi dengan PembayaranPusat: one CTK has many PembayaranPusat.

## Clarifications

### Session 2026-04-05

- Q: Apakah pembayaran ke pusat menggunakan tabel database terpisah atau extend tabel existing? → A: Tabel baru `pembayaran_pusat` - terpisah sepenuhnya dari ctk_payments
- Q: Apakah tabel pembayaran_pusat perlu kolom entity sendiri? → A: Ya, tambah kolom `entity` di pembayaran_pusat (redundant tapi query lebih cepat)
- Q: Apakah admin dapat mengedit atau menghapus pembayaran? → A: Full CRUD - admin bisa edit dan hapus pembayaran
- Q: Apakah Pimpinan dapat melihat semua pembayaran dari LPK dan PT? → A: Ya - Pimpinan bisa lihat semua pembayaran LPK dan PT

## Assumptions

- Setiap CTK dapat memiliki multiple pembayaran ke pusat (tidak dibatasi jumlahnya).
- Admin dapat melakukan full CRUD (Create, Read, Update, Delete) pada pembayaran.
- Tidak ada approval workflow - pembayaran langsung tercatat saat disimpan.
- Semua admin dengan akses ke menu ini dapat membuat/edit/hapus pembayaran untuk CTK dalam entity mereka.
- Bukti transfer disimpan di storage publik untuk kemudahan akses preview.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat mencatat pembayaran baru dalam waktu kurang dari 2 menit (termasuk upload bukti).
- **SC-002**: 100% pembayaran yang dicatat memiliki bukti transfer yang dapat diakses/dipreview.
- **SC-003**: Admin dapat menemukan pembayaran spesifik menggunakan filter dalam waktu kurang dari 30 detik.
- **SC-004**: Ringkasan total pembayaran akurat 100% (sesuai dengan penjumlahan manual semua entri).
