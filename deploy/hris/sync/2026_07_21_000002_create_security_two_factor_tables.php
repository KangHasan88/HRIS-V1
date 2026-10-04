<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('privileged_action_confirmations');
        Schema::dropIfExists('user_two_factor_settings');
        Schema::dropIfExists('security_two_factor_policies');

        Schema::create('security_two_factor_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_code', 100)->unique();
            $table->string('policy_name');
            $table->json('enforced_roles')->nullable();
            $table->json('privileged_actions')->nullable();
            $table->string('otp_channel', 60)->default('authenticator_app');
            $table->boolean('require_on_login')->default(true);
            $table->boolean('require_for_privileged_action')->default(true);
            $table->unsignedSmallInteger('challenge_ttl_minutes')->default(5);
            $table->unsignedSmallInteger('remember_device_days')->default(0);
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'sec_2fa_policy_company_status_idx');
            $table->index(['require_on_login', 'require_for_privileged_action'], 'sec_2fa_policy_require_idx');
        });

        Schema::create('user_two_factor_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('security_two_factor_policy_id')->nullable()->constrained('security_two_factor_policies')->nullOnDelete();
            $table->string('method', 60)->default('authenticator_app');
            $table->string('secret_reference')->nullable();
            $table->string('secret_mask')->nullable();
            $table->json('backup_code_masks')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_challenged_at')->nullable();
            $table->string('status', 40)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id', 'user_2fa_settings_user_unique');
            $table->index(['status', 'method'], 'user_2fa_status_method_idx');
        });

        Schema::create('privileged_action_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_key', 120);
            $table->string('action_label');
            $table->string('resource_type')->nullable();
            $table->string('resource_reference')->nullable();
            $table->string('confirmation_method', 60)->default('password_and_otp');
            $table->string('status', 40)->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address', 80)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('risk_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['action_key', 'status'], 'priv_action_key_status_idx');
            $table->index(['user_id', 'expires_at'], 'priv_action_user_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privileged_action_confirmations');
        Schema::dropIfExists('user_two_factor_settings');
        Schema::dropIfExists('security_two_factor_policies');
    }
};
