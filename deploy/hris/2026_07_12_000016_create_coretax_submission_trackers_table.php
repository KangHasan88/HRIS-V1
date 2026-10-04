<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coretax_submission_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coretax_retry_log_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('tax_monthly_export_template_id')->nullable();
            $table->string('submission_number')->unique();
            $table->string('submission_status')->default('processing');
            $table->unsignedInteger('submitted_rows')->default(0);
            $table->unsignedInteger('accepted_rows')->default(0);
            $table->unsignedInteger('rejected_rows')->default(0);
            $table->decimal('template_total_pph21', 18, 2)->default(0);
            $table->decimal('acknowledged_total_pph21', 18, 2)->default(0);
            $table->decimal('difference_amount', 18, 2)->default(0);
            $table->json('reconciliation_snapshot')->nullable();
            $table->json('status_history')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['submission_status', 'last_checked_at'], 'coretax_submission_status_checked_idx');
            $table->foreign('tax_monthly_export_template_id', 'coretax_submission_template_fk')
                ->references('id')
                ->on('tax_monthly_export_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coretax_submission_trackers');
    }
};
