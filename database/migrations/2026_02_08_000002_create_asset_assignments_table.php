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
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();

            // Asset Reference
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');

            // Polymorphic Employee Reference (can be EmployeeLPK or User)
            $table->morphs('assignable');  // Creates assignable_type and assignable_id

            // Assignment Metadata
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->text('return_notes')->nullable();

            $table->timestamps();

            // Indexes for performance (morphs() already creates assignable index)
            $table->index('asset_id');
            $table->index('assigned_date');
            $table->index('return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
