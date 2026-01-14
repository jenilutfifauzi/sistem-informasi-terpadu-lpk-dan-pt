<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLPK;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handle sertifikat kompetensi downloads for employees.
 *
 * This controller manages the secure download of employee sertifikat files with proper
 * authorization checks via the EmployeeLPKPolicy. Downloads are restricted to authorized
 * users (admin_lpk, pimpinan_lpk, instruktur own only) with proper file validation.
 */
class EmployeeSertifikatController extends Controller
{
    /**
     * Download employee sertifikat with authorization and validation checks.
     *
     * Performs three-step verification:
     * 1. Authorization check via EmployeeLPKPolicy::downloadSertifikat()
     * 2. File existence check on private disk
     * 3. Streamed download with proper filename
     *
     * @param  EmployeeLPK  $employee  The employee whose sertifikat is being downloaded
     * @return StreamedResponse File download response
     *
     * @throws AuthorizationException If user lacks permission to download
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If file doesn't exist
     */
    public function download(EmployeeLPK $employee): StreamedResponse
    {
        // Step 1: Check authorization via policy
        if (! auth()->user()->can('downloadSertifikat', $employee)) {
            throw new AuthorizationException('Anda tidak berhak mengunduh sertifikat ini');
        }

        // Step 2: Verify file exists on private disk
        if (! $employee->sertifikat_path || ! Storage::disk('private')->exists($employee->sertifikat_path)) {
            abort(404, 'File sertifikat tidak ditemukan');
        }

        // Step 3: Return streamed download with clean filename
        return Storage::disk('private')->download(
            $employee->sertifikat_path,
            "Sertifikat_{$employee->nama_lengkap}.pdf"
        );
    }
}
