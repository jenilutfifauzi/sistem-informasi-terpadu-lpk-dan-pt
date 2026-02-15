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
        Schema::create('asset_condition_histories', function (Blueprint $table) {
            $table->id();
            
            // Asset Reference
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // Condition Change Tracking
            $table->string('old_condition', 20);
            $table->string('new_condition', 20);
            $table->text('reason')->nullable();
            
            // Audit
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('changed_at');
            
            // Indexes for performance
            $table->index('asset_id');
            $table->index('changed_at');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_condition_histories');
    }
};
