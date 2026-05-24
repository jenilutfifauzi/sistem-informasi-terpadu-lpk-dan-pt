<?php

namespace App\Policies;

use App\Models\BukuIndukSiswa;
use App\Models\User;

class BukuIndukSiswaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_buku_induk_siswa');
    }

    public function view(User $user, BukuIndukSiswa $bukuIndukSiswa): bool
    {
        return $user->hasPermissionTo('view_buku_induk_siswa');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_buku_induk_siswa');
    }

    public function update(User $user, BukuIndukSiswa $bukuIndukSiswa): bool
    {
        return $user->hasPermissionTo('update_buku_induk_siswa');
    }

    public function delete(User $user, BukuIndukSiswa $bukuIndukSiswa): bool
    {
        return $user->hasPermissionTo('delete_buku_induk_siswa');
    }

    public function restore(User $user, BukuIndukSiswa $bukuIndukSiswa): bool
    {
        return $user->hasPermissionTo('restore_buku_induk_siswa');
    }

    public function forceDelete(User $user, BukuIndukSiswa $bukuIndukSiswa): bool
    {
        return $user->hasPermissionTo('force_delete_buku_induk_siswa');
    }
}
