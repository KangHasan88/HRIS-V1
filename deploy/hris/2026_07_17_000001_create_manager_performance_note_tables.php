<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_performance_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->date('note_date');
            $table->string('category', 60)->default('performance');
            $table->string('sentiment', 40)->default('neutral');
            $table->string('visibility', 40)->default('manager_only');
            $table->string('title');
            $table->text('note_body');
            $table->json('tags')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status', 40)->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['manager_user_id', 'note_date'], 'mgr_perf_notes_manager_date_idx');
            $table->index(['employee_profile_id', 'status'], 'mgr_perf_notes_emp_status_idx');
        });

        Schema::create('manager_one_on_one_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->string('meeting_type', 60)->default('regular');
            $table->string('mood', 40)->default('neutral');
            $table->string('topic_summary');
            $table->text('discussion_notes')->nullable();
            $table->text('employee_feedback')->nullable();
            $table->text('manager_commitment')->nullable();
            $table->json('action_items')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->string('status', 40)->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['manager_user_id', 'meeting_date'], 'mgr_ooo_manager_date_idx');
            $table->index(['employee_profile_id', 'status'], 'mgr_ooo_emp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_one_on_one_logs');
        Schema::dropIfExists('manager_performance_notes');
    }
};
