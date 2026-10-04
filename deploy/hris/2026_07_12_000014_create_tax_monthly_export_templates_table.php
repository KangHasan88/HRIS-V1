<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_monthly_export_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_monthly_report_package_id');
            $table->string('template_number')->unique();
            $table->string('schema_version');
            $table->string('format')->default('csv');
            $table->string('status')->default('generated');
            $table->json('columns_schema');
            $table->json('payload_snapshot');
            $table->string('checksum');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['schema_version', 'format', 'status'], 'tax_export_template_schema_format_status_idx');
            $table->foreign('tax_monthly_report_package_id', 'tax_export_template_package_fk')
                ->references('id')
                ->on('tax_monthly_report_packages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_monthly_export_templates');
    }
};
