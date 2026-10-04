<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('data_retention_execution_logs');
        Schema::dropIfExists('data_legal_hold_cases');
        Schema::dropIfExists('data_retention_policies');

        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_code', 100)->unique();
            $table->string('policy_name');
            $table->string('data_domain', 80);
            $table->unsignedSmallInteger('retention_years')->default(5);
            $table->string('archive_strategy', 60)->default('cold_storage');
            $table->string('disposal_action', 60)->default('anonymize');
            $table->boolean('requires_approval')->default(true);
            $table->boolean('legal_hold_override')->default(true);
            $table->string('owner_role', 120)->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'data_domain'], 'ret_policy_company_domain_idx');
            $table->index(['status', 'requires_approval'], 'ret_policy_status_approval_idx');
        });

        Schema::create('data_legal_hold_cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_retention_policy_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('case_number', 100)->unique();
            $table->string('case_title');
            $table->string('data_domain', 80);
            $table->string('scope_reference', 160)->nullable();
            $table->string('reason_category', 80)->default('legal_dispute');
            $table->date('hold_start_date');
            $table->date('hold_end_date')->nullable();
            $table->string('custodian_name')->nullable();
            $table->string('approval_status', 40)->default('pending');
            $table->string('status', 40)->default('active');
            $table->json('evidence_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('data_retention_policy_id', 'legal_hold_policy_fk')
                ->references('id')
                ->on('data_retention_policies')
                ->nullOnDelete();
            $table->foreign('company_id', 'legal_hold_company_fk')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
            $table->foreign('created_by', 'legal_hold_creator_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['data_domain', 'status'], 'legal_hold_domain_status_idx');
            $table->index(['approval_status', 'hold_start_date'], 'legal_hold_approval_start_idx');
        });

        Schema::create('data_retention_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_retention_policy_id')->nullable();
            $table->unsignedBigInteger('data_legal_hold_case_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('execution_number', 100)->unique();
            $table->string('data_domain', 80);
            $table->date('cutoff_date');
            $table->string('action_type', 60)->default('archive');
            $table->unsignedInteger('records_scanned')->default(0);
            $table->unsignedInteger('records_affected')->default(0);
            $table->string('status', 40)->default('planned');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('result_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('data_retention_policy_id', 'ret_exec_policy_fk')
                ->references('id')
                ->on('data_retention_policies')
                ->nullOnDelete();
            $table->foreign('data_legal_hold_case_id', 'ret_exec_hold_fk')
                ->references('id')
                ->on('data_legal_hold_cases')
                ->nullOnDelete();
            $table->foreign('company_id', 'ret_exec_company_fk')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
            $table->foreign('created_by', 'ret_exec_creator_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['data_domain', 'status'], 'ret_exec_domain_status_idx');
            $table->index(['cutoff_date', 'action_type'], 'ret_exec_cutoff_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_retention_execution_logs');
        Schema::dropIfExists('data_legal_hold_cases');
        Schema::dropIfExists('data_retention_policies');
    }
};
