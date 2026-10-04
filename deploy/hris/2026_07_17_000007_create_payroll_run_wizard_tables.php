<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('run_number', 80)->unique();
            $table->string('period', 20);
            $table->date('cutoff_start')->nullable();
            $table->date('cutoff_end')->nullable();
            $table->string('run_type', 40)->default('regular');
            $table->string('status', 40)->default('draft');
            $table->unsignedInteger('employee_count')->default(0);
            $table->unsignedInteger('validation_passed')->default(0);
            $table->unsignedInteger('validation_failed')->default(0);
            $table->unsignedInteger('anomaly_count')->default(0);
            $table->decimal('gross_estimate', 18, 2)->default(0);
            $table->decimal('deduction_estimate', 18, 2)->default(0);
            $table->decimal('net_estimate', 18, 2)->default(0);
            $table->json('source_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_run_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->string('check_code', 80);
            $table->string('check_name', 180);
            $table->string('severity', 40)->default('warning');
            $table->string('status', 40)->default('pending');
            $table->unsignedInteger('affected_count')->default(0);
            $table->text('message')->nullable();
            $table->json('sample_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_run_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('anomaly_code', 80);
            $table->string('anomaly_name', 180);
            $table->string('severity', 40)->default('medium');
            $table->decimal('previous_amount', 18, 2)->default(0);
            $table->decimal('current_amount', 18, 2)->default(0);
            $table->decimal('variance_percent', 10, 2)->default(0);
            $table->string('status', 40)->default('open');
            $table->text('message')->nullable();
            $table->json('evidence_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_anomalies');
        Schema::dropIfExists('payroll_run_validations');
        Schema::dropIfExists('payroll_runs');
    }
};
