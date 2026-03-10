<?php

namespace App\Policies;

use App\Models\EmployeePT;
use App\Models\User;

/**
 * Authorization policy for EmployeePT model.
 *
 * Role access matrix:
 * - super_admin: Full access
 * - admin_pt: Full CRUD (viewAny, view, create, update, delete, restore, forceDelete)
 * - keuangan_pt: viewAny + view + updateKompensasi only — cannot create/delete
 * - pimpinan: viewAny + view + downloadDokumen only
 * - admin_lpk / keuangan_lpk: DENIED
 */
class EmployeePTPolicy
{
    /**
     * Determine whether the user can view any employee PT records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_any_karyawan_pt',
            'view_any_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can view a specific employee PT record.
     */
    public function view(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasAnyPermission([
            'view_karyawan_pt',
            'view_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can create employee PT records.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create_karyawan_pt',
            'create_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can update employee PT records.
     *
     * Keuangan PT role can update (limited to kompensasi fields via form visibility).
     */
    public function update(User $user, EmployeePT $employeePT): bool
    {
        if ($user->hasRole('keuangan_pt')) {
            return true;
        }

        return $user->hasAnyPermission([
            'update_karyawan_pt',
            'update_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can update kompensasi fields (gaji_pokok, tunjangan).
     */
    public function updateKompensasi(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasRole('keuangan_pt') || $user->hasAnyPermission([
            'update_karyawan_pt',
            'update_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can delete employee PT records (soft delete).
     */
    public function delete(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasAnyPermission([
            'delete_karyawan_pt',
            'delete_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can restore soft-deleted employee PT records.
     */
    public function restore(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasAnyPermission([
            'restore_karyawan_pt',
            'restore_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can permanently delete employee PT records.
     */
    public function forceDelete(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasAnyPermission([
            'force_delete_karyawan_pt',
            'force_delete_karyawan_p_t',
        ]);
    }

    /**
     * Determine whether the user can download an employee's dokumen kepegawaian.
     *
     * Allowed: admin_pt, keuangan_pt, pimpinan, super_admin.
     */
    public function downloadDokumen(User $user, EmployeePT $employeePT): bool
    {
        return $user->hasAnyRole(['admin_pt', 'keuangan_pt', 'pimpinan', 'super_admin']);
    }
}
