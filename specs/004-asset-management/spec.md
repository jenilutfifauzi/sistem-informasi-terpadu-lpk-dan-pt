# Feature Specification: Employee Asset Management System

**Feature Branch**: `004-asset-management`  
**Created**: February 8, 2026  
**Status**: Draft  
**Input**: User description: "create fitur asset pada data karyawan ada fitur asset PT dan asset LPK"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Asset Registration and Inventory Tracking (Priority: P1) 🎯 MVP

Admin PT atau Admin LPK dapat mendaftarkan dan mengelola inventaris aset kantor yang dimiliki oleh entitas mereka dengan tracking lengkap untuk setiap item.

**Why this priority**: Core functionality yang memungkinkan organisasi untuk mulai mencatat dan mengelola aset mereka. Tanpa ini, tidak ada data aset yang bisa dikelola.

**Independent Test**: Admin LPK login → buka menu Asset LPK → tambah aset baru (Laptop Lenovo, 3 unit, kondisi baik) → simpan → lihat di tabel inventory → verify data tersimpan dengan entity='LPK' otomatis.

**Acceptance Scenarios**:

1. **Given** Admin LPK sedang login, **When** Admin mengakses menu "Asset LPK" dan menambah aset baru dengan nama "Laptop Lenovo", jumlah 3, satuan "Unit", kondisi "Baik", tahun pembelian 2024, **Then** sistem menyimpan aset dengan entity='LPK' otomatis dan menampilkan di daftar inventory.

2. **Given** Admin PT sedang login, **When** Admin mengakses menu "Asset PT" dan menambah aset "Printer Canon", jumlah 2, kondisi "Baik", **Then** sistem menyimpan aset dengan entity='PT' dan hanya Admin PT yang bisa melihatnya.

3. **Given** Admin LPK melihat daftar asset, **When** Admin mencari "Laptop", **Then** sistem menampilkan semua aset yang mengandung kata "Laptop" dalam nama barang.

4. **Given** Ada aset dengan kondisi "Rusak", **When** Admin melihat daftar inventory, **Then** aset rusak ditampilkan dengan badge merah untuk memudahkan identifikasi.

---

### User Story 2 - Asset Condition Updates and Maintenance Tracking (Priority: P2)

Admin dapat memperbarui kondisi aset dari "Baik" menjadi "Rusak" atau sebaliknya, serta menambahkan catatan maintenance untuk tracking riwayat perawatan aset.

**Why this priority**: Penting untuk maintenance management dan lifecycle tracking aset, tetapi sistem masih bisa beroperasi tanpa fitur ini di fase awal.

**Independent Test**: Admin pilih aset Laptop → update kondisi dari "Baik" ke "Rusak" → tambah keterangan "Layar retak" → simpan → lihat riwayat perubahan kondisi dan verify tercatat dengan timestamp dan user.

**Acceptance Scenarios**:

1. **Given** Aset laptop dalam kondisi "Baik", **When** Admin mengubah kondisi menjadi "Rusak" dan menambah keterangan "Layar retak, butuh penggantian", **Then** sistem update kondisi, simpan keterangan, dan log perubahan dengan timestamp dan nama admin.

2. **Given** Admin melihat detail aset, **When** Admin mengakses tab "Riwayat Kondisi", **Then** sistem menampilkan semua perubahan kondisi dengan tanggal, kondisi lama, kondisi baru, alasan, dan nama admin yang mengubah.

3. **Given** Aset dalam kondisi "Rusak" sudah diperbaiki, **When** Admin update kondisi kembali ke "Baik" dengan keterangan "Sudah diganti layar baru", **Then** sistem update kondisi dan catat di riwayat.

---

### User Story 3 - Asset Assignment to Employees (Priority: P2)

Admin dapat menetapkan aset tertentu kepada karyawan (employee assignment) untuk tracking siapa yang menggunakan aset tersebut, kapan ditetapkan, dan kapan dikembalikan.

**Why this priority**: Penting untuk accountability dan tracking penggunaan aset, tetapi inventory tracking (US1) bisa berfungsi tanpa assignment.

