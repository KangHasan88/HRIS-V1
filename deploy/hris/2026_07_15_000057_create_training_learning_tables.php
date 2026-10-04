<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('course_code', 80)->unique();
            $table->string('title');
            $table->string('category', 80)->default('technical');
            $table->string('delivery_mode', 60)->default('classroom');
            $table->string('provider')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->string('status', 40)->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['company_id', 'branch_id']);
        });

        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('enrollment_number', 80)->unique();
            $table->string('enrollment_status', 40)->default('enrolled');
            $table->string('attendance_status', 40)->default('not_started');
            $table->timestamp('attended_at')->nullable();
            $table->string('certificate_number', 120)->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
            $table->string('certificate_file_reference')->nullable();
            $table->decimal('evaluation_score', 5, 2)->default(0);
            $table->text('evaluation_feedback')->nullable();
            $table->text('manager_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['training_course_id', 'employee_profile_id'], 'training_enrollment_course_employee_unique');
            $table->index(['enrollment_status', 'attendance_status'], 'training_enrollment_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_enrollments');
        Schema::dropIfExists('training_courses');
    }
};
