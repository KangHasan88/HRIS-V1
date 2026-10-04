<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_employee_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_candidate_id');
            $table->foreignId('employee_profile_id')->nullable();
            $table->string('conversion_number', 80)->unique();
            $table->string('employee_number', 80)->unique();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->string('employment_status', 40)->default('probation');
            $table->string('position', 160)->nullable();
            $table->string('department', 160)->nullable();
            $table->string('grade', 80)->nullable();
            $table->string('cost_center', 120)->nullable();
            $table->date('join_date')->nullable();
            $table->string('work_email', 180)->nullable();
            $table->string('conversion_status', 40)->default('draft');
            $table->json('mapping_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();

            $table->foreign('recruitment_candidate_id', 'rec_candidate_fk')->references('id')->on('recruitment_candidates')->cascadeOnDelete();
            $table->foreign('employee_profile_id', 'rec_employee_fk')->references('id')->on('employee_profiles')->nullOnDelete();
            $table->foreign('company_id', 'rec_company_fk')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('branch_id', 'rec_branch_fk')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('created_by', 'rec_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['conversion_status', 'join_date'], 'rec_status_join_idx');
            $table->index(['recruitment_candidate_id', 'employee_profile_id'], 'rec_candidate_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_employee_conversions');
    }
};
