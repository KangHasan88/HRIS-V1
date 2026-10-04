<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->string('interview_number', 80)->unique();
            $table->string('interview_stage', 40)->default('hr');
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('interview_mode', 30)->default('online');
            $table->string('location_or_link')->nullable();
            $table->string('interviewer_name', 160);
            $table->string('interviewer_role', 120)->nullable();
            $table->string('status', 40)->default('scheduled');
            $table->unsignedTinyInteger('communication_score')->default(0);
            $table->unsignedTinyInteger('technical_score')->default(0);
            $table->unsignedTinyInteger('culture_score')->default(0);
            $table->unsignedTinyInteger('experience_score')->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->string('recommendation', 40)->default('hold');
            $table->text('feedback_notes')->nullable();
            $table->json('scorecard_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at'], 'ri_status_date_idx');
            $table->index(['recruitment_candidate_id', 'interview_stage'], 'ri_candidate_stage_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_interviews');
    }
};
