# ⚠️ DATABASE SAFETY GUIDELINES

## JANGAN PERNAH GUNAKAN COMMANDS INI:

### ❌ DILARANG - Commands yang MENGHAPUS data:
```bash
# BAHAYA! Menghapus SEMUA tabel dan data, lalu create ulang
php artisan migrate:fresh

# BAHAYA! Menghapus SEMUA tabel dan data, seed ulang
php artisan migrate:fresh --seed

# BAHAYA! Rollback sampai batch pertama (hapus hampir semua)
php artisan migrate:reset

# BAHAYA! Rollback semua, lalu migrate ulang
php artisan migrate:refresh
```

### ✅ AMAN - Commands yang TIDAK menghapus data:

```bash
# AMAN - Hanya jalankan migration baru yang belum dijalankan
php artisan migrate

# AMAN - Rollback batch terakhir saja (1 batch)
php artisan migrate:rollback

# AMAN - Rollback 2 batch terakhir
php artisan migrate:rollback --step=2

# AMAN - Melihat status migration tanpa mengubah apapun
php artisan migrate:status
```

## Testing Guidelines

### ❌ JANGAN gunakan RefreshDatabase di tests:
```php
<?php
use Illuminate\Foundation\Testing\RefreshDatabase; // JANGAN!

class MyTest extends TestCase 
{
    use RefreshDatabase; // INI AKAN HAPUS SEMUA DATA!
}
```

### ✅ GUNAKAN Database Transactions:
```php
<?php
use Illuminate\Support\Facades\DB;

class MyTest extends TestCase 
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction(); // Data akan rollback otomatis
    }

    protected function tearDown(): void
    {
        DB::rollBack(); // Semua perubahan dibatalkan
        parent::tearDown();
    }
}
```

## Cara Restore Data yang Terhapus

### Jika punya backup database:
```bash
# Restore dari backup
mysql -u username -p database_name < backup.sql
```

### Jika TIDAK ada backup:
1. **JANGAN PANIK** - Stop semua operasi database
2. Check `storage/logs/laravel.log` untuk melihat activity sebelum data hilang
3. Check Git history untuk melihat siapa yang run command terakhir
4. Jika ada Activity Log, bisa recover dari sana:
```bash
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::where('log_name', 'ctk')->latest()->take(100)->get();
```

## Best Practices

1. **Gunakan Seeder yang AMAN**:
```bash
# AMAN - SuperAdminSeeder tidak menghapus data yang ada
php artisan db:seed --class=SuperAdminSeeder

# AMAN - Hanya create roles/permissions/superadmin jika belum ada
# Tidak akan hapus user atau data yang sudah ada
```

2. **Gunakan Database Backup**:
```bash
# Backup sebelum migration
php artisan db:backup
```

2. **Testing di database terpisah**:
```env
DB_CONNECTION=mysql_testing
DB_DATABASE=sit_lpk_testing # Database testing terpisah
```

3. **Seeders hanya untuk development**:
```bash
# Jangan jalankan di production!
php artisan db:seed
```

4. **Git hook untuk mencegah migrate:fresh**:
```bash
# .git/hooks/pre-commit
if git diff --cached | grep -q "migrate:fresh"; then
    echo "WARNING: migrate:fresh detected!"
    exit 1
fi
```

## Recovery Checklist

Jika data hilang:
- [ ] Stop semua development work
- [ ] Check Git history: `git log --all --grep="migrate"`
- [ ] Check Laravel logs: `tail -n 100 storage/logs/laravel.log`
- [ ] Check Activity Log: `SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 100`
- [ ] Restore dari backup jika ada
- [ ] Document apa yang terjadi untuk mencegah terulang

## Untuk Developer Baru

**INGAT**: 
- `migrate` = Tambah tabel/kolom baru (AMAN) ✅
- `migrate:fresh` = HAPUS SEMUA lalu create ulang (BAHAYA) ❌
- `RefreshDatabase` = HAPUS SEMUA di setiap test (BAHAYA) ❌

**Jika ragu, TANYA dulu sebelum run command yang berhubungan dengan database!**
