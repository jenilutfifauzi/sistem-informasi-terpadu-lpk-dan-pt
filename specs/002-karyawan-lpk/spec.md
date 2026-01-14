# Feature Specification: Karyawan LPK Management

**Feature Branch**: `002-karyawan-lpk`  
**Created**: 2026-01-13  
**Status**: Draft  
**Input**: User description: "Karyawan LPK Management - CRUD untuk mengelola data karyawan LPK (Instruktur dan Staff) termasuk jabatan, honor, jadwal mengajar, dan sertifikat"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Kelola Data Karyawan LPK (Priority: P1)

Admin LPK perlu mengelola data lengkap karyawan LPK (Instruktur dan Staff) untuk keperluan operasional harian, dokumentasi, dan kepatuhan regulasi. Data mencakup informasi personal, jabatan, status kepegawaian, dan dokumen pendukung.

**Why this priority**: Foundational untuk seluruh operasi LPK. Tanpa data karyawan yang terkelola, tidak bisa assign instruktur ke pelatihan atau track staff administratif.

**Independent Test**: Admin LPK dapat membuat, melihat, mengedit, dan menonaktifkan karyawan LPK melalui Filament admin panel. Sistem menampilkan daftar karyawan dengan filter berdasarkan jabatan dan status.

**Acceptance Scenarios**:

1. **Given** Admin LPK login ke sistem, **When** mengakses menu Karyawan LPK, **Then** melihat tabel semua karyawan LPK dengan kolom: Nama, NIK, Jabatan, Status, Tanggal Bergabung
2. **Given** Admin LPK di halaman Karyawan LPK, **When** klik tombol "Tambah Karyawan", **Then** muncul form dengan field: Nama Lengkap, Email, NIK, Tanggal Lahir, Jenis Kelamin, Alamat, Telepon, Jabatan (dropdown: Instruktur/Admin/Staff), Status (dropdown: Aktif/Cuti/Resign), Tanggal Bergabung
3. **Given** Admin LPK mengisi semua field wajib, **When** submit form, **Then** data tersimpan dan muncul notifikasi sukses, karyawan baru tampil di tabel
4. **Given** Admin LPK memilih karyawan dari tabel, **When** klik "Edit", **Then** dapat mengubah semua field kecuali NIK dan tanggal bergabung
5. **Given** Admin LPK melihat karyawan berstatus Aktif, **When** ubah status menjadi Resign, **Then** sistem soft-delete record tersebut (tidak tampil di list default, bisa difilter "Lihat yang Resign")
6. **Given** karyawan dengan email duplikat, **When** Admin LPK coba tambah karyawan baru dengan email yang sama, **Then** sistem tolak dengan pesan error "Email sudah terdaftar"
7. **Given** karyawan dengan NIK duplikat, **When** Admin LPK coba tambah karyawan baru dengan NIK yang sama, **Then** sistem tolak dengan pesan error "NIK sudah terdaftar"

---

### User Story 2 - Kelola Honor Karyawan LPK (Priority: P2)

Admin LPK dan Keuangan LPK perlu mencatat dan mengelola data honor/kompensasi karyawan LPK untuk keperluan penggajian dan pelaporan keuangan.

**Why this priority**: Diperlukan untuk operasional bulanan (payroll). Namun bisa di-setup setelah master data karyawan ada.

**Independent Test**: Admin/Keuangan LPK dapat mencatat honor pokok dan tunjangan untuk setiap karyawan. Data honor dapat dilihat dan diupdate.

**Acceptance Scenarios**:

1. **Given** Admin LPK membuat/edit karyawan, **When** mengisi field "Honor Pokok" (Rupiah), **Then** nilai tersimpan dan tampil di detail karyawan
2. **Given** karyawan dengan jabatan Instruktur, **When** Admin LPK lihat detail, **Then** ada field tambahan "Honor per Jam Mengajar" yang bisa diisi
3. **Given** Keuangan LPK melihat daftar karyawan, **When** filter berdasarkan "Ada Honor", **Then** tampil hanya karyawan yang sudah diisi honor pokok
4. **Given** Admin LPK mengisi honor dengan format salah (huruf/negatif), **When** submit, **Then** validasi error "Honor harus berupa angka positif"

---

### User Story 3 - Kelola Sertifikat Instruktur (Priority: P3)

Admin LPK perlu mengupload dan menyimpan sertifikat kompetensi instruktur untuk memastikan instruktur qualified sesuai regulasi LPK.

**Why this priority**: Penting untuk compliance, tapi tidak blocking operasi harian. Bisa ditambahkan setelah basic CRUD dan honor management selesai.

