<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('platform_data_retention_jobs');
        Schema::dropIfExists('platform_data_consents');
        Schema::dropIfExists('platform_data_privacy_policies');

        Schema::create('platform_data_privacy_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_code', 100)->unique();
            $table->string('policy_name');
            $table->string('data_domain', 80);
            $table->json('masked_fields')->nullable();
            $table->json('restricted_roles')->nullable();
            $table->string('masking_mode', 60)->default('partial');
            $table->boolean('consent_required')->default(false);
            $table->unsignedSmallInteger('retention_years')->default(5);
            $table->boolean('legal_hold_enabled')->default(false);
            $table->string('legal_basis')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'data_domain'], 'plat_priv_policy_company_domain_idx');
            $table->index(['status', 'consent_required'], 'plat_priv_policy_status_consent_idx');
        });

        Schema::create('platform_data_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('platform_data_privacy_policy_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('subject_type', 60)->default('employee');
            $table->string('subject_reference', 120);
            $table->string('subject_name')->nullable();
            $table->string('consent_purpose');
            $table->string('consent_channel', 60)->default('web');
            $table->string('status', 40)->default('granted');
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('evidence_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('platform_data_privacy_policy_id', 'plat_consent_policy_fk')
                ->references('id')
                ->on('platform_data_privacy_policies')
                ->nullOnDelete();
            $table->foreign('company_id', 'plat_consent_company_fk')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
            $table->foreign('created_by', 'plat_consent_creator_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['subject_type', 'subject_reference'], 'plat_consent_subject_idx');
            $table->index(['status', 'expires_at'], 'plat_consent_status_expiry_idx');
        });

        Schema::create('platform_data_retention_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('platform_data_privacy_policy_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('job_number', 100)->unique();
            $table->string('data_domain', 80);
            $table->string('scope_reference', 160)->nullable();
            $table->date('cutoff_date');
            $table->string('action_type', 60)->default('archive');
            $table->boolean('legal_hold')->default(false);
            $table->unsignedInteger('estimated_records')->default(0);
            $table->string('status', 40)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('impact_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('platform_data_privacy_policy_id', 'plat_retention_policy_fk')
                ->references('id')
                ->on('platform_data_privacy_policies')
                ->nullOnDelete();
            $table->foreign('company_id', 'plat_retention_company_fk')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
            $table->foreign('created_by', 'plat_retention_creator_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['data_domain', 'status'], 'plat_retention_domain_status_idx');
            $table->index(['cutoff_date', 'legal_hold'], 'plat_retention_cutoff_hold_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_data_retention_jobs');
        Schema::dropIfExists('platform_data_consents');
        Schema::dropIfExists('platform_data_privacy_policies');
    }
};
