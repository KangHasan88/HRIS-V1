<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_onboarding_journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id');
            $table->foreignId('recruitment_employee_conversion_id')->nullable();
            $table->string('journey_number', 80)->unique();
            $table->string('buddy_name', 160);
            $table->string('buddy_role', 120)->nullable();
            $table->string('buddy_contact', 160)->nullable();
            $table->date('start_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->string('probation_reviewer', 160)->nullable();
            $table->string('journey_status', 40)->default('planned');
            $table->json('checklist_snapshot')->nullable();
            $table->json('probation_plan')->nullable();
            $table->json('welcome_schedule')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_profile_id', 'roj_employee_fk')->references('id')->on('employee_profiles')->cascadeOnDelete();
            $table->foreign('recruitment_employee_conversion_id', 'roj_conversion_fk')->references('id')->on('recruitment_employee_conversions')->nullOnDelete();
            $table->foreign('created_by', 'roj_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['journey_status', 'start_date'], 'roj_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_onboarding_journeys');
    }
};
