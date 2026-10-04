<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_annual_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('tax_year');
            $table->decimal('annual_gross_income', 18, 2)->default(0);
            $table->decimal('annual_taxable_allowance', 18, 2)->default(0);
            $table->decimal('annual_deduction', 18, 2)->default(0);
            $table->decimal('annual_net_income', 18, 2)->default(0);
            $table->decimal('ptkp_amount', 18, 2)->default(0);
            $table->decimal('pkp_amount', 18, 2)->default(0);
            $table->decimal('annual_pph21_due', 18, 2)->default(0);
            $table->decimal('monthly_pph21_paid', 18, 2)->default(0);
            $table->decimal('true_up_amount', 18, 2)->default(0);
            $table->string('true_up_status')->default('balanced');
            $table->string('calculation_status')->default('draft');
            $table->json('monthly_snapshot')->nullable();
            $table->json('calculation_steps')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tax_profile_id', 'tax_year'], 'tax_annual_profile_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_annual_reconciliations');
    }
};
