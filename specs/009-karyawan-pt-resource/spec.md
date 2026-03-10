# Feature Specification: Karyawan PT Management

**Feature Branch**: `009-karyawan-pt-resource`  
**Created**: 2026-03-09  
**Status**: Draft  
**Input**: User description: "create menu baru untuk Karyawan PT, samakan seperti http://localhost:8000/admin/karyawan-lpks"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Kelola Data Karyawan PT (Priority: P1)

Admin PT perlu mengelola data lengkap karyawan PT (staf HRD, keuangan, manajer, dan operasional) untuk keperluan administrasi, penggajian, dan kepatuhan regulasi. Data mencakup informasi personal, jabatan, divisi, status kepegawaian, jenis kontrak, dan dokumen pendukung.

**Why this priority**: Fitur inti yang menjadi fondasi operasional PT. Tanpa data karyawan yang terkelola, tidak bisa melacak tanggung jawab staf, mengelola penggajian, atau mengelola dokumen kepegawaian.

**Independent Test**: Admin PT dapat membuat, melihat, mengedit, dan menonaktifkan karyawan PT melalui Filament admin panel. Sistem menampilkan daftar karyawan PT dengan filter berdasarkan jabatan, divisi, dan status.

**Acceptance Scenarios**:

1. **Given** Admin PT login ke sistem, **When** mengakses menu "Karyawan PT" di sidebar, **Then** melihat tabel semua karyawan PT dengan kolom: Foto, Nama Lengkap, NIK, Jabatan, Divisi, Status, Tanggal Bergabung
2. **Given** Admin PT di halaman Karyawan PT, **When** klik tombol "Tambah Karyawan", **Then** muncul form dengan field: Nama Lengkap, Email, NIK, Tanggal Lahir, Jenis Kelamin, Alamat, Telepon, Jabatan (dropdown), Divisi (dropdown), Status Kepegawaian, Jenis Kontrak, Tanggal Bergabung, Foto Karyawan
3. **Given** Admin PT mengisi semua field wajib, **When** submit form, **Then** data tersimpan dengan entity='PT' otomatis, muncul notifikasi sukses, karyawan baru tampil di tabel
4. **Given** Admin PT memilih karyawan dari tabel, **When** klik "Lihat" (View), **Then** tampil infolist lengkap semua section: Personal, Kepegawaian, Kompensasi, Dokumen
5. **Given** Admin PT memilih karyawan dari tabel, **When** klik "Edit", **Then** dapat mengubah semua field kecuali NIK dan tanggal bergabung
6. **Given** Admin PT melihat karyawan berstatus Aktif, **When** ubah status menjadi Resign, **Then** sistem soft-delete record tersebut (tidak tampil di list default, bisa difilter dengan "Tampilkan Data Resign")
7. **Given** karyawan dengan email duplikat, **When** Admin PT coba tambah karyawan baru dengan email yang sama, **Then** sistem tolak dengan pesan validasi "Email sudah terdaftar"
8. **Given** karyawan dengan NIK duplikat, **When** Admin PT coba tambah dengan NIK yang sama, **Then** sistem tolak dengan pesan validasi "NIK sudah terdaftar"

---

### User Story 2 - Kelola Gaji & Tunjangan Karyawan PT (Priority: P2)

Admin PT dan Keuangan PT perlu mencatat dan mengelola gaji pokok serta tunjangan karyawan PT untuk keperluan penggajian dan pelaporan keuangan.

**Why this priority**: Diperlukan untuk operasional bulanan (payroll). Namun dapat dikerjakan setelah master data karyawan tersedia.

**Independent Test**: Admin PT / Keuangan PT dapat mencatat gaji pokok dan tunjangan untuk setiap karyawan. Data kompensasi dapat dilihat dan diperbarui melalui form edit.

**Acceptance Scenarios**:

