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
        Schema::create('mcu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->string('status'); // MCUStatus enum
            $table->date('examination_date');
            $table->string('clinic_name');
            $table->string('examiner_name');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('ctk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcu_records');
    }
};
