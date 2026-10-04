<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_non_permanent_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('worker_name');
            $table->string('worker_number')->nullable();
            $table->string('nik')->nullable();
            $table->string('npwp')->nullable();
            $table->string('worker_type');
            $table->string('ptkp_status')->default('TK/0');
            $table->boolean('is_npwp_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'worker_type', 'is_active'], 'tax_np_worker_company_type_idx');
        });

        Schema::create('tax_non_permanent_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_non_permanent_worker_id');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');
            $table->unsignedSmallInteger('work_days')->default(1);
            $table->decimal('daily_rate', 18, 2)->default(0);
            $table->decimal('gross_income', 18, 2)->default(0);
            $table->decimal('taxable_base', 18, 2)->default(0);
            $table->decimal('tax_rate', 8, 6)->default(0);
            $table->decimal('pph21_amount', 18, 2)->default(0);
            $table->string('calculation_status')->default('draft');
            $table->json('calculation_steps')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'company_id', 'calculation_status'], 'tax_np_calc_period_company_status_idx');
            $table->foreign('tax_non_permanent_worker_id', 'tax_np_calc_worker_fk')
                ->references('id')
                ->on('tax_non_permanent_workers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_non_permanent_calculations');
        Schema::dropIfExists('tax_non_permanent_workers');
    }
};
