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
        Schema::create('c_t_k_medical_fulls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->enum('status', ['Belum', 'Selesai'])->default('Belum');
            $table->date('examination_date');
            $table->string('medical_report_path')->nullable();
            $table->text('examination_findings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ctk_id');
            $table->index('status');
            $table->index('examination_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_t_k_medical_fulls');
    }
};
