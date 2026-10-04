<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_compliance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_number')->unique();
            $table->string('event_type')->default('monthly_pph21');
            $table->string('title');
            $table->string('period', 7);
            $table->date('due_date');
            $table->unsignedInteger('reminder_days_before')->default(7);
            $table->date('reminder_date')->nullable();
            $table->string('sla_level')->default('normal');
            $table->string('status')->default('planned');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('escalation_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->json('checklist_snapshot')->nullable();
            $table->json('notification_snapshot')->nullable();
            $table->json('escalation_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'status']);
            $table->index(['due_date', 'sla_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_compliance_events');
    }
};
