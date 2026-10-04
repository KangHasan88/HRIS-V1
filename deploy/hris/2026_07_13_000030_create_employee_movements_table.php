<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('movement_number', 80)->unique();
            $table->string('movement_type', 40);
            $table->date('request_date')->nullable();
            $table->date('effective_date');
            $table->string('status', 40)->default('draft');
            $table->foreignId('from_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('to_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('from_position')->nullable();
            $table->string('to_position')->nullable();
            $table->string('from_department')->nullable();
            $table->string('to_department')->nullable();
            $table->string('from_grade')->nullable();
            $table->string('to_grade')->nullable();
            $table->string('from_cost_center')->nullable();
            $table->string('to_cost_center')->nullable();
            $table->text('reason')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('notes')->nullable();
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['movement_type', 'status']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_movements');
    }
};
