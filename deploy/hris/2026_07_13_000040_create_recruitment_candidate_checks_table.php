<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_candidate_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->string('check_number', 80)->unique();
            $table->string('check_type', 50);
            $table->string('vendor_name', 160)->nullable();
            $table->string('reference_name', 160)->nullable();
            $table->string('reference_contact', 160)->nullable();
            $table->date('requested_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status', 40)->default('pending');
            $table->string('result', 40)->default('waiting');
            $table->boolean('is_blocking')->default(false);
            $table->text('findings')->nullable();
            $table->json('evidence_links')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'due_at'], 'rcc_status_due_idx');
            $table->index(['recruitment_candidate_id', 'check_type'], 'rcc_candidate_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate_checks');
    }
};
