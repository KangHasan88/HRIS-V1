<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('asset_code')->unique();
            $table->string('asset_type');
            $table->string('asset_name');
            $table->string('serial_number')->nullable();
            $table->decimal('asset_value', 14, 2)->nullable();
            $table->date('assigned_at');
            $table->date('due_return_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->string('assignment_status')->default('assigned');
            $table->string('condition_out')->default('good');
            $table->string('condition_in')->nullable();
            $table->boolean('requires_clearance')->default(true);
            $table->boolean('clearance_completed')->default(false);
            $table->string('handover_by')->nullable();
            $table->string('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('asset_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['asset_type', 'assignment_status']);
            $table->index(['due_return_at', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assets');
    }
};
