<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ess_offline_queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('queue_number')->unique();
            $table->string('device_uid')->nullable()->index();
            $table->string('device_name')->nullable();
            $table->string('platform')->default('mobile_webview');
            $table->string('app_version')->nullable();
            $table->enum('item_type', ['clock_event', 'leave_draft', 'overtime_draft', 'business_trip_draft', 'attendance_correction_draft']);
            $table->enum('queue_status', ['queued', 'syncing', 'synced', 'failed', 'cancelled'])->default('queued')->index();
            $table->timestamp('captured_at')->nullable()->index();
            $table->timestamp('last_sync_attempt_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->json('payload');
            $table->json('sync_result')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_profile_id', 'queue_status'], 'ess_offline_employee_status_idx');
            $table->index(['item_type', 'captured_at'], 'ess_offline_type_captured_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ess_offline_queue_items');
    }
};
