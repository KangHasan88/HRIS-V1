<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_document_id')->nullable()->constrained('employee_documents')->nullOnDelete();
            $table->string('document_number')->unique();
            $table->string('document_type');
            $table->string('title');
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->date('reminder_date')->nullable();
            $table->string('file_reference')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('ocr_status')->default('manual');
            $table->longText('ocr_text')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['document_type', 'status']);
            $table->index(['expired_at', 'reminder_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
