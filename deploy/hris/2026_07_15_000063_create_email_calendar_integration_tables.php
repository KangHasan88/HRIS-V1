<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_calendar_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('connector_code', 80)->unique();
            $table->string('connector_name');
            $table->string('provider', 60)->default('generic');
            $table->string('connection_type', 60)->default('smtp_ics');
            $table->string('sender_email')->nullable();
            $table->string('calendar_owner')->nullable();
            $table->string('endpoint_url')->nullable();
            $table->string('auth_mode', 60)->default('api_key');
            $table->string('default_timezone', 80)->default('Asia/Jakarta');
            $table->json('enabled_modules')->nullable();
            $table->string('status', 40)->default('draft');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('mapping_config')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['provider', 'status'], 'ecc_provider_status_idx');
            $table->index(['company_id', 'branch_id'], 'ecc_company_branch_idx');
        });

        Schema::create('email_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_calendar_connector_id')->constrained()->cascadeOnDelete();
            $table->string('event_number', 80)->unique();
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('event_title');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end')->nullable();
            $table->string('location_or_link')->nullable();
            $table->boolean('send_email')->default(true);
            $table->boolean('create_calendar_event')->default(true);
            $table->string('email_status', 40)->default('queued');
            $table->string('calendar_status', 40)->default('queued');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('external_event_id')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->text('last_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'ece_source_idx');
            $table->index(['email_status', 'calendar_status'], 'ece_status_idx');
            $table->index('scheduled_start', 'ece_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_calendar_events');
        Schema::dropIfExists('email_calendar_connectors');
    }
};
