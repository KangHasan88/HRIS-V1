<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individual_development_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_profile_id');
            $table->unsignedBigInteger('competency_role_requirement_id')->nullable();
            $table->unsignedBigInteger('training_course_id')->nullable();
            $table->string('plan_number', 100)->unique();
            $table->string('development_area');
            $table->string('career_goal')->nullable();
            $table->unsignedTinyInteger('current_level')->default(1);
            $table->unsignedTinyInteger('target_level')->default(3);
            $table->string('priority', 40)->default('medium');
            $table->string('learning_recommendation')->nullable();
            $table->text('action_plan')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->string('mentor_name', 160)->nullable();
            $table->string('reviewer_name', 160)->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_profile_id', 'status'], 'idp_employee_status_idx');
            $table->index(['priority', 'target_date'], 'idp_priority_target_idx');
            $table->foreign('employee_profile_id', 'idp_employee_fk')
                ->references('id')
                ->on('employee_profiles')
                ->cascadeOnDelete();
            $table->foreign('competency_role_requirement_id', 'idp_comp_req_fk')
                ->references('id')
                ->on('competency_role_requirements')
                ->nullOnDelete();
            $table->foreign('training_course_id', 'idp_course_fk')
                ->references('id')
                ->on('training_courses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_development_plans');
    }
};
