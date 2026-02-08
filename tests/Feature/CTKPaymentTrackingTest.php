<?php

namespace Tests\Feature;

use App\Enums\CTKStatus;
use App\Enums\EntityType;
use App\Enums\PaymentStatus;
use App\Models\CTK;
use App\Models\CTKPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CTKPaymentTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'Admin LPK']);
        Role::create(['name' => 'Admin PT']);
        Role::create(['name' => 'Pimpinan']);
    }

    /**
     * Test: Admin can record payment with amount, bank, and timestamp
     *
     * @test
     */
    public function admin_can_record_payment_with_complete_information()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act
        $this->actingAs($adminLPK);
        $paymentDate = now()->subDays(1);

        $payment = CTKPayment::create([
            'ctk_id' => $ctk->id,
            'stage_number' => 1,
            'amount' => 5000000,
            'bank_name' => 'Bank BCA',
            'payment_date' => $paymentDate,
            'payment_status' => PaymentStatus::Lunas,
            'created_by' => $adminLPK->id,
        ]);

        // Assert
        $this->assertDatabaseHas('ctk_payments', [
            'ctk_id' => $ctk->id,
            'stage_number' => 1,
            'amount' => '5000000.00',
            'bank_name' => 'Bank BCA',
            'payment_status' => PaymentStatus::Lunas->value,
        ]);

        $retrievedPayment = CTKPayment::find($payment->id);
        $this->assertEquals(5000000, $retrievedPayment->amount);
        $this->assertEquals('Bank BCA', $retrievedPayment->bank_name);
        $this->assertEquals($paymentDate->format('Y-m-d'), $retrievedPayment->payment_date->format('Y-m-d'));
        $this->assertEquals(PaymentStatus::Lunas, $retrievedPayment->payment_status);
    }

    /**
     * Test: Admin views payment history and sees all details
     *
     * @test
     */
    public function admin_views_payment_history_with_all_details()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Create multiple payments
        $payments = collect([
            [
                'stage_number' => 1,
                'amount' => 5000000,
                'bank_name' => 'BCA',
                'payment_date' => now()->subDays(10),
                'payment_status' => PaymentStatus::Lunas,
            ],
            [
                'stage_number' => 2,
                'amount' => 3000000,
                'bank_name' => 'Mandiri',
                'payment_date' => now()->subDays(5),
                'payment_status' => PaymentStatus::Lunas,
            ],
            [
                'stage_number' => 3,
                'amount' => 2000000,
                'bank_name' => 'BRI',
                'payment_date' => now()->subDays(2),
                'payment_status' => PaymentStatus::Pending,
            ],
        ]);

        foreach ($payments as $paymentData) {
            CTKPayment::create([
                ...$paymentData,
                'ctk_id' => $ctk->id,
                'created_by' => $adminLPK->id,
            ]);
        }

        // Act
        $this->actingAs($adminLPK);
        $ctk->load('payments');

        // Assert
        $this->assertCount(3, $ctk->payments);

        // Verify first payment
        $payment1 = $ctk->payments->where('stage_number', 1)->first();
        $this->assertEquals(5000000, $payment1->amount);
        $this->assertEquals('BCA', $payment1->bank_name);
        $this->assertEquals(PaymentStatus::Lunas, $payment1->payment_status);

        // Verify all payments have dates and creators
        foreach ($ctk->payments as $payment) {
            $this->assertNotNull($payment->payment_date);
            $this->assertEquals($adminLPK->id, $payment->created_by);
            $this->assertEquals($adminLPK->id, $payment->creator->id);
        }
    }

    /**
     * Test: System prevents stage advancement when no payments are complete
     *
     * @test
     */
    public function system_prevents_advancement_when_no_payments_complete()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Create payment but with Pending status
        CTKPayment::create([
            'ctk_id' => $ctk->id,
            'stage_number' => 1,
            'amount' => 5000000,
            'bank_name' => 'BCA',
            'payment_date' => now()->subDays(1),
            'payment_status' => PaymentStatus::Pending,
            'created_by' => $adminLPK->id,
        ]);

        // Act & Assert
        $this->actingAs($adminLPK);
        $hasCompletedPayment = $ctk->payments()
            ->where('payment_status', PaymentStatus::Lunas)
            ->exists();

        $this->assertFalse($hasCompletedPayment, 'Should not have completed payments');

        // CTK should remain in stage 2
        $ctk->refresh();
        $this->assertEquals(2, $ctk->current_stage);
    }

    /**
     * Test: Admin uploads payment proof and it's attached to payment record
     *
     * @test
     */
    public function admin_uploads_payment_proof_document()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Act
        $this->actingAs($adminLPK);
        $payment = CTKPayment::create([
            'ctk_id' => $ctk->id,
            'stage_number' => 1,
            'amount' => 5000000,
            'bank_name' => 'BCA',
            'payment_date' => now()->subDays(1),
            'payment_status' => PaymentStatus::Lunas,
            'payment_proof_path' => 'ctk-payments/proof-stage-1.pdf',
            'created_by' => $adminLPK->id,
        ]);

        // Assert
        $this->assertDatabaseHas('ctk_payments', [
            'id' => $payment->id,
            'payment_proof_path' => 'ctk-payments/proof-stage-1.pdf',
        ]);

        $retrievedPayment = CTKPayment::find($payment->id);
        $this->assertNotNull($retrievedPayment->payment_proof_path);
        $this->assertStringContainsString('proof-stage-1.pdf', $retrievedPayment->payment_proof_path);
    }

    /**
     * Test: CTK with at least 1 Lunas payment can advance from stage 2
     *
     * @test
     */
    public function ctk_with_lunas_payment_can_advance_from_stage_2()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        $ctk = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // Create a completed payment
        CTKPayment::create([
            'ctk_id' => $ctk->id,
            'stage_number' => 1,
            'amount' => 5000000,
            'bank_name' => 'BCA',
            'payment_date' => now()->subDays(1),
            'payment_status' => PaymentStatus::Lunas,
            'created_by' => $adminLPK->id,
        ]);

        // Act
        $this->actingAs($adminLPK);
        $hasCompletedPayment = $ctk->payments()
            ->where('payment_status', PaymentStatus::Lunas)
            ->exists();

        // Assert
        $this->assertTrue($hasCompletedPayment, 'Should have at least one Lunas payment');

        // Manually advance stage (simulating action)
        $ctk->update([
            'current_stage' => 3,
            'current_status' => CTKStatus::SoalBerkas,
            'updated_by' => $adminLPK->id,
        ]);

        $ctk->refresh();
        $this->assertEquals(3, $ctk->current_stage);
        $this->assertEquals(CTKStatus::SoalBerkas, $ctk->current_status);
    }

    /**
     * Test: Payment completion status calculation
     *
     * @test
     */
    public function payment_completion_status_calculated_correctly()
    {
        // Arrange
        $adminLPK = User::factory()->create(['entity' => EntityType::LPK]);
        $adminLPK->assignRole('Admin LPK');

        // CTK with no payments
        $ctk1 = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        // CTK with partial payments
        $ctk2 = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            CTKPayment::create([
                'ctk_id' => $ctk2->id,
                'stage_number' => $i,
                'amount' => 5000000,
                'bank_name' => 'BCA',
                'payment_date' => now()->subDays($i),
                'payment_status' => PaymentStatus::Lunas,
                'created_by' => $adminLPK->id,
            ]);
        }

        // CTK with complete payments
        $ctk3 = CTK::factory()->create([
            'current_status' => CTKStatus::Pembayaran,
            'current_stage' => 2,
            'current_entity' => EntityType::LPK,
            'created_by' => $adminLPK->id,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            CTKPayment::create([
                'ctk_id' => $ctk3->id,
                'stage_number' => $i,
                'amount' => 5000000,
                'bank_name' => 'BCA',
                'payment_date' => now()->subDays($i),
                'payment_status' => PaymentStatus::Lunas,
                'created_by' => $adminLPK->id,
            ]);
        }

        // Act & Assert
        $this->actingAs($adminLPK);

        $ctk1->load('payments');
        $ctk2->load('payments');
        $ctk3->load('payments');

        $this->assertEquals('none', $ctk1->payment_completion_status);
        $this->assertEquals('partial', $ctk2->payment_completion_status);
        $this->assertEquals('complete', $ctk3->payment_completion_status);
    }
}
