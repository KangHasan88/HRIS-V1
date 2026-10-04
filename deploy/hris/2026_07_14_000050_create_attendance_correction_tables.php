<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('correction_number', 80)->unique();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_clock_event_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date');
            $table->string('correction_type', 60)->default('missing_clock_in');
            $table->timestamp('original_event_at')->nullable();
            $table->timestamp('requested_event_at');
            $table->string('requested_event_type', 40)->default('clock_in');
            $table->string('request_source', 40)->default('web_admin');
            $table->string('approval_status', 40)->default('submitted');
            $table->string('current_approver')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by')->nullable();
            $table->boolean('creates_clock_event')->default(false);
            $table->string('generated_event_number', 80)->nullable();
            $table->string('attachment_reference')->nullable();
            $table->json('correction_snapshot')->nullable();
            $table->text('reason')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'attendance_date'], 'att_corr_status_date_idx');
            $table->index(['employee_profile_id', 'attendance_date'], 'att_corr_emp_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
