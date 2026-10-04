<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payroll_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('adjustment_number', 80)->unique();
            $table->string('adjustment_type', 60)->default('retroactive_salary');
            $table->string('component_code', 80);
            $table->string('component_name');
            $table->string('component_direction', 40)->default('earning');
            $table->string('period_from', 7);
            $table->string('period_to', 7);
            $table->string('posting_period', 7);
            $table->decimal('base_amount', 14, 2)->default(0);
            $table->decimal('adjustment_amount', 14, 2)->default(0);
            $table->decimal('taxable_amount', 14, 2)->default(0);
            $table->string('tax_treatment', 60)->default('regular_taxable');
            $table->string('status', 40)->default('draft');
            $table->string('current_approver')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->json('impact_snapshot')->nullable();
            $table->text('reason')->nullable();
            $table->text('audit_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['posting_period', 'status'], 'pay_adj_period_status_idx');
            $table->index(['employee_profile_id', 'posting_period'], 'pay_adj_emp_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
