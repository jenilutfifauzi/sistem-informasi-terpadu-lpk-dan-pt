<?php

namespace App\Policies;

use App\Models\EmployeeLPK;
use App\Models\User;

class EmployeeLPKPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_any_karyawan_lpk',
            'view_any_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'view_karyawan_lpk',
            'view_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create_karyawan_lpk',
            'create_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'update_karyawan_lpk',
            'update_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'delete_karyawan_lpk',
            'delete_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'restore_karyawan_lpk',
            'restore_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'force_delete_karyawan_lpk',
            'force_delete_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can view their own profile
     */
    public function viewOwn(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->email === $employeeLPK->email;
    }

    /**
     * Determine whether the user can update their own profile (limited fields)
     */
    public function updateOwn(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->email === $employeeLPK->email;
    }

    /**
     * Determine whether the user can download employee's certificate
     */
    public function downloadSertifikat(User $user, EmployeeLPK $employeeLPK): bool
    {
        // Admin LPK, Pimpinan can download any certificate
        if ($user->hasAnyRole(['Admin LPK', 'Pimpinan'])) {
            return true;
        }

        // Instruktur can download own certificate
        if ($user->email === $employeeLPK->email) {
            return true;
        }

        return false;
    }
}
