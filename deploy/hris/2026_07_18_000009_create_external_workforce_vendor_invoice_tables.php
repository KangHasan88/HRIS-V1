<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_vendor_id');
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 80)->unique();
            $table->string('vendor_invoice_number', 120)->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('source_scope', 60)->default('mixed');
            $table->decimal('claimed_amount', 18, 2)->default(0);
            $table->decimal('system_amount', 18, 2)->default(0);
            $table->decimal('variance_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('payable_amount', 18, 2)->default(0);
            $table->string('reconciliation_status', 40)->default('draft');
            $table->string('approval_status', 40)->default('draft');
            $table->string('payment_status', 40)->default('not_ready');
            $table->string('reviewed_by', 160)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period_start', 'period_end'], 'ext_wf_inv_period_idx');
            $table->index(['reconciliation_status', 'approval_status'], 'ext_wf_inv_status_idx');
            $table->foreign('external_workforce_vendor_id', 'ext_wf_inv_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->cascadeOnDelete();
            $table->foreign('external_workforce_contract_id', 'ext_wf_inv_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });

        Schema::create('external_workforce_vendor_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_vendor_invoice_id');
            $table->unsignedBigInteger('external_workforce_worker_id')->nullable();
            $table->string('source_type', 60)->default('manual_adjustment');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->date('work_date')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit_name', 40)->default('unit');
            $table->decimal('system_rate', 18, 2)->default(0);
            $table->decimal('system_amount', 18, 2)->default(0);
            $table->decimal('claimed_amount', 18, 2)->default(0);
            $table->decimal('variance_amount', 18, 2)->default(0);
            $table->string('reconciliation_status', 40)->default('matched');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'ext_wf_inv_line_source_idx');
            $table->foreign('external_workforce_vendor_invoice_id', 'ext_wf_inv_line_invoice_fk')
                ->references('id')
                ->on('external_workforce_vendor_invoices')
                ->cascadeOnDelete();
            $table->foreign('external_workforce_worker_id', 'ext_wf_inv_line_worker_fk')
                ->references('id')
                ->on('external_workforce_workers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_vendor_invoice_lines');
        Schema::dropIfExists('external_workforce_vendor_invoices');
    }
};
