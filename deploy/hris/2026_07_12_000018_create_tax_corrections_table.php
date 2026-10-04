<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_monthly_report_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('correction_number')->unique();
            $table->string('correction_type')->default('pembetulan_pelaporan');
            $table->string('period', 7);
            $table->string('payroll_period', 7)->nullable();
            $table->decimal('previous_gross_income', 18, 2)->default(0);
            $table->decimal('corrected_gross_income', 18, 2)->default(0);
            $table->decimal('previous_tax_base', 18, 2)->default(0);
            $table->decimal('corrected_tax_base', 18, 2)->default(0);
            $table->decimal('previous_pph21_amount', 18, 2)->default(0);
            $table->decimal('corrected_pph21_amount', 18, 2)->default(0);
            $table->decimal('delta_pph21_amount', 18, 2)->default(0);
            $table->string('payroll_adjustment_type')->default('none');
            $table->string('status')->default('draft');
            $table->text('reason');
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->json('workflow_snapshot')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'status']);
            $table->index(['tax_profile_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_corrections');
    }
};
