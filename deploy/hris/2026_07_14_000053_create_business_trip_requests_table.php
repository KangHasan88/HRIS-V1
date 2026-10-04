<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_trip_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 80)->unique();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('destination_city')->nullable();
            $table->string('destination_site')->nullable();
            $table->string('trip_type', 60)->default('domestic');
            $table->string('transport_mode', 60)->nullable();
            $table->decimal('estimated_budget', 14, 2)->default(0);
            $table->string('request_source', 40)->default('ess_webview');
            $table->string('approval_status', 40)->default('submitted');
            $table->string('current_approver')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decided_by')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->json('trip_snapshot')->nullable();
            $table->text('purpose')->nullable();
            $table->text('decision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['approval_status', 'start_date'], 'biz_trip_status_start_idx');
            $table->index(['employee_profile_id', 'start_date'], 'biz_trip_emp_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_trip_requests');
    }
};
