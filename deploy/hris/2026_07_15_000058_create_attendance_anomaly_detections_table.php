<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_anomaly_detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_clock_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('detection_number', 80)->unique();
            $table->string('detection_key', 160)->unique();
            $table->string('anomaly_type', 60);
            $table->string('severity', 40)->default('medium');
            $table->timestamp('detected_at');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->string('status', 40)->default('open');
            $table->json('evidence_snapshot')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['anomaly_type', 'severity'], 'attendance_anomaly_type_severity_idx');
            $table->index(['status', 'detected_at'], 'attendance_anomaly_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_anomaly_detections');
    }
};
