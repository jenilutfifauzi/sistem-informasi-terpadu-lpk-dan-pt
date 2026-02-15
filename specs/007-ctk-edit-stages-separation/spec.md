# Feature Specification: CTK Edit Stages Separation

**Feature Branch**: `007-ctk-edit-stages-separation`  
**Created**: 2026-02-15  
**Status**: Draft  
**Input**: User description: "perbaiki edit ctk pisah dari section berkas: paspor harus ada input no paspor di edit ctk (Stage 4), (stage 8 ijin desa, stage 9 rekomendasi, stage 10 working permit) dan belum ada pada stage 6 screening 1, stage 7 interview user"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Pisahkan Section Paspor dari Berkas dengan Input Nomor Paspor (Priority: P1)

Saat ini section "3-4. Dokumen CTK (Soal/Berkas & Paspor)" menggabungkan dua stage yang berbeda ke dalam satu section. Admin perlu bisa mengelola data paspor secara terpisah dari dokumen soal/berkas. Section Paspor (Stage 4) harus memiliki input field khusus untuk **Nomor Paspor** serta tetap bisa upload dokumen paspor.

**Why this priority**: Nomor paspor adalah data kritis yang saat ini tidak bisa diinput melalui form edit CTK meskipun field sudah ada di model. Memisahkan section juga membuat navigasi form lebih jelas sesuai alur 15 tahapan CTK.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section terpisah "4. Paspor" dengan input nomor paspor, dan section "3. Soal/Berkas" hanya mengelola dokumen soal/berkas.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section terpisah "3. Soal/Berkas" dan "4. Paspor"
2. **Given** admin berada di section "4. Paspor", **When** admin melihat form fields, **Then** ada input field "Nomor Paspor" yang bisa diisi
3. **Given** admin mengisi nomor paspor dan menyimpan, **When** data disimpan, **Then** nomor paspor tersimpan dengan benar pada data CTK
4. **Given** CTK sudah memiliki nomor paspor tersimpan, **When** admin membuka Edit CTK, **Then** nomor paspor tampil di input field section Paspor
5. **Given** admin berada di section "3. Soal/Berkas", **When** admin melihat form, **Then** hanya ada repeater dokumen tanpa field nomor paspor dan judul section tidak menyebut "Paspor"

---

### User Story 2 - Section Terpisah untuk Screening 1 (Stage 6) (Priority: P1)

Saat ini section "6-7. Screening Interview" menggabungkan dua stage yang berbeda. Admin perlu section terpisah untuk Stage 6 (Screening 1) agar bisa mencatat proses screening pertama secara independen dengan data pewawancara, tanggal, lokasi, dan hasil screening.

**Why this priority**: Screening 1 dan Interview User adalah dua proses berbeda dengan pelaku dan konteks yang berbeda. Pemisahan ini memungkinkan tracking progress yang lebih akurat per stage.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section "6. Screening 1" yang terpisah dari "7. Interview User", dengan data screening bisa diinput secara mandiri.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section "6. Screening 1" yang terpisah dari section Interview User
2. **Given** admin berada di section "6. Screening 1", **When** admin menambahkan data screening, **Then** bisa mengisi pewawancara, tanggal, lokasi, hasil (Lolos/Tidak Lolos), dan catatan
3. **Given** admin menyimpan data screening 1, **When** data tersimpan, **Then** status Stage 6 otomatis terupdate pada progress checklist
4. **Given** CTK sudah memiliki data screening 1, **When** admin membuka Edit CTK, **Then** data screening 1 tampil di section yang benar

---

### User Story 3 - Section Terpisah untuk Interview User (Stage 7) (Priority: P1)

Admin perlu section terpisah untuk Stage 7 (Interview User) agar proses interview dengan user/perusahaan penerima bisa dicatat secara independen dari Screening 1.

**Why this priority**: Interview User melibatkan pihak perusahaan penerima, berbeda dari Screening 1 yang internal. Pemisahan memungkinkan pencatatan yang lebih detail dan tracking progres yang akurat.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section "7. Interview User" yang terpisah, dengan data interview bisa diinput secara mandiri.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section "7. Interview User" yang terpisah dari section Screening 1
2. **Given** admin berada di section "7. Interview User", **When** admin menambahkan data interview, **Then** bisa mengisi pewawancara, tanggal, lokasi, hasil (Lolos/Tidak Lolos), dan catatan
3. **Given** admin menyimpan data interview user, **When** data tersimpan, **Then** status Stage 7 otomatis terupdate pada progress checklist
4. **Given** screening 1 belum diisi tapi interview user sudah diisi, **When** admin melihat progress, **Then** Stage 6 masih menunjukkan belum complete dan Stage 7 menunjukkan complete

