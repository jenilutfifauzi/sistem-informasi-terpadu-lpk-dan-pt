<?php

namespace App\Observers;

use App\Models\CTK;
use Illuminate\Support\Facades\Auth;

class CTKObserver
{
    /**
     * Handle the CTK "updating" event - Check immutability before allowing updates.
     */
    public function updating(CTK $ctk): void
    {
        // Check if CTK WAS already in final stage (check the original value before update)
        $originalOppStatus = $ctk->getOriginal('opp_status');

        // Only enforce immutability if OPP was ALREADY Diterima (not during the update that sets it to Diterima)
        if ($originalOppStatus === 'Diterima') {
            // Check if user has override permission
            if (! Auth::user()?->can('override_ctk_immutability')) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    response()->json([
                        'error' => 'CTK record is locked - final stage',
                        'message' => 'Data CTK telah mencapai tahap akhir dan tidak dapat diubah. Hubungi administrator jika diperlukan override.',
                    ], 403)
                );
            }

            // Log override action in activity log
            activity()
                ->causedBy(Auth::user())
                ->performedOn($ctk)
                ->event('override_immutability')
                ->log('CTK immutability override: CTK updated at final stage '.$ctk->current_stage);
        }
    }

    /**
     * Handle the CTK "deleting" event - Prevent deletion of final stage CTKs.
     */
    public function deleting(CTK $ctk): void
    {
        if ($ctk->opp_status === 'Diterima') {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json([
                    'error' => 'Cannot delete - CTK in final stage',
                    'message' => 'Data CTK telah mencapai tahap akhir dan tidak dapat dihapus.',
                ], 403)
            );
        }
    }
}
