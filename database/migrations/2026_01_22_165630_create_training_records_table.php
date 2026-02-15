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
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('karyawan_lpk')->nullOnDelete();
            $table->date('start_date');
            $table->date('completion_date')->nullable();
            $table->string('training_status'); // Aktif, Selesai
            $table->string('training_location')->nullable();
            $table->unsignedInteger('training_hours')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();

            $table->index('ctk_id');
            $table->index('instructor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_records');
    }
};
