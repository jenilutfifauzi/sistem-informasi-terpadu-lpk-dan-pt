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
        Schema::table('ctk', function (Blueprint $table) {
            // Stage 3: Soal/Berkas
            $table->enum('soal_berkas_status', ['Belum', 'Lengkap'])->nullable()->after('current_entity');

            // Stage 4: Paspor
            $table->string('paspor_number')->nullable()->after('soal_berkas_status');

            // Stage 8: Ijin Desa
            $table->enum('ijin_desa_status', ['Belum', 'Ada'])->nullable()->after('paspor_number');

            // Stage 9: Rekomendasi
            $table->enum('rekomendasi_status', ['Belum', 'Ada'])->nullable()->after('ijin_desa_status');

            // Stage 10: WP (Working Permit)
            $table->enum('wp_status', ['Belum', 'Lengkap'])->nullable()->after('rekomendasi_status');

            // Stage 11: Apply Visa
            $table->enum('apply_visa_status', ['Belum', 'Diajukan'])->nullable()->after('wp_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ctk', function (Blueprint $table) {
            $table->dropColumn([
                'soal_berkas_status',
                'paspor_number',
                'ijin_desa_status',
                'rekomendasi_status',
                'wp_status',
                'apply_visa_status',
            ]);
        });
    }
};
