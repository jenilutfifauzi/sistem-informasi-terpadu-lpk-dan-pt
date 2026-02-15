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
        Schema::create('c_t_k_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('karyawan_lpk')->onDelete('restrict');
            $table->date('training_start_date');
            $table->date('training_end_date')->nullable();
            $table->string('training_location');
            $table->integer('training_hours')->default(0);
            $table->text('completion_notes')->nullable();
            $table->enum('completion_status', ['Belum Selesai', 'Selesai'])->default('Belum Selesai');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_t_k_trainings');
    }
};
