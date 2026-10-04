<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('employee_onboarding_tasks');
        Schema::dropIfExists('employee_onboarding_templates');

        Schema::create('employee_onboarding_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('role_scope')->nullable();
            $table->string('department_scope')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->json('task_blueprint')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['role_scope', 'department_scope']);
        });

        Schema::create('employee_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('employee_onboarding_template_id')->nullable();
            $table->string('task_code', 80);
            $table->string('task_name');
            $table->string('category', 80)->default('general');
            $table->string('owner_role')->nullable();
            $table->string('owner_name')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status', 40)->default('open');
            $table->unsignedInteger('sort_order')->default(1);
            $table->text('instruction')->nullable();
            $table->text('completion_note')->nullable();
            $table->json('assignment_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_profile_id', 'task_code']);
            $table->index(['status', 'due_date']);
            $table->foreign('employee_onboarding_template_id', 'onboarding_tasks_template_fk')
                ->references('id')
                ->on('employee_onboarding_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_tasks');
        Schema::dropIfExists('employee_onboarding_templates');
    }
};
