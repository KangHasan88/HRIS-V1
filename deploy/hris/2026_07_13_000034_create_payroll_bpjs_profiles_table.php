<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_bpjs_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bpjs_kesehatan_number')->nullable();
            $table->string('bpjs_ketenagakerjaan_number')->nullable();
            $table->string('bpjs_class', 40)->nullable();
            $table->date('effective_date');
            $table->decimal('health_contribution_base', 18, 2)->default(0);
            $table->decimal('employment_contribution_base', 18, 2)->default(0);
            $table->decimal('health_employee_rate', 8, 4)->default(1);
            $table->decimal('health_employer_rate', 8, 4)->default(4);
            $table->decimal('jht_employee_rate', 8, 4)->default(2);
            $table->decimal('jht_employer_rate', 8, 4)->default(3.7);
            $table->decimal('jp_employee_rate', 8, 4)->default(1);
            $table->decimal('jp_employer_rate', 8, 4)->default(2);
            $table->boolean('is_active')->default(true);
            $table->string('verification_status', 40)->default('draft');
            $table->text('notes')->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'verification_status']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_bpjs_profiles');
    }
};
