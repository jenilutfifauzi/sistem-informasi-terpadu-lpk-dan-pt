<?php

namespace App\Http\Controllers;

use App\Models\CTK;
use App\Models\CTKMedicalFull;
use App\Models\VisaRecord;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateDocumentController extends Controller
{
    /**
     * Download a Medical Full Report document.
     */
    public function downloadMedical(CTKMedicalFull $medical): StreamedResponse
    {
        $this->authorize('view_ctk');

        if (! $medical->medical_report_path || ! Storage::disk('private')->exists($medical->medical_report_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download(
            $medical->medical_report_path,
            basename($medical->medical_report_path)
        );
    }

    /**
     * Download a Visa document.
     */
    public function downloadVisa(VisaRecord $visa): StreamedResponse
    {
        $this->authorize('view_ctk');

        if (! $visa->visa_document_path || ! Storage::disk('private')->exists($visa->visa_document_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download(
            $visa->visa_document_path,
            basename($visa->visa_document_path)
        );
    }

    /**
     * Download an OPP document from CTK.
     */
    public function downloadOpp(CTK $ctk): StreamedResponse
    {
        $this->authorize('view_ctk');

        if (! $ctk->opp_document_path || ! Storage::disk('private')->exists($ctk->opp_document_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download(
            $ctk->opp_document_path,
            basename($ctk->opp_document_path)
        );
    }
}
