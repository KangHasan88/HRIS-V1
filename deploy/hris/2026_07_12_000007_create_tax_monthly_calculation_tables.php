<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_ter_rates', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('income_from', 18, 2)->default(0);
            $table->decimal('income_to', 18, 2)->nullable();
            $table->decimal('rate', 8, 6)->default(0);
            $table->string('source_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'income_from', 'income_to'], 'tax_ter_category_income_idx');
        });

        Schema::create('tax_monthly_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_ter_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');
            $table->string('ter_category');
            $table->decimal('gross_income', 18, 2)->default(0);
            $table->decimal('taxable_allowance', 18, 2)->default(0);
            $table->decimal('deduction', 18, 2)->default(0);
            $table->decimal('ter_base_amount', 18, 2)->default(0);
            $table->decimal('ter_rate', 8, 6)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->string('calculation_status')->default('draft');
            $table->json('calculation_steps')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'calculation_status'], 'tax_monthly_period_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_monthly_calculations');
        Schema::dropIfExists('tax_ter_rates');
    }
};
