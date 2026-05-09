# Data Model: Data Siswa LPK Administration

## Entity: SiswaLPK

**Purpose**: Represents one Siswa LPK registration record captured from the administrative registration sheet.

### Fields

| Field | Type | Required | Rules | Notes |
|-------|------|----------|-------|-------|
| `id` | bigint | yes | primary key | Internal identifier |
| `nomor_urut` | unsigned integer | no | nullable, non-negative | Display and ordering aid from source sheet |
| `nomor_induk` | string | yes | unique, max 50 | Primary business identifier |
| `nama_siswa` | string | yes | max 255 | Student full name |
| `jenis_kelamin` | string | yes | one of `L`, `P` | Stored using source sheet semantics |
| `agama` | string | no | max 100 | Free-text or constrained select during implementation |
| `pendidikan_terakhir` | string | no | max 100 | Latest education level |
| `tanggal_masuk` | date | yes | must be on or after `tanggal_lahir` | Enrollment date |
| `tempat_lahir` | string | yes | max 255 | Birth place only |
| `tanggal_lahir` | date | yes | must be on or before `tanggal_masuk` and not in the future | Birth date only |
| `alamat` | text | yes | non-empty | Student address |
| `no_hp` | string | yes | max 25 | Primary phone number |
| `email` | string | no | nullable, valid email, max 255 | Optional because source data may omit it |
| `program_pendidikan` | string | yes | max 255 | Searchable program name |
| `created_by` | foreign key | no | nullable, references `users.id` | Actor who created the record |
| `updated_by` | foreign key | no | nullable, references `users.id` | Actor who last updated the record |
| `created_at` | timestamp | yes | managed by Laravel | Audit timestamp |
| `updated_at` | timestamp | yes | managed by Laravel | Audit timestamp |
| `deleted_at` | timestamp | no | nullable soft delete | Retention and recovery support |

### Relationships

- `creator`: belongs to `User` via `created_by`
- `updater`: belongs to `User` via `updated_by`

### Validation Rules

- `nomor_induk` must be globally unique across active and soft-deleted records.
- `nama_siswa`, `jenis_kelamin`, `tanggal_masuk`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_hp`, and `program_pendidikan` are required.
- `email` is optional but must be valid when present.
- `tanggal_lahir` must not be later than `tanggal_masuk`.
- `nomor_urut` cannot be negative when present.

### Indexing

- Unique index on `nomor_induk`
- Standard index on `nama_siswa`
- Standard index on `program_pendidikan`
- Optional composite index on `deleted_at, nomor_induk` if restore and trashed listing usage grows

### State Transitions

- `Created` → active record is available in admin listings and detail views
- `Updated` → active record retains same identity while field values change
- `SoftDeleted` → record is hidden from default listings but retained for audit/compliance
- `Restored` → previously deleted record returns to active state if restore is later enabled

## Supporting Value Objects

### JenisKelamin

- Domain values: `L`, `P`
- Used for validation, filtering, and badge display in the table view
