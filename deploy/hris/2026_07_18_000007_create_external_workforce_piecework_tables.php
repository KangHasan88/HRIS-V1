<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_piece_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_vendor_id')->nullable();
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rate_code', 80)->unique();
            $table->string('work_name');
            $table->string('work_category', 80)->default('borongan');
            $table->string('unit_name', 40)->default('unit');
            $table->decimal('rate_per_unit', 18, 2)->default(0);
            $table->decimal('minimum_output', 12, 2)->default(0);
            $table->string('quality_rule')->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['work_category', 'status'], 'ext_wf_rate_cat_status_idx');
            $table->index(['company_id', 'branch_id', 'cost_center_id'], 'ext_wf_rate_scope_idx');
            $table->foreign('external_workforce_vendor_id', 'ext_wf_rate_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->nullOnDelete();
            $table->foreign('external_workforce_contract_id', 'ext_wf_rate_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });

        Schema::create('external_workforce_piece_outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_piece_rate_id');
            $table->unsignedBigInteger('external_workforce_worker_id')->nullable();
            $table->unsignedBigInteger('external_workforce_vendor_id')->nullable();
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('output_number', 80)->unique();
            $table->date('work_date');
            $table->string('team_name', 160)->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('accepted_quantity', 12, 2)->default(0);
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            $table->decimal('rate_per_unit', 18, 2)->default(0);
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->string('foreman_name', 160)->nullable();
            $table->string('approval_status', 40)->default('submitted');
            $table->timestamp('approved_at')->nullable();
            $table->string('handoff_target', 60)->default('vendor_invoice');
            $table->string('handoff_status', 40)->default('pending');
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['work_date', 'approval_status'], 'ext_wf_out_date_approval_idx');
            $table->index(['handoff_target', 'handoff_status'], 'ext_wf_out_handoff_idx');
            $table->foreign('external_workforce_piece_rate_id', 'ext_wf_out_rate_fk')
                ->references('id')
                ->on('external_workforce_piece_rates')
                ->cascadeOnDelete();
            $table->foreign('external_workforce_worker_id', 'ext_wf_out_worker_fk')
                ->references('id')
                ->on('external_workforce_workers')
                ->nullOnDelete();
            $table->foreign('external_workforce_vendor_id', 'ext_wf_out_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->nullOnDelete();
            $table->foreign('external_workforce_contract_id', 'ext_wf_out_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_piece_outputs');
        Schema::dropIfExists('external_workforce_piece_rates');
    }
};
