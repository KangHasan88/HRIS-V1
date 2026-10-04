<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coretax_connector_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('environment')->default('sandbox');
            $table->string('base_url');
            $table->string('client_id');
            $table->string('secret_mask');
            $table->string('credential_hash');
            $table->unsignedSmallInteger('timeout_seconds')->default(30);
            $table->unsignedSmallInteger('max_retry')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->string('last_status')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'environment', 'is_active'], 'coretax_setting_company_env_idx');
        });

        Schema::create('coretax_retry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coretax_connector_setting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tax_monthly_export_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_type');
            $table->string('status')->default('queued');
            $table->unsignedSmallInteger('attempt')->default(0);
            $table->unsignedSmallInteger('max_retry')->default(3);
            $table->timestamp('next_retry_at')->nullable();
            $table->string('endpoint')->nullable();
            $table->string('payload_checksum')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_message')->nullable();
            $table->json('request_snapshot')->nullable();
            $table->json('response_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'next_retry_at'], 'coretax_retry_status_next_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coretax_retry_logs');
        Schema::dropIfExists('coretax_connector_settings');
    }
};