**Independent Test**: Admin pilih Laptop → klik "Assign to employee" → pilih karyawan "John Doe" → set tanggal assignment → simpan → lihat di profile karyawan bahwa laptop assigned → verify karyawan bisa melihat aset yang assigned kepadanya.

**Acceptance Scenarios**:

1. **Given** Ada laptop yang belum di-assign, **When** Admin assign laptop ke karyawan "John Doe" dengan tanggal assignment hari ini, **Then** sistem catat assignment, tampilkan status "Assigned to: John Doe" pada aset, dan karyawan bisa melihat laptop ini di daftar "My Assets".

2. **Given** Laptop sudah di-assign ke karyawan, **When** Karyawan resign atau laptop dikembalikan dan Admin klik "Return Asset", **Then** sistem catat tanggal return, ubah status menjadi "Available", dan hapus dari daftar aset karyawan.

3. **Given** Admin melihat daftar karyawan, **When** Admin membuka profile karyawan, **Then** sistem menampilkan semua aset yang saat ini assigned ke karyawan tersebut dengan detail nama aset, nomor inventaris, dan tanggal assignment.

4. **Given** Sebuah aset sudah di-assign, **When** Admin mencoba assign aset yang sama ke karyawan lain tanpa return dulu, **Then** sistem menolak dengan pesan error "Asset sudah di-assign ke [nama karyawan], silakan return terlebih dahulu".

---

### User Story 4 - Entity-Based Asset Isolation (Priority: P1) 🔒 Security

Sistem memastikan Admin LPK hanya bisa melihat dan mengelola aset milik LPK, Admin PT hanya bisa melihat dan mengelola aset milik PT, dan Pimpinan bisa melihat semua aset kedua entitas (read-only).

**Why this priority**: Critical untuk security dan data isolation. Harus ada dari awal untuk mencegah unauthorized access.

**Independent Test**: Login sebagai Admin LPK → verify hanya melihat Asset LPK menu dan data → login sebagai Admin PT → verify hanya melihat Asset PT menu dan data → login sebagai Pimpinan → verify bisa melihat semua aset (read-only).

**Acceptance Scenarios**:

1. **Given** Admin LPK login, **When** Admin mengakses menu Asset, **Then** sistem hanya menampilkan menu "Asset LPK" dan hanya aset dengan entity='LPK' yang terlihat di daftar.

2. **Given** Admin PT login, **When** Admin mengakses menu Asset, **Then** sistem hanya menampilkan menu "Asset PT" dan hanya aset dengan entity='PT' yang terlihat.

3. **Given** Pimpinan login, **When** Pimpinan mengakses Dashboard Aset, **Then** sistem menampilkan statistik gabungan PT dan LPK, dengan filter untuk melihat per entitas, tetapi tanpa aksi edit/delete.

4. **Given** Admin LPK mencoba mengakses URL langsung aset PT, **When** Request diproses, **Then** sistem menolak dengan 403 Forbidden atau menampilkan "Asset not found".

---

### User Story 5 - Asset Reporting and Statistics (Priority: P3)

Pimpinan dan Admin dapat melihat laporan dan statistik aset seperti total nilai aset, jumlah aset per kategori, kondisi aset (baik vs rusak), dan aset yang assigned vs available.

**Why this priority**: Nice to have untuk manajemen dan decision making, tetapi operational tracking (US1-4) lebih penting di fase awal.

**Independent Test**: Pimpinan login → akses Dashboard Aset → lihat widget statistik (total aset, nilai, kondisi breakdown) → filter per entitas → export laporan Excel → verify data sesuai dengan inventory.

**Acceptance Scenarios**:

1. **Given** Admin mengakses Dashboard Aset, **When** Dashboard dimuat, **Then** sistem menampilkan widget: Total aset, Total nilai (sum dari nilai pembelian), Breakdown kondisi (Baik: X, Rusak: Y), Breakdown assignment (Assigned: A, Available: B).

2. **Given** Pimpinan melihat laporan, **When** Pimpinan memilih filter "LPK only", **Then** semua statistik dan grafik hanya menampilkan data aset LPK.

3. **Given** Admin ingin laporan tertulis, **When** Admin klik "Export to Excel", **Then** sistem generate file Excel dengan semua data aset (nama, jumlah, satuan, kondisi, nilai, tahun, keterangan) sesuai scope akses user.

