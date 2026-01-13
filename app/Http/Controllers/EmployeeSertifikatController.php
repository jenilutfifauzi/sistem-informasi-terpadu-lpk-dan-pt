<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLPK;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeSertifikatController extends Controller
{
    /**
     * Download employee sertifikat with authorization check.
     *
     * @throws AuthorizationException
     */
    public function download(EmployeeLPK $employee): StreamedResponse
    {
        // Check authorization
        if (! auth()->user()->can('downloadSertifikat', $employee)) {
            throw new AuthorizationException('Anda tidak berhak mengunduh sertifikat ini');
        }

        // Check if file exists
        if (! $employee->sertifikat_path || ! Storage::disk('private')->exists($employee->sertifikat_path)) {
            abort(404, 'File sertifikat tidak ditemukan');
        }

        // Return file download
        return Storage::disk('private')->download(
            $employee->sertifikat_path,
            "Sertifikat_{$employee->nama_lengkap}.pdf"
        );
    }
}
