<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_disbursement_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payroll_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number', 80)->unique();
            $table->string('period', 20);
            $table->string('bank_channel', 80)->default('bank_transfer');
            $table->string('source_account', 120)->nullable();
            $table->date('scheduled_payment_date')->nullable();
            $table->string('status', 40)->default('draft');
            $table->unsignedInteger('item_count')->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('file_format', 40)->default('csv');
            $table->string('file_checksum', 120)->nullable();
            $table->json('export_payload')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_disbursement_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_disbursement_batch_id');
            $table->foreignId('payroll_payslip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number', 80)->nullable();
            $table->string('employee_name', 180)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_number', 120)->nullable();
            $table->string('bank_account_holder', 180)->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('status', 40)->default('ready');
            $table->text('validation_message')->nullable();
            $table->timestamps();
            $table->foreign('payroll_disbursement_batch_id', 'pay_disb_item_batch_fk')->references('id')->on('payroll_disbursement_batches')->cascadeOnDelete();
        });

        Schema::create('payroll_disbursement_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_disbursement_batch_id');
            $table->string('approval_step', 80);
            $table->string('approver_role', 120);
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 40)->default('pending');
            $table->timestamp('decided_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('payroll_disbursement_batch_id', 'pay_disb_appr_batch_fk')->references('id')->on('payroll_disbursement_batches')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_disbursement_approvals');
        Schema::dropIfExists('payroll_disbursement_items');
        Schema::dropIfExists('payroll_disbursement_batches');
    }
};
