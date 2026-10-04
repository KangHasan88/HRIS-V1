<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('simulation_number')->unique();
            $table->string('scenario_name');
            $table->string('period', 7);
            $table->string('tax_method')->default('gross');
            $table->decimal('gross_income', 18, 2)->default(0);
            $table->decimal('taxable_allowance', 18, 2)->default(0);
            $table->decimal('deduction', 18, 2)->default(0);
            $table->decimal('tax_base', 18, 2)->default(0);
            $table->decimal('ter_rate', 8, 5)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->decimal('tax_allowance_amount', 18, 2)->default(0);
            $table->decimal('company_tax_borne_amount', 18, 2)->default(0);
            $table->decimal('take_home_pay', 18, 2)->default(0);
            $table->string('scenario_status')->default('draft');
            $table->json('input_snapshot')->nullable();
            $table->json('result_snapshot')->nullable();
            $table->json('comparison_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'scenario_status']);
            $table->index(['tax_profile_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_simulations');
    }
};
