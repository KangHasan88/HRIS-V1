<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychotest_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruitment_candidate_id');
            $table->unsignedBigInteger('psychotest_package_id');
            $table->string('invitation_number', 100)->unique();
            $table->string('token', 120)->unique();
            $table->string('delivery_channel', 60)->default('email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 40)->default('draft');
            $table->string('result_sync_status', 40)->default('not_started');
            $table->text('instruction')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'psy_inv_status_expiry_idx');
            $table->index(['result_sync_status', 'completed_at'], 'psy_inv_sync_completed_idx');
            $table->foreign('recruitment_candidate_id', 'psy_inv_candidate_fk')
                ->references('id')->on('recruitment_candidates')->cascadeOnDelete();
            $table->foreign('psychotest_package_id', 'psy_inv_package_fk')
                ->references('id')->on('psychotest_packages')->cascadeOnDelete();
        });

        Schema::create('psychotest_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('psychotest_invitation_id');
            $table->unsignedBigInteger('recruitment_candidate_id');
            $table->unsignedBigInteger('psychotest_package_id');
            $table->string('result_number', 100)->unique();
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->string('fit_recommendation', 60)->default('need_review');
            $table->string('risk_level', 40)->default('medium');
            $table->json('dimension_scores')->nullable();
            $table->json('raw_result')->nullable();
            $table->text('summary')->nullable();
            $table->string('reviewed_by', 160)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('status', 40)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['overall_score', 'fit_recommendation'], 'psy_result_score_fit_idx');
            $table->index(['risk_level', 'status'], 'psy_result_risk_status_idx');
            $table->foreign('psychotest_invitation_id', 'psy_result_invitation_fk')
                ->references('id')->on('psychotest_invitations')->cascadeOnDelete();
            $table->foreign('recruitment_candidate_id', 'psy_result_candidate_fk')
                ->references('id')->on('recruitment_candidates')->cascadeOnDelete();
            $table->foreign('psychotest_package_id', 'psy_result_package_fk')
                ->references('id')->on('psychotest_packages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychotest_results');
        Schema::dropIfExists('psychotest_invitations');
    }
};