---

### Edge Cases

- **Aset dengan jumlah 0**: Bagaimana sistem menandai aset yang sudah habis atau dipindahkan? Haruskah di-archive atau tetap visible dengan jumlah 0?
- **Duplicate asset names**: Jika ada 2 Laptop Lenovo yang berbeda, bagaimana sistem membedakannya? Perlu nomor inventaris unik?
- **Transfer asset antar entitas**: Apakah bisa terjadi transfer aset dari LPK ke PT atau sebaliknya? Siapa yang bisa approve?
- **Bulk import**: Jika sudah ada inventory list di Excel, apakah perlu fitur import massal untuk onboarding data existing?
- **Photo/documentation**: Apakah aset perlu foto atau dokumen pendukung (invoice, warranty)?
- **Depreciation**: Apakah perlu tracking nilai depresiasi aset seiring waktu?
- **Multi-location tracking**: Apakah aset bisa berada di lokasi fisik berbeda (cabang, ruangan)?

## Requirements *(mandatory)*

### Functional Requirements

#### Asset Management Core

- **FR-001**: Sistem MUST menyediakan CRUD (Create, Read, Update, Delete) untuk aset kantor dengan fields: nama barang, jumlah, satuan, kondisi, tahun pembelian/pembuatan, nilai pembelian, nomor inventaris, keterangan/notes.
- **FR-002**: Sistem MUST secara otomatis mengisi field `entity` (PT atau LPK) berdasarkan user yang login saat menambah aset baru.
- **FR-003**: Sistem MUST menyediakan field kondisi dengan options: "Baik" dan "Rusak" dengan badge visual (hijau untuk Baik, merah untuk Rusak).
- **FR-004**: Sistem MUST menyediakan field kategori aset untuk pengelompokan: Elektronik, Furniture, Dokumen/Ijin, Perlengkapan Kantor, Kendaraan, Lainnya.
- **FR-005**: Sistem MUST generate nomor inventaris unik untuk setiap aset dengan format: [PT/LPK]-[KATEGORI]-[TAHUN]-[SEQUENCE] (contoh: LPK-ELK-2024-001).

#### Search and Filtering

- **FR-006**: Sistem MUST menyediakan pencarian aset berdasarkan nama barang, nomor inventaris, atau kategori.
- **FR-007**: Sistem MUST menyediakan filter untuk: kondisi (Baik/Rusak), kategori, tahun pembelian, status assignment (Assigned/Available).
- **FR-008**: Sistem MUST menampilkan total jumlah aset dan total nilai aset di header tabel inventory.

#### Asset Assignment

- **FR-009**: Sistem MUST memungkinkan Admin untuk assign aset ke karyawan tertentu dengan tracking tanggal assignment.
- **FR-010**: Sistem MUST mencegah assignment aset yang sudah di-assign ke karyawan lain (harus return dulu).
- **FR-011**: Sistem MUST mencatat tanggal return ketika aset dikembalikan dan mengubah status aset menjadi "Available".
- **FR-012**: Karyawan MUST bisa melihat daftar aset yang assigned kepada mereka di halaman profile/dashboard mereka.

#### Condition Updates and History

- **FR-013**: Sistem MUST memungkinkan Admin untuk update kondisi aset dengan menambahkan keterangan/alasan perubahan.
- **FR-014**: Sistem MUST mencatat riwayat perubahan kondisi (audit trail) dengan timestamp, user yang mengubah, kondisi lama, kondisi baru, dan keterangan.
- **FR-015**: Sistem MUST menampilkan badge peringatan jika aset dalam kondisi "Rusak" lebih dari 30 hari tanpa update.

#### Entity Isolation & Security

- **FR-016**: Sistem MUST memastikan Admin LPK hanya bisa melihat dan mengelola aset dengan entity='LPK'.
- **FR-017**: Sistem MUST memastikan Admin PT hanya bisa melihat dan mengelola aset dengan entity='PT'.
- **FR-018**: Sistem MUST memungkinkan Pimpinan untuk melihat aset dari kedua entitas (read-only, tanpa edit/delete).
- **FR-019**: Sistem MUST menggunakan policy authorization untuk enforce entity-based access control di semua operasi CRUD.

