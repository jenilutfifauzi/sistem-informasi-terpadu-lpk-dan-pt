<?php

namespace App\Policies;

use App\Models\PembayaranPusat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PembayaranPusatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Pimpinan can view all payments (both PT and LPK)
        if ($user->hasRole('Pimpinan')) {
            return $user->can('view_any_pembayaran_pusat');
        }

        // Other roles can view payments from their entity
        return $user->can('view_any_pembayaran_pusat');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PembayaranPusat $pembayaranPusat): bool|Response
    {
        // Check basic permission
        if (! $user->can('view_pembayaran_pusat')) {
            return false;
        }

        // Pimpinan can view payments from any entity
        if ($user->hasRole('Pimpinan')) {
            return true;
        }

        // Other roles can only view payments from their entity
        if ($user->entity !== $pembayaranPusat->entity) {
            return Response::deny('Anda tidak dapat mengakses pembayaran dari entity lain.');
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pembayaran_pusat');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PembayaranPusat $pembayaranPusat): bool|Response
    {
        // Pimpinan cannot edit payments
        if ($user->hasRole('Pimpinan')) {
            return Response::deny('Role Pimpinan hanya memiliki akses baca.');
        }

        // Check basic permission
        if (! $user->can('update_pembayaran_pusat')) {
            return false;
        }

        // User can only update payments from their entity
        if ($user->entity !== $pembayaranPusat->entity) {
            return Response::deny('Anda tidak dapat mengubah pembayaran dari entity lain.');
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PembayaranPusat $pembayaranPusat): bool|Response
    {
        // Pimpinan cannot delete payments
        if ($user->hasRole('Pimpinan')) {
            return Response::deny('Role Pimpinan tidak dapat menghapus pembayaran.');
        }

        // Check basic permission
        if (! $user->can('delete_pembayaran_pusat')) {
            return false;
        }

        // User can only delete payments from their entity
        if ($user->entity !== $pembayaranPusat->entity) {
            return Response::deny('Anda tidak dapat menghapus pembayaran dari entity lain.');
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PembayaranPusat $pembayaranPusat): bool|Response
    {
        // Check basic permission
        if (! $user->can('restore_pembayaran_pusat')) {
            return false;
        }

        // User can only restore payments from their entity
        if ($user->entity !== $pembayaranPusat->entity) {
            return Response::deny('Anda tidak dapat memulihkan pembayaran dari entity lain.');
        }

        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PembayaranPusat $pembayaranPusat): bool|Response
    {
        // Check basic permission
        if (! $user->can('force_delete_pembayaran_pusat')) {
            return false;
        }

        // User can only force delete payments from their entity
        if ($user->entity !== $pembayaranPusat->entity) {
            return Response::deny('Anda tidak dapat menghapus permanen pembayaran dari entity lain.');
        }

        return true;
    }
}
