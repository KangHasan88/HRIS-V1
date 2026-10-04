<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_contract_id')->nullable()->constrained('employee_contracts')->nullOnDelete();
            $table->string('contract_number')->unique();
            $table->string('contract_type')->default('pkwt');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('signed_at')->nullable();
            $table->date('reminder_date')->nullable();
            $table->unsignedSmallInteger('renewal_sequence')->default(0);
            $table->string('status')->default('draft');
            $table->string('document_reference')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('contract_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['contract_type', 'status']);
            $table->index(['end_date', 'reminder_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
