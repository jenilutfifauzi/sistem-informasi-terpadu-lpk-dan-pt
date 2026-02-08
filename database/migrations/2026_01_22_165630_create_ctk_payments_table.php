<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ctk_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_number'); // 1-5
            $table->decimal('amount', 15, 2);
            $table->string('bank_name');
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('payment_status'); // PaymentStatus enum
            $table->string('payment_proof_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ctk_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctk_payments');
    }
};
