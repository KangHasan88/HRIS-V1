<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_mobility_opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_position_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('opportunity_number', 100)->unique();
            $table->string('title');
            $table->string('career_track', 120)->default('general');
            $table->string('movement_type', 60)->default('promotion');
            $table->string('readiness_requirement', 60)->default('ready_12_months');
            $table->unsignedInteger('open_slots')->default(1);
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->text('eligibility_note')->nullable();
            $table->text('development_benefit')->nullable();
            $table->string('owner_name', 160)->nullable();
            $table->string('status', 40)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'close_date'], 'mobility_opp_status_close_idx');
            $table->index(['career_track', 'movement_type'], 'mobility_opp_track_type_idx');
            $table->foreign('job_position_id', 'mobility_opp_position_fk')->references('id')->on('job_positions')->cascadeOnDelete();
            $table->foreign('company_id', 'mobility_opp_company_fk')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('branch_id', 'mobility_opp_branch_fk')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('internal_mobility_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_mobility_opportunity_id');
            $table->unsignedBigInteger('employee_profile_id');
            $table->unsignedBigInteger('succession_candidate_id')->nullable();
            $table->unsignedBigInteger('individual_development_plan_id')->nullable();
            $table->string('application_number', 100)->unique();
            $table->string('source', 60)->default('employee_self_nomination');
            $table->unsignedTinyInteger('match_score')->default(50);
            $table->string('readiness_level', 60)->default('ready_12_months');
            $table->string('manager_support', 60)->default('pending');
            $table->string('mobility_constraint')->nullable();
            $table->text('motivation_note')->nullable();
            $table->text('screening_note')->nullable();
            $table->string('status', 40)->default('submitted');
            $table->date('target_movement_date')->nullable();
            $table->string('owner_name', 160)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'match_score'], 'mobility_app_status_score_idx');
            $table->foreign('internal_mobility_opportunity_id', 'mobility_app_opp_fk')->references('id')->on('internal_mobility_opportunities')->cascadeOnDelete();
            $table->foreign('employee_profile_id', 'mobility_app_employee_fk')->references('id')->on('employee_profiles')->cascadeOnDelete();
            $table->foreign('succession_candidate_id', 'mobility_app_succ_candidate_fk')->references('id')->on('succession_candidates')->nullOnDelete();
            $table->foreign('individual_development_plan_id', 'mobility_app_idp_fk')->references('id')->on('individual_development_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_mobility_applications');
        Schema::dropIfExists('internal_mobility_opportunities');
    }
};
