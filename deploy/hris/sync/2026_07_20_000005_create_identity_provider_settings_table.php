<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_code', 100)->unique();
            $table->string('provider_name');
            $table->string('protocol', 30);
            $table->string('domain_hint')->nullable();
            $table->string('issuer_url')->nullable();
            $table->string('sso_login_url')->nullable();
            $table->string('sso_logout_url')->nullable();
            $table->string('metadata_url')->nullable();
            $table->string('client_id')->nullable();
            $table->string('certificate_fingerprint')->nullable();
            $table->json('claim_mapping')->nullable();
            $table->json('role_mapping')->nullable();
            $table->boolean('auto_provision')->default(false);
            $table->boolean('jit_update_profile')->default(true);
            $table->boolean('enforce_sso')->default(false);
            $table->string('status', 40)->default('draft');
            $table->timestamp('last_tested_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'idp_company_status_idx');
            $table->index(['protocol', 'status'], 'idp_protocol_status_idx');
            $table->index('domain_hint', 'idp_domain_hint_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_provider_settings');
    }
};
