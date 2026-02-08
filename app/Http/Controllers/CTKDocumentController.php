<?php

namespace App\Http\Controllers;

use App\Models\CTKDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CTKDocumentController extends Controller
{
    /**
     * Download a CTK document file.
     */
    public function download(CTKDocument $document): StreamedResponse
    {
        // Check if user has permission to view CTK documents
        $this->authorize('view_ctk');

        // Verify the file exists
        if (! Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // Return the file as a download with the original filename
        return Storage::disk('private')->download(
            $document->file_path,
            $document->filename ?? basename($document->file_path)
        );
    }
}
