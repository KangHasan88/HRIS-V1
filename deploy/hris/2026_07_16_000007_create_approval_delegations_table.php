<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope_type', 60)->default('all');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 40)->default('active');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['delegator_user_id', 'status', 'start_date'], 'approval_delegate_owner_idx');
            $table->index(['delegate_user_id', 'status', 'end_date'], 'approval_delegate_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
    }
};
