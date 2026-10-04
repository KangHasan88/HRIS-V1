<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychotest_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_code', 100)->unique();
            $table->string('provider_name');
            $table->string('provider_type', 60)->default('external_api');
            $table->string('base_url')->nullable();
            $table->string('auth_method', 60)->default('api_key');
            $table->string('credential_reference')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('callback_secret_reference')->nullable();
            $table->string('sla_result_hours', 20)->nullable();
            $table->string('owner_name', 160)->nullable();
            $table->string('connection_status', 40)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['connection_status', 'is_active'], 'psy_provider_status_active_idx');
            $table->index(['provider_type', 'auth_method'], 'psy_provider_type_auth_idx');
        });

        Schema::create('psychotest_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_provider_id');
            $table->string('package_code', 100)->unique();
            $table->string('package_name');
            $table->string('assessment_category', 80)->default('personality');
            $table->string('target_role_level', 80)->default('all_level');
            $table->unsignedSmallInteger('estimated_minutes')->default(60);
            $table->json('competency_mapping')->nullable();
            $table->string('result_scale', 80)->default('score_0_100');
            $table->string('interpretation_method', 80)->default('provider_report');
            $table->boolean('auto_pull_result')->default(true);
            $table->boolean('requires_candidate_consent')->default(true);
            $table->string('status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assessment_category', 'status'], 'psy_package_category_status_idx');
            $table->foreign('psychotest_provider_id', 'psy_package_provider_fk')
                ->references('id')
                ->on('psychotest_providers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychotest_packages');
        Schema::dropIfExists('psychotest_providers');
    }
};
