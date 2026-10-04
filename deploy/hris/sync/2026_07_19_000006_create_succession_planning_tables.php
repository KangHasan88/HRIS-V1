<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('succession_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_position_id');
            $table->unsignedBigInteger('incumbent_employee_profile_id')->nullable();
            $table->string('plan_number', 100)->unique();
            $table->string('criticality', 40)->default('medium');
            $table->string('vacancy_risk', 40)->default('medium');
            $table->date('target_ready_by')->nullable();
            $table->string('business_impact')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['criticality', 'vacancy_risk'], 'succ_plan_risk_idx');
            $table->index(['status', 'target_ready_by'], 'succ_plan_status_target_idx');
            $table->foreign('job_position_id', 'succ_plan_position_fk')
                ->references('id')
                ->on('job_positions')
                ->cascadeOnDelete();
            $table->foreign('incumbent_employee_profile_id', 'succ_plan_incumbent_fk')
                ->references('id')
                ->on('employee_profiles')
                ->nullOnDelete();
        });

        Schema::create('succession_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('succession_plan_id');
            $table->unsignedBigInteger('employee_profile_id');
            $table->unsignedBigInteger('individual_development_plan_id')->nullable();
            $table->string('candidate_number', 100)->unique();
            $table->string('readiness_level', 40)->default('ready_12_months');
            $table->unsignedTinyInteger('readiness_score')->default(50);
            $table->string('performance_rating', 40)->default('meets');
            $table->string('potential_rating', 40)->default('medium');
            $table->string('mobility_preference', 80)->default('same_site');
            $table->string('retention_risk', 40)->default('medium');
            $table->text('development_gap')->nullable();
            $table->text('next_action')->nullable();
            $table->string('owner_name', 160)->nullable();
            $table->date('review_date')->nullable();
            $table->string('status', 40)->default('nominated');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['readiness_level', 'readiness_score'], 'succ_candidate_ready_idx');
            $table->index(['performance_rating', 'potential_rating'], 'succ_candidate_grid_idx');
            $table->foreign('succession_plan_id', 'succ_candidate_plan_fk')
                ->references('id')
                ->on('succession_plans')
                ->cascadeOnDelete();
            $table->foreign('employee_profile_id', 'succ_candidate_employee_fk')
                ->references('id')
                ->on('employee_profiles')
                ->cascadeOnDelete();
            $table->foreign('individual_development_plan_id', 'succ_candidate_idp_fk')
                ->references('id')
                ->on('individual_development_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_candidates');
        Schema::dropIfExists('succession_plans');
    }
};
