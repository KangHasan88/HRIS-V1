<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_calibration_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performance_review_cycle_id');
            $table->string('session_number')->unique();
            $table->string('session_name');
            $table->date('session_date');
            $table->string('facilitator_name')->nullable();
            $table->string('calibration_scope')->default('company');
            $table->string('target_distribution')->default('balanced');
            $table->string('status')->default('draft');
            $table->json('rating_rules')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('performance_review_cycle_id', 'perf_cal_sess_cycle_fk')
                ->references('id')
                ->on('performance_review_cycles')
                ->cascadeOnDelete();
        });

        Schema::create('performance_calibration_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performance_calibration_session_id');
            $table->unsignedBigInteger('performance_review_form_id');
            $table->foreignId('employee_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->decimal('original_score', 5, 2)->default(0);
            $table->decimal('calibrated_score', 5, 2)->default(0);
            $table->string('original_rating')->nullable();
            $table->string('calibrated_rating');
            $table->string('rating_bucket')->default('meets');
            $table->string('calibration_decision')->default('keep');
            $table->text('decision_reason')->nullable();
            $table->json('evidence_snapshot')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('performance_calibration_session_id', 'perf_cal_item_session_fk')
                ->references('id')
                ->on('performance_calibration_sessions')
                ->cascadeOnDelete();
            $table->foreign('performance_review_form_id', 'perf_cal_item_form_fk')
                ->references('id')
                ->on('performance_review_forms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_calibration_items');
        Schema::dropIfExists('performance_calibration_sessions');
    }
};
