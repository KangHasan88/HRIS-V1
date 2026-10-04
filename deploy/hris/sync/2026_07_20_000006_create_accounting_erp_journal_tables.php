<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payroll_accounting_journal_lines');
        Schema::dropIfExists('payroll_accounting_journal_batches');
        Schema::dropIfExists('accounting_erp_connectors');

        Schema::create('accounting_erp_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('connector_code', 100)->unique();
            $table->string('connector_name');
            $table->string('erp_system', 80)->default('generic');
            $table->string('connection_type', 60)->default('file_export');
            $table->string('base_url')->nullable();
            $table->string('auth_mode', 60)->default('api_key');
            $table->string('credential_reference')->nullable();
            $table->json('gl_mapping')->nullable();
            $table->json('cost_center_mapping')->nullable();
            $table->string('journal_mode', 60)->default('summary_by_cost_center');
            $table->string('status', 40)->default('draft');
            $table->timestamp('last_posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'acct_erp_company_status_idx');
            $table->index(['erp_system', 'connection_type'], 'acct_erp_system_type_idx');
        });

        Schema::create('payroll_accounting_journal_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accounting_erp_connector_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('payroll_run_id')->nullable();
            $table->string('journal_number', 100)->unique();
            $table->string('period', 20);
            $table->date('posting_date');
            $table->string('journal_type', 60)->default('payroll_accrual');
            $table->string('status', 40)->default('draft');
            $table->unsignedInteger('line_count')->default(0);
            $table->decimal('debit_total', 18, 2)->default(0);
            $table->decimal('credit_total', 18, 2)->default(0);
            $table->json('export_payload')->nullable();
            $table->string('payload_checksum', 120)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('accounting_erp_connector_id', 'pay_acct_journal_connector_fk')
                ->references('id')
                ->on('accounting_erp_connectors')
                ->nullOnDelete();
            $table->foreign('company_id', 'pay_acct_journal_company_fk')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
            $table->foreign('payroll_run_id', 'pay_acct_journal_run_fk')
                ->references('id')
                ->on('payroll_runs')
                ->nullOnDelete();
            $table->foreign('created_by', 'pay_acct_journal_creator_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['company_id', 'period'], 'pay_acct_journal_company_period_idx');
            $table->index(['status', 'posting_date'], 'pay_acct_journal_status_date_idx');
        });

        Schema::create('payroll_accounting_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_accounting_journal_batch_id');
            $table->string('line_number', 100);
            $table->string('cost_center_code', 120)->nullable();
            $table->string('gl_account', 120);
            $table->string('gl_name')->nullable();
            $table->string('debit_credit', 10);
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->string('employee_scope', 120)->nullable();
            $table->text('description')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->timestamps();

            $table->foreign('payroll_accounting_journal_batch_id', 'pay_acct_line_batch_fk')
                ->references('id')
                ->on('payroll_accounting_journal_batches')
                ->cascadeOnDelete();
            $table->index(['gl_account', 'debit_credit'], 'pay_acct_line_gl_dc_idx');
            $table->index('cost_center_code', 'pay_acct_line_cost_center_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_accounting_journal_lines');
        Schema::dropIfExists('payroll_accounting_journal_batches');
        Schema::dropIfExists('accounting_erp_connectors');
    }
};
