<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_workforce_vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('worker_number', 80)->unique();
            $table->string('full_name');
            $table->string('worker_type', 60)->default('outsource');
            $table->string('identity_number', 80)->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('position_title')->nullable();
            $table->string('supervisor_name', 160)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('payment_handling', 60)->default('vendor_invoice');
            $table->decimal('daily_rate', 18, 2)->default(0);
            $table->decimal('piece_rate', 18, 2)->default(0);
            $table->json('document_status')->nullable();
            $table->json('access_scope')->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['worker_type', 'status']);
            $table->index(['company_id', 'branch_id', 'cost_center_id'], 'ext_wf_worker_scope_idx');
            $table->foreign('external_workforce_contract_id', 'ext_wf_worker_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_workers');
    }
};