1. **Given** Admin PT membuat/edit karyawan, **When** mengisi field "Gaji Pokok" (Rupiah), **Then** nilai tersimpan dan tampil di detail karyawan
2. **Given** Admin PT membuat/edit karyawan, **When** mengisi field "Tunjangan" (Rupiah, opsional), **Then** nilai tersimpan bersama gaji pokok
3. **Given** Keuangan PT melihat daftar karyawan, **When** filter berdasarkan "Ada Gaji", **Then** tampil hanya karyawan yang sudah diisi gaji pokok
4. **Given** Admin PT mengisi gaji dengan format salah (huruf atau angka negatif), **When** submit, **Then** validasi error "Gaji harus berupa angka positif"
5. **Given** kolom Gaji Pokok di tabel, **When** ditampilkan, **Then** tersembunyi secara default (toggleable, hanya muncul jika diaktifkan)

---

### User Story 3 - Upload Dokumen Kepegawaian (Priority: P3)

Admin PT perlu mengupload dan menyimpan dokumen kepegawaian (contoh: kontrak kerja, SK pengangkatan, KTP) untuk keperluan dokumentasi dan kepatuhan.

**Why this priority**: Penting untuk compliance dan audit, namun tidak menghalangi operasi harian. Dapat ditambahkan setelah CRUD dasar dan kompensasi selesai.

**Independent Test**: Admin PT dapat mengupload file dokumen (PDF/JPG/PNG max 5MB) ke profil karyawan. File tersimpan dan dapat diunduh/dilihat.

**Acceptance Scenarios**:

1. **Given** Admin PT edit karyawan, **When** melihat form, **Then** ada section "Dokumen Kepegawaian" dengan field upload file
2. **Given** Admin PT upload file dokumen (PDF/JPG/PNG, max 5MB), **When** submit, **Then** file tersimpan dan tampil indikator "Tersedia" di kolom Dokumen pada tabel
3. **Given** Admin PT upload file >5MB atau format tidak didukung, **When** submit, **Then** validasi error "File maksimal 5MB dengan format PDF/JPG/PNG"
4. **Given** karyawan sudah punya dokumen, **When** Admin PT upload dokumen baru, **Then** file lama diganti dengan yang baru

---

### Edge Cases

- Apa yang terjadi jika Admin PT mencoba menghapus karyawan yang terhubung dengan data lain? Sistem harus mencegah hard delete dan hanya mengizinkan soft delete (status Resign).
- Bagaimana sistem menangani jika karyawan resign lalu bergabung kembali? Admin dapat mengaktifkan kembali record lama yang soft-deleted.
- Apa yang terjadi jika ada dua karyawan PT dengan nama identik? NIK dan email tetap sebagai unique identifier, tidak ada masalah.
- Apa yang terjadi jika Admin LPK mencoba mengakses menu Karyawan PT? Akses ditolak sesuai kebijakan RBAC (hanya Admin PT, Keuangan PT, Pimpinan, dan super_admin yang memiliki akses view; hanya Admin PT dan super_admin yang dapat membuat/edit/hapus).
- Bagaimana jika nilai gaji 0 diinput? Sistem menerima nilai 0 sebagai valid (belum diisi), berbeda dengan null (tidak ada gaji set).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyediakan CRUD lengkap untuk karyawan PT dengan field wajib: nama lengkap, email (unique), NIK (unique, 16 digit), tanggal lahir, jenis kelamin, alamat, telepon, jabatan, divisi, status kepegawaian, jenis kontrak, tanggal bergabung
- **FR-002**: Sistem MUST memvalidasi uniqueness untuk email (case-insensitive) dan NIK pada scope tabel karyawan PT
- **FR-003**: Sistem MUST menyediakan dropdown untuk Jabatan PT dengan pilihan: Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, Staf Administrasi
- **FR-004**: Sistem MUST menyediakan dropdown untuk Divisi dengan pilihan: Manajemen, HRD, Keuangan, Operasional, Administrasi
- **FR-005**: Sistem MUST menyediakan dropdown untuk Jenis Kontrak dengan pilihan: Tetap, PKWT (Kontrak), Probasi
- **FR-006**: Sistem MUST menyediakan dropdown untuk Status Kepegawaian dengan pilihan: Aktif, Cuti, Resign (menggunakan StatusKepegawaian enum yang sudah ada)
- **FR-007**: Sistem MUST menyimpan entity='PT' secara otomatis untuk setiap karyawan PT (hardcoded, tidak bisa diubah oleh user)
- **FR-008**: Sistem MUST implement soft delete untuk karyawan dengan status Resign (tidak tampil di list default, bisa difilter "Tampilkan Data Resign")
- **FR-009**: Sistem MUST memungkinkan Admin PT mengupload foto karyawan (format gambar, max 2MB)
- **FR-010**: Sistem MUST memungkinkan Admin PT mengupload dokumen kepegawaian (PDF/JPG/PNG, max 5MB, disimpan private)
- **FR-011**: Sistem MUST menampilkan kolom gaji pokok secara tersembunyi default (toggleable)
- **FR-012**: Sistem MUST menyediakan filter tabel berdasarkan: Jabatan, Divisi, Status Kepegawaian, Jenis Kontrak, Ada Gaji
- **FR-013**: Sistem MUST menyediakan fitur export data karyawan PT ke format CSV
- **FR-014**: Sistem MUST mencatat audit log setiap perubahan data karyawan PT (menggunakan Spatie Activity Log yang sudah ada)
- **FR-015**: NIK dan Tanggal Bergabung MUST tidak dapat diubah setelah data disimpan (disabled pada form edit)
- **FR-016**: Sistem MUST menampilkan menu "Karyawan PT" dalam navigasi grup "Data Master" di Filament admin panel

