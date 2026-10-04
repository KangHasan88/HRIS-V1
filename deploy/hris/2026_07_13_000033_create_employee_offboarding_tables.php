<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_offboarding_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('case_number', 80)->unique();
            $table->string('reason_type', 50);
            $table->date('notice_date')->nullable();
            $table->date('last_working_date');
            $table->date('exit_interview_date')->nullable();
            $table->string('status', 40)->default('initiated');
            $table->string('handled_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->decimal('final_settlement_amount', 18, 2)->default(0);
            $table->string('final_settlement_status', 40)->default('pending');
            $table->text('reason_note')->nullable();
            $table->text('exit_interview_summary')->nullable();
            $table->text('handover_note')->nullable();
            $table->json('employee_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reason_type', 'status']);
            $table->index('last_working_date');
        });

        Schema::create('employee_clearance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_offboarding_case_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80);
            $table->string('item_name');
            $table->string('owner_role')->nullable();
            $table->string('owner_name')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status', 40)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_clearance_items');
        Schema::dropIfExists('employee_offboarding_cases');
    }
};
