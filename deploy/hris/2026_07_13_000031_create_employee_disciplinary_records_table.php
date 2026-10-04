<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('case_number', 80)->unique();
            $table->string('case_type', 50);
            $table->string('risk_level', 30)->default('low');
            $table->date('incident_date');
            $table->date('issued_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status', 40)->default('open');
            $table->string('reported_by')->nullable();
            $table->string('handled_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->text('summary');
            $table->text('corrective_action')->nullable();
            $table->text('employee_response')->nullable();
            $table->text('follow_up_result')->nullable();
            $table->json('case_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['case_type', 'status']);
            $table->index(['risk_level', 'follow_up_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_disciplinary_records');
    }
};
