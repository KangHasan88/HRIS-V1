<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('location_type', 60)->default('office');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meter')->default(100);
            $table->string('address')->nullable();
            $table->boolean('allow_clock_in')->default(true);
            $table->boolean('allow_clock_out')->default(true);
            $table->boolean('requires_photo')->default(true);
            $table->boolean('requires_liveness')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['location_type', 'is_active'], 'att_geo_type_active_idx');
            $table->index(['company_id', 'branch_id'], 'att_geo_scope_idx');
        });

        Schema::create('attendance_location_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_geofence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_number', 80)->unique();
            $table->string('policy_type', 60)->default('office');
            $table->string('assignment_scope', 60)->default('employee');
            $table->date('effective_start');
            $table->date('effective_end')->nullable();
            $table->string('approval_status', 40)->default('draft');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('allowed_days')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['policy_type', 'approval_status'], 'att_loc_policy_status_idx');
            $table->index(['assignment_scope', 'effective_start'], 'att_loc_policy_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_location_policies');
        Schema::dropIfExists('attendance_geofences');
    }
};
