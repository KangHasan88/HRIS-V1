<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manager_claim_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('manager_claim_requests', 'request_source')) {
                $table->string('request_source', 40)->default('manual')->after('claim_type');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'merchant_name')) {
                $table->string('merchant_name')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'receipt_capture_method')) {
                $table->string('receipt_capture_method', 40)->nullable()->after('attachment_reference');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'receipt_captured_at')) {
                $table->timestamp('receipt_captured_at')->nullable()->after('receipt_capture_method');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'receipt_metadata')) {
                $table->json('receipt_metadata')->nullable()->after('receipt_captured_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manager_claim_requests', function (Blueprint $table) {
            foreach (['receipt_metadata', 'receipt_captured_at', 'receipt_capture_method', 'merchant_name', 'request_source'] as $column) {
                if (Schema::hasColumn('manager_claim_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
