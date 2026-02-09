<?php

namespace App\Observers;

use App\Enums\AssetCondition;
use App\Helpers\AssetNumberGenerator;
use App\Models\Asset;
use App\Models\AssetConditionHistory;
use Illuminate\Support\Facades\Auth;

class AssetObserver
{
    /**
     * Handle the Asset "creating" event - Auto-set entity and nomor_inventaris.
     */
    public function creating(Asset $asset): void
    {
        // Auto-set entity based on authenticated user's entity
        if (!$asset->entity && Auth::check()) {
            $asset->entity = Auth::user()->entity;
        }

        // Auto-generate nomor_inventaris if not provided
        if (!$asset->nomor_inventaris) {
            $asset->nomor_inventaris = AssetNumberGenerator::generate(
                $asset->entity,
                $asset->kategori,
                $asset->tahun_pembelian
            );
        }

        // Set created_by
        if (!$asset->created_by && Auth::check()) {
            $asset->created_by = Auth::id();
        }
    }

    /**
     * Handle the Asset "updating" event - Prevent entity changes and log condition changes.
     */
    public function updating(Asset $asset): void
    {
        // Prevent entity field changes (immutability)
        if ($asset->isDirty('entity')) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json([
                    'error' => 'Entity field is immutable',
                    'message' => 'Entity field cannot be changed after creation. Create a new asset if transfer is needed.',
                ], 403)
            );
        }

        // Log condition changes to asset_condition_histories
        if ($asset->isDirty('kondisi')) {
            $oldCondition = $asset->getOriginal('kondisi');
            $newCondition = $asset->kondisi;

            AssetConditionHistory::create([
                'asset_id' => $asset->id,
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition instanceof AssetCondition ? $newCondition->value : $newCondition,
                'reason' => 'Updated via form',
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        }

        // Set updated_by
        if (Auth::check()) {
            $asset->updated_by = Auth::id();
        }
    }
}
