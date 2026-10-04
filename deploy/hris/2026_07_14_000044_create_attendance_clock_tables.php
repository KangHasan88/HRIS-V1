<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_device_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('device_uid', 160);
            $table->string('device_name')->nullable();
            $table->string('platform', 40)->default('webview');
            $table->string('app_version', 40)->nullable();
            $table->timestamp('bound_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_profile_id', 'device_uid']);
            $table->index(['status', 'platform']);
        });

        Schema::create('attendance_clock_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_shift_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendance_device_binding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_number', 80)->unique();
            $table->string('event_type', 40);
            $table->timestamp('event_at');
            $table->string('source', 60)->default('mobile_webview');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('gps_accuracy_meter')->nullable();
            $table->string('location_label')->nullable();
            $table->string('selfie_reference')->nullable();
            $table->unsignedTinyInteger('liveness_score')->default(0);
            $table->boolean('is_liveness_passed')->default(false);
            $table->boolean('is_device_trusted')->default(false);
            $table->string('device_uid')->nullable();
            $table->string('device_name')->nullable();
            $table->string('status', 40)->default('submitted');
            $table->json('risk_flags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['event_type', 'status']);
            $table->index('event_at');
            $table->index(['employee_profile_id', 'event_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_clock_events');
        Schema::dropIfExists('attendance_device_bindings');
    }
};
