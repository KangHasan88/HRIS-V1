<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerClaimRequest extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'company_id',
        'branch_id',
        'claim_number',
        'claim_date',
        'claim_type',
        'request_source',
        'amount',
        'merchant_name',
        'approval_status',
        'current_approver',
        'submitted_at',
        'decided_at',
        'decided_by',
        'attachment_reference',
        'receipt_capture_method',
        'receipt_captured_at',
        'receipt_metadata',
        'ocr_status',
        'ocr_confidence',
        'ocr_vendor_name',
        'ocr_receipt_date',
        'ocr_total_amount',
        'duplicate_risk_score',
        'payroll_treatment',
        'payroll_period',
        'payroll_posting_status',
        'reviewed_by',
        'reviewed_at',
        'description',
        'decision_notes',
        'created_by',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'receipt_captured_at' => 'datetime',
        'receipt_metadata' => 'array',
        'ocr_receipt_date' => 'date',
        'ocr_total_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
