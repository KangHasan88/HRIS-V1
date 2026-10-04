<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_workforce_compliance_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_workforce_vendor_id');
            $table->unsignedBigInteger('external_workforce_contract_id')->nullable();
            $table->string('item_number', 100)->unique();
            $table->string('compliance_area', 60);
            $table->string('requirement_name');
            $table->string('responsible_party', 40)->default('vendor');
            $table->string('document_reference')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('verification_status', 40)->default('draft');
            $table->string('risk_level', 40)->default('medium');
            $table->string('owner_name', 160)->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['compliance_area', 'verification_status'], 'ext_wf_comp_area_status_idx');
            $table->index(['risk_level', 'expiry_date'], 'ext_wf_comp_risk_exp_idx');
            $table->foreign('external_workforce_vendor_id', 'ext_wf_comp_vendor_fk')
                ->references('id')
                ->on('external_workforce_vendors')
                ->cascadeOnDelete();
            $table->foreign('external_workforce_contract_id', 'ext_wf_comp_contract_fk')
                ->references('id')
                ->on('external_workforce_contracts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_workforce_compliance_items');
    }
};
