<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('allocation_number', 80)->unique();
            $table->string('category', 60)->default('overtime');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('allocated_amount', 14, 2)->default(0);
            $table->unsignedTinyInteger('alert_threshold_percent')->default(80);
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'status'], 'mgr_budget_category_status_idx');
            $table->index(['period_start', 'period_end'], 'mgr_budget_period_idx');
            $table->index(['company_id', 'branch_id'], 'mgr_budget_scope_idx');
        });

        Schema::create('manager_claim_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_number', 80)->unique();
            $table->date('claim_date');
            $table->string('claim_type', 60)->default('other');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('approval_status', 40)->default('submitted');
            $table->string('current_approver')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->text('description')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'claim_date'], 'mgr_claim_status_date_idx');
            $table->index(['employee_profile_id', 'claim_date'], 'mgr_claim_emp_date_idx');
            $table->index(['company_id', 'branch_id'], 'mgr_claim_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_claim_requests');
        Schema::dropIfExists('manager_budget_allocations');
    }
};