### Key Entities

- **EmployeePT**: Representasi karyawan PT, menyimpan data personal (nama, NIK, email, tanggal lahir, jenis kelamin, alamat, telepon), data kepegawaian (jabatan, divisi, status, jenis kontrak, tanggal bergabung), kompensasi (gaji pokok, tunjangan), dan file (foto, dokumen kepegawaian). Selalu memiliki entity='PT'.
- **JabatanPT** (Enum): Daftar jabatan valid untuk karyawan PT: Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, Staf Administrasi.
- **DivisiPT** (Enum): Daftar divisi valid: Manajemen, HRD, Keuangan, Operasional, Administrasi.
- **JenisKontrak** (Enum): Tipe kontrak kerja: Tetap, PKWT, Probasi.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin PT dapat menambahkan karyawan PT baru dalam waktu kurang dari 3 menit menggunakan form yang tersedia
- **SC-002**: Seluruh data karyawan PT terisolasi dari data karyawan LPK — tidak ada karyawan PT yang muncul di daftar karyawan LPK dan sebaliknya
- **SC-003**: Sistem menolak 100% percobaan input dengan NIK duplikat atau email duplikat dengan pesan error yang jelas
- **SC-004**: Admin LPK dan Keuangan LPK tidak dapat mengakses menu Karyawan PT (akses kontrol berbasis role bekerja dengan benar)
- **SC-005**: Data karyawan PT yang di-resign tidak hilang dari sistem dan dapat diakses kembali melalui filter "Tampilkan Data Resign"
- **SC-006**: File dokumen kepegawaian yang diupload hanya dapat diakses oleh pengguna yang memiliki izin (tidak dapat diakses publik)
- **SC-007**: Export CSV menghasilkan file yang berisi data karyawan PT sesuai filter yang aktif saat itu

## Assumptions

- Jabatan PT yang umum digunakan di PJTKI (Perusahaan Jasa Tenaga Kerja Indonesia) mencakup: Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, Staf Administrasi. Jika ada jabatan lain yang dibutuhkan, dapat ditambahkan ke enum setelah diskusi.
- Divisi PT mengikuti struktur organisasi umum PJTKI. Dapat disesuaikan ke struktur PT spesifik.
- Jenis kontrak menggunakan 3 kategori umum (Tetap, PKWT, Probasi). Jika ada kategori tambahan seperti Magang, dapat ditambahkan.
- Struktur tabel database baru (`karyawan_pt`) dibuat terpisah dari `karyawan_lpk`, mengikuti pendekatan yang sudah ada.
- Fitur Jadwal Mengajar tidak relevan untuk PT (berbeda dengan LPK), sehingga tidak dimasukkan dalam scope ini.
- RBAC untuk resource ini mengikuti pola yang sudah ada: super_admin mendapat akses penuh, Admin PT mendapat akses CRUD, Keuangan PT mendapat akses view untuk semua data karyawan PT dan dapat mengedit field kompensasi (gaji_pokok, tunjangan) sesuai tanggung jawab penggajian, Pimpinan mendapat akses view dan dapat mengunduh dokumen kepegawaian.
