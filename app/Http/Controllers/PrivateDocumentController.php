<?php

namespace App\Http\Controllers;

use App\Models\CTK;
use App\Models\CTKMedicalFull;
use App\Models\VisaRecord;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateDocumentController extends Controller
{
    /**
     * View a Medical Full Report document inline.
     */
    public function downloadMedical(CTKMedicalFull $medical): BinaryFileResponse
    {
        $this->authorize('view_ctk');

        if (! $medical->medical_report_path || ! Storage::disk('private')->exists($medical->medical_report_path)) {
            abort(404, 'File not found');
        }

        return $this->previewPrivateFile($medical->medical_report_path);
    }

    /**
     * View a Visa document inline.
     */
    public function downloadVisa(VisaRecord $visa): BinaryFileResponse
    {
        $this->authorize('view_ctk');

        if (! $visa->visa_document_path || ! Storage::disk('private')->exists($visa->visa_document_path)) {
            abort(404, 'File not found');
        }

        return $this->previewPrivateFile($visa->visa_document_path);
    }

    /**
     * View an OPP document from CTK inline.
     */
    public function downloadOpp(CTK $ctk): BinaryFileResponse
    {
        $this->authorize('view_ctk');

        if (! $ctk->opp_document_path || ! Storage::disk('private')->exists($ctk->opp_document_path)) {
            abort(404, 'File not found');
        }

        return $this->previewPrivateFile($ctk->opp_document_path);
    }

    private function previewPrivateFile(string $path): BinaryFileResponse
    {
        $disk = Storage::disk('private');
        $absolutePath = $disk->path($path);
        $filename = basename($path);
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
