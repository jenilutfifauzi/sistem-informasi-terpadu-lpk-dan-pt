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
            $table->enum('opp_status', ['Belum', 'Diterima'])->default('Belum')->after('current_entity');
            $table->date('opp_receipt_date')->nullable()->after('opp_status');
            $table->string('opp_document_path')->nullable()->after('opp_receipt_date');
            $table->date('departure_date')->nullable()->after('opp_document_path');
            $table->string('flight_number', 50)->nullable()->after('departure_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ctk', function (Blueprint $table) {
            $table->dropColumn(['opp_status', 'opp_receipt_date', 'opp_document_path', 'departure_date', 'flight_number']);
        });
    }
};
