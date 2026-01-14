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
        Schema::create('karyawan_lpk', function (Blueprint $table) {
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
            $table->enum('jabatan', ['Instruktur', 'Admin LPK', 'Staff']);
            $table->enum('status', ['Aktif', 'Cuti', 'Resign'])->default('Aktif');
            $table->date('tanggal_bergabung');

            // Compensation (Nullable - may not be set for all employees)
            $table->decimal('honor_pokok', 15, 2)->nullable()->comment('Rupiah');
            $table->decimal('honor_per_jam', 15, 2)->nullable()->comment('Rupiah - Only for Instruktur');

            // Certificate (Nullable - only for Instruktur)
            $table->string('sertifikat_path')->nullable()->comment('Path to certificate file in private storage');

            // Entity Isolation (Constitution Principle II)
            $table->enum('entity', ['PT', 'LPK'])->default('LPK')->comment('Entity isolation: LPK only');

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete for data retention');

            // Indexes
            $table->index('jabatan');
            $table->index('status');
            $table->index('entity');
            $table->index('deleted_at');

            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_lpk');
    }
};
