<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('shift_type', 60)->default('regular');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('break_minutes')->default(60);
            $table->boolean('is_cross_day')->default(false);
            $table->boolean('is_split_shift')->default(false);
            $table->time('split_start_time')->nullable();
            $table->time('split_end_time')->nullable();
            $table->decimal('planned_hours', 5, 2)->default(8);
            $table->unsignedSmallInteger('grace_late_minutes')->default(0);
            $table->unsignedSmallInteger('grace_early_leave_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id']);
            $table->index(['shift_type', 'is_active']);
        });

        Schema::create('attendance_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_shift_pattern_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('assignment_scope', 60)->default('employee');
            $table->string('rotation_cycle', 80)->default('fixed');
            $table->date('effective_start');
            $table->date('effective_end')->nullable();
            $table->json('work_days')->nullable();
            $table->json('off_days')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assignment_scope', 'status']);
            $table->index(['effective_start', 'effective_end']);
            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_assignments');
        Schema::dropIfExists('attendance_shift_patterns');
    }
};