#### Reporting and Export

- **FR-020**: Sistem MUST menyediakan dashboard widget dengan statistik: total aset, total nilai, breakdown kondisi, breakdown kategori, breakdown assignment status.
- **FR-021**: Sistem MUST memungkinkan export data aset ke format Excel dengan semua fields.
- **FR-022**: Sistem MUST menyediakan filter tanggal untuk laporan (aset yang dibeli dalam periode tertentu).

#### Data Validation

- **FR-023**: Field jumlah MUST berupa angka positif (tidak boleh negatif atau nol kecuali untuk archive).
- **FR-024**: Tahun pembelian MUST berupa tahun valid (tidak boleh tahun masa depan, tidak boleh sebelum 1900).
- **FR-025**: Nilai pembelian MUST berupa angka positif atau nol (untuk aset donasi/gratis).
- **FR-026**: Nomor inventaris MUST unik di seluruh sistem (tidak boleh duplicate).

#### Soft Delete & Audit

- **FR-027**: Sistem MUST menggunakan soft delete untuk aset (deleted_at) agar data historis tetap ada.
- **FR-028**: Sistem MUST mencatat semua perubahan data aset menggunakan activity log (created, updated, deleted, kondisi changes, assignments).
- **FR-029**: Sistem MUST menampilkan "Last updated by [nama user] on [tanggal]" di detail halaman aset.

### Key Entities

- **Asset**: Representasi inventaris aset kantor
  - Attributes: id, entity (PT/LPK), kategori, nama_barang, deskripsi, nomor_inventaris (unique), jumlah, satuan, kondisi (Baik/Rusak), status_assignment (Assigned/Available), tahun_pembelian, nilai_pembelian, lokasi, keterangan, created_by, updated_by, timestamps, soft_deletes
  - Relationships: belongsTo User (creator), belongsTo User (updater), hasMany AssetAssignments, hasMany AssetConditionHistories

- **AssetAssignment**: Penugasan aset ke karyawan
  - Attributes: id, asset_id (FK), employee_id (FK to karyawan_lpk or users), assigned_by (FK to users), assigned_date, return_date (nullable), return_notes (nullable), timestamps
  - Relationships: belongsTo Asset, belongsTo Employee (polymorphic or specific), belongsTo User (assigner)

- **AssetConditionHistory**: Riwayat perubahan kondisi aset
  - Attributes: id, asset_id (FK), old_condition, new_condition, reason/notes, changed_by (FK to users), changed_at (timestamp)
  - Relationships: belongsTo Asset, belongsTo User (changer)

- **AssetCategory**: Kategori aset (optional enum atau lookup table)
  - Options: Elektronik, Furniture, Dokumen/Ijin, Perlengkapan Kantor, Kendaraan, Lainnya

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambah aset baru lengkap dengan semua field dalam waktu kurang dari 1 menit per item.
- **SC-002**: Sistem dapat menampilkan daftar 1000+ aset dengan pencarian dan filtering dalam waktu kurang dari 2 detik.
- **SC-003**: 100% aset otomatis ter-isolasi berdasarkan entity tanpa kebocoran data antar PT dan LPK.
- **SC-004**: Semua perubahan kondisi aset tercatat dalam audit log dengan lengkap (who, when, what changed, why).
- **SC-005**: Admin dapat assign aset ke karyawan dan karyawan dapat melihat aset mereka dalam waktu kurang dari 30 detik.
- **SC-006**: Export laporan Excel untuk 500+ aset selesai dalam waktu kurang dari 5 detik.
- **SC-007**: Dashboard statistik aset dapat dimuat dalam waktu kurang dari 1 detik dengan data real-time.
- **SC-008**: 95% admin berhasil menyelesaikan task "tambah aset baru dan assign ke karyawan" tanpa bantuan dalam percobaan pertama.
- **SC-009**: Nomor inventaris unik ter-generate otomatis tanpa collision untuk 10,000+ aset.
- **SC-010**: Sistem mendukung minimal 50 concurrent users melakukan operasi CRUD aset tanpa degradasi performa.

