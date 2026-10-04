<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_offer_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_candidate_id')->constrained('recruitment_candidates')->cascadeOnDelete();
            $table->string('offer_number', 80)->unique();
            $table->string('position_title', 180);
            $table->date('join_date')->nullable();
            $table->decimal('salary_offer', 18, 2)->default(0);
            $table->string('employment_type', 40)->default('permanent');
            $table->string('template_name', 120)->default('Standard Offer Letter');
            $table->string('status', 40)->default('draft');
            $table->date('sent_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->date('signed_at')->nullable();
            $table->string('esign_provider', 80)->nullable();
            $table->string('esign_reference', 160)->nullable();
            $table->string('signer_email', 180)->nullable();
            $table->text('offer_summary')->nullable();
            $table->json('tracking_events')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'rol_status_expiry_idx');
            $table->index(['recruitment_candidate_id', 'status'], 'rol_candidate_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_offer_letters');
    }
};
