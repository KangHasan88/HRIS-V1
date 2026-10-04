<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_irregular_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');
            $table->string('income_type');
            $table->string('tax_method');
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->decimal('taxable_amount', 18, 2)->default(0);
            $table->decimal('tax_rate', 8, 6)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->string('calculation_status')->default('draft');
            $table->json('calculation_steps')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'income_type', 'calculation_status'], 'tax_irregular_period_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_irregular_incomes');
    }
};
