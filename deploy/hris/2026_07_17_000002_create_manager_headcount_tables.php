<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_headcount_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('plan_number')->unique();
            $table->string('department')->nullable();
            $table->string('position_title');
            $table->string('request_type');
            $table->unsignedInteger('current_headcount')->default(0);
            $table->unsignedInteger('requested_headcount')->default(1);
            $table->date('planning_period');
            $table->date('target_join_date')->nullable();
            $table->string('priority')->default('medium');
            $table->decimal('budget_estimate', 18, 2)->default(0);
            $table->string('budget_status')->default('not_checked');
            $table->string('approval_status')->default('draft');
            $table->string('current_approver')->nullable();
            $table->text('business_reason')->nullable();
            $table->text('risk_if_not_filled')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('manager_replacement_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('requisition_number')->unique();
            $table->string('position_title');
            $table->string('replacement_type');
            $table->date('vacancy_date')->nullable();
            $table->date('target_join_date')->nullable();
            $table->string('employment_type')->default('permanent');
            $table->string('priority')->default('medium');
            $table->string('approval_status')->default('draft');
            $table->string('current_approver')->nullable();
            $table->json('skill_requirements')->nullable();
            $table->text('replacement_reason')->nullable();
            $table->text('handover_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_replacement_requisitions');
        Schema::dropIfExists('manager_headcount_plans');
    }
};
