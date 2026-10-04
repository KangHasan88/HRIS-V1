<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_goal_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('cycle_code')->unique();
            $table->string('cycle_name');
            $table->string('period_type')->default('quarter');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('checkin_deadline')->nullable();
            $table->string('scoring_method')->default('weighted_progress');
            $table->string('status')->default('draft');
            $table->json('calibration_rules')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('performance_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_goal_cycle_id')->constrained('performance_goal_cycles')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('parent_goal_id')->nullable();
            $table->string('goal_number')->unique();
            $table->string('goal_type')->default('individual');
            $table->string('objective_title');
            $table->text('objective_description')->nullable();
            $table->string('key_result_metric');
            $table->decimal('baseline_value', 12, 2)->default(0);
            $table->decimal('target_value', 12, 2)->default(100);
            $table->decimal('current_value', 12, 2)->default(0);
            $table->decimal('weight', 5, 2)->default(100);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->string('confidence_level')->default('medium');
            $table->string('status')->default('draft');
            $table->json('cascading_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('parent_goal_id', 'perf_goal_parent_fk')
                ->references('id')
                ->on('performance_goals')
                ->nullOnDelete();
        });

        Schema::create('performance_goal_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_goal_id')->constrained('performance_goals')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checkin_number')->unique();
            $table->date('checkin_date');
            $table->decimal('previous_value', 12, 2)->default(0);
            $table->decimal('new_value', 12, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->string('confidence_level')->default('medium');
            $table->string('risk_status')->default('on_track');
            $table->text('achievement_notes')->nullable();
            $table->text('blocker_notes')->nullable();
            $table->text('next_action')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_goal_check_ins');
        Schema::dropIfExists('performance_goals');
        Schema::dropIfExists('performance_goal_cycles');
    }
};
