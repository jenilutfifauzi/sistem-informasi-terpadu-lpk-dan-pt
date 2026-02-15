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
        Schema::create('stage_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctk_id')->constrained('ctk')->cascadeOnDelete();
            $table->unsignedTinyInteger('from_stage');
            $table->unsignedTinyInteger('to_stage');
            $table->timestamp('transition_timestamp')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('transition_reason')->nullable();
            $table->foreignId('approval_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index('ctk_id');
            $table->index('transition_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_transitions');
    }
};
