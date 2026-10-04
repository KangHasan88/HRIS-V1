<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_manpower_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_number', 80)->unique();
            $table->string('position_title');
            $table->string('department')->nullable();
            $table->string('employment_type', 60)->default('permanent');
            $table->unsignedSmallInteger('headcount')->default(1);
            $table->date('target_join_date')->nullable();
            $table->string('priority', 40)->default('normal');
            $table->string('request_reason', 80);
            $table->decimal('budget_min', 14, 2)->nullable();
            $table->decimal('budget_max', 14, 2)->nullable();
            $table->text('job_summary')->nullable();
            $table->json('requirements')->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('requested_by')->nullable();
            $table->date('requested_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('approval_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['company_id', 'branch_id']);
            $table->index('target_join_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_manpower_requests');
    }
};
