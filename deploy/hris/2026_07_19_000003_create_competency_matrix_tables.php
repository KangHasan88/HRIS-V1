<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('competency_code')->unique();
            $table->string('competency_name');
            $table->string('category')->default('technical');
            $table->text('description')->nullable();
            $table->json('proficiency_descriptors')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('competency_role_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_id')->constrained('competencies')->cascadeOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained('job_positions')->nullOnDelete();
            $table->foreignId('job_grade_id')->nullable()->constrained('job_grades')->nullOnDelete();
            $table->foreignId('training_course_id')->nullable()->constrained('training_courses')->nullOnDelete();
            $table->string('requirement_number')->unique();
            $table->string('career_path')->nullable();
            $table->unsignedSmallInteger('required_level')->default(1);
            $table->unsignedSmallInteger('target_level')->default(3);
            $table->decimal('weight', 5, 2)->default(100);
            $table->string('criticality')->default('medium');
            $table->string('assessment_method')->default('manager_review');
            $table->json('evidence_examples')->nullable();
            $table->text('development_action')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['job_position_id', 'job_grade_id', 'criticality'], 'comp_req_role_grade_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_role_requirements');
        Schema::dropIfExists('competencies');
    }
};
