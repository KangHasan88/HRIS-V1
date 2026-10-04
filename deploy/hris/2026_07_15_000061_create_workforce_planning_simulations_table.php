<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_planning_simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('simulation_number', 80)->unique();
            $table->string('scenario_name');
            $table->string('department')->nullable();
            $table->unsignedSmallInteger('current_headcount')->default(0);
            $table->unsignedSmallInteger('required_headcount')->default(0);
            $table->unsignedSmallInteger('hire_headcount')->default(0);
            $table->decimal('monthly_salary_budget', 14, 2)->default(0);
            $table->decimal('overtime_hourly_rate', 14, 2)->default(0);
            $table->decimal('overtime_hours_per_week', 8, 2)->default(0);
            $table->unsignedSmallInteger('planning_months')->default(1);
            $table->decimal('hiring_cost', 14, 2)->default(0);
            $table->decimal('training_cost', 14, 2)->default(0);
            $table->decimal('hire_scenario_cost', 14, 2)->default(0);
            $table->decimal('overtime_scenario_cost', 14, 2)->default(0);
            $table->decimal('hybrid_scenario_cost', 14, 2)->default(0);
            $table->string('recommended_scenario', 60)->default('hybrid');
            $table->json('assumption_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['department', 'recommended_scenario'], 'wf_plan_department_reco_idx');
            $table->index(['company_id', 'branch_id'], 'wf_plan_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_planning_simulations');
    }
};
