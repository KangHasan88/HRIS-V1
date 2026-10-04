<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_manpower_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('candidate_number', 80)->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source_channel', 80)->default('direct');
            $table->string('current_position')->nullable();
            $table->string('current_company')->nullable();
            $table->decimal('expected_salary', 14, 2)->nullable();
            $table->string('pipeline_stage', 40)->default('sourcing');
            $table->string('candidate_status', 40)->default('active');
            $table->unsignedTinyInteger('fit_score')->default(0);
            $table->string('owner_recruiter')->nullable();
            $table->date('applied_at')->nullable();
            $table->date('last_touch_at')->nullable();
            $table->json('stage_history')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['pipeline_stage', 'candidate_status']);
            $table->index('source_channel');
            $table->index('fit_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
