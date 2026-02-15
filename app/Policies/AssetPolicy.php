<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Pimpinan can view all assets (both PT and LPK)
        if ($user->hasRole('Pimpinan')) {
            return $user->can('view_any_asset');
        }

        // Other roles can view assets from their entity
        return $user->can('view_any_asset');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool|Response
    {
        // Check basic permission
        if (! $user->can('view_asset')) {
            return false;
        }

        // Pimpinan can view assets from any entity
        if ($user->hasRole('Pimpinan')) {
            return true;
        }

        // Other roles can only view assets from their entity
        if ($user->entity !== $asset->entity) {
            return Response::deny('You cannot access assets from other entity.');
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_asset');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool|Response
    {
        // Pimpinan cannot edit assets
        if ($user->hasRole('Pimpinan')) {
            return Response::deny('Pimpinan role has read-only access.');
        }

        // Check basic permission
        if (! $user->can('update_asset')) {
            return false;
        }

        // User can only update assets from their entity
        if ($user->entity !== $asset->entity) {
            return Response::deny('You cannot update assets from other entity.');
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool|Response
    {
        // Pimpinan cannot delete assets
        if ($user->hasRole('Pimpinan')) {
            return Response::deny('Pimpinan role cannot delete assets.');
        }

        // Check basic permission
        if (! $user->can('delete_asset')) {
            return false;
        }

        // User can only delete assets from their entity
        if ($user->entity !== $asset->entity) {
            return Response::deny('You cannot delete assets from other entity.');
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Asset $asset): bool|Response
    {
        // Check basic permission
        if (! $user->can('restore_asset')) {
            return false;
        }

        // User can only restore assets from their entity
        if ($user->entity !== $asset->entity) {
            return Response::deny('You cannot restore assets from other entity.');
        }

        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Asset $asset): bool|Response
    {
        // Check basic permission
        if (! $user->can('force_delete_asset')) {
            return false;
        }

        // User can only force delete assets from their entity
        if ($user->entity !== $asset->entity) {
            return Response::deny('You cannot permanently delete assets from other entity.');
        }

        return true;
    }
}
