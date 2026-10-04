<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('connector_code', 80)->unique();
            $table->string('connector_name');
            $table->string('vendor', 80)->default('generic');
            $table->string('connection_type', 60)->default('api_pull');
            $table->string('endpoint_url')->nullable();
            $table->string('auth_mode', 60)->default('api_key');
            $table->string('machine_code', 80)->nullable();
            $table->string('machine_location')->nullable();
            $table->unsignedInteger('sync_interval_minutes')->default(60);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();
            $table->string('status', 40)->default('draft');
            $table->json('mapping_config')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor', 'status']);
            $table->index(['company_id', 'branch_id']);
        });

        Schema::create('biometric_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biometric_connector_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_import_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sync_number', 80)->unique();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 40)->default('running');
            $table->unsignedInteger('pulled_rows')->default(0);
            $table->unsignedInteger('matched_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('payload_summary')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_sync_logs');
        Schema::dropIfExists('biometric_connectors');
    }
};
