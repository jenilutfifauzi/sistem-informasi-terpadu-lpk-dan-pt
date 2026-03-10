# Research Notes: Karyawan PT Management

**Feature**: 009-karyawan-pt-resource
**Date**: 2026-03-09
**Purpose**: Resolve semua keputusan teknis untuk implementasi EmployeePTResource

---

## Ringkasan

Semua keputusan teknis untuk fitur ini merupakan **turunan langsung dari implementasi EmployeeLPK yang sudah ada dan berjalan** (spec 002). Tidak ada NEEDS CLARIFICATION baru. Research di bawah mengkonfirmasi ulang setiap keputusan berdasarkan codebase aktual.

---

## 1. Audit Logging

**Decision**: `spatie/laravel-activitylog` v4.x via `LogsActivity` trait  
**Rationale**: Sudah digunakan oleh `EmployeeLPK`. Package sudah terinstall. Activity log table sudah ada.  
**Alternatives considered**: Custom observer — ditolak karena menambah kode duplikat yang tidak perlu.

**Implementasi** (identik dengan EmployeeLPK, ganti label):

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn (string $eventName) => "Karyawan PT {$eventName}: {$this->nama_lengkap}");
}
```

---

## 2. File Upload — Dokumen Kepegawaian

**Decision**: `FileUpload::make('dokumen_path')` dengan `disk('private')`, `acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])`, `maxSize(5120)`.  
**Directory**: `documents/` (berbeda dari `certificates/` milik LPK untuk isolasi storage).  
**Authorization**: Custom route dengan Policy gate check (identik dengan pola sertifikat LPK).  
**Alternatives considered**: Public disk — ditolak karena dokumen kepegawaian bersifat sensitif (kontrak kerja, KTP, SK).

```php
Forms\Components\FileUpload::make('dokumen_path')
    ->label('Dokumen Kepegawaian')
    ->disk('private')
    ->directory('documents')
    ->visibility('private')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(5120)
    ->preserveFilenames(),
```

---

## 3. Enum Patterns

**Decision**: PHP 8.1 backed string enums implementing `HasLabel` (pola identik dengan `JabatanLPK`, `StatusKepegawaian`).  
**Rationale**: Consistency dengan codebase. Filament mendukung enum as options secara native via `->options(JabatanPT::class)`.

**JabatanPT enum**:

```php
enum JabatanPT: string implements HasLabel
{
    case Direktur = 'Direktur';
    case Manajer = 'Manajer';
    case StafHRD = 'Staf HRD';
    case StafKeuangan = 'Staf Keuangan';
    case StafOperasional = 'Staf Operasional';
    case StafAdministrasi = 'Staf Administrasi';

    public function getLabel(): string { return $this->value; }
}
```

**DivisiPT enum**:

```php
enum DivisiPT: string implements HasLabel
{
    case Manajemen = 'Manajemen';
    case HRD = 'HRD';
    case Keuangan = 'Keuangan';
    case Operasional = 'Operasional';
    case Administrasi = 'Administrasi';

    public function getLabel(): string { return $this->value; }
}
```

**JenisKontrak enum**:

```php
enum JenisKontrak: string implements HasLabel
{
    case Tetap = 'Tetap';
    case PKWT = 'PKWT';
    case Probasi = 'Probasi';

    public function getLabel(): string { return $this->value; }
}
```

---

## 4. Soft Delete Filter in Filament Tables

**Decision**: `TrashedFilter::make()` bawaan Filament (identik dengan EmployeeLPKResource).  
**Rationale**: Zero custom code, behavior sudah proven di resource LPK.  
**Label**: `->label('Tampilkan Data Resign')` untuk konteks yang familiar bagi user.

---

## 5. Entity Auto-Assignment (Defense-in-Depth)

**Decision**: Tiga layer pertahanan untuk memastikan `entity='PT'`:

| Layer | Implementasi |
|-------|-------------|
| DB level | `$table->enum('entity', ['PT', 'LPK'])->default('PT')` |
| Model level | `boot()` → `static::creating(fn($e) => $e->entity = EntityType::PT)` |
| Form level | `Forms\Components\Hidden::make('entity')->default('PT')` |

**Alternatives considered**: Single layer (model only) — ditolak karena tidak konsisten dengan pola LPK yang sudah ada.

---

## 6. Export CSV

**Decision**: `Maatwebsite\Excel` dengan class `EmployeePTExport`, format CSV.  
**Rationale**: Sudah digunakan oleh `EmployeeLPKExport`. Package sudah terinstall.  
**Pola**: `headerActions` di tabel resource, dengan activity logging setelah export (identik dengan EmployeeLPKResource).

---

## 7. RBAC Scoping

**Decision**:

| Role | Akses |
|------|-------|
| `super_admin` | Full CRUD + view semua |
| `Admin PT` | Full CRUD |
| `Keuangan PT` | View only (read-only) |
| `Pimpinan` | View only (read-only, sudah ada di RBAC) |
| `Admin LPK` | **Tidak ada akses** |
| `Keuangan LPK` | **Tidak ada akses** |

**Implementasi**: `EmployeePTPolicy` dengan method `viewAny()`, `view()`, `create()`, `update()`, `delete()`, `restore()`.

---

## 8. Navigation & URL Slug

**Decision**:
- `protected static ?string $slug = 'karyawan-pts'` → URL: `/admin/karyawan-pts`
- `protected static ?int $navigationSort = 2` (setelah Karyawan LPK yang sort=1)
- Grup navigasi: `'Data Master'` (sama dengan Karyawan LPK)

---

## 9. Database Migration Timestamp

**Decision**: Gunakan timestamp `2026_03_09` dengan suffix `_000001` untuk ordering.  
**Tabel**: `karyawan_pt` (konsisten dengan konvensi `karyawan_lpk`).

---

## Kesimpulan

Tidak ada keputusan teknis yang memerlukan research tambahan atau persetujuan eksternal. Semua pola sudah proven dalam codebase. Implementasi adalah adaptasi langsung dari EmployeeLPK dengan penyesuaian domain (PT vs LPK, jabatan baru, divisi baru, gaji vs honor).
