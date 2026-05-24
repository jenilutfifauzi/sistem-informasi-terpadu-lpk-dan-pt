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
        Schema::create('buku_induk_siswa', function (Blueprint $table) {
            $table->id();
            $table->string('foto_path')->nullable();
            $table->string('nama_lengkap');
            $table->string('nomor_induk', 50)->unique();
            $table->string('program_pendidikan');
            $table->string('program_bahasa')->nullable();
            $table->string('nama_panggilan')->nullable();
            $table->string('jenis_kelamin', 20);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 100)->nullable();
            $table->string('kewarganegaraan', 100)->default('Indonesia');
            $table->string('status_perkawinan', 50)->nullable();
            $table->string('nama_suami_istri')->nullable();
            $table->string('no_hp_suami_istri', 25)->nullable();
            $table->text('alamat_siswa')->nullable();
            $table->string('no_hp_siswa', 25)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat_orang_tua')->nullable();
            $table->string('no_hp_orang_tua', 25)->nullable();
            $table->string('golongan_darah', 20)->nullable();
            $table->text('penyakit_pernah_diderita')->nullable();
            $table->text('kelainan_jasmani')->nullable();
            $table->unsignedSmallInteger('tinggi_badan_cm')->nullable();
            $table->unsignedSmallInteger('berat_badan_kg')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_lengkap');
            $table->index('program_pendidikan');
            $table->index('program_bahasa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_induk_siswa');
    }
};
