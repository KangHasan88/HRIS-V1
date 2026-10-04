<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_team_document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('request_number')->unique();
            $table->string('document_type');
            $table->string('purpose');
            $table->date('needed_date')->nullable();
            $table->string('delivery_channel')->default('portal');
            $table->string('approval_status')->default('draft');
            $table->string('current_approver')->nullable();
            $table->text('request_note')->nullable();
            $table->text('hr_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('manager_work_certificate_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('certificate_type');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_institution')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('approval_status')->default('draft');
            $table->string('manager_decision')->default('pending');
            $table->timestamp('decided_at')->nullable();
            $table->text('certificate_purpose')->nullable();
            $table->text('decision_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_work_certificate_approvals');
        Schema::dropIfExists('manager_team_document_requests');
    }
};
