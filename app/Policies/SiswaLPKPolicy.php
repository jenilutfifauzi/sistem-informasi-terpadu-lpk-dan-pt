<?php

namespace App\Policies;

use App\Models\SiswaLPK;
use App\Models\User;

class SiswaLPKPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_any_siswa_lpk',
            'view_any_siswa_l_p_k',
        ]);
    }

    public function view(User $user, SiswaLPK $siswaLPK): bool
    {
        return $user->hasAnyPermission([
            'view_siswa_lpk',
            'view_siswa_l_p_k',
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create_siswa_lpk',
            'create_siswa_l_p_k',
        ]);
    }

    public function update(User $user, SiswaLPK $siswaLPK): bool
    {
        return $user->hasAnyPermission([
            'update_siswa_lpk',
            'update_siswa_l_p_k',
        ]);
    }

    public function delete(User $user, SiswaLPK $siswaLPK): bool
    {
        return $user->hasAnyPermission([
            'delete_siswa_lpk',
            'delete_siswa_l_p_k',
        ]);
    }

    public function restore(User $user, SiswaLPK $siswaLPK): bool
    {
        return $user->hasAnyPermission([
            'restore_siswa_lpk',
            'restore_siswa_l_p_k',
        ]);
    }

    public function forceDelete(User $user, SiswaLPK $siswaLPK): bool
    {
        return $user->hasAnyPermission([
            'force_delete_siswa_lpk',
            'force_delete_siswa_l_p_k',
        ]);
    }

    public function export(User $user): bool
    {
        return $user->hasAnyPermission([
            'export_siswa_lpk',
            'export_siswa_l_p_k',
        ]);
    }
}
