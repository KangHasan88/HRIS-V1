<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_review_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('cycle_code')->unique();
            $table->string('cycle_name');
            $table->string('review_type')->default('360');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('submission_deadline')->nullable();
            $table->string('status')->default('draft');
            $table->json('competency_groups')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('performance_review_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_cycle_id')->constrained('performance_review_cycles')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('form_number')->unique();
            $table->string('review_scope')->default('annual');
            $table->string('status')->default('draft');
            $table->decimal('target_score', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->text('summary_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('performance_review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_form_id')->constrained('performance_review_forms')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('reviewer_employee_profile_id')->nullable();
            $table->string('reviewer_type');
            $table->decimal('score', 5, 2)->default(0);
            $table->json('competency_scores')->nullable();
            $table->text('strength_feedback')->nullable();
            $table->text('improvement_feedback')->nullable();
            $table->text('development_suggestion')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('reviewer_employee_profile_id', 'perf_resp_reviewer_emp_fk')
                ->references('id')
                ->on('employee_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_review_responses');
        Schema::dropIfExists('performance_review_forms');
        Schema::dropIfExists('performance_review_cycles');
    }
};
