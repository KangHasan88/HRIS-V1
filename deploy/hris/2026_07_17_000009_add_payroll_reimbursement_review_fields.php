<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manager_claim_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('manager_claim_requests', 'ocr_status')) {
                $table->string('ocr_status', 40)->default('not_scanned')->after('receipt_metadata');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'ocr_confidence')) {
                $table->unsignedTinyInteger('ocr_confidence')->default(0)->after('ocr_status');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'ocr_vendor_name')) {
                $table->string('ocr_vendor_name')->nullable()->after('ocr_confidence');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'ocr_receipt_date')) {
                $table->date('ocr_receipt_date')->nullable()->after('ocr_vendor_name');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'ocr_total_amount')) {
                $table->decimal('ocr_total_amount', 14, 2)->nullable()->after('ocr_receipt_date');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'duplicate_risk_score')) {
                $table->unsignedTinyInteger('duplicate_risk_score')->default(0)->after('ocr_total_amount');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'payroll_treatment')) {
                $table->string('payroll_treatment', 40)->default('non_taxable_reimbursement')->after('duplicate_risk_score');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'payroll_period')) {
                $table->string('payroll_period', 7)->nullable()->after('payroll_treatment');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'payroll_posting_status')) {
                $table->string('payroll_posting_status', 40)->default('not_posted')->after('payroll_period');
            }
            if (! Schema::hasColumn('manager_claim_requests', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('payroll_posting_status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('manager_claim_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manager_claim_requests', function (Blueprint $table) {
            if (Schema::hasColumn('manager_claim_requests', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }

            foreach ([
                'reviewed_at',
                'payroll_posting_status',
                'payroll_period',
                'payroll_treatment',
                'duplicate_risk_score',
                'ocr_total_amount',
                'ocr_receipt_date',
                'ocr_vendor_name',
                'ocr_confidence',
                'ocr_status',
            ] as $column) {
                if (Schema::hasColumn('manager_claim_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
