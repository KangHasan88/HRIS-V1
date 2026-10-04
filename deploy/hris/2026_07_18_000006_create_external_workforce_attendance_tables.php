<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_worker_id');
            $table->unsignedBigInteger('external_workforce_vendor_id')->nullable();
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('attendance_number', 80)->unique();
            $table->date('attendance_date');
            $table->string('shift_code', 80)->nullable();
            $table->dateTime('clock_in_at')->nullable();
            $table->dateTime('clock_out_at')->nullable();
            $table->string('attendance_status', 40)->default('present');
            $table->decimal('mandays', 8, 2)->default(1);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('daily_rate', 18, 2)->default(0);
            $table->decimal('overtime_rate', 18, 2)->default(0);
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->string('approval_status', 40)->default('draft');
            $table->string('approved_by', 160)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('handoff_target', 60)->default('vendor_invoice');
            $table->string('handoff_status', 40)->default('pending');
            $table->string('source_type', 60)->default('manual');
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['attendance_date', 'approval_status'], 'ext_wf_att_date_approval_idx');
            $table->index(['handoff_target', 'handoff_status'], 'ext_wf_att_handoff_idx');
            $table->index(['company_id', 'branch_id', 'cost_center_id'], 'ext_wf_att_scope_idx');
            $table->foreign('external_workforce_worker_id', 'ext_wf_att_worker_fk')
                ->references('id')
                ->on('external_workforce_workers')
                ->cascadeOnDelete();
            $table->foreign('external_workforce_vendor_id', 'ext_wf_att_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->nullOnDelete();
            $table->foreign('external_workforce_contract_id', 'ext_wf_att_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_attendances');
    }
};
