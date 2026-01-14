# Quickstart Guide: Karyawan LPK Management

**Feature**: 002-karyawan-lpk  
**Date**: 2026-01-13  
**Audience**: Admin LPK, Keuangan LPK, Instruktur, Pimpinan

---

## 1. Creating a New Karyawan LPK (Admin LPK)

### 1.1 Access the Form

1. Login sebagai **Admin LPK**
2. Di sidebar, klik **"Karyawan LPK"**
3. Klik tombol **"Tambah Karyawan"** (hijau, kanan atas)

### 1.2 Fill Required Fields

**Tab: Informasi Personal**
- **Nama Lengkap**: Masukkan nama lengkap karyawan
- **NIK**: 16 digit angka (contoh: 3201234567890001)
- **Email**: Email unik, belum terdaftar di sistem
- **Tanggal Lahir**: Pilih dari kalender (harus sebelum hari ini)
- **Jenis Kelamin**: Pilih Laki-laki atau Perempuan
- **Alamat**: Alamat lengkap karyawan
- **Telepon**: Nomor telepon (format bebas)

**Tab: Kepegawaian**
- **Jabatan**: Pilih salah satu:
  - **Instruktur** (untuk pengajar)
  - **Admin LPK** (untuk staff administratif)
  - **Staff** (untuk support staff)
- **Status**: Default "Aktif" (bisa pilih Cuti/Resign)
- **Tanggal Bergabung**: Pilih dari kalender (tidak boleh sebelum tanggal lahir)

**Tab: Kompensasi** (Optional)
- **Honor Pokok**: Masukkan angka (Rupiah, contoh: 5000000)
- **Honor per Jam**: Muncul hanya jika Jabatan = Instruktur

### 1.3 Upload Sertifikat (Only for Instruktur)

Jika Jabatan = **Instruktur**, section **"Sertifikat Kompetensi"** akan muncul:

1. Klik **"Pilih File"** atau drag & drop
2. File yang diterima: PDF, JPG, PNG (max 5MB)
3. Sistem akan menampilkan preview (untuk image) atau nama file (untuk PDF)

### 1.4 Submit

1. Klik **"Simpan"** (biru, bawah kanan)
2. Sistem akan validasi:
   - NIK 16 digit & unik
   - Email format valid & unik
   - Tanggal lahir < hari ini
   - Tanggal bergabung >= tanggal lahir
   - File sertifikat (jika ada) valid format & size
3. Jika sukses: Notifikasi hijau "Karyawan berhasil ditambahkan"
4. Redirect ke halaman daftar karyawan, record baru muncul di tabel

### 1.5 Possible Errors

- **"NIK sudah terdaftar"**: NIK duplikat, gunakan NIK lain
- **"Email sudah terdaftar"**: Email duplikat, gunakan email lain
- **"Tanggal lahir tidak boleh di masa depan"**: Pilih tanggal sebelum hari ini
- **"Tanggal bergabung tidak boleh sebelum tanggal lahir"**: Adjust tanggal
- **"File maksimal 5MB"**: Compress file atau gunakan file lebih kecil

---

## 2. Viewing & Editing Karyawan (Admin LPK)

### 2.1 View List

1. Di sidebar, klik **"Karyawan LPK"**
2. Tabel menampilkan kolom:
   - **Nama** (sortable)
   - **NIK**
   - **Jabatan** (badge berwarna)
   - **Status** (badge: hijau=Aktif, kuning=Cuti, merah=Resign)
   - **Tanggal Bergabung**
   - **Actions** (Edit, Delete)

### 2.2 Filter & Search

**Search Bar** (kanan atas):
- Search by nama, NIK, atau email

**Filters** (klik tombol "Filter"):
- **Jabatan**: Instruktur / Admin LPK / Staff
- **Status**: Aktif / Cuti / Resign
- **Honor**: Ada Honor / Tidak Ada Honor
- **Trashed**: Without Trashed / With Trashed / Only Trashed

### 2.3 Edit Karyawan

1. Klik **ikon pensil** (Edit) di baris karyawan
2. Form muncul dengan data existing
3. **Field yang TIDAK bisa diubah**:
   - NIK (disabled, read-only)
   - Entity (hidden, always 'LPK')
4. **Field yang bisa diubah**: Semua field lain
5. **Conditional field**:
   - Jika ubah Jabatan ke Instruktur → sertifikat field muncul
   - Jika ubah Jabatan dari Instruktur → sertifikat field hide (file tetap tersimpan)
6. Klik **"Simpan"** untuk submit perubahan

### 2.4 Delete Karyawan (Soft Delete)

1. Klik **ikon trash** (Delete) di baris karyawan
2. Konfirmasi dialog: "Yakin ingin menghapus?"
3. Klik **"Hapus"**
4. Sistem soft-delete record (deleted_at diisi, status set to 'Resign')
5. Record hilang dari tabel default (bisa dilihat dengan filter "With Trashed")

### 2.5 Restore Deleted Karyawan

1. Filter: Pilih **"Only Trashed"**
2. Klik **ikon restore** (panah melingkar) di baris karyawan
3. Record restored (deleted_at cleared, status set to 'Aktif')
4. Muncul kembali di tabel default

