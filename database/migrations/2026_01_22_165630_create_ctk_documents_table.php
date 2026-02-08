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
        Schema::create('ctk_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->string('document_type'); // DocumentType enum
            $table->string('filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('upload_timestamp')->useCurrent();

            $table->index('ctk_id');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctk_documents');
    }
};
