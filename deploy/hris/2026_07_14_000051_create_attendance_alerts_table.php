<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_number', 80)->unique();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_clock_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendance_shift_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('alert_date');
            $table->string('alert_type', 60);
            $table->string('severity', 30)->default('medium');
            $table->string('status', 40)->default('open');
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->decimal('worked_hours', 6, 2)->default(0);
            $table->decimal('fatigue_score', 5, 2)->default(0);
            $table->string('compliance_rule')->nullable();
            $table->string('owner_name')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            $table->json('alert_snapshot')->nullable();
            $table->text('trigger_reason')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['alert_type', 'status'], 'att_alert_type_status_idx');
            $table->index(['severity', 'alert_date'], 'att_alert_sev_date_idx');
            $table->index(['employee_profile_id', 'alert_date'], 'att_alert_emp_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_alerts');
    }
};
