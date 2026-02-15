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
        Schema::create('visa_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->string('application_status'); // Diajukan, Terbit
            $table->date('application_date');
            $table->string('visa_number')->nullable();
            $table->date('issuance_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_country')->nullable();
            $table->string('visa_type')->nullable();
            $table->string('visa_document_path')->nullable();
            $table->timestamps();

            $table->index('ctk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_records');
    }
};
