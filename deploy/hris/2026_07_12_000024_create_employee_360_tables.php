<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tax_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->string('employment_status')->default('probation');
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('grade')->nullable();
            $table->string('cost_center')->nullable();
            $table->date('join_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('work_email')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->json('address_snapshot')->nullable();
            $table->json('document_summary')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'employment_status']);
        });

        Schema::create('employee_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->date('birth_date')->nullable();
            $table->boolean('is_dependent')->default(false);
            $table->timestamps();
        });

        Schema::create('employee_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_bpjs_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('bpjs_kesehatan_number')->nullable();
            $table->string('bpjs_ketenagakerjaan_number')->nullable();
            $table->string('bpjs_class')->nullable();
            $table->date('registered_at')->nullable();
            $table->json('coverage_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bpjs_profiles');
        Schema::dropIfExists('employee_bank_accounts');
        Schema::dropIfExists('employee_family_members');
        Schema::dropIfExists('employee_profiles');
    }
};