## Assumptions *(generated)*

1. **Entity Assignment**: Aset selalu belong to satu entitas (PT atau LPK) dan tidak bisa transfer antar entitas (atau jika bisa, perlu approval khusus - out of scope untuk MVP).

2. **Employee Reference**: Asset assignment akan reference ke tabel `karyawan_lpk` untuk LPK employees dan `users` table untuk PT employees (atau perlu unified employee table).

3. **Depreciation**: Nilai depresiasi aset tidak di-track di MVP. Nilai pembelian adalah nilai tetap. Depreciation tracking bisa ditambah di fase berikutnya jika dibutuhkan accounting.

4. **Photo Upload**: MVP tidak termasuk upload foto aset. Bisa ditambah di fase enhancement jika dibutuhkan. Keterangan text dianggap cukup untuk MVP.

5. **Location Tracking**: Aset dianggap berada di satu lokasi primary (kantor PT atau LPK). Multi-location tracking dengan detail ruangan/cabang bisa ditambah kemudian.

6. **Bulk Import**: Tidak ada bulk import di MVP. Admin input manual atau bisa ditambah CSV import di fase berikutnya untuk onboarding data existing.

7. **Maintenance Schedule**: MVP hanya track kondisi changes manually. Scheduled maintenance reminders atau preventive maintenance tracking bisa ditambah kemudian.

8. **Asset Disposal**: Aset yang dibuang/dijual akan di-soft delete. Detail disposal process (approval, sale value, disposal reason) bisa diperkaya di enhancement.

9. **Warranty Tracking**: Informasi warranty (tanggal mulai, durasi, vendor) tidak di-track di MVP. Bisa ditambah sebagai enhancement.

10. **QR Code/Barcode**: MVP tidak include barcode/QR code generation untuk physical labeling. Bisa ditambah untuk easier mobile scanning di future.

## Dependencies

- **Module 001 - User Management & RBAC**: Required untuk role-based access control (Admin LPK, Admin PT, Pimpinan permissions).
- **Module 002 - Karyawan LPK**: Required untuk employee reference dalam asset assignment ke karyawan LPK.
- **EntityType Enum**: Required dari module 001 untuk entity-based separation (PT vs LPK).
- **Spatie Activity Log**: Required untuk audit trail dan history tracking.
- **Filament Shield**: Required untuk permission-based access control pada Asset resources.

## Out of Scope

Berikut adalah hal-hal yang explicitly NOT included dalam MVP ini dan bisa dipertimbangkan untuk fase berikutnya:

1. **Asset Transfer Between Entities**: Transfer aset dari PT ke LPK atau sebaliknya (need approval workflow).
2. **Depreciation Calculation**: Automatic calculation of asset depreciation over time.
3. **Maintenance Scheduling**: Scheduled maintenance reminders dan preventive maintenance tracking.
4. **Photo/Document Attachments**: Upload foto aset, invoice, warranty documents.
5. **QR Code/Barcode Generation**: Physical labeling system dengan barcode/QR untuk scanning.
6. **Multi-Location Tracking**: Detailed tracking per ruangan, cabang, atau lokasi fisik lainnya.
7. **Bulk Import/Export**: CSV/Excel bulk import untuk onboarding existing data (export included, import not).
8. **Asset Reservation System**: Booking/reservation system untuk aset yang bisa dipinjam.
9. **Asset Disposal Workflow**: Detailed approval process untuk disposal/sale aset.
10. **Warranty Management**: Tracking warranty dates, claim process, vendor information.
11. **Mobile App**: Dedicated mobile app untuk scanning dan updating aset di lapangan.
12. **Integration**: Integration dengan accounting software untuk asset valuation.

## Technical Notes *(for planning phase)*

- Consider using Filament's Repeater component untuk multiple asset entry.
- Entity scoping should use Eloquent global scope atau policy untuk enforce isolation.
- Nomor inventaris generation bisa use database sequence atau helper function.
- Asset assignment might need polymorphic relationship jika reference ke multiple employee tables.
- Dashboard statistics bisa di-cache untuk improved performance.
- Consider indexes pada: entity, kategori, kondisi, status_assignment, nomor_inventaris untuk faster queries.
