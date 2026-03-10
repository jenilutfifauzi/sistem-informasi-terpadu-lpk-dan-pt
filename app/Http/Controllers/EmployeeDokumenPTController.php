<?php

namespace App\Http\Controllers;

use App\Models\EmployeePT;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handle dokumen kepegawaian downloads for PT employees.
 *
 * Serves private HR documents (contracts, decrees, etc.) with authorization
 * checks via EmployeePTPolicy::downloadDokumen().
 */
class EmployeeDokumenPTController extends Controller
{
    /**
     * Download employee dokumen kepegawaian with authorization and validation checks.
     *
     * @param  EmployeePT  $employee  The employee whose dokumen is being downloaded
     * @return StreamedResponse File download response
     *
     * @throws AuthorizationException If user lacks permission to download
     */
    public function download(EmployeePT $employee): StreamedResponse
    {
        if (! auth()->user()->can('downloadDokumen', $employee)) {
            throw new AuthorizationException('Anda tidak berhak mengunduh dokumen ini');
        }

        if (! $employee->dokumen_path || ! Storage::disk('private')->exists($employee->dokumen_path)) {
            abort(404, 'File dokumen tidak ditemukan');
        }

        return Storage::disk('private')->download(
            $employee->dokumen_path,
            "Dokumen_{$employee->nama_lengkap}.pdf"
        );
    }
}
