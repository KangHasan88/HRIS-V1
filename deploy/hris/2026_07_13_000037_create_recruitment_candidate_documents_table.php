<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recruitment_candidates', 'portal_token')) {
            Schema::table('recruitment_candidates', function (Blueprint $table) {
                $table->string('portal_token', 80)->nullable()->unique('rc_portal_token_unique')->after('candidate_status');
                $table->timestamp('portal_invited_at')->nullable()->after('portal_token');
            });
        }

        if (! Schema::hasTable('recruitment_candidate_documents')) {
            Schema::create('recruitment_candidate_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recruitment_candidate_id')->constrained()->cascadeOnDelete();
                $table->string('document_type', 80);
                $table->string('document_title');
                $table->string('file_reference')->nullable();
                $table->string('verification_status', 40)->default('submitted');
                $table->date('submitted_at')->nullable();
                $table->string('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['document_type', 'verification_status'], 'rc_docs_type_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate_documents');

        Schema::table('recruitment_candidates', function (Blueprint $table) {
            $table->dropUnique(['portal_token']);
            $table->dropColumn(['portal_token', 'portal_invited_at']);
        });
    }
};
