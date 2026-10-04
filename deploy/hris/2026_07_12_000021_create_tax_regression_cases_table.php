<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_regression_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('case_type')->default('pph21_monthly');
            $table->string('case_name');
            $table->string('period', 7);
            $table->json('input_payload');
            $table->json('expected_payload');
            $table->json('actual_payload')->nullable();
            $table->decimal('expected_pph21_amount', 18, 2)->default(0);
            $table->decimal('actual_pph21_amount', 18, 2)->default(0);
            $table->decimal('variance_amount', 18, 2)->default(0);
            $table->string('coretax_schema_version')->nullable();
            $table->string('coretax_validation_status')->default('not_required');
            $table->string('run_status')->default('draft');
            $table->json('assertion_snapshot')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'run_status']);
            $table->index(['case_type', 'coretax_validation_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_regression_cases');
    }
};
