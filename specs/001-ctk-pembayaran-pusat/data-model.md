# Data Model: Pembayaran ke Pusat

**Feature**: 001-ctk-pembayaran-pusat  
**Date**: 2026-04-05

## Entity: PembayaranPusat

### Table: `pembayaran_pusat`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| entity | enum('LPK','PT') | NOT NULL, index | Entity isolation (redundant for query perf) |
| ctk_id | bigint unsigned | FK → ctk.id, NOT NULL, index | Reference to CTK |
| tanggal_pembayaran | date | NOT NULL | Payment date (max: today) |
| nominal | decimal(15,2) | NOT NULL, > 0 | Payment amount in IDR |
| bukti_transfer_path | varchar(500) | NULLABLE | File path for transfer proof |
| keterangan | text | NULLABLE | Optional notes |
| created_by | bigint unsigned | FK → users.id, NOT NULL | User who created record |
| updated_by | bigint unsigned | FK → users.id, NULLABLE | User who last updated |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |
| deleted_at | timestamp | NULLABLE, index | Soft delete timestamp |

### Indexes

```sql
-- Primary key
PRIMARY KEY (id)

-- Foreign keys
FOREIGN KEY (ctk_id) REFERENCES ctk(id) ON DELETE RESTRICT
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL

-- Query optimization
INDEX idx_pembayaran_pusat_entity (entity)
INDEX idx_pembayaran_pusat_ctk_id (ctk_id)
INDEX idx_pembayaran_pusat_tanggal (tanggal_pembayaran)
INDEX idx_pembayaran_pusat_deleted_at (deleted_at)

-- Composite for common queries
INDEX idx_pembayaran_pusat_entity_tanggal (entity, tanggal_pembayaran)
```

## Relationships

```
┌─────────────────┐       ┌─────────────────┐
│      User       │       │       CTK       │
│  (existing)     │       │   (existing)    │
└────────┬────────┘       └────────┬────────┘
         │                         │
         │ created_by              │ ctk_id
         │ updated_by              │
         ▼                         ▼
┌─────────────────────────────────────────────┐
│            PembayaranPusat                  │
│  - entity (LPK/PT)                          │
│  - tanggal_pembayaran                       │
│  - nominal                                  │
│  - bukti_transfer_path                      │
│  - keterangan                               │
└─────────────────────────────────────────────┘
```

### Eloquent Relationships

```php
// PembayaranPusat Model
class PembayaranPusat extends Model
{
    // Belongs to CTK
    public function ctk(): BelongsTo
    {
        return $this->belongsTo(CTK::class);
    }

    // Belongs to User (creator)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Belongs to User (updater)
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

// CTK Model (add inverse)
class CTK extends Model
{
    public function pembayaranPusat(): HasMany
    {
        return $this->hasMany(PembayaranPusat::class);
    }
}
```

## Validation Rules

| Field | Rules |
|-------|-------|
| entity | required, in:LPK,PT |
| ctk_id | required, exists:ctk,id |
| tanggal_pembayaran | required, date, before_or_equal:today |
| nominal | required, numeric, min:1 |
| bukti_transfer_path | nullable, file, mimes:jpg,jpeg,png,pdf, max:10240 |
| keterangan | nullable, string, max:1000 |

## Casts

```php
protected function casts(): array
{
    return [
        'entity' => Entity::class,  // Use existing Entity enum
        'tanggal_pembayaran' => 'date',
        'nominal' => 'decimal:2',
    ];
}
```

## Fillable Attributes

```php
protected $fillable = [
    'entity',
    'ctk_id',
    'tanggal_pembayaran',
    'nominal',
    'bukti_transfer_path',
    'keterangan',
    'created_by',
    'updated_by',
];
```

## Migration

```php
Schema::create('pembayaran_pusat', function (Blueprint $table) {
    $table->id();
    $table->enum('entity', ['LPK', 'PT'])->index();
    $table->foreignId('ctk_id')->constrained('ctk')->restrictOnDelete();
    $table->date('tanggal_pembayaran');
    $table->decimal('nominal', 15, 2);
    $table->string('bukti_transfer_path', 500)->nullable();
    $table->text('keterangan')->nullable();
    $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();

    // Composite index for common queries
    $table->index(['entity', 'tanggal_pembayaran']);
});
```

## Query Patterns

### List with eager loading
```php
PembayaranPusat::query()
    ->with(['ctk:id,nama_lengkap,nik', 'creator:id,name'])
    ->orderByDesc('tanggal_pembayaran')
    ->paginate();
```

### Summary statistics
```php
PembayaranPusat::query()
    ->selectRaw('COUNT(*) as total_transaksi')
    ->selectRaw('SUM(nominal) as total_nominal')
    ->selectRaw('AVG(nominal) as rata_rata')
    ->whereMonth('tanggal_pembayaran', now()->month)
    ->first();
```

### Per CTK totals
```php
PembayaranPusat::query()
    ->select('ctk_id')
    ->selectRaw('COUNT(*) as jumlah_pembayaran')
    ->selectRaw('SUM(nominal) as total_nominal')
    ->groupBy('ctk_id')
    ->get();
```
