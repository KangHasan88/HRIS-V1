<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_withholding_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_monthly_report_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_number')->unique();
            $table->string('document_type')->default('monthly_pph21');
            $table->string('period');
            $table->string('status')->default('draft');
            $table->decimal('gross_income', 18, 2)->default(0);
            $table->decimal('tax_base', 18, 2)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->json('employee_snapshot');
            $table->json('document_payload');
            $table->string('checksum');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'status'], 'tax_withholding_period_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_withholding_documents');
    }
};
