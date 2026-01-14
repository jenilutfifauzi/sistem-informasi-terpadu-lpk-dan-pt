<?php

namespace App\Policies;

use App\Models\EmployeeLPK;
use App\Models\User;

/**
 * Authorization policy for EmployeeLPK model.
 *
 * Defines granular access control for employee data across different roles:
 * - super_admin: Full access
 * - admin_lpk: Full CRUD access
 * - keuangan_lpk: Update honor fields only
 * - pimpinan_lpk: View/download access
 * - instruktur: Self-service profile and certificate access
 */
class EmployeeLPKPolicy
{
    /**
     * Determine whether the user can view any employee records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_any_karyawan_lpk',
            'view_any_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can view a specific employee record.
     */
    public function view(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'view_karyawan_lpk',
            'view_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can create employee records.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create_karyawan_lpk',
            'create_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can update employee records.
     *
     * Keuangan LPK role can update (limited to honor fields via form visibility).
     * Other users require standard update permission.
     */
    public function update(User $user, EmployeeLPK $employeeLPK): bool
    {
        // Allow Keuangan LPK to update (limited by form visibility)
        if ($user->hasRole('keuangan_lpk')) {
            return true;
        }

        return $user->hasAnyPermission([
            'update_karyawan_lpk',
            'update_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can update honor fields.
     *
     * Only keuangan_lpk role and users with standard update permission.
     */
    public function updateHonor(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasRole('keuangan_lpk') || $user->hasAnyPermission([
            'update_karyawan_lpk',
            'update_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can delete employee records.
     */
    public function delete(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'delete_karyawan_lpk',
            'delete_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can restore soft-deleted employee records.
     */
    public function restore(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'restore_karyawan_lpk',
            'restore_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can permanently delete employee records.
     */
    public function forceDelete(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->hasAnyPermission([
            'force_delete_karyawan_lpk',
            'force_delete_karyawan_l_p_k',
        ]);
    }

    /**
     * Determine whether the user can view their own employee profile.
     *
     * Used for self-service profile access in EmployeeLPKProfileResource.
     */
    public function viewOwn(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->email === $employeeLPK->email;
    }

    /**
     * Determine whether the user can update their own profile (limited fields).
     *
     * Currently disabled via read-only forms in EmployeeLPKProfileResource.
     */
    public function updateOwn(User $user, EmployeeLPK $employeeLPK): bool
    {
        return $user->email === $employeeLPK->email;
    }

    /**
     * Determine whether the user can download an employee's sertifikat kompetensi.
     *
     * Authorization rules:
     * - admin_lpk, pimpinan_lpk: Can download any sertifikat
     * - instruktur: Can download only their own sertifikat
     * - others: No access
     *
     * @param  User  $user  The authenticated user requesting download
     * @param  EmployeeLPK  $employeeLPK  The employee whose sertifikat is being accessed
     * @return bool true if authorized, false otherwise
     */
    public function downloadSertifikat(User $user, EmployeeLPK $employeeLPK): bool
    {
        // Admin LPK and Pimpinan can download any sertifikat
        if ($user->hasAnyRole(['admin_lpk', 'pimpinan'])) {
            return true;
        }

        // Instruktur can download their own sertifikat only
        if ($user->email === $employeeLPK->email && $employeeLPK->jabatan->value === 'Instruktur') {
            return true;
        }

        return false;
    }
}
