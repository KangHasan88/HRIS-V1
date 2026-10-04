<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway_code')->unique();
            $table->string('gateway_name');
            $table->string('provider', 60);
            $table->string('connection_type', 60);
            $table->string('endpoint_url')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('auth_mode', 40);
            $table->string('status', 40)->default('draft');
            $table->json('enabled_events')->nullable();
            $table->json('credential_snapshot')->nullable();
            $table->timestamp('last_ping_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('whatsapp_message_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_gateway_setting_id')->constrained()->cascadeOnDelete();
            $table->string('message_number')->unique();
            $table->string('event_key', 120);
            $table->string('source_type', 80)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 40);
            $table->string('template_name')->nullable();
            $table->text('message_body');
            $table->string('priority', 30)->default('normal');
            $table->string('status', 40)->default('queued');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->json('payload_snapshot')->nullable();
            $table->json('provider_response')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('whatsapp_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_gateway_setting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_message_queue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('webhook_number')->unique();
            $table->string('event_type', 80);
            $table->string('provider_message_id')->nullable();
            $table->string('delivery_status', 60)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_logs');
        Schema::dropIfExists('whatsapp_message_queues');
        Schema::dropIfExists('whatsapp_gateway_settings');
    }
};