---

### User Story 4 - Section Baru untuk Ijin Desa (Stage 8) (Priority: P2)

Admin perlu section khusus untuk Stage 8 (Ijin Desa) agar bisa mencatat status ijin desa dan upload dokumen terkait. Saat ini tidak ada section form untuk stage ini di halaman Edit CTK.

**Why this priority**: Model CTK sudah memiliki field status untuk ijin desa, namun admin tidak bisa mengelola data ini melalui form karena belum ada section-nya.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section "8. Ijin Desa" dengan status toggle dan area upload dokumen.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section "8. Ijin Desa"
2. **Given** admin berada di section "8. Ijin Desa", **When** admin mengubah status, **Then** bisa memilih status "Ada" atau "Belum Ada"
3. **Given** admin set status ijin desa menjadi "Ada" dan upload dokumen Ijin Desa, **When** data disimpan, **Then** Stage 8 pada progress checklist menunjukkan complete (membutuhkan status "Ada" DAN dokumen terupload)
4. **Given** admin berada di section "8. Ijin Desa", **When** admin ingin upload dokumen, **Then** bisa upload dokumen dengan tipe "Ijin Desa" melalui repeater dokumen

---

### User Story 5 - Section Baru untuk Rekomendasi (Stage 9) (Priority: P2)

Admin perlu section khusus untuk Stage 9 (Rekomendasi) agar bisa mencatat status dokumen rekomendasi dan upload file terkait. Saat ini tidak ada section form untuk stage ini di halaman Edit CTK.

**Why this priority**: Model CTK sudah memiliki field status untuk rekomendasi, tapi belum bisa diakses melalui UI form.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section "9. Rekomendasi" dengan status toggle dan area upload dokumen.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section "9. Rekomendasi"
2. **Given** admin berada di section "9. Rekomendasi", **When** admin mengubah status, **Then** bisa memilih status "Ada" atau "Belum Ada"
3. **Given** admin set status rekomendasi menjadi "Ada" dan upload dokumen rekomendasi, **When** data disimpan, **Then** Stage 9 pada progress checklist menunjukkan complete
4. **Given** admin set status "Ada" tanpa upload dokumen, **When** admin melihat progress, **Then** Stage 9 tetap belum complete (membutuhkan status Ada DAN dokumen terupload)

---

### User Story 6 - Pisahkan Section Working Permit dari Visa (Stage 10) (Priority: P2)

Admin perlu section khusus untuk Stage 10 (Working Permit/WP) yang dipisahkan dari section Visa. Saat ini WP digabung dengan Visa dan Apply Visa dalam satu section "10-11-13. Visa & Working Permit".

**Why this priority**: Working Permit diproses terpisah dari Visa. Penggabungan dalam satu section membuat navigasi membingungkan dan tidak sesuai alur 15 tahapan CTK.

**Independent Test**: Buka halaman Edit CTK, pastikan ada section "10. Working Permit" yang terpisah dari section Visa.

**Acceptance Scenarios**:

1. **Given** admin membuka halaman Edit CTK, **When** form dimuat, **Then** terdapat section "10. Working Permit" yang terpisah dari section Visa
2. **Given** admin berada di section "10. Working Permit", **When** admin mengubah status, **Then** bisa memilih status "Lengkap" atau "Belum Lengkap"
3. **Given** admin set status WP "Lengkap", **When** data disimpan, **Then** Stage 10 pada progress checklist menunjukkan complete (stage completion hanya memerlukan `wp_status = 'Lengkap'`; upload dokumen WP diperlukan oleh AdvanceStageAction namun bukan oleh stage completion badge)
4. **Given** section Working Permit sudah terpisah, **When** admin melihat section Visa, **Then** section Visa hanya mengelola Stage 11 (Apply Visa) dan Stage 13 (Visa Terbit)

---

### Edge Cases

