<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_calculation_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');
            $table->decimal('gross_income', 18, 2)->default(0);
            $table->decimal('taxable_allowance', 18, 2)->default(0);
            $table->decimal('deduction', 18, 2)->default(0);
            $table->decimal('net_income', 18, 2)->default(0);
            $table->decimal('annualized_income', 18, 2)->default(0);
            $table->decimal('ptkp_amount', 18, 2)->default(0);
            $table->decimal('pkp_amount', 18, 2)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->string('tax_method');
            $table->string('calculation_status')->default('draft');
            $table->json('calculation_steps')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'calculation_status'], 'tax_audit_period_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calculation_audits');
    }
};
