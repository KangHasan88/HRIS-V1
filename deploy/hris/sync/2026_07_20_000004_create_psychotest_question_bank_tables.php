<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychotest_question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 100)->unique();
            $table->string('category_name');
            $table->string('assessment_domain', 80)->default('personality');
            $table->string('owner_name', 160)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assessment_domain', 'is_active'], 'psy_cat_domain_active_idx');
        });

        Schema::create('psychotest_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_question_category_id');
            $table->string('dimension_code', 100)->unique();
            $table->string('dimension_name');
            $table->string('scoring_direction', 40)->default('positive');
            $table->unsignedTinyInteger('weight_percent')->default(100);
            $table->text('interpretation_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['scoring_direction', 'is_active'], 'psy_dim_direction_active_idx');
            $table->foreign('psychotest_question_category_id', 'psy_dim_category_fk')
                ->references('id')->on('psychotest_question_categories')->cascadeOnDelete();
        });

        Schema::create('psychotest_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_dimension_id');
            $table->string('question_code', 100)->unique();
            $table->string('question_type', 60)->default('likert');
            $table->text('question_text');
            $table->text('instruction')->nullable();
            $table->unsignedSmallInteger('time_limit_seconds')->nullable();
            $table->string('difficulty_level', 40)->default('normal');
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status', 40)->default('draft');
            $table->json('scoring_key')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['question_type', 'status'], 'psy_question_type_status_idx');
            $table->index(['difficulty_level', 'version'], 'psy_question_diff_version_idx');
            $table->foreign('psychotest_dimension_id', 'psy_question_dimension_fk')
                ->references('id')->on('psychotest_dimensions')->cascadeOnDelete();
        });

        Schema::create('psychotest_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_question_id');
            $table->string('option_code', 100);
            $table->text('option_text');
            $table->decimal('score_value', 8, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->boolean('is_correct')->default(false);
            $table->text('interpretation_note')->nullable();
            $table->timestamps();

            $table->unique(['psychotest_question_id', 'option_code'], 'psy_option_question_code_unique');
            $table->foreign('psychotest_question_id', 'psy_option_question_fk')
                ->references('id')->on('psychotest_questions')->cascadeOnDelete();
        });

        Schema::create('psychotest_battery_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_package_id');
            $table->string('template_code', 100)->unique();
            $table->string('template_name');
            $table->string('target_role_level', 80)->default('all_level');
            $table->unsignedSmallInteger('total_duration_minutes')->default(60);
            $table->json('section_config')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status', 40)->default('draft');
            $table->text('instruction')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['target_role_level', 'status'], 'psy_battery_role_status_idx');
            $table->foreign('psychotest_package_id', 'psy_battery_package_fk')
                ->references('id')->on('psychotest_packages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychotest_battery_templates');
        Schema::dropIfExists('psychotest_question_options');
        Schema::dropIfExists('psychotest_questions');
        Schema::dropIfExists('psychotest_dimensions');
        Schema::dropIfExists('psychotest_question_categories');
    }
};
