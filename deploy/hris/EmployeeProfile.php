<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'tax_profile_id',
        'employee_number',
        'full_name',
        'birth_date',
        'gender',
        'employment_status',
        'position',
        'department',
        'grade',
        'cost_center',
        'join_date',
        'contract_end_date',
        'work_email',
        'personal_email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address_snapshot',
        'document_summary',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'join_date' => 'date',
        'birth_date' => 'date',
        'contract_end_date' => 'date',
        'address_snapshot' => 'array',
        'document_summary' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function bpjsProfile(): HasOne
    {
        return $this->hasOne(EmployeeBpjsProfile::class);
    }

    public function payrollBpjsProfiles(): HasMany
    {
        return $this->hasMany(PayrollBpjsProfile::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(PayrollPayslip::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmployeeStatusHistory::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(EmployeeAsset::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(EmployeeMovement::class);
    }

    public function disciplinaryRecords(): HasMany
    {
        return $this->hasMany(EmployeeDisciplinaryRecord::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }

    public function offboardingCases(): HasMany
    {
        return $this->hasMany(EmployeeOffboardingCase::class);
    }

    public function recruitmentOnboardingJourneys(): HasMany
    {
        return $this->hasMany(RecruitmentOnboardingJourney::class);
    }
}
