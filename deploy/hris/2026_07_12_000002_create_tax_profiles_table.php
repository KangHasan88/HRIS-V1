<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->nullable();
            $table->string('employee_name');
            $table->string('nik', 32);
            $table->string('npwp', 32)->nullable();
            $table->string('ptkp_status', 10);
            $table->string('tax_method', 20)->default('gross');
            $table->string('klu', 20)->nullable();
            $table->string('kpp_code', 30)->nullable();
            $table->string('kpp_name')->nullable();
            $table->boolean('is_npwp_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'nik']);
            $table->index(['company_id', 'branch_id', 'ptkp_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_profiles');
    }
};
