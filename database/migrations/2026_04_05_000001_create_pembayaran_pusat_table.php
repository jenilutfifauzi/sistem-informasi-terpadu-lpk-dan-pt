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
        Schema::create('pembayaran_pusat', function (Blueprint $table) {
            $table->id();
            $table->enum('entity', ['LPK', 'PT'])->index();
            $table->foreignId('ctk_id')->constrained('ctk')->restrictOnDelete();
            $table->date('tanggal_pembayaran');
            $table->decimal('nominal', 15, 2);
            $table->string('bukti_transfer_path', 500)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Composite index for common queries
            $table->index(['entity', 'tanggal_pembayaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pusat');
    }
};
