<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('module_key');
            $table->string('trigger_event');
            $table->string('status')->default('draft');
            $table->boolean('is_enabled')->default(true);
            $table->json('condition_schema')->nullable();
            $table->json('fallback_route')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['module_key', 'status', 'is_enabled']);
        });

        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order')->default(1);
            $table->string('step_name');
            $table->string('approver_type')->default('role');
            $table->string('approver_value');
            $table->string('routing_condition')->nullable();
            $table->unsignedInteger('sla_hours')->default(24);
            $table->boolean('is_parallel')->default(false);
            $table->boolean('can_delegate')->default(true);
            $table->json('notification_channels')->nullable();
            $table->timestamps();

            $table->index(['approval_workflow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_steps');
        Schema::dropIfExists('approval_workflows');
    }
};
