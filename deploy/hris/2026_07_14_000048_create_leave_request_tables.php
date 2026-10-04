<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 80)->unique();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_policy_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('leave_policy_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('requested_days', 8, 2)->default(0);
            $table->decimal('balance_before', 8, 2)->default(0);
            $table->decimal('balance_after', 8, 2)->default(0);
            $table->string('duration_unit', 40)->default('day');
            $table->string('request_source', 40)->default('web_admin');
            $table->string('approval_status', 40)->default('submitted');
            $table->string('current_approver')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->json('simulation_snapshot')->nullable();
            $table->text('reason')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'start_date'], 'leave_req_status_start_idx');
            $table->index(['employee_profile_id', 'start_date'], 'leave_req_emp_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
