<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('loan_number', 80)->unique();
            $table->string('loan_type', 60)->default('employee_loan');
            $table->string('provider', 120)->nullable();
            $table->date('loan_date');
            $table->decimal('principal_amount', 14, 2)->default(0);
            $table->decimal('admin_fee', 14, 2)->default(0);
            $table->decimal('interest_amount', 14, 2)->default(0);
            $table->unsignedSmallInteger('tenor_months')->default(1);
            $table->decimal('monthly_installment', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('outstanding_amount', 14, 2)->default(0);
            $table->string('deduction_component_code', 80)->nullable();
            $table->string('payroll_posting_status', 40)->default('not_posted');
            $table->string('approval_status', 40)->default('draft');
            $table->string('current_approver')->nullable();
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'loan_date'], 'emp_loans_status_date_idx');
            $table->index(['employee_profile_id', 'payroll_posting_status'], 'emp_loans_emp_posting_idx');
        });

        Schema::create('employee_loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('installment_no');
            $table->string('payroll_period', 7);
            $table->date('due_date')->nullable();
            $table->decimal('scheduled_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('scheduled');
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_loan_id', 'installment_no'], 'emp_loan_inst_no_unique');
            $table->index(['payroll_period', 'status'], 'emp_loan_inst_period_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_loan_installments');
        Schema::dropIfExists('employee_loans');
    }
};
