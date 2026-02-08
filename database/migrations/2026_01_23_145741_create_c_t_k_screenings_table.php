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
        Schema::create('c_t_k_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('restrict');
            $table->date('interview_date');
            $table->string('interview_location');
            $table->enum('screening_result', ['Lolos', 'Tidak Lolos'])->default('Lolos');
            $table->text('interview_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_t_k_screenings');
    }
};
