<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ess_trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_uid', 160);
            $table->string('device_name')->nullable();
            $table->string('platform', 40)->default('webview');
            $table->string('app_version', 40)->nullable();
            $table->boolean('biometric_enabled')->default(false);
            $table->string('biometric_type', 40)->nullable();
            $table->string('fallback_pin_hash')->nullable();
            $table->string('trust_status', 40)->default('pending');
            $table->timestamp('trusted_at')->nullable();
            $table->timestamp('last_authenticated_at')->nullable();
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->json('risk_flags')->nullable();
            $table->json('capability_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'device_uid'], 'ess_dev_user_uid_unique');
            $table->index(['trust_status', 'platform'], 'ess_dev_status_platform_idx');
            $table->index(['employee_profile_id', 'trust_status'], 'ess_dev_emp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ess_trusted_devices');
    }
};
