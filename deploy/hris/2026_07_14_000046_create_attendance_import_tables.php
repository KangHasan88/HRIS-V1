<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 80)->unique();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 60)->default('biometric_machine');
            $table->string('machine_code', 80)->nullable();
            $table->string('machine_name')->nullable();
            $table->date('import_period_start')->nullable();
            $table->date('import_period_end')->nullable();
            $table->string('file_reference')->nullable();
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('matched_rows')->default(0);
            $table->unsignedInteger('anomaly_rows')->default(0);
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'status'], 'att_imp_batch_src_status_idx');
            $table->index(['company_id', 'branch_id'], 'att_imp_batch_scope_idx');
        });

        Schema::create('attendance_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number', 80)->nullable();
            $table->string('employee_name')->nullable();
            $table->string('raw_identifier', 120)->nullable();
            $table->string('event_type', 40)->default('clock_in');
            $table->timestamp('event_at');
            $table->string('source_device')->nullable();
            $table->string('match_status', 40)->default('needs_review');
            $table->json('anomaly_flags')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['match_status', 'event_type'], 'att_imp_row_status_event_idx');
            $table->index(['employee_profile_id', 'event_at'], 'att_imp_row_emp_time_idx');
            $table->index('event_at', 'att_imp_row_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_import_rows');
        Schema::dropIfExists('attendance_import_batches');
    }
};
