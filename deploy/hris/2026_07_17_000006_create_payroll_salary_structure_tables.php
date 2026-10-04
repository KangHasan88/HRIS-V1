<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_salary_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_grade_id')->nullable()->constrained()->nullOnDelete();
            $table->string('band_code', 80)->unique();
            $table->string('band_name', 180);
            $table->unsignedInteger('level')->default(1);
            $table->decimal('minimum_salary', 18, 2)->default(0);
            $table->decimal('midpoint_salary', 18, 2)->default(0);
            $table->decimal('maximum_salary', 18, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->date('effective_start')->nullable();
            $table->date('effective_end')->nullable();
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_salary_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_salary_band_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('base_salary', 18, 2)->default(0);
            $table->decimal('fixed_allowance', 18, 2)->default(0);
            $table->decimal('variable_allowance', 18, 2)->default(0);
            $table->string('payroll_frequency', 40)->default('monthly');
            $table->date('effective_start')->nullable();
            $table->date('effective_end')->nullable();
            $table->string('approval_status', 40)->default('draft');
            $table->string('confidentiality_level', 40)->default('restricted');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('compensation_access_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name', 180);
            $table->string('role_scope', 120);
            $table->string('access_level', 40)->default('masked');
            $table->boolean('can_view_salary')->default(false);
            $table->boolean('can_edit_salary')->default(false);
            $table->boolean('can_export_salary')->default(false);
            $table->boolean('requires_approval')->default(true);
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensation_access_policies');
        Schema::dropIfExists('employee_salary_assignments');
        Schema::dropIfExists('payroll_salary_bands');
    }
};
