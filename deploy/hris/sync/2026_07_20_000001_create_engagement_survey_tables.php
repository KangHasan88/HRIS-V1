<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('survey_number', 100)->unique();
            $table->string('title');
            $table->string('survey_type', 60)->default('pulse');
            $table->string('audience_scope', 80)->default('all_employee');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('question_bank')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->string('owner_name', 160)->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'end_date'], 'eng_survey_status_end_idx');
            $table->index(['survey_type', 'audience_scope'], 'eng_survey_type_scope_idx');
        });

        Schema::create('engagement_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('engagement_survey_id');
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('response_number', 100)->unique();
            $table->unsignedTinyInteger('engagement_score')->default(0);
            $table->unsignedTinyInteger('enps_score')->default(0);
            $table->string('sentiment', 40)->default('neutral');
            $table->json('dimension_scores')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 40)->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['engagement_score', 'sentiment'], 'eng_resp_score_sentiment_idx');
            $table->foreign('engagement_survey_id', 'eng_resp_survey_fk')
                ->references('id')
                ->on('engagement_surveys')
                ->cascadeOnDelete();
        });

        Schema::create('engagement_action_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('engagement_survey_id');
            $table->string('action_number', 100)->unique();
            $table->string('focus_area');
            $table->string('root_cause')->nullable();
            $table->text('action_plan')->nullable();
            $table->string('owner_name', 160)->nullable();
            $table->date('target_date')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('impact_metric')->nullable();
            $table->string('priority', 40)->default('medium');
            $table->string('status', 40)->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['priority', 'status'], 'eng_action_priority_status_idx');
            $table->foreign('engagement_survey_id', 'eng_action_survey_fk')
                ->references('id')
                ->on('engagement_surveys')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_action_plans');
        Schema::dropIfExists('engagement_survey_responses');
        Schema::dropIfExists('engagement_surveys');
    }
};
