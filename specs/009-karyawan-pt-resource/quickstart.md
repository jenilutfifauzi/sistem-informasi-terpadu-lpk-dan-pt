# Quickstart Guide: Karyawan PT Management

**Feature**: 009-karyawan-pt-resource
**Date**: 2026-03-09
**Audience**: Admin PT, Keuangan PT, Pimpinan, super_admin

---

## 1. Menambah Karyawan PT Baru (Admin PT)

### 1.1 Akses Form

1. Login sebagai **Admin PT** atau **super_admin**
2. Di sidebar, klik **"Karyawan PT"** (grup "Data Master")
3. Klik tombol **"Tambah Karyawan"** (kanan atas)

### 1.2 Isi Field Wajib

**Section: Informasi Personal**
- **Nama Lengkap**: Nama lengkap karyawan
- **NIK**: 16 digit angka (contoh: `3201234567890001`) — tidak bisa diubah setelah disimpan
- **Email**: Email unik, belum terdaftar sebagai karyawan PT lain
- **Tanggal Lahir**: Pilih dari kalender (harus sebelum hari ini)
- **Jenis Kelamin**: Pilih Laki-laki atau Perempuan
- **Alamat**: Alamat lengkap karyawan
- **Telepon**: Nomor telepon aktif
- **Foto Karyawan** *(opsional)*: Upload foto (format gambar, max 2MB)

**Section: Informasi Kepegawaian**
- **Jabatan**: Pilih salah satu:
  - Direktur
  - Manajer
  - Staf HRD
  - Staf Keuangan
  - Staf Operasional
  - Staf Administrasi
- **Divisi**: Pilih salah satu: Manajemen, HRD, Keuangan, Operasional, Administrasi
- **Jenis Kontrak**: Pilih: Tetap, PKWT (kontrak), atau Probasi
- **Status**: Default "Aktif"
- **Tanggal Bergabung**: Pilih dari kalender — tidak bisa diubah setelah disimpan

**Section: Kompensasi** *(opsional)*
- **Gaji Pokok**: Angka Rupiah (contoh: `8000000`) — tersembunyi di tabel secara default
- **Tunjangan**: Angka Rupiah (contoh: `1500000`) — opsional

**Section: Dokumen Kepegawaian** *(opsional)*
- Upload file kontrak, SK pengangkatan, KTP, dll
- Format yang diterima: PDF, JPG, PNG (max 5MB)
- File disimpan secara private (tidak dapat diakses publik)

### 1.3 Submit

1. Klik **"Simpan"**
2. Jika validasi lulus: notifikasi sukses, redirect ke daftar karyawan PT
3. Data otomatis disimpan dengan `entity='PT'` (tidak dapat diubah)

### 1.4 Error yang Mungkin Muncul

| Error | Penyebab | Solusi |
|-------|----------|--------|
| "NIK sudah terdaftar" | NIK duplikat | Gunakan NIK yang berbeda |
| "Email sudah terdaftar" | Email duplikat | Gunakan email lain |
| "NIK harus 16 digit" | Format NIK salah | Pastikan tepat 16 angka |
| "Gaji harus berupa angka positif" | Input huruf atau negatif | Masukkan angka ≥ 0 |
| "File maksimal 5MB" | File terlalu besar | Compress atau gunakan file lebih kecil |

---

## 2. Melihat & Mengedit Data Karyawan (Admin PT)

### 2.1 Lihat Daftar

1. Klik **"Karyawan PT"** di sidebar
2. Tabel menampilkan:
   - Foto (avatar)
   - Nama Lengkap (sortable, searchable)
   - NIK (searchable)
   - Jabatan (badge berwarna)
   - Divisi
   - Status (badge: hijau=Aktif, kuning=Cuti, merah=Resign)
   - Tanggal Bergabung (sortable)
   - Dokumen (icon ✓ jika ada)
   - **Gaji Pokok** (tersembunyi default — klik ikon kolom untuk tampilkan)
   - Email (tersembunyi default)

### 2.2 Filter Data

Klik ikon filter (kanan atas tabel) untuk akses:
- **Jabatan**: Filter per jabatan
- **Divisi**: Filter per divisi
- **Status Kepegawaian**: Filter Aktif/Cuti/Resign
- **Jenis Kontrak**: Filter Tetap/PKWT/Probasi
- **Ada Gaji**: Tampilkan hanya yang sudah ada gaji pokok
- **Tampilkan Data Resign**: Toggle untuk tampilkan karyawan yang sudah resign (soft-deleted)

### 2.3 Edit Karyawan

1. Klik ikon **Edit** (pensil) di baris karyawan
2. Semua field dapat diedit **kecuali**: NIK dan Tanggal Bergabung (disabled)
3. Klik **"Simpan"** untuk konfirmasi perubahan

### 2.4 Lihat Detail (View)

1. Klik ikon **Lihat** (mata) di baris karyawan
2. Infolist menampilkan semua data dalam section:
   - Informasi Personal (foto, data personal)
   - Informasi Kepegawaian (jabatan, divisi, kontrak, status, tanggal bergabung)
   - Kompensasi (gaji pokok, tunjangan)
   - Dokumen Kepegawaian (status ada/tidak)

---

## 3. Nonaktifkan Karyawan (Resign)

1. Edit karyawan yang bersangkutan
2. Ubah **Status** dari "Aktif" menjadi **"Resign"**
3. Klik **"Simpan"**
4. Data karyawan akan soft-deleted (tidak muncul di daftar default)
5. Untuk melihat karyawan resign: aktifkan filter **"Tampilkan Data Resign"**

---

## 4. Export Data Karyawan PT

1. Buka halaman daftar Karyawan PT
2. (Opsional) Terapkan filter yang diinginkan
3. Klik tombol **"Export CSV"** di header tabel
4. File CSV akan otomatis terunduh dengan nama: `karyawan-pt-YYYY-MM-DD.csv`

---

## 5. Akses Berdasarkan Role

| Role | Lihat | Tambah | Edit | Hapus | Export | Gaji |
|------|-------|--------|------|-------|--------|------|
| super_admin | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Admin PT | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Keuangan PT | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Pimpinan | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Admin LPK | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Keuangan LPK | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 6. Perbedaan vs Karyawan LPK

| Aspek | Karyawan LPK | Karyawan PT |
|-------|-------------|-------------|
| URL | `/admin/karyawan-lpks` | `/admin/karyawan-pts` |
| Jabatan | Instruktur, Admin LPK, Staff | Direktur, Manajer, Staf HRD, Staf Keuangan, Staf Operasional, Staf Administrasi |
| Divisi | — | Manajemen, HRD, Keuangan, Operasional, Administrasi |
| Jenis Kontrak | — | Tetap, PKWT, Probasi |
| Kompensasi | Honor Pokok + Honor per Jam | Gaji Pokok + Tunjangan |
| Dokumen | Sertifikat Kompetensi | Dokumen Kepegawaian |
| Akses | Admin LPK | Admin PT |
