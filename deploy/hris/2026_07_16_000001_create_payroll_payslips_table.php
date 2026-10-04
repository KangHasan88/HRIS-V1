<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payslip_number')->unique();
            $table->string('period');
            $table->string('status')->default('draft');
            $table->decimal('gross_pay', 18, 2)->default(0);
            $table->decimal('total_deduction', 18, 2)->default(0);
            $table->decimal('net_pay', 18, 2)->default(0);
            $table->json('earning_snapshot');
            $table->json('deduction_snapshot');
            $table->json('employee_snapshot');
            $table->string('checksum');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('resend_count')->default(0);
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'status'], 'payroll_payslips_period_company_status_idx');
            $table->index(['employee_profile_id', 'period'], 'payroll_payslips_employee_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payslips');
    }
};
