<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan_pt', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->char('nik', 16)->unique()->comment('NIK Indonesia 16 digit');
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->text('alamat');
            $table->string('telepon', 20);

            // Employment Information
            $table->enum('jabatan', ['Direktur', 'Manajer', 'Staf HRD', 'Staf Keuangan', 'Staf Operasional', 'Staf Administrasi']);
            $table->enum('divisi', ['Manajemen', 'HRD', 'Keuangan', 'Operasional', 'Administrasi']);
            $table->enum('status', ['Aktif', 'Cuti', 'Resign'])->default('Aktif');
            $table->enum('jenis_kontrak', ['Tetap', 'PKWT', 'Probasi']);
            $table->date('tanggal_bergabung');

            // Compensation (Nullable)
            $table->decimal('gaji_pokok', 15, 2)->nullable()->comment('Rupiah');
            $table->decimal('tunjangan', 15, 2)->nullable()->comment('Rupiah');

            // Photo & Document (Nullable)
            $table->string('foto_path')->nullable()->comment('Path to photo in public disk');
            $table->string('dokumen_path')->nullable()->comment('Path to HR document in private storage');

            // Entity Isolation (Constitution Principle II)
            $table->enum('entity', ['PT', 'LPK'])->default('PT')->comment('Entity isolation: PT only');

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete for data retention');

            // Indexes
            $table->index('jabatan');
            $table->index('divisi');
            $table->index('status');
            $table->index('jenis_kontrak');
            $table->index('entity');
            $table->index('deleted_at');

            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan_pt');
    }
};
