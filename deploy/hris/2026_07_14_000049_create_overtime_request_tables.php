<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 80)->unique();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->date('overtime_date');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->decimal('requested_hours', 8, 2)->default(0);
            $table->decimal('approved_hours', 8, 2)->default(0);
            $table->decimal('payroll_multiplier', 8, 2)->default(1);
            $table->decimal('hourly_rate', 14, 2)->default(0);
            $table->decimal('estimated_amount', 14, 2)->default(0);
            $table->string('overtime_type', 60)->default('regular_day');
            $table->string('request_source', 40)->default('web_admin');
            $table->string('approval_status', 40)->default('submitted');
            $table->string('current_approver')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by')->nullable();
            $table->boolean('handoff_to_payroll')->default(false);
            $table->timestamp('payroll_handoff_at')->nullable();
            $table->string('payroll_component_code', 80)->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->text('reason')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'overtime_date'], 'ot_req_status_date_idx');
            $table->index(['employee_profile_id', 'overtime_date'], 'ot_req_emp_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
