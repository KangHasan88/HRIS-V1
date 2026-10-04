<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_privacy_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->boolean('mask_nik')->default(true);
            $table->boolean('mask_npwp')->default(true);
            $table->boolean('export_requires_approval')->default(true);
            $table->unsignedSmallInteger('retention_years')->default(10);
            $table->string('legal_basis')->default('Kepatuhan perpajakan dan ketenagakerjaan Indonesia');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tax_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('export_type');
            $table->string('scope')->default('company');
            $table->string('period')->nullable();
            $table->string('purpose');
            $table->string('status')->default('pending');
            $table->json('masked_fields')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'export_type'], 'tax_export_company_status_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_export_logs');
        Schema::dropIfExists('tax_privacy_policies');
    }
};
