<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_overtime_capacity_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('prediction_number')->unique();
            $table->string('prediction_name');
            $table->string('department')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('planned_headcount')->default(0);
            $table->unsignedInteger('minimum_required_headcount')->default(0);
            $table->decimal('existing_leave_days', 10, 2)->default(0);
            $table->decimal('forecast_leave_days', 10, 2)->default(0);
            $table->decimal('existing_overtime_hours', 10, 2)->default(0);
            $table->decimal('forecast_overtime_hours', 10, 2)->default(0);
            $table->decimal('estimated_capacity_days', 10, 2)->default(0);
            $table->decimal('projected_available_days', 10, 2)->default(0);
            $table->decimal('capacity_gap_days', 10, 2)->default(0);
            $table->decimal('overtime_needed_hours', 10, 2)->default(0);
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->string('risk_level')->default('low');
            $table->json('recommendation_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'locp_company_branch_idx');
            $table->index(['period_start', 'period_end'], 'locp_period_idx');
            $table->index('risk_level', 'locp_risk_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_overtime_capacity_predictions');
    }
};
