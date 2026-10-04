<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_tax_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('component_code');
            $table->string('component_name');
            $table->string('component_type');
            $table->string('tax_treatment');
            $table->string('payroll_formula_key')->nullable();
            $table->decimal('default_amount', 18, 2)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_recurring')->default(true);
            $table->json('dependencies')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'component_code'], 'payroll_tax_component_company_code_unique');
            $table->index(['component_type', 'tax_treatment', 'is_active'], 'payroll_tax_component_type_tax_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_tax_components');
    }
};