**Independent Test**: Admin LPK dapat upload file sertifikat (PDF/image) ke profil instruktur. File tersimpan dan dapat didownload/dilihat.

**Acceptance Scenarios**:

1. **Given** Admin LPK edit karyawan dengan jabatan Instruktur, **When** melihat form, **Then** ada section "Sertifikat Kompetensi" dengan field upload file
2. **Given** Admin LPK upload file sertifikat (PDF/JPG/PNG, max 5MB), **When** submit, **Then** file tersimpan dan tampil link download di detail karyawan
3. **Given** Admin LPK upload file >5MB atau format selain PDF/JPG/PNG, **When** submit, **Then** validasi error "File maksimal 5MB dengan format PDF/JPG/PNG"
4. **Given** karyawan non-Instruktur, **When** Admin LPK lihat form, **Then** section sertifikat TIDAK tampil (hanya untuk Instruktur)
5. **Given** instruktur sudah punya sertifikat, **When** Admin LPK upload sertifikat baru, **Then** file lama diganti dengan yang baru (dengan konfirmasi)

---

### User Story 4 - Instruktur Lihat Profil Sendiri (Priority: P3)

Instruktur dapat melihat dan update informasi kontak pribadi mereka sendiri untuk menjaga data tetap akurat.

**Why this priority**: Nice to have untuk user experience, tapi Admin LPK bisa handle update jika perlu. Tidak blocking.

**Independent Test**: User dengan role Instruktur dapat login dan melihat profil sendiri (read-only untuk sebagian besar field, edit untuk kontak).

**Acceptance Scenarios**:

1. **Given** user dengan role Instruktur login, **When** akses menu "Profil Saya", **Then** tampil detail: Nama, NIK, Jabatan, Honor (read-only), Alamat & Telepon (editable)
2. **Given** Instruktur melihat profilnya, **When** ubah alamat/telepon dan submit, **Then** data terupdate dan notifikasi sukses
3. **Given** Instruktur coba akses daftar karyawan lain, **When** buka menu Karyawan LPK, **Then** akses ditolak (hanya Admin LPK yang bisa)

---

### Edge Cases

- Apa yang terjadi jika Admin LPK mencoba menghapus karyawan yang sudah di-assign sebagai instruktur ke pelatihan aktif? (Sistem harus mencegah hard delete, hanya izinkan soft delete dengan status Resign)
- Bagaimana sistem handle jika karyawan resign lalu bergabung lagi? (Admin bisa reaktivasi record lama atau buat baru dengan NIK berbeda jika NIK berubah)
- Apa yang terjadi jika file sertifikat corrupt atau tidak bisa dibuka? (Sistem simpan file tapi beri warning "File mungkin rusak, silakan re-upload")
- Bagaimana jika ada 2 instruktur dengan nama identik? (NIK dan email tetap unique identifier, tidak ada masalah)
- Apa yang terjadi jika karyawan LPK pindah ke PT? (Di luar scope fitur ini - akan dihandle oleh workflow transfer entity di fitur future)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyediakan CRUD lengkap untuk karyawan LPK dengan field: nama lengkap, email (unique), NIK (unique), tanggal lahir, jenis kelamin, alamat, telepon, jabatan, status kepegawaian, tanggal bergabung, honor pokok, honor per jam (untuk Instruktur)
- **FR-002**: Sistem MUST memvalidasi uniqueness untuk email dan NIK secara case-insensitive
- **FR-003**: Sistem MUST menyediakan dropdown untuk Jabatan dengan pilihan: Instruktur, Admin LPK, Staff
- **FR-004**: Sistem MUST menyediakan dropdown untuk Status dengan pilihan: Aktif, Cuti, Resign
- **FR-005**: Sistem MUST implement soft delete untuk karyawan dengan status Resign (tidak tampil di list default tapi bisa difilter)
- **FR-006**: Sistem MUST menyimpan entity='LPK' secara otomatis untuk setiap karyawan LPK (hard-coded, tidak bisa diubah)
- **FR-007**: Sistem MUST menampilkan field upload sertifikat hanya untuk karyawan dengan jabatan Instruktur
- **FR-008**: Sistem MUST validasi file upload sertifikat: format PDF/JPG/PNG, maksimal 5MB
- **FR-009**: Sistem MUST menyimpan file sertifikat dengan visibility private (hanya user authorized yang bisa download)
- **FR-010**: Sistem MUST menyediakan filter tabel berdasarkan: Jabatan, Status, Ada/Tidak Ada Honor
- **FR-011**: Admin LPK MUST memiliki akses full CRUD untuk semua karyawan LPK
- **FR-012**: Keuangan LPK MUST dapat view dan edit honor, tapi tidak bisa delete karyawan
- **FR-013**: Instruktur MUST dapat view dan edit profil sendiri (hanya alamat & telepon), tidak bisa lihat karyawan lain
- **FR-014**: Pimpinan MUST dapat view all karyawan LPK (read-only)
- **FR-015**: Sistem MUST validasi format email yang valid
- **FR-016**: Sistem MUST validasi NIK berupa 16 digit angka
- **FR-017**: Sistem MUST validasi tanggal lahir tidak boleh di masa depan
- **FR-018**: Sistem MUST validasi tanggal bergabung tidak boleh sebelum tanggal lahir
- **FR-019**: Sistem MUST mencatat audit log untuk operasi create, update, delete karyawan (siapa, kapan, apa yang diubah)
- **FR-020**: Sistem MUST menampilkan timestamp created_at dan updated_at di detail karyawan