- Apa yang terjadi ketika admin menghapus nomor paspor yang sudah tersimpan? Nomor paspor bisa dikosongkan karena CTK mungkin belum memiliki paspor di stage awal.
- Bagaimana jika dokumen paspor diupload tapi nomor paspor belum diisi? Stage 4 tetap belum complete — membutuhkan nomor paspor terisi.
- Bagaimana filtering screening records berdasarkan stage (Screening 1 vs Interview User)? Gunakan field `screening_stage` yang **akan ditambahkan** melalui migrasi ke tabel `c_t_k_screenings` untuk memfilter per section.
- Apa yang terjadi dengan data yang sudah diinput di section gabungan sebelumnya? Data tetap utuh karena hanya UI section yang berubah, bukan struktur data.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem HARUS memisahkan section "3-4. Dokumen CTK" menjadi dua section terpisah: "3. Soal/Berkas" dan "4. Paspor"
- **FR-002**: Section "4. Paspor" HARUS memiliki input field khusus untuk Nomor Paspor (text input) yang tersimpan ke data CTK
- **FR-003**: Section "4. Paspor" HARUS tetap memiliki kemampuan upload dokumen paspor melalui repeater yang terfilter hanya untuk tipe dokumen Paspor
- **FR-004**: Section "3. Soal/Berkas" HARUS hanya menampilkan repeater dokumen yang terfilter untuk tipe dokumen Soal/Berkas
- **FR-005**: Sistem HARUS memisahkan section "6-7. Screening Interview" menjadi dua section terpisah: "6. Screening 1" dan "7. Interview User"
- **FR-006**: Section "6. Screening 1" HARUS menampilkan hanya data screening untuk stage Screening 1
- **FR-007**: Section "7. Interview User" HARUS menampilkan hanya data screening untuk stage Interview User
- **FR-008**: Sistem HARUS menambahkan section baru "8. Ijin Desa" dengan field status (Ada/Belum Ada) dan kemampuan upload dokumen Ijin Desa
- **FR-009**: Sistem HARUS menambahkan section baru "9. Rekomendasi" dengan field status (Ada/Belum Ada) dan kemampuan upload dokumen Rekomendasi
- **FR-010**: Sistem HARUS memisahkan Working Permit dari section Visa, membuat section baru "10. Working Permit" dengan field status (Lengkap/Belum Lengkap) dan upload dokumen
- **FR-011**: Section Visa yang tersisa HARUS diubah menjadi "11-13. Apply Visa & Visa" tanpa mencantumkan data Working Permit
- **FR-012**: Setiap section HARUS menampilkan status badge yang menunjukkan complete/incomplete untuk stage terkait
- **FR-013**: Urutan section di dalam form Edit CTK HARUS mengikuti urutan stage 1-15 sesuai alur CTK

### Key Entities

- **CTK**: Entitas utama Calon Tenaga Kerja, memiliki field nomor paspor, status ijin desa, status rekomendasi, dan status WP yang perlu diekspos melalui form sections terpisah
- **CTKDocument**: Dokumen terlampir pada CTK, memiliki tipe dokumen yang digunakan untuk memfilter dokumen per section (Soal/Berkas, Paspor, Ijin Desa, Rekomendasi, Working Permit)
- **CTKScreening**: Record screening/interview, memiliki penanda stage yang menentukan apakah record adalah Screening 1 atau Interview User
- **Form Sections**: Komponen UI yang mendefinisikan layout form per stage — perlu dipisah dari class gabungan menjadi class per stage

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat mengisi nomor paspor langsung dari section terpisah "4. Paspor" di halaman Edit CTK tanpa kebingungan navigasi
- **SC-002**: Setiap stage (3, 4, 6, 7, 8, 9, 10) memiliki section form yang terpisah dan independen di halaman Edit CTK
- **SC-003**: Data yang sudah ada sebelumnya (dokumen, screening records) tetap tampil dengan benar setelah pemisahan section
- **SC-004**: Progress checklist (15 tahapan) tetap akurat — setiap stage menunjukkan status complete/incomplete berdasarkan data di section masing-masing
- **SC-005**: Admin menyelesaikan pengisian data untuk satu stage dalam waktu kurang dari 2 menit karena section yang jelas dan terstruktur
- **SC-006**: Tidak ada data hilang atau corrupt selama perubahan dari layout section gabungan ke section terpisah
- **SC-007**: 100% data screening lama tetap ter-assign dengan benar ke section Screening 1 atau Interview User

## Assumptions

- Field nomor paspor sudah tersedia di model CTK dan tabel database, hanya belum diekspos di form Edit CTK
- Field status ijin desa, status rekomendasi, dan status WP sudah tersedia di model CTK
- Model CTKScreening **belum** memiliki field `screening_stage` — kolom ini perlu ditambahkan melalui migrasi database baru. Enum `ScreeningStage` sudah ada di codebase.
- Enum tipe dokumen sudah memiliki values untuk Ijin Desa, Rekomendasi, dan Working Permit
- Perubahan ini **sebagian besar** menyangkut layout form (UI), namun juga memerlukan satu migrasi database untuk menambahkan kolom `screening_stage` pada tabel `c_t_k_screenings` beserta data backfill
- Data yang sudah diinput melalui section gabungan sebelumnya tetap kompatibel dengan layout baru
