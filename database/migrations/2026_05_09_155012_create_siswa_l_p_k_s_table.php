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
        Schema::create('siswa_lpk', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut')->nullable();
            $table->string('nomor_induk', 50)->unique();
            $table->string('nama_siswa');
            $table->string('jenis_kelamin', 1);
            $table->string('agama', 100)->nullable();
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->date('tanggal_masuk');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp', 25);
            $table->string('email')->nullable();
            $table->string('program_pendidikan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_siswa');
            $table->index('program_pendidikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_lpk');
    }
};
