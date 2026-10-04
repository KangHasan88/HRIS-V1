<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->date('effective_date');
            $table->date('review_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('reason')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('status_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['to_status', 'effective_date']);
            $table->index(['review_date', 'contract_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_status_histories');
    }
};
