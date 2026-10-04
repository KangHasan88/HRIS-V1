<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vendor_code', 80)->unique();
            $table->string('vendor_name');
            $table->string('vendor_type', 60)->default('outsourcing');
            $table->string('npwp', 80)->nullable();
            $table->string('nib', 80)->nullable();
            $table->string('pic_name', 160)->nullable();
            $table->string('pic_phone', 80)->nullable();
            $table->string('pic_email', 160)->nullable();
            $table->text('address')->nullable();
            $table->json('compliance_snapshot')->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id']);
            $table->index(['vendor_type', 'status']);
        });

        Schema::create('external_workforce_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_vendor_id');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_number', 100)->unique();
            $table->string('contract_title');
            $table->string('workforce_scope', 80)->default('outsourced_manpower');
            $table->string('worker_type_scope', 120)->default('Outsource');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('renewal_reminder_date')->nullable();
            $table->unsignedInteger('headcount_quota')->default(0);
            $table->decimal('monthly_service_fee', 18, 2)->default(0);
            $table->decimal('daily_rate', 18, 2)->default(0);
            $table->decimal('overtime_rate', 18, 2)->default(0);
            $table->string('sla_summary')->nullable();
            $table->string('document_reference')->nullable();
            $table->json('location_scope')->nullable();
            $table->json('responsibility_matrix')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'end_date']);
            $table->index(['company_id', 'branch_id', 'cost_center_id'], 'ext_wf_contract_scope_idx');
            $table->foreign('external_workforce_vendor_id', 'ext_wf_contract_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_contracts');
        Schema::dropIfExists('external_workforce_vendors');
    }
};
