<?php

namespace App\Policies;

use App\Enums\EntityType;
use App\Models\CTK;
use App\Models\User;

class CTKPolicy
{
    /**
     * Determine whether the user can view any CTK records.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ctk');
    }

    /**
     * Determine whether the user can view the CTK.
     */
    public function view(User $user, CTK $ctk): bool
    {
        if (! $user->can('view_ctk')) {
            return false;
        }

        // Super Admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Pimpinan can view all (read-only)
        if ($user->hasRole('Pimpinan')) {
            return true;
        }

        // Apply entity scoping
        return $this->checkEntityAccess($user, $ctk);
    }

    /**
     * Determine whether the user can create CTK.
     */
    public function create(User $user): bool
    {
        // Only Admin LPK and Super Admin can create CTK
        return $user->can('create_ctk') &&
               ($user->hasRole('Admin LPK') || $user->hasRole('super_admin'));
    }

    /**
     * Determine whether the user can update the CTK.
     */
    public function update(User $user, CTK $ctk): bool
    {
        if (! $user->can('update_ctk')) {
            return false;
        }

        // Super Admin can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Pimpinan cannot update (read-only)
        if ($user->hasRole('Pimpinan')) {
            return false;
        }

        // Apply entity scoping
        return $this->checkEntityAccess($user, $ctk);
    }

    /**
     * Determine whether the user can delete the CTK.
     */
    public function delete(User $user, CTK $ctk): bool
    {
        // Only Super Admin can delete
        return $user->can('delete_ctk') && $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the CTK.
     */
    public function restore(User $user, CTK $ctk): bool
    {
        // Only Super Admin can restore
        return $user->can('restore_ctk') && $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the CTK.
     */
    public function forceDelete(User $user, CTK $ctk): bool
    {
        // Only Super Admin can force delete
        return $user->can('force_delete_ctk') && $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can override CTK immutability rules.
     */
    public function overrideImmutability(User $user): bool
    {
        return $user->can('override_ctk_immutability') && $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view CTK audit logs.
     */
    public function viewAudit(User $user, CTK $ctk): bool
    {
        if (! $user->can('view_ctk_audit')) {
            return false;
        }

        // If user can view the CTK, they can view its audit log
        return $this->view($user, $ctk);
    }

    /**
     * Check entity-based access control.
     * LPK users can only access CTK in stages 1-5 (LPK stages)
     * PT users can only access CTK in stages 6-15 (PT stages)
     */
    protected function checkEntityAccess(User $user, CTK $ctk): bool
    {
        $userEntity = $user->entity;
        $ctkEntity = $ctk->current_entity;
        $ctkStage = $ctk->current_stage;

        // Admin LPK can only access CTK in LPK stages (1-5)
        if ($user->hasRole('Admin LPK')) {
            return $userEntity === EntityType::LPK &&
                   $ctkEntity === EntityType::LPK &&
                   $ctkStage >= 1 &&
                   $ctkStage <= 5;
        }

        // Admin PT can only access CTK in PT stages (6-15)
        if ($user->hasRole('Admin PT')) {
            return $userEntity === EntityType::PT &&
                   $ctkEntity === EntityType::PT &&
                   $ctkStage >= 6 &&
                   $ctkStage <= 15;
        }

        // HR PT can access CTK in PT stages
        if ($user->hasRole('HR PT')) {
            return $userEntity === EntityType::PT &&
                   $ctkEntity === EntityType::PT &&
                   $ctkStage >= 6 &&
                   $ctkStage <= 15;
        }

        // Legal PT and Keuangan PT can access relevant stages
        if ($user->hasAnyRole(['Legal PT', 'Keuangan PT'])) {
            return $userEntity === EntityType::PT &&
                   $ctkEntity === EntityType::PT;
        }

        // Keuangan LPK can access LPK stages
        if ($user->hasRole('Keuangan LPK')) {
            return $userEntity === EntityType::LPK &&
                   $ctkEntity === EntityType::LPK;
        }

        return false;
    }
}
