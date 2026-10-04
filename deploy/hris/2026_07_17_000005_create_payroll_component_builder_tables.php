<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('component_code')->unique();
            $table->string('component_name');
            $table->string('component_category');
            $table->string('calculation_method')->default('fixed');
            $table->decimal('default_amount', 18, 2)->default(0);
            $table->decimal('rate_value', 12, 6)->default(0);
            $table->text('formula_expression')->nullable();
            $table->json('dependencies')->nullable();
            $table->string('prorate_basis')->default('none');
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_bpjs_basis')->default(false);
            $table->boolean('is_payslip_visible')->default(true);
            $table->string('gl_account')->nullable();
            $table->date('effective_start')->nullable();
            $table->date('effective_end')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_component_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_component_id')->constrained('payroll_components')->cascadeOnDelete();
            $table->string('rule_name');
            $table->string('condition_field');
            $table->string('operator')->default('equals');
            $table->string('condition_value')->nullable();
            $table->string('action')->default('include');
            $table->unsignedInteger('priority')->default(10);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_component_rules');
        Schema::dropIfExists('payroll_components');
    }
};