### Key Entities

- **Karyawan LPK**: Representasi data karyawan yang bekerja di LPK (baik Instruktur maupun Staff). Key attributes: NIK (unique identifier), nama lengkap, email, jabatan (enum: Instruktur/Admin/Staff), status (enum: Aktif/Cuti/Resign), honor pokok, tanggal bergabung, entity (fixed: 'LPK'). Relationship: Satu karyawan dapat menjadi instruktur di banyak pelatihan (future).
- **Sertifikat Instruktur**: File upload (PDF/image) yang merepresentasikan sertifikat kompetensi instruktur. Attached to Karyawan LPK dengan jabatan Instruktur. Attributes: file path, file name, upload date, file size.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin LPK dapat menambahkan karyawan baru dalam waktu kurang dari 2 menit (dari klik "Tambah" hingga tersimpan)
- **SC-002**: Sistem mencegah 100% duplikasi NIK dan email (zero duplicate records)
- **SC-003**: Data karyawan LPK dengan entity='LPK' terisolasi sempurna dari data karyawan PT (verified via permission checks)
- **SC-004**: File sertifikat tersimpan dengan visibility private dan hanya dapat diakses oleh role authorized (Admin LPK, Pimpinan, dan instruktur pemilik sertifikat)
- **SC-005**: Audit log mencatat 100% operasi CRUD pada karyawan LPK dengan detail lengkap (user, timestamp, changes)
- **SC-006**: Instruktur dapat melihat dan update profil sendiri tanpa bantuan Admin LPK (self-service rate 90%+)
- **SC-007**: Sistem dapat menampilkan tabel karyawan LPK dengan filter dan pagination untuk hingga 500 karyawan tanpa penurunan performa (response time <2 detik)
- **SC-008**: 100% karyawan LPK yang dibuat memiliki entity='LPK' secara otomatis (verified via database constraint)

## Assumptions

- Email karyawan LPK adalah email pribadi atau email kantor LPK (format bebas, hanya validasi format email standard)
- NIK menggunakan format NIK Indonesia (16 digit), tidak ada karyawan asing di fase 1
- Honor pokok dicatat dalam Rupiah, tidak ada mata uang lain
- Sertifikat instruktur dapat berupa sertifikat pelatihan dari BNSP, institusi resmi, atau internal LPK (tidak ada validasi lembaga penerbit di fase 1)
- Karyawan yang resign akan soft-deleted tapi data tetap retained untuk audit purpose (sesuai constitution principle IV)
- Jabatan "Admin LPK" dan "Staff" dibedakan untuk keperluan future permission/role assignment yang lebih granular
- Honor per jam mengajar hanya relevan untuk Instruktur (optional field)
- File sertifikat disimpan di storage/app/private/certificates/ dengan naming convention: {nik}_{timestamp}.{ext}
- Photo profil karyawan tidak diperlukan di fase 1 (future enhancement)
- Riwayat jabatan/promosi tidak ditrack di fase 1 (current state only)

## Out of Scope (Future Enhancements)

- Upload multiple sertifikat per instruktur (fase 1 hanya 1 file)
- Jadwal mengajar instruktur (akan dihandle di modul Pelatihan)
- Absensi karyawan LPK
- Slip gaji otomatis
- Kontrak kerja karyawan (document management)
- Photo profil karyawan
- Riwayat jabatan dan promosi
- Import/export data karyawan via Excel
- Integrasi dengan sistem payroll eksternal
- Notifikasi email ke karyawan saat data berubah
- Mobile app untuk instruktur
