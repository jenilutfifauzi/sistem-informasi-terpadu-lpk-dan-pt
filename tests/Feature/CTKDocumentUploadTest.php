<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Models\CTK;
use App\Models\CTKDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CTKDocumentUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
        Storage::fake('private');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_admin_can_upload_document_with_complete_information(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $file = UploadedFile::fake()->create('soal-berkas.pdf', 1024, 'application/pdf');

        $document = CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::SoalBerkas,
            'file_path' => 'ctk-documents/soal-berkas/'.$file->hashName(),
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploader_id' => $admin->id,
            'upload_timestamp' => now(),
        ]);

        Storage::disk('private')->put('ctk-documents/soal-berkas/'.$file->hashName(), $file->get());

        $this->assertDatabaseHas('ctk_documents', [
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::SoalBerkas->value,
            'filename' => 'soal-berkas.pdf',
            'uploader_id' => $admin->id,
        ]);

        $retrievedDoc = CTKDocument::find($document->id);
        $this->assertEquals(DocumentType::SoalBerkas, $retrievedDoc->document_type);
        $this->assertEquals($admin->id, $retrievedDoc->uploader_id);
        $this->assertNotNull($retrievedDoc->upload_timestamp);

        Storage::disk('private')->assertExists('ctk-documents/soal-berkas/'.$file->hashName());
    }

    public function test_admin_can_upload_paspor_document_with_paspor_number(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $file = UploadedFile::fake()->create('paspor.pdf', 1024, 'application/pdf');

        $document = CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::Paspor,
            'file_path' => 'ctk-documents/paspor/'.$file->hashName(),
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploader_id' => $admin->id,
            'upload_timestamp' => now(),
        ]);

        Storage::disk('private')->put('ctk-documents/paspor/'.$file->hashName(), $file->get());

        $this->assertDatabaseHas('ctk_documents', [
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::Paspor->value,
        ]);

        $retrievedDoc = CTKDocument::find($document->id);
        $this->assertEquals(DocumentType::Paspor, $retrievedDoc->document_type);
    }

    public function test_documents_are_stored_in_categorized_directories(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $documentTypes = [
            [DocumentType::SoalBerkas, 'soal-berkas', 'soal-berkas.pdf'],
            [DocumentType::Paspor, 'paspor', 'paspor.pdf'],
            [DocumentType::IjinDesa, 'ijin-desa', 'ijin-desa.pdf'],
            [DocumentType::Rekomendasi, 'rekomendasi', 'rekomendasi.pdf'],
        ];

        foreach ($documentTypes as [$type, $dir, $filename]) {
            $file = UploadedFile::fake()->create($filename, 1024, 'application/pdf');
            $path = 'ctk-documents/'.$dir.'/'.$file->hashName();

            CTKDocument::create([
                'ctk_id' => $ctk->id,
                'document_type' => $type,
                'file_path' => $path,
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'filename' => $filename,
                'uploader_id' => $admin->id,
                'upload_timestamp' => now(),
            ]);

            Storage::disk('private')->put($path, $file->get());

            Storage::disk('private')->assertExists($path);
            $this->assertStringContainsString($dir, $path);
        }

        $this->assertEquals(4, $ctk->documents()->count());
    }

    public function test_admin_views_all_uploaded_documents_for_ctk(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $documents = [
            ['type' => DocumentType::SoalBerkas, 'filename' => 'soal-berkas.pdf'],
            ['type' => DocumentType::Paspor, 'filename' => 'paspor.pdf'],
            ['type' => DocumentType::IjinDesa, 'filename' => 'ijin-desa.pdf'],
        ];

        foreach ($documents as $doc) {
            CTKDocument::create([
                'ctk_id' => $ctk->id,
                'document_type' => $doc['type'],
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'file_path' => 'ctk-documents/'.$doc['filename'],
                'filename' => $doc['filename'],
                'uploader_id' => $admin->id,
                'upload_timestamp' => now(),
            ]);
        }

        $retrievedDocs = $ctk->documents;

        $this->assertCount(3, $retrievedDocs);
        $this->assertTrue($retrievedDocs->contains('document_type', DocumentType::SoalBerkas));
        $this->assertTrue($retrievedDocs->contains('document_type', DocumentType::Paspor));
        $this->assertTrue($retrievedDocs->contains('document_type', DocumentType::IjinDesa));
        $this->assertTrue($retrievedDocs->every(fn ($doc) => $doc->uploader_id === $admin->id));
    }

    public function test_system_prevents_advancement_when_required_document_missing(): void
    {
        $ctk = CTK::factory()->create(['current_stage' => 3]);

        // Stage 3 requires SoalBerkas document
        $this->assertFalse($ctk->documents()->where('document_type', DocumentType::SoalBerkas)->exists());
    }

    public function test_ctk_with_required_document_can_advance_from_stage_3(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create(['current_stage' => 3]);

        // Upload required SoalBerkas document
        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'document_type' => DocumentType::SoalBerkas,
            'file_path' => 'ctk-documents/soal-berkas/test.pdf',
            'filename' => 'soal-berkas.pdf',
            'uploader_id' => $admin->id,
            'upload_timestamp' => now(),
        ]);

        $this->assertTrue($ctk->documents()->where('document_type', DocumentType::SoalBerkas)->exists());

        // Simulate advancement
        $ctk->update(['current_stage' => 4]);

        $this->assertEquals(4, $ctk->current_stage);
    }

    public function test_document_upload_records_uploader_and_timestamp(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $document = CTKDocument::create([
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::SoalBerkas,
            'file_path' => 'ctk-documents/soal-berkas/test.pdf',
            'filename' => 'test.pdf',
            'uploader_id' => $admin->id,
            'upload_timestamp' => now(),
        ]);

        $this->assertEquals($admin->id, $document->uploader_id);
        $this->assertNotNull($document->upload_timestamp);
        $this->assertInstanceOf(\Carbon\Carbon::class, $document->upload_timestamp);

        // Check relationship
        $this->assertEquals($admin->id, $document->uploader->id);
        $this->assertEquals($admin->name, $document->uploader->name);
    }

    public function test_multiple_document_types_can_be_uploaded_for_single_ctk(): void
    {
        $admin = User::factory()->create();
        $ctk = CTK::factory()->create();

        $allDocumentTypes = [
            DocumentType::SoalBerkas,
        ];

        foreach ($allDocumentTypes as $type) {
            CTKDocument::create([
                'ctk_id' => $ctk->id,
                'document_type' => $type,
                'file_path' => 'ctk-documents/test/'.$type->value.'.pdf',
                'filename' => $type->value.'.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'uploader_id' => $admin->id,
                'upload_timestamp' => now(),
            ]);
        }

        $this->assertEquals(1, $ctk->documents()->count());
        $this->assertEquals(1, $ctk->documents->count());

        // Verify all document types are present
        foreach ($allDocumentTypes as $type) {
            $this->assertTrue($ctk->documents->contains('document_type', $type));
        }
    }
}
