<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('c_t_k_screenings', function (Blueprint $table) {
            $table->enum('screening_stage', ['Screening 1', 'Interview User'])
                ->default('Screening 1')
                ->after('screening_result');
        });

        // Data backfill: Set screening_stage based on existing interview_location patterns
        DB::table('c_t_k_screenings')
            ->whereRaw("LOWER(interview_location) LIKE '%interview%'")
            ->orWhereRaw("LOWER(interview_location) LIKE '%user%'")
            ->orWhereRaw("LOWER(interview_location) LIKE '%tahap 2%'")
            ->update(['screening_stage' => 'Interview User']);

        // Set remaining NULL records to 'Screening 1' (should be none due to default, but being explicit)
        DB::table('c_t_k_screenings')
            ->whereNull('screening_stage')
            ->update(['screening_stage' => 'Screening 1']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_t_k_screenings', function (Blueprint $table) {
            $table->dropColumn('screening_stage');
        });
    }
};
