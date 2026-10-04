<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ess_profile_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('request_number', 80)->unique();
            $table->string('request_source', 40)->default('ess_webview');
            $table->json('current_snapshot')->nullable();
            $table->json('requested_snapshot');
            $table->string('evidence_reference')->nullable();
            $table->text('employee_note')->nullable();
            $table->string('approval_status', 30)->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_profile_id', 'approval_status'], 'ess_profile_update_emp_status_idx');
            $table->index(['approval_status', 'submitted_at'], 'ess_profile_update_status_submitted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ess_profile_update_requests');
    }
};
