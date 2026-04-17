<?php

namespace Tests\Feature;

use App\Enums\EntityType;
use App\Models\CTK;
use App\Models\CTKMedicalFull;
use App\Models\VisaRecord;
use Illuminate\Support\Facades\Storage;

class PrivateDocumentPreviewTest extends CTKActionsTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $this->actingAsUserWithRole('super_admin', EntityType::LPK);
    }

    /** @test */
    public function medical_document_route_returns_inline_preview_response(): void
    {
        $medical = CTKMedicalFull::factory()->create([
            'medical_report_path' => 'medical-full-reports/report_test.pdf',
        ]);

        Storage::disk('private')->put($medical->medical_report_path, 'fake medical content');

        $response = $this->get(route('ctk.medical.download', $medical));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'inline; filename="report_test.pdf"');
    }

    /** @test */
    public function visa_document_route_returns_inline_preview_response(): void
    {
        $visa = VisaRecord::factory()->create([
            'visa_document_path' => 'visa-documents/visa_test.png',
        ]);

        Storage::disk('private')->put($visa->visa_document_path, 'fake visa content');

        $response = $this->get(route('ctk.visa.download', $visa));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'inline; filename="visa_test.png"');
    }

    /** @test */
    public function opp_document_route_returns_inline_preview_response(): void
    {
        $ctk = CTK::factory()->create([
            'opp_document_path' => 'opp-documents/opp_test.pdf',
        ]);

        Storage::disk('private')->put($ctk->opp_document_path, 'fake opp content');

        $response = $this->get(route('ctk.opp.download', $ctk));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'inline; filename="opp_test.pdf"');
    }
}