---

## 3. Managing Honor (Keuangan LPK)

### 3.1 Access

1. Login sebagai **Keuangan LPK**
2. Klik **"Karyawan LPK"** di sidebar
3. **Permission**: Bisa view all, edit honor fields, TIDAK bisa delete

### 3.2 Edit Honor

1. Klik **ikon pensil** (Edit) di baris karyawan
2. Navigate ke tab **"Kompensasi"**
3. Update field:
   - **Honor Pokok**: Angka (Rupiah)
   - **Honor per Jam**: Muncul hanya jika Jabatan = Instruktur
4. **Field lain**: Read-only (tidak bisa edit nama, NIK, dll)
5. Klik **"Simpan"**

### 3.3 Filter by Honor

- Filter: **"Ada Honor"** → Tampil karyawan yang honor_pokok NOT NULL
- Filter: **"Tidak Ada Honor"** → Tampil karyawan yang honor_pokok NULL

**Use Case**: Keuangan perlu tahu siapa saja yang belum di-set honor untuk payroll.

---

## 4. Self-Service Profile (Instruktur)

### 4.1 Access Own Profile

1. Login sebagai **Instruktur**
2. Di sidebar, klik **"Profil Saya"** (jika tersedia)
3. **Permission**: Hanya lihat & edit profil sendiri, TIDAK bisa lihat karyawan lain

### 4.2 View Profile

Menampilkan:
- **Read-Only Fields**: Nama, NIK, Jabatan, Honor (grayed out)
- **Editable Fields**: Alamat, Telepon
- **Download**: Link download sertifikat (jika ada)

### 4.3 Update Contact Info

1. Edit field **Alamat** dan/atau **Telepon**
2. Klik **"Simpan"**
3. Notifikasi: "Profil berhasil diperbarui"

**Note**: Instruktur TIDAK bisa upload/update sertifikat sendiri. Harus via Admin LPK.

---

## 5. Viewing Karyawan (Pimpinan)

### 5.1 Read-Only Access

1. Login sebagai **Pimpinan**
2. Klik **"Karyawan LPK"** di sidebar
3. **Permission**: View all karyawan, TIDAK bisa edit/delete

### 5.2 Available Actions

- **View**: Klik row untuk lihat detail lengkap
- **Filter & Search**: Sama seperti Admin LPK
- **Export** (jika tersedia): Export tabel ke PDF/Excel

**Use Case**: Pimpinan monitoring jumlah karyawan aktif, distribusi jabatan, dll untuk decision making.

---

## 6. Certificate Download Workflow

### 6.1 Who Can Download?

- **Admin LPK**: All certificates
- **Pimpinan**: All certificates
- **Instruktur**: Own certificate only
- **Keuangan LPK**: NO access to certificates

### 6.2 Download Steps

1. Buka detail karyawan (View atau Edit page)
2. Jika karyawan = Instruktur dan sertifikat ada:
   - Link **"Download Sertifikat"** muncul
3. Klik link → File download otomatis
4. **Authorization**: Sistem check policy sebelum serving file
   - Jika unauthorized: Error 403 "Access Denied"
   - Jika file not found: Error 404 "File Not Found"

---

## 7. Troubleshooting

### Issue: "Access Denied" saat buka Karyawan LPK

**Solution**: Check role assignment. Only Admin LPK, Keuangan LPK, Pimpinan dapat akses menu.

### Issue: Tidak bisa upload sertifikat >5MB

**Solution**: Compress file atau gunakan online compressor (PDF: ilovepdf.com, Image: tinypng.com)

### Issue: Sertifikat field tidak muncul

**Solution**: Pastikan Jabatan = **Instruktur**. Field conditional, hanya muncul untuk Instruktur.

### Issue: NIK/Email duplikat error saat edit

**Solution**: Kemungkinan ada karyawan lain (termasuk yang soft-deleted) dengan NIK/Email sama. Check filter "With Trashed".

### Issue: Tanggal bergabung tidak bisa di-save

**Solution**: Pastikan tanggal bergabung >= tanggal lahir. Sistem validasi cross-field.

---

## 8. Common Workflows Summary

| Task | Role | Steps |
|------|------|-------|
| Add new Instruktur | Admin LPK | Create → Fill form → Select Jabatan=Instruktur → Upload sertifikat → Save |
| Set Honor for karyawan | Keuangan LPK | Edit → Tab Kompensasi → Fill honor_pokok → Save |
| Employee resigned | Admin LPK | Edit → Change Status to Resign → Save (auto soft-delete) |
| Restore ex-employee | Admin LPK | Filter "Only Trashed" → Click Restore icon |
| Instruktur update contact | Instruktur | Profil Saya → Edit alamat/telepon → Save |
| Pimpinan view stats | Pimpinan | View list → Use filters (Jabatan, Status) → Review count |

---

**Quickstart Complete** ✅  
**For Implementation Details**: See data-model.md and research.md  
**For Task Breakdown**: Run `/speckit.tasks` after plan approval
