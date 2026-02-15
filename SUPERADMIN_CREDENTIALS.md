# 🔐 Superadmin Login Credentials

## Superadmin yang Baru Dibuat

**Email:** `admin@sitlpk.com`  
**Password:** `password`  

⚠️ **PENTING**: Segera ganti password setelah login pertama kali!

---

## Roles yang Tersedia

| Role | Entity | Access Level |
|------|--------|--------------|
| **super_admin** | PT | Full access ke semua fitur |
| **Pimpinan** | PT | Read-only ke semua entity (PT + LPK) |
| **Admin PT** | PT | Full access ke data PT |
| **Admin LPK** | LPK | Full access ke data LPK |
| **Keuangan PT** | PT | Read-only ke data PT |
| **Keuangan LPK** | LPK | Read-only ke data LPK |

---

## Cara Assign Role ke User Lain

### Via Tinker:
```php
php artisan tinker

// Assign role ke user
$user = User::find(303); // Ganti dengan ID user yang diinginkan
$user->assignRole('Admin PT'); // atau 'Admin LPK', 'Pimpinan', dll

// Ganti entity user
$user->entity = 'PT'; // atau 'LPK'
$user->save();

// Cek role user
$user->getRoleNames();
```

### Via Filament UI:
1. Login sebagai superadmin
2. Buka menu **Users**
3. Edit user yang diinginkan
4. Assign role dari dropdown
5. Set entity (PT/LPK)
6. Save

---

## Menghapus Fake Users (Opsional)

Jika ingin menghapus 54 fake users yang dibuat oleh faker:

```bash
php artisan tinker
```

```php
// Hapus semua user dengan email @example.com/@example.net/@example.org
User::where('email', 'like', '%@example.%')->delete();

// Atau hapus user tanpa role
User::doesntHave('roles')->delete();
```

---

## Re-run Seeder (Jika Diperlukan)

Seeder ini **AMAN** - tidak menghapus data yang ada:

```bash
php artisan db:seed --class=SuperAdminSeeder
```

Seeder akan:
- ✅ Create roles jika belum ada
- ✅ Create permissions jika belum ada
- ✅ Assign permissions ke roles
- ✅ Create superadmin jika belum ada (skip jika sudah ada)

---

## Ganti Password Superadmin

### Via Tinker:
```bash
php artisan tinker
```

```php
$admin = User::where('email', 'admin@sitlpk.com')->first();
$admin->password = bcrypt('password_baru_yang_kuat');
$admin->save();
```

### Via Filament UI:
1. Login dengan credentials di atas
2. Klik nama Anda di pojok kanan atas
3. Pilih **Profile**
4. Ganti password
5. Save

---

## Troubleshooting

### Lupa Password Superadmin?
```bash
php artisan tinker

$admin = User::where('email', 'admin@sitlpk.com')->first();
$admin->password = bcrypt('password');
$admin->save();
```

### User tidak bisa login?
Pastikan:
1. Email sudah verified: `$user->email_verified_at = now(); $user->save();`
2. User punya role: `$user->assignRole('super_admin');`
3. Password benar (reset jika perlu)

### Permission denied?
```bash
php artisan cache:clear
php artisan permission:cache-reset
```

---

## Security Checklist

- [ ] Ganti password default 'password' ke password yang kuat
- [ ] Hapus fake users yang tidak diperlukan
- [ ] Set email_verified_at untuk semua user real
- [ ] Backup database secara berkala
- [ ] Jangan pernah commit password ke Git
- [ ] Gunakan .env untuk credentials sensitif

---

**Status Sekarang:**
- ✅ 6 Roles created
- ✅ 15 Permissions created  
- ✅ 1 Superadmin created (admin@sitlpk.com)
- ✅ 54 Fake users (bisa dihapus jika tidak diperlukan)
- ✅ Total users: 55
