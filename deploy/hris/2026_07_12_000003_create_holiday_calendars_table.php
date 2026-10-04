<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->date('holiday_date');
            $table->string('scope')->default('national');
            $table->string('category')->default('national');
            $table->string('religion')->nullable();
            $table->string('region')->nullable();
            $table->boolean('affects_attendance')->default(true);
            $table->boolean('affects_leave')->default(true);
            $table->boolean('affects_payroll')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'holiday_date', 'name'], 'holiday_scope_date_name_unique');
            $table->index(['holiday_date', 'scope', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendars');
    }
};
