<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_monthly_report_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');
            $table->string('package_number')->unique();
            $table->string('status')->default('draft');
            $table->unsignedInteger('employee_count')->default(0);
            $table->decimal('total_gross_income', 18, 2)->default(0);
            $table->decimal('total_tax_base', 18, 2)->default(0);
            $table->decimal('total_pph21', 18, 2)->default(0);
            $table->json('summary_snapshot')->nullable();
            $table->json('detail_snapshot')->nullable();
            $table->json('validation_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'status'], 'tax_monthly_pkg_period_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_monthly_report_packages');
    }
};
