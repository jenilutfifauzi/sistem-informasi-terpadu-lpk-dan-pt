<?php

namespace Tests\Feature;

use App\Enums\CTKStatus;
use App\Enums\DocumentType;
use App\Enums\MCUStatus;
use App\Enums\PaymentStatus;
use App\Enums\ScreeningStage;
use App\Models\CTK;
use App\Models\CTKDocument;
use App\Models\CTKMedicalFull;
use App\Models\CTKPayment;
use App\Models\CTKScreening;
use App\Models\CTKTraining;
use App\Models\MCURecord;
use App\Models\User;
use App\Models\VisaRecord;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CTKStageTrackingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function stage_1_mcu_completes_when_status_is_fit()
    {
        $ctk = CTK::factory()->create();
        MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage1_complete);
        $this->assertEquals(1, $ctk->completed_stages_count);
        $this->assertEquals('1/15', $ctk->completion_progress);
    }

    /** @test */
    public function stage_1_not_complete_when_mcu_is_unfit_or_pending()
    {
        $ctk = CTK::factory()->create();
        MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::UNFIT,
        ]);

        $ctk->refresh();

        $this->assertFalse($ctk->stage1_complete);
        $this->assertEquals(0, $ctk->completed_stages_count);
    }

    /** @test */
    public function stage_2_payment_completes_when_all_5_payments_are_lunas_with_proof()
    {
        $ctk = CTK::factory()->create();
        $user = User::factory()->create();

        // Create 5 payments with proof
        for ($i = 1; $i <= 5; $i++) {
            CTKPayment::create([
                'ctk_id' => $ctk->id,
                'stage_number' => $i,
                'amount' => 1000000,
                'bank_name' => 'BCA',
                'payment_date' => now(),
                'payment_status' => PaymentStatus::Lunas,
                'payment_proof_path' => 'payments/proof-'.$i.'.pdf',
                'created_by' => $user->id,
            ]);
        }

        $ctk->refresh();

        $this->assertTrue($ctk->stage2_complete);
        $this->assertEquals('5/5', $ctk->payment_progress);
    }

    /** @test */
    public function stage_2_not_complete_with_only_3_of_5_payments()
    {
        $ctk = CTK::factory()->create();
        $user = User::factory()->create();

        // Create only 3 payments
        for ($i = 1; $i <= 3; $i++) {
            CTKPayment::create([
                'ctk_id' => $ctk->id,
                'stage_number' => $i,
                'amount' => 1000000,
                'bank_name' => 'BCA',
                'payment_date' => now(),
                'payment_status' => PaymentStatus::Lunas,
                'payment_proof_path' => 'payments/proof-'.$i.'.pdf',
                'created_by' => $user->id,
            ]);
        }

        $ctk->refresh();

        $this->assertFalse($ctk->stage2_complete);
        $this->assertEquals('3/5', $ctk->payment_progress);
    }

    /** @test */
    public function stage_3_soal_berkas_completes_when_document_uploaded_and_status_lengkap()
    {
        $ctk = CTK::factory()->create([
            'soal_berkas_status' => 'Lengkap',
        ]);
        $user = User::factory()->create();

        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::SoalBerkas,
            'filename' => 'soal.pdf',
            'file_path' => 'documents/soal.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploader_id' => $user->id,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage3_complete);
    }

    /** @test */
    public function stage_4_paspor_completes_when_paspor_number_filled_and_document_uploaded()
    {
        $ctk = CTK::factory()->create([
            'paspor_number' => 'X1234567',
        ]);
        $user = User::factory()->create();

        // Upload paspor document
        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::Paspor,
            'filename' => 'paspor.pdf',
            'file_path' => 'documents/paspor.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploader_id' => $user->id,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage4_complete);
    }

    /** @test */
    public function stage_4_not_complete_without_paspor_number()
    {
        $ctk = CTK::factory()->create([
            'paspor_number' => null,
        ]);

        $this->assertFalse($ctk->stage4_complete);
    }

    /** @test */
    public function stage_5_training_completes_when_status_is_selesai()
    {
        $ctk = CTK::factory()->create();
        $instructor = \App\Models\EmployeeLPK::factory()->create();
        $user = User::factory()->create();

        CTKTraining::create([
            'ctk_id' => $ctk->id,
            'instructor_id' => $instructor->id,
            'training_start_date' => now()->subDays(30),
            'training_end_date' => now(),
            'training_location' => 'LPK Jakarta',
            'training_hours' => 160,
            'completion_status' => 'Selesai',
            'created_by' => $user->id,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage5_complete);
    }

    /** @test */
    public function stage_8_ijin_desa_completes_when_document_uploaded_and_status_ada()
    {
        $ctk = CTK::factory()->create([
            'ijin_desa_status' => 'Ada',
        ]);
        $user = User::factory()->create();

        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::IjinDesa,
            'filename' => 'ijin_desa.pdf',
            'file_path' => 'documents/ijin_desa.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploader_id' => $user->id,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage8_complete);
    }

    /** @test */
    public function stage_10_wp_completes_when_status_is_lengkap()
    {
        $ctk = CTK::factory()->create([
            'wp_status' => 'Lengkap',
        ]);

        $this->assertTrue($ctk->stage10_complete);
    }

    /** @test */
    public function stage_11_apply_visa_completes_when_status_is_diajukan()
    {
        $ctk = CTK::factory()->create([
            'apply_visa_status' => 'Diajukan',
        ]);

        $this->assertTrue($ctk->stage11_complete);
    }

    /** @test */
    public function stage_12_medical_full_completes_when_status_is_selesai()
    {
        $ctk = CTK::factory()->create();
        $user = User::factory()->create();

        CTKMedicalFull::create([
            'ctk_id' => $ctk->id,
            'status' => 'Selesai',
            'examination_date' => now(),
            'created_by' => $user->id,
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage12_complete);
    }

    /** @test */
    public function stage_13_visa_completes_when_status_terbit_with_visa_number()
    {
        $ctk = CTK::factory()->create();

        VisaRecord::create([
            'ctk_id' => $ctk->id,
            'application_status' => 'Terbit',
            'application_date' => now()->subDays(30),
            'visa_number' => 'V12345',
            'issuance_date' => now(),
            'expiry_date' => now()->addYear(),
        ]);

        $ctk->refresh();

        $this->assertTrue($ctk->stage13_complete);
    }

    /** @test */
    public function stage_14_opp_completes_when_status_diterima_with_receipt_date()
    {
        $ctk = CTK::factory()->create([
            'opp_status' => 'Diterima',
            'opp_receipt_date' => now(),
        ]);

        $this->assertTrue($ctk->stage14_complete);
    }

    /** @test */
    public function stage_15_terbang_completes_when_departure_date_filled()
    {
        $ctk = CTK::factory()->create([
            'departure_date' => now(),
        ]);

        $this->assertTrue($ctk->stage15_complete);
    }

    /** @test */
    public function stage_15_not_complete_without_departure_date()
    {
        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Terbang,
            'departure_date' => null,
        ]);

        $this->assertFalse($ctk->stage15_complete);
    }

    /** @test */
    public function completion_percentage_calculated_correctly()
    {
        $ctk = CTK::factory()->create([
            'paspor_number' => 'X1234567', // Stage 4
        ]);
        $user = User::factory()->create();

        // Stage 1: MCU
        MCURecord::factory()->create([
            'ctk_id' => $ctk->id,
            'status' => MCUStatus::FIT,
        ]);

        // Stage 4: Paspor document
        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::Paspor,
            'filename' => 'paspor.pdf',
            'file_path' => 'documents/paspor.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploader_id' => $user->id,
        ]);

        $ctk->refresh();

        // 2 stages complete (1 and 4) out of 15 = 13.33% ≈ 13%
        $this->assertEquals(2, $ctk->completed_stages_count);
        $this->assertEquals(13, $ctk->completion_percentage);
        $this->assertEquals('2/15', $ctk->completion_progress);
    }

    /** @test */
    public function all_15_stages_complete_shows_100_percent()
    {
        $ctk = CTK::factory()->create([
            'soal_berkas_status' => 'Lengkap',
            'paspor_number' => 'X1234567',
            'ijin_desa_status' => 'Ada',
            'rekomendasi_status' => 'Ada',
            'wp_status' => 'Lengkap',
            'apply_visa_status' => 'Diajukan',
            'opp_status' => 'Diterima',
            'opp_receipt_date' => now(),
            'current_status' => CTKStatus::Terbang,
            'departure_date' => now(),
        ]);
        $user = User::factory()->create();
        $instructor = \App\Models\EmployeeLPK::factory()->create();

        // Stage 1: MCU
        MCURecord::factory()->create(['ctk_id' => $ctk->id, 'status' => MCUStatus::FIT]);

        // Stage 2: Payments (5 payments)
        for ($i = 1; $i <= 5; $i++) {
            CTKPayment::create([
                'ctk_id' => $ctk->id,
                'stage_number' => $i,
                'amount' => 1000000,
                'bank_name' => 'BCA',
                'payment_date' => now(),
                'payment_status' => PaymentStatus::Lunas,
                'payment_proof_path' => 'proof.pdf',
                'created_by' => $user->id,
            ]);
        }

        // Stage 3: Soal Berkas
        CTKDocument::create(['ctk_id' => $ctk->id, 'document_type' => DocumentType::SoalBerkas, 'filename' => 'soal.pdf', 'file_path' => 'soal.pdf', 'file_size' => 1024, 'mime_type' => 'application/pdf', 'uploader_id' => $user->id]);

        // Stage 4: Paspor document
        CTKDocument::create(['ctk_id' => $ctk->id, 'document_type' => DocumentType::Paspor, 'filename' => 'paspor.pdf', 'file_path' => 'paspor.pdf', 'file_size' => 1024, 'mime_type' => 'application/pdf', 'uploader_id' => $user->id]);

        // Stage 5: Training
        CTKTraining::create(['ctk_id' => $ctk->id, 'instructor_id' => $instructor->id, 'training_start_date' => now(), 'training_end_date' => now(), 'training_location' => 'LPK', 'completion_status' => 'Selesai', 'created_by' => $user->id]);

        // Stage 6: Screening 1
        CTKScreening::create(['ctk_id' => $ctk->id, 'interviewer_id' => $user->id, 'interview_date' => now(), 'interview_location' => 'Screening Tahap 1', 'screening_stage' => ScreeningStage::Screening1, 'screening_result' => 'Lolos', 'created_by' => $user->id]);

        // Stage 7: Interview User
        CTKScreening::create(['ctk_id' => $ctk->id, 'interviewer_id' => $user->id, 'interview_date' => now(), 'interview_location' => 'Interview User', 'screening_stage' => ScreeningStage::InterviewUser, 'screening_result' => 'Lolos', 'created_by' => $user->id]);

        // Stage 8: Ijin Desa
        CTKDocument::create(['ctk_id' => $ctk->id, 'document_type' => DocumentType::IjinDesa, 'filename' => 'ijin.pdf', 'file_path' => 'ijin.pdf', 'file_size' => 1024, 'mime_type' => 'application/pdf', 'uploader_id' => $user->id]);

        // Stage 9: Rekomendasi
        CTKDocument::create(['ctk_id' => $ctk->id, 'document_type' => DocumentType::Rekomendasi, 'filename' => 'rekom.pdf', 'file_path' => 'rekom.pdf', 'file_size' => 1024, 'mime_type' => 'application/pdf', 'uploader_id' => $user->id]);

        // Stage 12: Medical Full
        CTKMedicalFull::create(['ctk_id' => $ctk->id, 'status' => 'Selesai', 'examination_date' => now(), 'created_by' => $user->id]);

        // Stage 13: Visa
        VisaRecord::create(['ctk_id' => $ctk->id, 'application_status' => 'Terbit', 'application_date' => now(), 'visa_number' => 'V123', 'issuance_date' => now()]);

        $ctk->refresh();

        $this->assertEquals(15, $ctk->completed_stages_count);
        $this->assertEquals(100, $ctk->completion_percentage);
        $this->assertEquals('15/15', $ctk->completion_progress);
    }

    /** @test */
    public function stage_completions_array_returns_correct_format()
    {
        $ctk = CTK::factory()->create([
            'paspor_number' => 'X1234567',
        ]);
        $user = User::factory()->create();

        // Stage 4: Paspor document
        CTKDocument::create([
            'ctk_id' => $ctk->id,
            'document_type' => DocumentType::Paspor,
            'filename' => 'paspor.pdf',
            'file_path' => 'documents/paspor.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploader_id' => $user->id,
        ]);

        $ctk->refresh();

        $completions = $ctk->stage_completions;

        $this->assertIsArray($completions);
        $this->assertCount(15, $completions);
        $this->assertEquals(1, $completions[1]['stage_number']);
        $this->assertFalse($completions[1]['complete']); // No MCU record
        $this->assertEquals('[ ]', $completions[1]['checkbox']);
        $this->assertTrue($completions[4]['complete']); // Paspor number + document exists
        $this->assertEquals('[x]', $completions[4]['checkbox']);
    }
}
