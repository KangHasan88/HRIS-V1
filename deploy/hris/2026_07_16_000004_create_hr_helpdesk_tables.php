<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('category', 80);
            $table->string('priority', 30)->default('normal');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->string('status', 40)->default('submitted');
            $table->string('channel', 60)->default('ess_webview');
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_helpdesk_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_helpdesk_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type', 30);
            $table->text('message_body');
            $table->string('attachment_reference')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_helpdesk_messages');
        Schema::dropIfExists('hr_helpdesk_tickets');
    }
};
