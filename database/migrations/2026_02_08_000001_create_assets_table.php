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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Entity & Classification
            $table->string('entity', 10);  // ENUM: 'PT', 'LPK' - from EntityType enum
            $table->string('kategori', 50);  // ENUM: AssetCategory values

            // Asset Identification
            $table->string('nomor_inventaris', 50)->unique();  // Format: PT-ELK-2024-001
            $table->string('nama_barang');
            $table->text('deskripsi')->nullable();

            // Quantity & Condition
            $table->integer('jumlah')->unsigned();  // Must be >= 1 when active
            $table->string('satuan', 50);  // Unit, Set, Buah, etc.
            $table->string('kondisi', 20);  // ENUM: 'Baik', 'Rusak' - from AssetCondition enum
            $table->string('status_assignment', 20)->default('Available');  // 'Assigned', 'Available'

            // Purchase Information
            $table->integer('tahun_pembelian')->unsigned();  // Year (e.g., 2024)
            $table->decimal('nilai_pembelian', 15, 2)->default(0);  // Purchase value in IDR

            // Location & Notes
            $table->string('lokasi')->nullable();  // Free-text location
            $table->text('keterangan')->nullable();  // General notes

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('entity');
            $table->index('kategori');
            $table->index('kondisi');
            $table->index('status_assignment');
            $table->index(['entity', 'kategori']);  // Composite for filtered queries
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
