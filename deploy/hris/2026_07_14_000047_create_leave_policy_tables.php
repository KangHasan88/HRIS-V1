<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policy_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('category', 60)->default('annual');
            $table->string('unit', 40)->default('day');
            $table->decimal('annual_quota_days', 8, 2)->default(0);
            $table->unsignedSmallInteger('max_days_per_request')->nullable();
            $table->boolean('requires_balance')->default(true);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('approval_required')->default(true);
            $table->boolean('carry_forward_allowed')->default(false);
            $table->decimal('carry_forward_max_days', 8, 2)->default(0);
            $table->unsignedTinyInteger('carry_forward_expiry_months')->default(0);
            $table->boolean('negative_balance_allowed')->default(false);
            $table->boolean('prorate_on_join')->default(true);
            $table->string('gender_rule', 40)->default('all');
            $table->unsignedSmallInteger('min_service_months')->default(0);
            $table->string('status', 40)->default('active');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'status'], 'leave_type_cat_status_idx');
        });

        Schema::create('leave_policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_policy_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employment_status', 60)->nullable();
            $table->date('effective_start');
            $table->date('effective_end')->nullable();
            $table->string('accrual_frequency', 40)->default('yearly');
            $table->decimal('accrual_days', 8, 2)->default(0);
            $table->unsignedTinyInteger('reset_month')->default(1);
            $table->boolean('include_holidays')->default(false);
            $table->boolean('include_weekends')->default(false);
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id'], 'leave_rule_scope_idx');
            $table->index(['status', 'effective_start'], 'leave_rule_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_rules');
        Schema::dropIfExists('leave_policy_types');
    }
};
