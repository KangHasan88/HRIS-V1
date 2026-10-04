<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccessReview;
use App\Models\AccessReviewItem;
use App\Models\AccountingErpConnector;
use App\Models\AttendanceAlert;
use App\Models\AttendanceAnomalyDetection;
use App\Models\AttendanceClockEvent;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDeviceBinding;
use App\Models\AttendanceGeofence;
use App\Models\AttendanceImportBatch;
use App\Models\AttendanceImportRow;
use App\Models\AttendanceLocationPolicy;
use App\Models\AttendanceShiftAssignment;
use App\Models\AttendanceShiftPattern;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalDelegation;
use App\Models\AuditLog;
use App\Models\BiometricConnector;
use App\Models\BiometricSyncLog;
use App\Models\Branch;
use App\Models\BusinessTripRequest;
use App\Models\Company;
use App\Models\CompanyModuleSetting;
use App\Models\CostCenter;
use App\Models\CoretaxConnectorSetting;
use App\Models\CoretaxRetryLog;
use App\Models\CoretaxSubmissionTracker;
use App\Models\DataLegalHoldCase;
use App\Models\DataRetentionExecutionLog;
use App\Models\DataRetentionPolicy;
use App\Models\EmployeeAsset;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeBpjsProfile;
use App\Models\EmployeeProfile;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EngagementActionPlan;
use App\Models\EngagementSurvey;
use App\Models\EngagementSurveyResponse;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\EmployeeDisciplinaryRecord;
use App\Models\EmployeeMovement;
use App\Models\EmployeeOnboardingTask;
use App\Models\EmployeeOnboardingTemplate;
use App\Models\EmployeeOffboardingCase;
use App\Models\EmployeeClearanceItem;
use App\Models\EmployeeStatusHistory;
use App\Models\EmailCalendarConnector;
use App\Models\EmailCalendarEvent;
use App\Models\IdentityProviderSetting;
use App\Models\EssTrustedDevice;
use App\Models\EssOfflineQueueItem;
use App\Models\EssProfileUpdateRequest;
use App\Models\ExternalWorkforceAttendance;
use App\Models\ExternalWorkforceComplianceItem;
use App\Models\ExternalWorkforceContract;
use App\Models\ExternalWorkforcePieceOutput;
use App\Models\ExternalWorkforcePieceRate;
use App\Models\ExternalWorkforceVendor;
use App\Models\ExternalWorkforceVendorInvoice;
use App\Models\ExternalWorkforceVendorInvoiceLine;
use App\Models\ExternalWorkforceWorker;
use App\Models\HolidayCalendar;
use App\Models\HrHelpdeskMessage;
use App\Models\HrHelpdeskTicket;
use App\Models\IndividualDevelopmentPlan;
use App\Models\InternalMobilityApplication;
use App\Models\InternalMobilityOpportunity;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\LeavePolicyRule;
use App\Models\LeavePolicyType;
use App\Models\LeaveOvertimeCapacityPrediction;
use App\Models\LeaveRequest;
use App\Models\ManagerBudgetAllocation;
use App\Models\ManagerClaimRequest;
use App\Models\ManagerHeadcountPlan;
use App\Models\ManagerOneOnOneLog;
use App\Models\ManagerPerformanceNote;
use App\Models\ManagerReplacementRequisition;
use App\Models\ManagerTeamDocumentRequest;
use App\Models\ManagerWorkCertificateApproval;
use App\Models\ModuleSetting;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\OrganizationUnit;
use App\Models\OvertimeRequest;
use App\Models\PayrollBpjsProfile;
use App\Models\PayrollAdjustment;
use App\Models\PayrollAccountingJournalBatch;
use App\Models\PayrollAccountingJournalLine;
use App\Models\PayrollComponent;
use App\Models\PayrollComponentRule;
use App\Models\PayrollDisbursementApproval;
use App\Models\PayrollDisbursementBatch;
use App\Models\PayrollDisbursementItem;
use App\Models\PayrollPayslip;
use App\Models\PayrollRun;
use App\Models\PayrollRunAnomaly;
use App\Models\PayrollRunValidation;
use App\Models\PayrollSalaryBand;
use App\Models\PlatformDataConsent;
use App\Models\PlatformDataPrivacyPolicy;
use App\Models\PlatformDataRetentionJob;
use App\Models\EmployeeSalaryAssignment;
use App\Models\CompensationAccessPolicy;
use App\Models\PayrollTaxComponent;
use App\Models\PerformanceReviewCycle;
use App\Models\PerformanceReviewForm;
use App\Models\PerformanceReviewResponse;
use App\Models\PerformanceGoal;
use App\Models\PerformanceGoalCheckIn;
use App\Models\PerformanceGoalCycle;
use App\Models\PerformanceCalibrationItem;
use App\Models\PerformanceCalibrationSession;
use App\Models\PrivilegedActionConfirmation;
use App\Models\Competency;
use App\Models\CompetencyRoleRequirement;
use App\Models\PsychotestInvitation;
use App\Models\PsychotestBatteryTemplate;
use App\Models\PsychotestDimension;
use App\Models\PsychotestPackage;
use App\Models\PsychotestProvider;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestQuestionCategory;
use App\Models\PsychotestResult;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCandidateCheck;
use App\Models\RecruitmentCandidateDocument;
use App\Models\RecruitmentEmployeeConversion;
use App\Models\RecruitmentInterview;
use App\Models\RecruitmentManpowerRequest;
use App\Models\RecruitmentOfferLetter;
use App\Models\RecruitmentOnboardingJourney;
use App\Models\Role;
use App\Models\SecurityTwoFactorPolicy;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionPlan;
use App\Models\TaxAnnualReconciliation;
use App\Models\TaxCalculationAudit;
use App\Models\TaxComplianceEvent;
use App\Models\TaxCorrection;
use App\Models\TaxExportLog;
use App\Models\TaxIrregularIncome;
use App\Models\TaxMonthlyCalculation;
use App\Models\TaxMonthlyExportTemplate;
use App\Models\TaxMonthlyReportPackage;
use App\Models\TaxNonPermanentCalculation;
use App\Models\TaxNonPermanentWorker;
use App\Models\TaxPrivacyPolicy;
use App\Models\TaxProfile;
use App\Models\TaxRegressionCase;
use App\Models\TaxSimulation;
use App\Models\TaxTerRate;
use App\Models\TaxWithholdingDocument;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Models\UserTwoFactorSetting;
use App\Models\WorkforcePlanningSimulation;
use App\Models\WhatsappGatewaySetting;
use App\Models\WhatsappMessageQueue;
use App\Models\WhatsappWebhookLog;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk HRIS')
            ->assertDontSee('SBB Pattern');
    }

    public function test_ess_login_page_uses_employee_self_service_theme(): void
    {
        $this->get('http://localhost:8092/login')
            ->assertOk()
            ->assertSee('Masuk ESS')
            ->assertSee('Kurmigo ESS')
            ->assertSee('Employee Self Service')
            ->assertDontSee('Administration Panel')
            ->assertDontSee('SBB Pattern');
    }

    public function test_seeded_admin_can_access_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard HRIS')
               ->assertSee('Progress Delivery Awal');
    }

    public function test_default_hris_roles_and_demo_users_are_seeded(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'HR Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Payroll Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Manager']);
        $this->assertDatabaseHas('roles', ['name' => 'Employee']);

        $this->assertTrue(Role::where('name', 'HR Admin')->firstOrFail()->permissions()->where('name', 'manage modules')->exists());
        $this->assertTrue(Role::where('name', 'Payroll Admin')->firstOrFail()->permissions()->where('name', 'manage payroll settings')->exists());

        $this->assertSame('Super Admin', User::where('email', 'admin@kurmigo.id')->firstOrFail()->role->name);
        $this->assertSame('HR Admin', User::where('email', 'ayu@kurmigo.id')->firstOrFail()->role->name);
        $this->assertSame('Payroll Admin', User::where('email', 'citra@kurmigo.id')->firstOrFail()->role->name);
        $this->assertSame('Manager', User::where('email', 'bima@kurmigo.id')->firstOrFail()->role->name);
    }

    public function test_default_company_branch_and_legal_entity_scope_is_seeded(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('companies', ['code' => 'BMP', 'name' => 'PT Bumi Mineral Persada']);
        $this->assertDatabaseHas('legal_entities', ['code' => 'BMP-PT', 'is_primary' => true]);
        $this->assertDatabaseHas('branches', ['code' => 'BMP-HO', 'is_head_office' => true]);
        $this->assertDatabaseHas('branches', ['code' => 'BMP-SITE-01', 'city' => 'Morowali']);

        $admin = User::with(['company', 'legalEntity', 'branch'])->where('email', 'admin@kurmigo.id')->firstOrFail();
        $employee = User::with(['company', 'legalEntity', 'branch'])->where('email', 'dimas@kurmigo.id')->firstOrFail();

        $this->assertSame('BMP', $admin->company->code);
        $this->assertSame('BMP-PT', $admin->legalEntity->code);
        $this->assertSame('BMP-HO', $admin->branch->code);
        $this->assertSame('BMP-SITE-01', $employee->branch->code);
    }

    public function test_seeded_admin_can_view_organization_scope_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/organization')
            ->assertOk()
            ->assertSeeText('Organisasi')
            ->assertSeeText('PT Bumi Mineral Persada')
            ->assertSeeText('BMP-SITE-01');
    }

    public function test_seeded_admin_can_create_organization_scope_records(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/organization/companies', [
                'code' => 'KMG',
                'name' => 'PT Kurmigo Digital Nusantara',
                'brand_name' => 'Kurmigo',
                'tax_id' => '09.876.543.2-100.000',
                'address' => 'Jakarta',
            ])
            ->assertRedirect('/settings/organization');

        $company = Company::where('code', 'KMG')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/organization/legal-entities', [
                'company_id' => $company->id,
                'code' => 'KMG-PT',
                'name' => 'PT Kurmigo Digital Nusantara',
                'npwp' => '09.876.543.2-100.000',
                'nib' => '9120000000002',
                'is_primary' => 1,
            ])
            ->assertRedirect('/settings/organization');

        $this->actingAs($admin)
            ->post('/settings/organization/branches', [
                'company_id' => $company->id,
                'code' => 'KMG-HO',
                'name' => 'Head Office Kurmigo',
                'city' => 'Jakarta',
                'is_head_office' => 1,
            ])
            ->assertRedirect('/settings/organization');

        $this->assertDatabaseHas('legal_entities', ['code' => 'KMG-PT', 'company_id' => $company->id]);
        $this->assertDatabaseHas('branches', ['code' => 'KMG-HO', 'company_id' => $company->id]);
    }

    public function test_seeded_admin_can_manage_job_structure_master_data(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/job-structure')
            ->assertOk()
            ->assertSeeText('Master Jabatan')
            ->assertSeeText('Tambah Posisi');

        $this->actingAs($admin)
            ->post('/settings/job-structure/units', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'OPS',
                'name' => 'Operasional Tambang',
                'unit_type' => 'department',
                'manager_name' => 'Bima Santoso',
            ])
            ->assertRedirect('/settings/job-structure');

        $unit = OrganizationUnit::where('code', 'OPS')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/job-structure/job-grades', [
                'company_id' => $company->id,
                'code' => 'G5',
                'name' => 'Supervisor',
                'level' => 5,
                'minimum_salary' => 8000000,
                'maximum_salary' => 15000000,
            ])
            ->assertRedirect('/settings/job-structure');

        $grade = JobGrade::where('code', 'G5')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/job-structure/cost-centers', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'OPS-SITE',
                'name' => 'Operasional Site',
                'owner_name' => 'Finance Site',
                'is_billable' => 1,
            ])
            ->assertRedirect('/settings/job-structure');

        $costCenter = CostCenter::where('code', 'OPS-SITE')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/job-structure/job-positions', [
                'company_id' => $company->id,
                'organization_unit_id' => $unit->id,
                'job_grade_id' => $grade->id,
                'cost_center_id' => $costCenter->id,
                'code' => 'POS-SUP-OPS',
                'name' => 'Site Supervisor',
                'employment_category' => 'supervisor',
                'headcount_plan' => 4,
            ])
            ->assertRedirect('/settings/job-structure');

        $position = JobPosition::with(['organizationUnit', 'jobGrade', 'costCenter'])->where('code', 'POS-SUP-OPS')->firstOrFail();

        $this->assertSame('OPS', $position->organizationUnit->code);
        $this->assertSame('G5', $position->jobGrade->code);
        $this->assertSame('OPS-SITE', $position->costCenter->code);
        $this->assertSame(4, $position->headcount_plan);

        $this->actingAs($admin)
            ->get('/settings/job-structure')
            ->assertOk()
            ->assertSeeText('Operasional Tambang')
            ->assertSeeText('Site Supervisor')
            ->assertSeeText('OPS-SITE');

        $this->actingAs($admin)
            ->get('/employees')
            ->assertOk()
            ->assertSee('job-position-options')
            ->assertSee('Site Supervisor')
            ->assertSee('OPS-SITE');

        $this->assertDatabaseHas('audit_logs', ['event' => 'job_position.created']);
    }

    public function test_seeded_admin_can_create_employee_360_profile(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $user = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-360-011',
            'employee_name' => 'Profil Karyawan Demo',
            'nik' => '3175091201010111',
            'npwp' => '09.123.456.7-111.000',
            'ptkp_status' => 'K/1',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/employees')
            ->assertOk()
            ->assertSeeText('Data Karyawan 360')
            ->assertSeeText('Tambah Karyawan');

        $this->actingAs($admin)
            ->post('/employees', [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'tax_profile_id' => $taxProfile->id,
                'employee_number' => 'KRM-360-011',
                'full_name' => 'Profil Karyawan Demo',
                'employment_status' => 'permanent',
                'position' => 'Site Supervisor',
                'department' => 'Operasional',
                'grade' => 'G5',
                'cost_center' => 'OPS-SITE',
                'join_date' => '2026-07-15',
                'work_email' => 'profil360@kurmigo.id',
                'personal_email' => 'profil360.personal@example.test',
                'phone' => '6281200000111',
                'address' => 'Morowali Site',
                'emergency_contact_name' => 'Kontak Darurat',
                'emergency_contact_phone' => '6281200000222',
                'family_members' => [
                    [
                        'name' => 'Pasangan Demo',
                        'relationship' => 'Pasangan',
                        'birth_date' => '1995-01-01',
                        'is_dependent' => 1,
                    ],
                    [
                        'name' => 'Anak Demo',
                        'relationship' => 'Anak',
                        'birth_date' => '2020-01-01',
                        'is_dependent' => 1,
                    ],
                ],
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'Profil Karyawan Demo',
                'bpjs_kesehatan_number' => 'BPJSKES-011',
                'bpjs_ketenagakerjaan_number' => 'BPJSTK-011',
                'bpjs_class' => 'Kelas 1',
                'is_active' => 1,
            ])
            ->assertRedirect('/employees');

        $employee = EmployeeProfile::with(['familyMembers', 'bankAccounts', 'bpjsProfile', 'taxProfile'])->where('employee_number', 'KRM-360-011')->firstOrFail();

        $this->assertSame('permanent', $employee->employment_status);
        $this->assertSame('Site Supervisor', $employee->position);
        $this->assertSame('09.123.456.7-111.000', $employee->taxProfile->npwp);
        $this->assertCount(2, $employee->familyMembers);
        $this->assertSame('Pasangan Demo', $employee->familyMembers->first()->name);
        $this->assertSame('Anak Demo', $employee->familyMembers->last()->name);
        $this->assertSame('BCA', $employee->bankAccounts->first()->bank_name);
        $this->assertSame('BPJSKES-011', $employee->bpjsProfile->bpjs_kesehatan_number);
        $this->assertTrue($employee->document_summary['tax_profile_linked']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_profile.created',
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_seeded_admin_can_manage_employee_status_lifecycle(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-STATUS-013',
            'full_name' => 'Status Lifecycle Demo',
            'employment_status' => 'contract',
            'position' => 'Operator',
            'department' => 'Operasional',
            'grade' => 'G3',
            'cost_center' => 'OPS-SITE',
            'join_date' => '2026-07-01',
            'contract_end_date' => '2026-12-31',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/status-lifecycle')
            ->assertOk()
            ->assertSeeText('Status Kerja')
            ->assertSeeText('Ubah Status');

        $this->actingAs($admin)
            ->post('/employees/status-lifecycle', [
                'employee_profile_id' => $employee->id,
                'to_status' => 'permanent',
                'effective_date' => '2026-08-01',
                'review_date' => '2026-11-01',
                'contract_end_date' => '2026-12-31',
                'reason' => 'Lulus evaluasi kontrak',
                'approved_by' => 'HR Manager',
                'notes' => 'Konversi menjadi karyawan tetap.',
            ])
            ->assertRedirect('/employees/status-lifecycle');

        $employee->refresh();
        $history = EmployeeStatusHistory::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->assertSame('permanent', $employee->employment_status);
        $this->assertTrue($employee->is_active);
        $this->assertSame('contract', $history->from_status);
        $this->assertSame('permanent', $history->to_status);
        $this->assertSame('HR Manager', $history->approved_by);
        $this->assertSame('KRM-STATUS-013', $history->status_snapshot['employee_number']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_status.changed',
            'auditable_id' => $history->id,
        ]);
    }

    public function test_seeded_admin_can_manage_employee_contracts_and_renewal(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-CONTRACT-014',
            'full_name' => 'Contract Lifecycle Demo',
            'employment_status' => 'probation',
            'position' => 'Planner',
            'department' => 'Operasional',
            'grade' => 'G4',
            'cost_center' => 'OPS-SITE',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/contracts')
            ->assertOk()
            ->assertSeeText('Kontrak Kerja')
            ->assertSeeText('Tambah Kontrak');

        $this->actingAs($admin)
            ->post('/employees/contracts', [
                'employee_profile_id' => $employee->id,
                'contract_number' => 'PKWT/BMP/014/2026',
                'contract_type' => 'pkwt',
                'start_date' => '2026-08-01',
                'end_date' => '2027-01-31',
                'signed_at' => '2026-07-25',
                'reminder_date' => '2027-01-01',
                'status' => 'active',
                'document_reference' => 'DOC-HR-014',
                'approved_by' => 'HR Manager',
                'notes' => 'Kontrak PKWT pertama.',
            ])
            ->assertRedirect('/employees/contracts');

        $contract = EmployeeContract::where('contract_number', 'PKWT/BMP/014/2026')->firstOrFail();

        $this->actingAs($admin)
            ->post('/employees/contracts', [
                'employee_profile_id' => $employee->id,
                'parent_contract_id' => $contract->id,
                'contract_number' => 'PKWT/BMP/014-R1/2027',
                'contract_type' => 'pkwt',
                'start_date' => '2027-02-01',
                'end_date' => '2027-07-31',
                'signed_at' => '2027-01-20',
                'reminder_date' => '2027-07-01',
                'status' => 'active',
                'document_reference' => 'DOC-HR-014-R1',
                'approved_by' => 'HR Manager',
                'notes' => 'Perpanjangan PKWT pertama.',
            ])
            ->assertRedirect('/employees/contracts');

        $renewal = EmployeeContract::where('contract_number', 'PKWT/BMP/014-R1/2027')->firstOrFail();
        $employee->refresh();

        $this->assertSame('contract', $employee->employment_status);
        $this->assertSame('2027-07-31', $employee->contract_end_date->format('Y-m-d'));
        $this->assertSame(1, $renewal->renewal_sequence);
        $this->assertSame($contract->id, $renewal->parent_contract_id);
        $this->assertSame('KRM-CONTRACT-014', $renewal->contract_snapshot['employee_number']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_contract.created',
            'auditable_id' => $renewal->id,
        ]);
    }

    public function test_seeded_admin_can_manage_employee_document_archive_with_versions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-DOC-015',
            'full_name' => 'Document Archive Demo',
            'employment_status' => 'contract',
            'position' => 'Document Controller',
            'department' => 'HR',
            'grade' => 'G4',
            'cost_center' => 'HR-HO',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/documents')
            ->assertOk()
            ->assertSeeText('Dokumen Karyawan')
            ->assertSeeText('Tambah Dokumen');

        $this->actingAs($admin)
            ->post('/employees/documents', [
                'employee_profile_id' => $employee->id,
                'document_number' => 'DOC/BMP/015/001',
                'document_type' => 'contract',
                'title' => 'Kontrak Kerja PKWT',
                'status' => 'active',
                'issued_at' => '2026-07-01',
                'expired_at' => '2027-01-31',
                'reminder_date' => '2027-01-01',
                'file_reference' => 'HR-FILE-015',
                'storage_path' => 'employees/KRM-DOC-015/contracts/pkwt.pdf',
                'ocr_status' => 'processed',
                'ocr_text' => 'Perjanjian kerja waktu tertentu untuk Document Archive Demo.',
                'metadata_tags' => 'kontrak,pkwt,hr',
                'notes' => 'Dokumen kontrak awal.',
            ])
            ->assertRedirect('/employees/documents');

        $document = EmployeeDocument::where('document_number', 'DOC/BMP/015/001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/employees/documents', [
                'employee_profile_id' => $employee->id,
                'parent_document_id' => $document->id,
                'document_number' => 'DOC/BMP/015/001-V2',
                'document_type' => 'contract',
                'title' => 'Kontrak Kerja PKWT Revisi',
                'status' => 'active',
                'issued_at' => '2026-07-15',
                'expired_at' => '2027-01-31',
                'reminder_date' => '2027-01-01',
                'file_reference' => 'HR-FILE-015-V2',
                'storage_path' => 'employees/KRM-DOC-015/contracts/pkwt-v2.pdf',
                'ocr_status' => 'manual',
                'ocr_text' => 'Revisi metadata kontrak karyawan.',
                'metadata_tags' => 'kontrak,revisi',
                'notes' => 'Versi kedua.',
            ])
            ->assertRedirect('/employees/documents');

        $version = EmployeeDocument::where('document_number', 'DOC/BMP/015/001-V2')->firstOrFail();

        $this->assertSame(1, $document->version);
        $this->assertSame(2, $version->version);
        $this->assertSame($document->id, $version->parent_document_id);
        $this->assertSame('KRM-DOC-015', $version->metadata['employee_number']);
        $this->assertContains('revisi', $version->metadata['tags']);
        $this->assertStringContainsString('Revisi metadata', $version->metadata['ocr_excerpt']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_document.created',
            'auditable_id' => $version->id,
        ]);
    }

    public function test_seeded_admin_can_assign_and_return_employee_assets(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ASSET-016',
            'full_name' => 'Asset Assignment Demo',
            'employment_status' => 'permanent',
            'position' => 'Engineer',
            'department' => 'IT',
            'grade' => 'G5',
            'cost_center' => 'IT-HO',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/assets')
            ->assertOk()
            ->assertSeeText('Aset Karyawan')
            ->assertSeeText('Tugaskan Aset');

        $this->actingAs($admin)
            ->post('/employees/assets', [
                'employee_profile_id' => $employee->id,
                'asset_code' => 'AST-LAP-016',
                'asset_type' => 'laptop',
                'asset_name' => 'Lenovo ThinkPad T14',
                'serial_number' => 'SN-ASSET-016',
                'asset_value' => 18000000,
                'assigned_at' => '2026-07-20',
                'due_return_at' => '2027-07-20',
                'condition_out' => 'good',
                'requires_clearance' => 1,
                'handover_by' => 'IT Asset',
                'notes' => 'Laptop kerja engineer.',
            ])
            ->assertRedirect('/employees/assets');

        $asset = EmployeeAsset::where('asset_code', 'AST-LAP-016')->firstOrFail();

        $this->assertSame('assigned', $asset->assignment_status);
        $this->assertFalse($asset->clearance_completed);
        $this->assertSame('KRM-ASSET-016', $asset->asset_snapshot['employee_number']);

        $this->actingAs($admin)
            ->post("/employees/assets/{$asset->id}/return", [
                'returned_at' => '2027-07-10',
                'condition_in' => 'good',
                'received_by' => 'IT Asset',
                'clearance_completed' => 1,
                'notes' => 'Dikembalikan lengkap.',
            ])
            ->assertRedirect('/employees/assets');

        $asset->refresh();

        $this->assertSame('returned', $asset->assignment_status);
        $this->assertTrue($asset->clearance_completed);
        $this->assertSame('good', $asset->condition_in);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_asset.returned',
            'auditable_id' => $asset->id,
        ]);
    }

    public function test_seeded_admin_can_create_employee_movement_workflow(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $fromBranch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $toBranch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $fromBranch->id,
            'employee_number' => 'KRM-MOVE-017',
            'full_name' => 'Movement Workflow Demo',
            'employment_status' => 'permanent',
            'position' => 'Engineer',
            'department' => 'IT',
            'grade' => 'G5',
            'cost_center' => 'IT-HO',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/movements')
            ->assertOk()
            ->assertSeeText('Mutasi Karyawan')
            ->assertSeeText('Ajukan Movement');

        $this->actingAs($admin)
            ->post('/employees/movements', [
                'employee_profile_id' => $employee->id,
                'movement_number' => 'MOV/BMP/017/001',
                'movement_type' => 'promotion',
                'request_date' => '2026-07-20',
                'effective_date' => '2026-08-01',
                'status' => 'approved',
                'to_company_id' => $company->id,
                'to_branch_id' => $toBranch->id,
                'to_position' => 'Senior Engineer',
                'to_department' => 'Operational Excellence',
                'to_grade' => 'G6',
                'to_cost_center' => 'OPS-EXCELLENCE',
                'reason' => 'Promosi berdasarkan evaluasi performa.',
                'approved_by' => 'HR Manager',
                'approval_notes' => 'Disetujui untuk periode Agustus.',
            ])
            ->assertRedirect('/employees/movements');

        $movement = EmployeeMovement::where('movement_number', 'MOV/BMP/017/001')->firstOrFail();
        $employee->refresh();

        $this->assertSame('promotion', $movement->movement_type);
        $this->assertSame('Engineer', $movement->before_snapshot['position']);
        $this->assertSame('Senior Engineer', $movement->after_snapshot['position']);
        $this->assertSame('Senior Engineer', $employee->position);
        $this->assertSame('Operational Excellence', $employee->department);
        $this->assertSame('G6', $employee->grade);
        $this->assertSame('OPS-EXCELLENCE', $employee->cost_center);
        $this->assertSame($toBranch->id, $employee->branch_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_movement.created',
            'auditable_id' => $movement->id,
        ]);
    }

    public function test_seeded_admin_can_create_employee_disciplinary_record(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-DISC-018',
            'full_name' => 'Disciplinary Record Demo',
            'employment_status' => 'permanent',
            'position' => 'Operator',
            'department' => 'Operasional',
            'grade' => 'G3',
            'cost_center' => 'OPS-SITE',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/disciplinary-records')
            ->assertOk()
            ->assertSeeText('Disiplin & Coaching')
            ->assertSeeText('Tambah Case');

        $this->actingAs($admin)
            ->post('/employees/disciplinary-records', [
                'employee_profile_id' => $employee->id,
                'case_number' => 'DISC/BMP/018/001',
                'case_type' => 'sp1',
                'risk_level' => 'medium',
                'incident_date' => '2026-07-18',
                'issued_date' => '2026-07-19',
                'valid_until' => '2027-01-19',
                'follow_up_date' => '2026-08-19',
                'status' => 'monitoring',
                'reported_by' => 'Site Supervisor',
                'handled_by' => 'HR Site',
                'approved_by' => 'HR Manager',
                'attachment_reference' => 'DOC-SP1-018',
                'summary' => 'Pelanggaran disiplin operasional pertama.',
                'corrective_action' => 'Coaching mingguan dan evaluasi 30 hari.',
                'employee_response' => 'Karyawan menerima coaching.',
                'follow_up_result' => 'Menunggu monitoring.',
            ])
            ->assertRedirect('/employees/disciplinary-records');

        $record = EmployeeDisciplinaryRecord::where('case_number', 'DISC/BMP/018/001')->firstOrFail();

        $this->assertSame('sp1', $record->case_type);
        $this->assertSame('monitoring', $record->status);
        $this->assertSame('KRM-DISC-018', $record->case_snapshot['employee_number']);
        $this->assertSame('Operator', $record->case_snapshot['position']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_disciplinary_record.created',
            'auditable_id' => $record->id,
        ]);
    }

    public function test_seeded_admin_can_create_and_assign_onboarding_tasks(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ONB-019',
            'full_name' => 'Onboarding Task Demo',
            'employment_status' => 'probation',
            'position' => 'Mine Engineer',
            'department' => 'Operasional',
            'grade' => 'G4',
            'cost_center' => 'OPS-SITE',
            'join_date' => '2026-08-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/onboarding')
            ->assertOk()
            ->assertSeeText('Onboarding Karyawan')
            ->assertSeeText('Buat Paket Onboarding');

        $this->actingAs($admin)
            ->post('/employees/onboarding/templates', [
                'code' => 'ONB-SITE-019',
                'name' => 'Onboarding Site Engineer',
                'role_scope' => 'Engineer',
                'department_scope' => 'Operasional',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'description' => 'Paket onboarding engineer site.',
                'task_names' => [
                    'Lengkapi dokumen personal',
                    'Setup akun dan perangkat kerja',
                    'Induksi HSE dan orientasi departemen',
                ],
            ])
            ->assertRedirect('/employees/onboarding');

        $template = EmployeeOnboardingTemplate::where('code', 'ONB-SITE-019')->firstOrFail();
        $this->assertCount(3, $template->task_blueprint);

        $this->actingAs($admin)
            ->post('/employees/onboarding/assign', [
                'employee_profile_id' => $employee->id,
                'employee_onboarding_template_id' => $template->id,
                'base_due_date' => '2026-08-07',
                'owner_role' => 'HR Site',
                'owner_name' => 'Ayu Prameswari',
            ])
            ->assertRedirect('/employees/onboarding');

        $tasks = EmployeeOnboardingTask::where('employee_profile_id', $employee->id)->orderBy('sort_order')->get();
        $this->assertCount(3, $tasks);
        $this->assertSame('Lengkapi dokumen personal', $tasks->first()->task_name);
        $this->assertSame('KRM-ONB-019', $tasks->first()->assignment_snapshot['employee_number']);

        $this->actingAs($admin)
            ->post("/employees/onboarding/tasks/{$tasks->first()->id}", [
                'status' => 'completed',
                'completed_at' => '2026-08-03',
                'completion_note' => 'Dokumen sudah lengkap.',
            ])
            ->assertRedirect('/employees/onboarding');

        $this->assertSame('completed', $tasks->first()->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'employee_onboarding_template.created']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'employee_onboarding_task.assigned']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'employee_onboarding_task.updated']);
    }

    public function test_seeded_admin_can_create_offboarding_case_and_update_clearance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-OFF-020',
            'full_name' => 'Offboarding Case Demo',
            'employment_status' => 'permanent',
            'position' => 'Finance Staff',
            'department' => 'Finance',
            'grade' => 'G4',
            'cost_center' => 'FIN-HO',
            'join_date' => '2026-01-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/employees/offboarding')
            ->assertOk()
            ->assertSeeText('Offboarding')
            ->assertSeeText('Buat Case Offboarding');

        $this->actingAs($admin)
            ->post('/employees/offboarding', [
                'employee_profile_id' => $employee->id,
                'case_number' => 'OFF/BMP/020/001',
                'reason_type' => 'resign',
                'notice_date' => '2026-08-01',
                'last_working_date' => '2026-08-31',
                'exit_interview_date' => '2026-08-25',
                'status' => 'clearance',
                'handled_by' => 'HR Admin',
                'approved_by' => 'HR Manager',
                'final_settlement_amount' => 12500000,
                'final_settlement_status' => 'calculated',
                'reason_note' => 'Karyawan mengundurkan diri.',
                'exit_interview_summary' => 'Feedback sudah dicatat.',
                'handover_note' => 'Handover ke tim finance.',
                'clearance_items' => [
                    'Pengembalian aset kerja',
                    'Penutupan akses sistem dan akun',
                    'Final settlement payroll',
                ],
            ])
            ->assertRedirect('/employees/offboarding');

        $case = EmployeeOffboardingCase::where('case_number', 'OFF/BMP/020/001')->firstOrFail();
        $items = EmployeeClearanceItem::where('employee_offboarding_case_id', $case->id)->get();

        $this->assertSame('clearance', $case->status);
        $this->assertSame('KRM-OFF-020', $case->employee_snapshot['employee_number']);
        $this->assertCount(3, $items);

        $this->actingAs($admin)
            ->post("/employees/offboarding/clearance/{$items->first()->id}", [
                'status' => 'completed',
                'completed_at' => '2026-08-20',
                'notes' => 'Aset sudah dikembalikan.',
            ])
            ->assertRedirect('/employees/offboarding');

        $this->assertSame('completed', $items->first()->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'employee_offboarding_case.created']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'employee_clearance_item.updated']);
    }

    public function test_seeded_admin_can_manage_module_switcher(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $module = ModuleSetting::create([
            'code' => 'hris.test.module',
            'name' => 'Test Module',
            'group' => 'Testing',
            'description' => 'Module untuk test toggle.',
            'platform' => 'Web Admin',
            'is_enabled' => false,
            'is_core' => false,
            'progress' => 0,
            'sort_order' => 999,
        ]);

        $this->actingAs($user)
            ->get('/settings/modules?company_id=' . $company->id)
            ->assertOk()
            ->assertSee('Module Switcher')
            ->assertSee('Test Module')
            ->assertSeeText('BMP');

        $this->actingAs($user)
            ->post("/settings/modules/{$module->id}/toggle", [
                'company_id' => $company->id,
                'is_enabled' => 1,
            ])
            ->assertRedirect('/settings/modules?company_id=' . $company->id);

        $this->assertFalse($module->fresh()->is_enabled);
        $this->assertTrue(CompanyModuleSetting::where('company_id', $company->id)
            ->where('module_setting_id', $module->id)
            ->firstOrFail()
            ->is_enabled);
    }

    public function test_seeded_admin_can_manage_roles_and_user_assignments(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employee = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $managerRole = Role::where('name', 'Manager')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/roles')
            ->assertOk()
            ->assertSeeText('Role & Permission')
            ->assertSeeText('Payroll Admin')
            ->assertSeeText('Payroll & Pajak')
            ->assertSeeText('Sensitive Access')
            ->assertSeeText('Simpan Izin');

        $this->actingAs($admin)
            ->get('/settings/users')
            ->assertOk()
            ->assertSeeText('User & Role')
            ->assertSee('dimas@kurmigo.id')
            ->assertSeeText('BMP')
            ->assertSeeText('Edit Akses');

        $this->actingAs($admin)
            ->post("/settings/users/{$employee->id}/role", [
                'role_id' => $managerRole->id,
                'company_id' => $company->id,
                'legal_entity_id' => $branch->legal_entity_id,
                'branch_id' => $branch->id,
            ])
            ->assertRedirect('/settings/users');

        $this->assertSame('Manager', $employee->fresh()->role->name);
        $this->assertSame('BMP-HO', $employee->fresh()->branch->code);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.access_updated',
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_seeded_admin_can_create_conditional_approval_workflow(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/approval-workflows')
            ->assertOk()
            ->assertSeeText('Workflow Approval')
            ->assertSeeText('Buat Workflow');

        $this->actingAs($admin)
            ->post('/settings/approval-workflows', [
                'company_id' => $company->id,
                'name' => 'Approval Cuti Lebih Dari 3 Hari',
                'module_key' => 'leave',
                'trigger_event' => 'submitted',
                'status' => 'published',
                'is_enabled' => 1,
                'condition_field' => 'days',
                'condition_operator' => 'greater_than',
                'condition_value' => '3',
                'fallback_approver' => 'HR Admin',
                'description' => 'Routing cuti panjang ke manager dan HR.',
                'steps' => [
                    [
                        'step_name' => 'Review Manager',
                        'approver_type' => 'manager',
                        'approver_value' => 'direct_manager',
                        'routing_condition' => 'days > 3',
                        'sla_hours' => 24,
                        'can_delegate' => 1,
                    ],
                    [
                        'step_name' => 'Review HR',
                        'approver_type' => 'hr_admin',
                        'approver_value' => 'HR Admin',
                        'routing_condition' => 'manager_approved',
                        'sla_hours' => 48,
                        'is_parallel' => 0,
                        'can_delegate' => 1,
                    ],
                ],
            ])
            ->assertRedirect('/settings/approval-workflows');

        $workflow = ApprovalWorkflow::with('steps')->where('module_key', 'leave')->firstOrFail();

        $this->assertSame('published', $workflow->status);
        $this->assertTrue($workflow->is_enabled);
        $this->assertSame('days', $workflow->condition_schema['field']);
        $this->assertSame('greater_than', $workflow->condition_schema['operator']);
        $this->assertSame('HR Admin', $workflow->fallback_route['approver']);
        $this->assertCount(2, $workflow->steps);
        $this->assertSame('Review Manager', $workflow->steps[0]->step_name);
        $this->assertSame(48, $workflow->steps[1]->sla_hours);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'approval_workflow.created',
            'auditable_id' => $workflow->id,
        ]);
    }

    public function test_seeded_admin_can_create_notification_template_and_queue_delivery(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employee = User::where('email', 'dimas@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/notification-center')
            ->assertOk()
            ->assertSeeText('Pusat Notifikasi')
            ->assertSeeText('Buat Template');

        $this->actingAs($admin)
            ->post('/settings/notification-center/templates', [
                'name' => 'Approval Cuti Submitted',
                'event_key' => 'approval.leave.submitted',
                'channels' => ['in_app', 'email', 'whatsapp', 'push'],
                'subject' => 'Approval cuti {{name}}',
                'body_template' => 'Halo {{name}}, pengajuan cuti menunggu approval sampai {{deadline}}.',
                'variables' => 'name,deadline',
                'is_enabled' => 1,
            ])
            ->assertRedirect('/settings/notification-center');

        $template = NotificationTemplate::where('event_key', 'approval.leave.submitted')->firstOrFail();

        $this->assertTrue($template->is_enabled);
        $this->assertContains('whatsapp', $template->channels);
        $this->assertContains('deadline', $template->variables_schema);

        $this->actingAs($admin)
            ->post('/settings/notification-center/deliveries', [
                'notification_template_id' => $template->id,
                'user_id' => $employee->id,
                'recipient_phone' => '6281200000001',
                'priority' => 'high',
                'payload_key' => 'deadline',
                'payload_value' => '20 Jul 2026',
            ])
            ->assertRedirect('/settings/notification-center');

        $delivery = NotificationDelivery::where('notification_template_id', $template->id)->firstOrFail();

        $this->assertSame('queued', $delivery->status);
        $this->assertSame('high', $delivery->priority);
        $this->assertSame($employee->email, $delivery->recipient_email);
        $this->assertStringContainsString('Dimas Wirawan', $delivery->rendered_message);
        $this->assertStringContainsString('20 Jul 2026', $delivery->rendered_message);
        $this->assertSame('waiting_worker', $delivery->delivery_log['provider_status']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'notification_template.created',
            'auditable_id' => $template->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'notification_delivery.queued',
            'auditable_id' => $delivery->id,
        ]);
    }

    public function test_seeded_admin_can_view_audit_trail_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        AuditLog::create([
            'actor_id' => $admin->id,
            'event' => 'test.event',
            'summary' => 'Audit test event.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get('/settings/audit-trail')
            ->assertOk()
            ->assertSeeText('Audit Trail')
            ->assertSeeText('test.event')
            ->assertSeeText('Audit test event.');
    }

    public function test_seeded_admin_can_manage_tax_profiles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/payroll/tax-profiles')
            ->assertOk()
            ->assertSeeText('Master PPh 21');

        $this->actingAs($admin)
            ->post('/payroll/tax-profiles', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employee_number' => 'EMP-001',
                'employee_name' => 'Hasan Tax Demo',
                'nik' => '3175091201010001',
                'npwp' => '09.123.456.7-890.000',
                'ptkp_status' => 'K/1',
                'tax_method' => 'gross',
                'klu' => '62090',
                'kpp_code' => 'KPP-001',
                'kpp_name' => 'KPP Pratama Demo',
                'is_npwp_verified' => 1,
            ])
            ->assertRedirect('/payroll/tax-profiles');

        $profile = TaxProfile::where('nik', '3175091201010001')->firstOrFail();

        $this->assertSame('Hasan Tax Demo', $profile->employee_name);
        $this->assertTrue($profile->is_npwp_verified);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_profile.created',
            'auditable_id' => $profile->id,
        ]);
    }

    public function test_seeded_admin_can_manage_payroll_bpjs_profiles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-BPJS-033',
            'employee_name' => 'BPJS Payroll Demo',
            'nik' => '3175091201010333',
            'npwp' => '09.123.456.7-333.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-BPJS-033',
            'full_name' => 'BPJS Payroll Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Analyst',
            'department' => 'Finance',
            'grade' => 'G5',
            'cost_center' => 'FIN-HO',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/bpjs-profiles')
            ->assertOk()
            ->assertSeeText('BPJS Payroll')
            ->assertSeeText('Tambah Profil BPJS');

        $this->actingAs($admin)
            ->post('/payroll/bpjs-profiles', [
                'employee_profile_id' => $employee->id,
                'tax_profile_id' => $taxProfile->id,
                'bpjs_kesehatan_number' => 'KES-033',
                'bpjs_ketenagakerjaan_number' => 'TK-033',
                'bpjs_class' => 'Kelas 1',
                'effective_date' => '2026-07-15',
                'health_contribution_base' => 12000000,
                'employment_contribution_base' => 10000000,
                'health_employee_rate' => 1,
                'health_employer_rate' => 4,
                'jht_employee_rate' => 2,
                'jht_employer_rate' => 3.7,
                'jp_employee_rate' => 1,
                'jp_employer_rate' => 2,
                'verification_status' => 'verified',
                'is_active' => 1,
                'notes' => 'Profil BPJS payroll verified.',
            ])
            ->assertRedirect('/payroll/bpjs-profiles');

        $profile = PayrollBpjsProfile::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->assertSame('KES-033', $profile->bpjs_kesehatan_number);
        $this->assertSame('verified', $profile->verification_status);
        $this->assertEquals(120000, $profile->calculation_snapshot['health_employee_amount']);
        $this->assertEquals(480000, $profile->calculation_snapshot['health_employer_amount']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_bpjs_profile.created',
            'auditable_id' => $profile->id,
        ]);
    }

    public function test_seeded_admin_can_export_tax_and_bpjs_validation_templates(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-EXP-090',
            'employee_name' => 'Export Pajak BPJS Demo',
            'nik' => '3175091201010900',
            'npwp' => '09.123.456.7-900.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'klu' => '62019',
            'kpp_code' => '071',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-EXP-090',
            'full_name' => 'Export Pajak BPJS Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Specialist',
            'department' => 'Finance',
            'grade' => 'G5',
            'cost_center' => 'FIN-HO',
            'join_date' => '2026-07-01',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        PayrollBpjsProfile::create([
            'employee_profile_id' => $employee->id,
            'tax_profile_id' => $taxProfile->id,
            'bpjs_kesehatan_number' => 'KES-EXP-090',
            'bpjs_ketenagakerjaan_number' => 'TK-EXP-090',
            'bpjs_class' => 'Kelas 1',
            'effective_date' => '2026-07-15',
            'health_contribution_base' => 12000000,
            'employment_contribution_base' => 10000000,
            'health_employee_rate' => 1,
            'health_employer_rate' => 4,
            'jht_employee_rate' => 2,
            'jht_employer_rate' => 3.7,
            'jp_employee_rate' => 1,
            'jp_employer_rate' => 2,
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/integrations/export-templates')
            ->assertOk()
            ->assertSeeText('Export Pajak & BPJS')
            ->assertSeeText('Template Pajak')
            ->assertSeeText('Template BPJS')
            ->assertSeeText('Export Pajak BPJS Demo');

        $this->actingAs($admin)
            ->get('/integrations/export-templates/tax/download')
            ->assertOk()
            ->assertSee('employee_number')
            ->assertSee('KRM-EXP-090');

        $this->actingAs($admin)
            ->get('/integrations/export-templates/bpjs/download')
            ->assertOk()
            ->assertSee('bpjs_kesehatan')
            ->assertSee('KES-EXP-090');
    }

    public function test_employee_can_view_and_download_ess_document_center(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-045',
            'employee_name' => 'Raka Mahendra',
            'nik' => '3175091201010455',
            'npwp' => '09.123.456.7-455.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-ESS-045',
            'full_name' => 'Raka Mahendra',
            'employment_status' => 'permanent',
            'position' => 'Super Admin',
            'department' => 'HR',
            'grade' => 'G7',
            'cost_center' => 'HR-HO',
            'join_date' => '2026-07-01',
            'work_email' => $user->email,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        EmployeeBpjsProfile::create([
            'employee_profile_id' => $employee->id,
            'bpjs_kesehatan_number' => 'KES-ESS-045',
            'bpjs_ketenagakerjaan_number' => 'TK-ESS-045',
            'bpjs_class' => 'Kelas 1',
            'registered_at' => '2026-07-01',
            'coverage_snapshot' => ['source' => 'ESS test'],
        ]);

        $contract = EmployeeContract::create([
            'employee_profile_id' => $employee->id,
            'contract_number' => 'CTR-ESS-045',
            'contract_type' => 'PKWTT',
            'start_date' => '2026-07-01',
            'signed_at' => '2026-07-01',
            'status' => 'active',
            'document_reference' => 'Kontrak PKWTT digital.',
            'contract_snapshot' => [],
            'created_by' => $user->id,
        ]);

        EmployeeDocument::create([
            'employee_profile_id' => $employee->id,
            'document_number' => 'SURAT-ESS-045',
            'document_type' => 'surat_keterangan',
            'title' => 'Surat Keterangan Kerja',
            'version' => 1,
            'status' => 'published',
            'issued_at' => '2026-07-10',
            'file_reference' => 'SKK digital.',
            'metadata' => [],
            'created_by' => $user->id,
        ]);

        TaxWithholdingDocument::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'document_number' => 'BUPOT-ESS-045',
            'document_type' => 'monthly_pph21',
            'period' => '2026-07',
            'status' => 'published',
            'gross_income' => 12000000,
            'tax_base' => 12000000,
            'pph21_amount' => 120000,
            'employee_snapshot' => ['employee_number' => $taxProfile->employee_number],
            'document_payload' => [],
            'checksum' => hash('sha256', 'BUPOT-ESS-045'),
            'published_at' => now(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/ess/documents')
            ->assertOk()
            ->assertSeeText('Dokumen')
            ->assertSeeText('Slip Gaji')
            ->assertSeeText('Bukti Potong PPh 21')
            ->assertSeeText('Kartu BPJS')
            ->assertSeeText('Kontrak Kerja')
            ->assertSeeText('Surat Keterangan Kerja');

        $this->actingAs($user)
            ->get("/ess/documents/contract/{$contract->id}/download")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_employee_can_view_mobile_ess_home_with_schedule_pending_actions_and_payslip_shortcut(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESSH-041',
            'full_name' => 'Raka Mahendra',
            'employment_status' => 'permanent',
            'position' => 'Supervisor HR',
            'department' => 'HR',
            'work_email' => $user->email,
            'address_snapshot' => [],
            'document_summary' => [],
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $pattern = AttendanceShiftPattern::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'SHIFT-ESS-041',
            'name' => 'Reguler HO',
            'shift_type' => 'regular',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'planned_hours' => 8,
            'grace_late_minutes' => 10,
            'grace_early_leave_minutes' => 10,
            'is_cross_day' => false,
            'is_split_shift' => false,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        AttendanceShiftAssignment::create([
            'attendance_shift_pattern_id' => $pattern->id,
            'employee_profile_id' => $employee->id,
            'assignment_scope' => 'employee',
            'rotation_cycle' => 'fixed',
            'effective_start' => now()->subDay()->toDateString(),
            'effective_end' => now()->addDay()->toDateString(),
            'work_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            'off_days' => [],
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'AL-ESS-041',
            'name' => 'Annual Leave ESS',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'max_days_per_request' => 5,
            'requires_balance' => true,
            'is_paid' => true,
            'requires_attachment' => false,
            'approval_required' => true,
            'carry_forward_allowed' => false,
            'negative_balance_allowed' => false,
            'prorate_on_join' => true,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'ESS-LEAVE-041',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'requested_days' => 1,
            'balance_before' => 12,
            'balance_after' => 11,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'simulation_snapshot' => [],
            'reason' => 'Cuti keluarga.',
            'created_by' => $user->id,
        ]);

        BusinessTripRequest::create([
            'request_number' => 'ESS-DINAS-041',
            'employee_profile_id' => $employee->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'destination_city' => 'Morowali',
            'trip_type' => 'domestic',
            'estimated_budget' => 1500000,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'trip_snapshot' => [],
            'purpose' => 'Site visit.',
            'created_by' => $user->id,
        ]);

        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-ESS-041',
            'period' => '2026-07',
            'status' => 'published',
            'gross_pay' => 10000000,
            'total_deduction' => 250000,
            'net_pay' => 9750000,
            'earning_snapshot' => ['Gaji Pokok' => 10000000],
            'deduction_snapshot' => ['BPJS' => 250000],
            'employee_snapshot' => ['employee_number' => $employee->employee_number, 'full_name' => $employee->full_name],
            'checksum' => hash('sha256', 'SLIP-ESS-041'),
            'published_at' => now(),
            'last_sent_at' => now(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/ess')
            ->assertOk()
            ->assertSeeText('Beranda ESS')
            ->assertSeeText('Jadwal Hari Ini')
            ->assertSeeText('Reguler HO')
            ->assertSeeText('Pengajuan Pending')
            ->assertSeeText('Cuti menunggu approval')
            ->assertSeeText('Dinas menunggu approval')
            ->assertSeeText('Slip belum dikonfirmasi')
            ->assertSeeText('Pusat Pengajuan')
            ->assertSeeText('Slip Gaji');
    }

    public function test_employee_portal_only_shows_employee_self_service_menu(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $employee = User::where('email', 'dimas@kurmigo.id')->firstOrFail();

        $this->actingAs($employee)
            ->get('http://localhost:8092/')
            ->assertRedirect('/ess');

        $this->actingAs($employee)
            ->get('http://localhost:8092/ess')
            ->assertOk()
            ->assertSeeText('ESS')
            ->assertSeeText('Layanan Karyawan')
            ->assertSeeText('Beranda ESS')
            ->assertSeeText('Pusat Pengajuan')
            ->assertSeeText('Slip Gaji')
            ->assertDontSeeText('Administration Panel')
            ->assertDontSeeText('HRIS Dashboard')
            ->assertDontSeeText('Core HR')
            ->assertDontSeeText('Data Karyawan')
            ->assertDontSeeText('Payroll & Tax')
            ->assertDontSeeText('System Administration');
    }

    public function test_seeded_admin_can_view_mobile_bridge_contract_and_sdk(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/mobile-bridge')
            ->assertOk()
            ->assertSeeText('Mobile WebView Bridge')
            ->assertSeeText('Capability Contract')
            ->assertSeeText('location.current')
            ->assertSee('kurmigo-mobile-bridge.js');

        $this->assertFileExists(public_path('js/kurmigo-mobile-bridge.js'));
        $this->assertStringContainsString('window.KurmigoBridge', file_get_contents(public_path('js/kurmigo-mobile-bridge.js')));
    }

    public function test_android_webview_shell_contract_files_are_available(): void
    {
        $manifest = base_path('mobile/android/app/src/main/AndroidManifest.xml');
        $activity = base_path('mobile/android/app/src/main/java/id/kurmigo/hris/MainActivity.kt');
        $contract = base_path('mobile/android/mobile-shell-contract.json');
        $buildFile = base_path('mobile/android/app/build.gradle');

        $this->assertFileExists($manifest);
        $this->assertFileExists($activity);
        $this->assertFileExists($contract);
        $this->assertFileExists($buildFile);
        $this->assertStringContainsString('android.permission.INTERNET', file_get_contents($manifest));
        $this->assertStringContainsString('WebView', file_get_contents($activity));
        $this->assertStringContainsString('KurmigoNative.postMessage', file_get_contents($contract));
        $this->assertStringContainsString('https://31.97.106.123:8092/ess/request-center-entry', file_get_contents($buildFile));
    }

    public function test_ios_wkwebview_shell_contract_files_are_available(): void
    {
        $config = base_path('mobile/ios/KurmigoHRIS/AppConfig.swift');
        $controller = base_path('mobile/ios/KurmigoHRIS/WebViewController.swift');
        $plist = base_path('mobile/ios/KurmigoHRIS/Info.plist');
        $contract = base_path('mobile/ios/mobile-shell-contract.json');

        $this->assertFileExists($config);
        $this->assertFileExists($controller);
        $this->assertFileExists($plist);
        $this->assertFileExists($contract);
        $this->assertStringContainsString('https://31.97.106.123:8092/ess/request-center-entry', file_get_contents($config));
        $this->assertStringContainsString('WKWebView', file_get_contents($controller));
        $this->assertStringContainsString('kurmigoNative', file_get_contents($contract));
        $this->assertStringContainsString('NSCameraUsageDescription', file_get_contents($plist));
    }

    public function test_seeded_admin_can_view_mobile_webview_qa_matrix(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/mobile-qa-matrix')
            ->assertOk()
            ->assertSeeText('QA Mobile WebView')
            ->assertSeeText('Matriks Skenario')
            ->assertSeeText('Pusat Pengajuan')
            ->assertSeeText('Android Ready')
            ->assertSeeText('iOS Ready');

        $this->assertFileExists(base_path('mobile/qa/mobile-webview-qa-matrix.json'));
        $this->assertStringContainsString('Offline Queue', file_get_contents(base_path('mobile/qa/mobile-webview-qa-matrix.json')));
    }

    public function test_seeded_admin_can_register_ess_trusted_device_with_biometric_and_pin(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-042',
            'full_name' => 'Dimas ESS Security',
            'nik' => '3174014200010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/settings/ess-security')
            ->assertOk()
            ->assertSeeText('ESS Security')
            ->assertSeeText('Daftarkan Device');

        $this->actingAs($admin)
            ->post('/settings/ess-security', [
                'user_id' => $employeeUser->id,
                'employee_profile_id' => $employee->id,
                'device_uid' => 'android-ess-042',
                'device_name' => 'Android Dimas',
                'platform' => 'android',
                'app_version' => '1.0.0',
                'biometric_enabled' => 1,
                'biometric_type' => 'fingerprint',
                'fallback_pin' => '123456',
                'trust_status' => 'trusted',
                'failed_attempts' => 0,
                'notes' => 'Device diverifikasi oleh HR.',
            ])
            ->assertRedirect('/settings/ess-security');

        $device = EssTrustedDevice::where('device_uid', 'android-ess-042')->firstOrFail();

        $this->assertSame($employeeUser->id, $device->user_id);
        $this->assertSame($employee->id, $device->employee_profile_id);
        $this->assertSame('trusted', $device->trust_status);
        $this->assertTrue($device->biometric_enabled);
        $this->assertSame('fingerprint', $device->biometric_type);
        $this->assertTrue(Hash::check('123456', $device->fallback_pin_hash));
        $this->assertSame([], $device->risk_flags);
        $this->assertSame('biometric.verify', $device->capability_snapshot['bridge_capability']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ess_trusted_device.saved',
            'auditable_id' => $device->id,
        ]);
    }

    public function test_employee_can_submit_ess_request_center_overtime_trip_and_correction(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-044',
            'full_name' => 'Dimas Request Center',
            'nik' => '3174014400010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/ess/request-center')
            ->assertOk()
            ->assertSeeText('Pusat Pengajuan')
            ->assertSee('ess-webview-page')
            ->assertSee('mobile-card')
            ->assertSee('mobileMenuButton')
            ->assertSee('#form-cuti')
            ->assertSeeText('Ajukan Lembur')
            ->assertSeeText('Ajukan Dinas')
            ->assertSeeText('Ajukan Koreksi Absensi');

        $this->actingAs($user)
            ->post('/ess/request-center/overtime', [
                'request_number' => 'ESS-OT-044',
                'overtime_date' => '2026-07-21',
                'start_at' => '2026-07-21 18:00:00',
                'end_at' => '2026-07-21 21:30:00',
                'overtime_type' => 'regular_day',
                'request_source' => 'mobile_webview',
                'reason' => 'Closing pekerjaan site.',
            ])
            ->assertRedirect('/ess/request-center');

        $this->actingAs($user)
            ->post('/ess/request-center/trips', [
                'request_number' => 'ESS-DINAS-044',
                'start_date' => '2026-07-22',
                'end_date' => '2026-07-24',
                'destination_city' => 'Morowali',
                'destination_site' => 'BMP-SITE-01',
                'trip_type' => 'site_visit',
                'transport_mode' => 'Pesawat',
                'estimated_budget' => 3500000,
                'request_source' => 'mobile_webview',
                'attachment_reference' => 'DOC-DINAS-044',
                'purpose' => 'Koordinasi operasional site.',
            ])
            ->assertRedirect('/ess/request-center');

        $this->actingAs($user)
            ->post('/ess/request-center/corrections', [
                'correction_number' => 'ESS-COR-044',
                'attendance_date' => '2026-07-21',
                'correction_type' => 'missing_clock_out',
                'requested_event_at' => '2026-07-21 17:05:00',
                'requested_event_type' => 'clock_out',
                'request_source' => 'mobile_webview',
                'attachment_reference' => 'DOC-COR-044',
                'reason' => 'Lupa clock-out dari mobile.',
            ])
            ->assertRedirect('/ess/request-center');

        $overtime = OvertimeRequest::where('request_number', 'ESS-OT-044')->firstOrFail();
        $trip = BusinessTripRequest::where('request_number', 'ESS-DINAS-044')->firstOrFail();
        $correction = AttendanceCorrection::where('correction_number', 'ESS-COR-044')->firstOrFail();

        $this->assertSame($employee->id, $overtime->employee_profile_id);
        $this->assertSame('submitted', $overtime->approval_status);
        $this->assertEquals(3.5, (float) $overtime->requested_hours);
        $this->assertSame($employee->id, $trip->employee_profile_id);
        $this->assertSame('submitted', $trip->approval_status);
        $this->assertSame(3, $trip->trip_snapshot['duration_days']);
        $this->assertSame($employee->id, $correction->employee_profile_id);
        $this->assertSame('submitted', $correction->approval_status);
        $this->assertFalse($correction->creates_clock_event);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_overtime_request.submitted', 'auditable_id' => $overtime->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_business_trip.submitted', 'auditable_id' => $trip->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_attendance_correction.submitted', 'auditable_id' => $correction->id]);
    }

    public function test_seeded_admin_can_manage_ess_offline_queue_and_sync_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-OFF-050',
            'full_name' => 'Dimas Offline Queue',
            'nik' => '3174014500010001',
            'gender' => 'male',
            'birth_place' => 'Jakarta',
            'birth_date' => '1992-05-10',
            'work_email' => 'dimas@kurmigo.id',
            'employment_status' => 'permanent',
            'join_date' => now()->subYear()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/ess/offline-queue')
            ->assertOk()
            ->assertSeeText('Offline Queue ESS')
            ->assertSeeText('Tambah Queue Offline');

        $this->actingAs($admin)
            ->post('/ess/offline-queue', [
                'employee_profile_id' => $employee->id,
                'queue_number' => 'OFF-CLK-050',
                'item_type' => 'clock_event',
                'queue_status' => 'queued',
                'captured_at' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
                'device_uid' => 'device-offline-050',
                'device_name' => 'Android Dimas',
                'platform' => 'android_webview',
                'app_version' => '1.0.0',
                'payload_key' => 'event_type',
                'payload_value' => 'clock_in',
                'notes' => 'Device offline saat masuk site.',
            ])
            ->assertRedirect('/ess/offline-queue');

        $queue = EssOfflineQueueItem::where('queue_number', 'OFF-CLK-050')->firstOrFail();

        $this->assertSame('queued', $queue->queue_status);
        $this->assertSame('clock_event', $queue->item_type);
        $this->assertSame('clock_in', $queue->payload['payload_value']);

        $this->actingAs($admin)
            ->post("/ess/offline-queue/{$queue->id}/sync")
            ->assertRedirect('/ess/offline-queue');

        $queue->refresh();
        $this->assertSame('synced', $queue->queue_status);
        $this->assertNotNull($queue->synced_at);
        $this->assertSame(1, $queue->retry_count);

        $failedQueue = EssOfflineQueueItem::create([
            'employee_profile_id' => $employee->id,
            'queue_number' => 'OFF-DRAFT-050',
            'item_type' => 'leave_draft',
            'queue_status' => 'queued',
            'captured_at' => now(),
            'platform' => 'mobile_webview',
            'payload' => ['request' => 'annual_leave'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post("/ess/offline-queue/{$failedQueue->id}/fail", [
                'failure_reason' => 'Payload draft tidak lengkap.',
            ])
            ->assertRedirect('/ess/offline-queue');

        $failedQueue->refresh();
        $this->assertSame('failed', $failedQueue->queue_status);
        $this->assertSame('Payload draft tidak lengkap.', $failedQueue->failure_reason);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_offline_queue.created', 'auditable_id' => $queue->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_offline_queue.synced', 'auditable_id' => $queue->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_offline_queue.failed', 'auditable_id' => $failedQueue->id]);
    }

    public function test_seeded_admin_can_create_recruitment_manpower_request(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/recruitment/manpower-requests')
            ->assertOk()
            ->assertSeeText('Pengajuan Manpower')
            ->assertSeeText('Buat Pengajuan');

        $this->actingAs($admin)
            ->post('/recruitment/manpower-requests', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'request_number' => 'MPR-059',
                'position_title' => 'HR Recruitment Specialist',
                'department' => 'Human Capital',
                'employment_type' => 'permanent',
                'headcount' => 2,
                'target_join_date' => '2026-08-01',
                'priority' => 'high',
                'request_reason' => 'expansion',
                'budget_min' => 8000000,
                'budget_max' => 12000000,
                'job_summary' => 'Mengelola sourcing, interview, dan onboarding kandidat.',
                'requirements' => ['Pengalaman recruitment', 'Interview competency based', 'Administrasi HRIS'],
                'status' => 'approved',
                'requested_by' => 'Raka Mahendra',
                'requested_at' => '2026-07-13',
                'approved_by' => 'Ayu Prameswari',
                'approval_notes' => 'Disetujui untuk ekspansi tim HR.',
            ])
            ->assertRedirect('/recruitment/manpower-requests');

        $request = RecruitmentManpowerRequest::where('request_number', 'MPR-059')->firstOrFail();

        $this->assertSame('HR Recruitment Specialist', $request->position_title);
        $this->assertSame(2, $request->headcount);
        $this->assertSame('approved', $request->status);
        $this->assertSame('BMP', $request->approval_snapshot['company']);
        $this->assertSame('Pengalaman recruitment', $request->requirements[0]['item']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_manpower_request.created',
            'auditable_id' => $request->id,
        ]);
    }

    public function test_seeded_admin_can_manage_recruitment_candidate_pipeline(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'MPR-060',
            'position_title' => 'HR Recruitment Specialist',
            'department' => 'Human Capital',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'target_join_date' => '2026-08-15',
            'priority' => 'high',
            'request_reason' => 'expansion',
            'budget_min' => 8000000,
            'budget_max' => 12000000,
            'status' => 'approved',
            'requested_by' => 'Raka Mahendra',
            'requested_at' => '2026-07-13',
            'approved_by' => 'Ayu Prameswari',
            'approved_at' => now(),
            'approval_snapshot' => ['company' => 'BMP', 'approval_status' => 'approved'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/candidates')
            ->assertOk()
            ->assertSeeText('ATS Pipeline')
            ->assertSeeText('Tambah Kandidat');

        $this->actingAs($admin)
            ->post('/recruitment/candidates', [
                'recruitment_manpower_request_id' => $manpower->id,
                'candidate_number' => 'CAND-060',
                'full_name' => 'Naufal Kandidat',
                'email' => 'naufal@example.com',
                'phone' => '081200000060',
                'source_channel' => 'job_portal',
                'current_position' => 'Recruiter',
                'current_company' => 'PT Sumber Talenta',
                'expected_salary' => 11000000,
                'pipeline_stage' => 'interview',
                'candidate_status' => 'active',
                'fit_score' => 86,
                'owner_recruiter' => 'Ayu Prameswari',
                'applied_at' => '2026-07-14',
                'last_touch_at' => '2026-07-15',
                'tags' => ['HR', 'Recruitment', 'Fast response'],
                'notes' => 'Masuk shortlist interview user.',
            ])
            ->assertRedirect('/recruitment/candidates');

        $candidate = RecruitmentCandidate::where('candidate_number', 'CAND-060')->firstOrFail();

        $this->assertSame('interview', $candidate->pipeline_stage);
        $this->assertSame(86, $candidate->fit_score);
        $this->assertSame('MPR-060', $candidate->manpowerRequest->request_number);
        $this->assertSame('Recruitment', $candidate->tags[1]);
        $this->assertSame('interview', $candidate->stage_history[0]['stage']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_candidate.created',
            'auditable_id' => $candidate->id,
        ]);
    }

    public function test_seeded_admin_can_manage_candidate_portal_documents_and_public_tracking(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'request_number' => 'MPR-061',
            'position_title' => 'People Analyst',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'normal',
            'request_reason' => 'new_position',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-061',
            'full_name' => 'Portal Kandidat Demo',
            'email' => 'portal@example.com',
            'source_channel' => 'direct',
            'pipeline_stage' => 'screening',
            'candidate_status' => 'active',
            'portal_token' => 'portal-token-061',
            'portal_invited_at' => now(),
            'fit_score' => 80,
            'stage_history' => [['stage' => 'screening', 'status' => 'active']],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/candidate-portal')
            ->assertOk()
            ->assertSeeText('Portal Kandidat')
            ->assertSeeText('Catat Dokumen Manual')
            ->assertSeeText('Review Dokumen & Link Portal');

        $this->actingAs($admin)
            ->post('/recruitment/candidate-portal/documents', [
                'recruitment_candidate_id' => $candidate->id,
                'document_type' => 'cv',
                'document_title' => 'CV Portal Kandidat',
                'file_reference' => 'candidate/cv-061.pdf',
                'verification_status' => 'verified',
                'submitted_at' => '2026-07-15',
                'verified_by' => 'Ayu Prameswari',
                'notes' => 'CV sudah sesuai format.',
            ])
            ->assertRedirect('/recruitment/candidate-portal');

        $document = RecruitmentCandidateDocument::where('document_title', 'CV Portal Kandidat')->firstOrFail();

        $this->assertSame('verified', $document->verification_status);
        $this->assertSame('Portal Kandidat Demo', $document->candidate->full_name);

        $this->get('/candidate-portal/portal-token-061')
            ->assertOk()
            ->assertSeeText('Portal Kandidat Demo')
            ->assertSeeText('Progress Rekrutmen')
            ->assertSeeText('Submit Dokumen')
            ->assertSeeText('CV Portal Kandidat')
            ->assertSeeText('Verified');

        $this->post('/candidate-portal/portal-token-061/documents', [
            'document_type' => 'portfolio',
            'document_title' => 'Portfolio Candidate Portal',
            'file_reference' => 'https://example.com/portfolio-061',
            'notes' => 'Disubmit kandidat via portal.',
        ])
            ->assertRedirect('/candidate-portal/portal-token-061');

        $portalDocument = RecruitmentCandidateDocument::where('document_title', 'Portfolio Candidate Portal')->firstOrFail();

        $this->assertSame('submitted', $portalDocument->verification_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_candidate_document.submitted',
            'auditable_id' => $portalDocument->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_candidate_document.created',
            'auditable_id' => $document->id,
        ]);
    }

    public function test_seeded_admin_can_schedule_recruitment_interview_with_scorecard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'request_number' => 'MPR-062',
            'position_title' => 'HRIS Product Analyst',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'high',
            'request_reason' => 'replacement',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-062',
            'full_name' => 'Interview Kandidat Demo',
            'email' => 'interview@example.com',
            'source_channel' => 'referral',
            'pipeline_stage' => 'screening',
            'candidate_status' => 'active',
            'portal_token' => 'portal-token-062',
            'portal_invited_at' => now(),
            'fit_score' => 82,
            'stage_history' => [['stage' => 'screening', 'status' => 'active']],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/interviews')
            ->assertOk()
            ->assertSeeText('Jadwal Interview')
            ->assertSeeText('Scorecard Interview')
            ->assertSeeText('Kalender Interview & Scorecard');

        $this->actingAs($admin)
            ->post('/recruitment/interviews', [
                'recruitment_candidate_id' => $candidate->id,
                'interview_number' => 'INTV-062',
                'interview_stage' => 'user',
                'scheduled_at' => '2026-07-17 10:00:00',
                'duration_minutes' => 60,
                'interview_mode' => 'online',
                'location_or_link' => 'https://meet.example.com/intv-062',
                'interviewer_name' => 'Ayu Prameswari',
                'interviewer_role' => 'HR Manager',
                'status' => 'completed',
                'communication_score' => 80,
                'technical_score' => 85,
                'culture_score' => 90,
                'experience_score' => 75,
                'recommendation' => 'hire',
                'feedback_notes' => 'Kandidat kuat untuk kebutuhan HRIS dan payroll.',
            ])
            ->assertRedirect('/recruitment/interviews');

        $interview = RecruitmentInterview::where('interview_number', 'INTV-062')->firstOrFail();

        $this->assertSame('hire', $interview->recommendation);
        $this->assertSame('82.50', (string) $interview->overall_score);
        $this->assertSame('CAND-062', $interview->scorecard_snapshot['candidate_number']);
        $this->assertSame('interview', $candidate->fresh()->pipeline_stage);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_interview.scheduled',
            'auditable_id' => $interview->id,
        ]);
    }

    public function test_seeded_admin_can_create_offer_letter_and_track_esign_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'request_number' => 'MPR-063',
            'position_title' => 'Payroll Implementation Lead',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'high',
            'request_reason' => 'new_position',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-063',
            'full_name' => 'Offer Kandidat Demo',
            'email' => 'offer@example.com',
            'source_channel' => 'job_portal',
            'pipeline_stage' => 'offer',
            'candidate_status' => 'active',
            'portal_token' => 'portal-token-063',
            'portal_invited_at' => now(),
            'fit_score' => 88,
            'stage_history' => [['stage' => 'offer', 'status' => 'active']],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/offer-letters')
            ->assertOk()
            ->assertSeeText('Offer Letter')
            ->assertSeeText('Tracking Tanda Tangan Elektronik')
            ->assertSeeText('Tracking Offer & E-sign');

        $this->actingAs($admin)
            ->post('/recruitment/offer-letters', [
                'recruitment_candidate_id' => $candidate->id,
                'offer_number' => 'OFFER-063',
                'position_title' => 'Payroll Implementation Lead',
                'join_date' => '2026-08-01',
                'salary_offer' => 18000000,
                'employment_type' => 'permanent',
                'template_name' => 'Standard Offer Letter',
                'status' => 'signed',
                'sent_at' => '2026-07-18',
                'expires_at' => '2026-07-25',
                'signed_at' => '2026-07-20',
                'esign_provider' => 'Privy',
                'esign_reference' => 'PRV-063',
                'signer_email' => 'offer@example.com',
                'offer_summary' => 'Offer sudah disetujui kandidat.',
            ])
            ->assertRedirect('/recruitment/offer-letters');

        $offer = RecruitmentOfferLetter::where('offer_number', 'OFFER-063')->firstOrFail();

        $this->assertSame('signed', $offer->status);
        $this->assertSame('PRV-063', $offer->esign_reference);
        $this->assertSame('hired', $candidate->fresh()->pipeline_stage);
        $this->assertSame('hired', $candidate->fresh()->candidate_status);
        $this->assertSame('signed', $offer->tracking_events[0]['status']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_offer_letter.created',
            'auditable_id' => $offer->id,
        ]);
    }

    public function test_seeded_admin_can_track_candidate_medical_background_and_reference_check(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'request_number' => 'MPR-064',
            'position_title' => 'Talent Risk Analyst',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'normal',
            'request_reason' => 'replacement',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-064',
            'full_name' => 'Check Kandidat Demo',
            'email' => 'check@example.com',
            'source_channel' => 'headhunter',
            'pipeline_stage' => 'offer',
            'candidate_status' => 'active',
            'portal_token' => 'portal-token-064',
            'portal_invited_at' => now(),
            'fit_score' => 86,
            'stage_history' => [['stage' => 'offer', 'status' => 'active']],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/candidate-checks')
            ->assertOk()
            ->assertSeeText('Pengecekan Kandidat')
            ->assertSeeText('Tambah Pengecekan')
            ->assertSeeText('Tracker Pengecekan');

        $this->actingAs($admin)
            ->post('/recruitment/candidate-checks', [
                'recruitment_candidate_id' => $candidate->id,
                'check_number' => 'CHECK-064',
                'check_type' => 'background_check',
                'vendor_name' => 'Vendor Screening Indonesia',
                'reference_name' => 'Budi Santoso',
                'reference_contact' => 'budi@example.com',
                'requested_at' => '2026-07-18',
                'due_at' => '2026-07-21',
                'completed_at' => '2026-07-20',
                'status' => 'completed',
                'result' => 'passed_with_note',
                'is_blocking' => '1',
                'findings' => 'Ada catatan alamat lama yang perlu dikonfirmasi HR.',
                'evidence_links' => ['https://example.com/check-064.pdf'],
            ])
            ->assertRedirect('/recruitment/candidate-checks');

        $check = RecruitmentCandidateCheck::where('check_number', 'CHECK-064')->firstOrFail();

        $this->assertSame('background_check', $check->check_type);
        $this->assertSame('passed_with_note', $check->result);
        $this->assertTrue($check->is_blocking);
        $this->assertSame('https://example.com/check-064.pdf', $check->evidence_links[0]);
        $this->assertNotNull($candidate->fresh()->last_touch_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_candidate_check.created',
            'auditable_id' => $check->id,
        ]);
    }

    public function test_seeded_admin_can_manage_psychotest_provider_and_package_hub(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/recruitment/psychotest-integrations')
            ->assertOk()
            ->assertSeeText('Integrasi Psikotest')
            ->assertSeeText('Tambah Provider')
            ->assertSeeText('Tambah Paket Test');

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-integrations/providers', [
                'company_id' => $company->id,
                'provider_code' => 'PSY-PROV-001',
                'provider_name' => 'Assessment Partner Indonesia',
                'provider_type' => 'external_api',
                'base_url' => 'https://api.assessment.example',
                'auth_method' => 'api_key',
                'credential_reference' => 'secret://psychotest/main',
                'webhook_url' => 'https://hris.example.com/webhook/psychotest',
                'callback_secret_reference' => 'secret://psychotest/callback',
                'sla_result_hours' => '24 jam',
                'owner_name' => 'Ayu Prameswari',
                'connection_status' => 'connected',
                'is_active' => '1',
                'notes' => 'Provider siap untuk invite dan callback hasil psikotest.',
            ])
            ->assertRedirect('/recruitment/psychotest-integrations');

        $provider = PsychotestProvider::where('provider_code', 'PSY-PROV-001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-integrations/packages', [
                'psychotest_provider_id' => $provider->id,
                'package_code' => 'PSY-PKG-001',
                'package_name' => 'Staff Fit Assessment',
                'assessment_category' => 'complete_battery',
                'target_role_level' => 'staff',
                'estimated_minutes' => 90,
                'competency_mapping' => 'integritas, teamwork, problem solving',
                'result_scale' => 'score_0_100',
                'interpretation_method' => 'provider_report',
                'auto_pull_result' => '1',
                'requires_candidate_consent' => '1',
                'status' => 'active',
                'notes' => 'Dipakai untuk kandidat staff dan officer.',
            ])
            ->assertRedirect('/recruitment/psychotest-integrations');

        $package = PsychotestPackage::where('package_code', 'PSY-PKG-001')->firstOrFail();

        $this->assertSame('Assessment Partner Indonesia', $package->provider->provider_name);
        $this->assertSame('complete_battery', $package->assessment_category);
        $this->assertSame('teamwork', $package->competency_mapping[1]);
        $this->assertTrue($package->auto_pull_result);
        $this->assertTrue($package->requires_candidate_consent);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_provider.created',
            'auditable_id' => $provider->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_package.created',
            'auditable_id' => $package->id,
        ]);
    }

    public function test_seeded_admin_can_invite_candidate_to_internal_psychotest_and_sync_result(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'request_number' => 'MPR-067',
            'position_title' => 'People Assessment Specialist',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'high',
            'request_reason' => 'new_position',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-067',
            'full_name' => 'Psikotest Kandidat Demo',
            'email' => 'psikotest@example.com',
            'source_channel' => 'direct',
            'pipeline_stage' => 'screening',
            'candidate_status' => 'active',
            'fit_score' => 70,
            'stage_history' => [['stage' => 'screening', 'status' => 'active']],
            'created_by' => $admin->id,
        ]);

        $provider = PsychotestProvider::create([
            'company_id' => $company->id,
            'provider_code' => 'PSY-INT-067',
            'provider_name' => 'HRIS Internal Engine',
            'provider_type' => 'internal_engine',
            'auth_method' => 'internal',
            'connection_status' => 'connected',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $package = PsychotestPackage::create([
            'psychotest_provider_id' => $provider->id,
            'package_code' => 'PSY-PKG-067',
            'package_name' => 'Internal Staff Battery',
            'assessment_category' => 'complete_battery',
            'target_role_level' => 'staff',
            'estimated_minutes' => 75,
            'competency_mapping' => ['integritas', 'teamwork', 'problem solving'],
            'result_scale' => 'score_0_100',
            'interpretation_method' => 'hr_mapping',
            'auto_pull_result' => true,
            'requires_candidate_consent' => true,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/psychotest-invitations')
            ->assertOk()
            ->assertSeeText('Undangan Psikotest')
            ->assertSeeText('Buat Undangan')
            ->assertSeeText('Tracker Undangan');

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-invitations', [
                'recruitment_candidate_id' => $candidate->id,
                'psychotest_package_id' => $package->id,
                'invitation_number' => 'PSY-INV-067',
                'delivery_channel' => 'email',
                'sent_at' => '2026-08-01 09:00:00',
                'expires_at' => '2026-08-03 17:00:00',
                'status' => 'sent',
                'instruction' => 'Kerjakan test internal di tempat tenang.',
                'notes' => 'Prioritas kandidat untuk role assessment.',
            ])
            ->assertRedirect('/recruitment/psychotest-invitations');

        $invitation = PsychotestInvitation::where('invitation_number', 'PSY-INV-067')->firstOrFail();

        $this->assertSame('assessment', $candidate->fresh()->pipeline_stage);
        $this->assertSame('not_started', $invitation->result_sync_status);

        $this->get('/psychotest/'.$invitation->token)
            ->assertOk()
            ->assertSeeText('Internal Psikotest')
            ->assertSeeText('Psikotest Kandidat Demo');

        $this->post('/psychotest/'.$invitation->token.'/consent', [
            'candidate_consent' => '1',
        ])->assertRedirect('/psychotest/'.$invitation->token);

        $invitation->refresh();
        $this->assertSame('in_progress', $invitation->status);
        $this->assertNotNull($invitation->consented_at);

        $this->post('/psychotest/'.$invitation->token.'/submit-result', [
            'overall_score' => 88,
            'fit_recommendation' => 'fit',
            'risk_level' => 'low',
            'dimension_scores' => 'integritas:90, teamwork:86, problem solving:88',
            'summary' => 'Kandidat fit untuk kebutuhan role dan siap lanjut user interview.',
        ])->assertRedirect('/psychotest/'.$invitation->token);

        $result = PsychotestResult::where('psychotest_invitation_id', $invitation->id)->firstOrFail();

        $this->assertSame(88, $result->overall_score);
        $this->assertSame('fit', $result->fit_recommendation);
        $this->assertSame('synced', $invitation->fresh()->result_sync_status);
        $this->assertSame(88, $candidate->fresh()->fit_score);
        $this->assertDatabaseHas('recruitment_candidate_checks', [
            'check_number' => 'PSY-CHECK-'.$invitation->id,
            'check_type' => 'psychotest',
            'result' => 'passed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_invitation.created',
            'auditable_id' => $invitation->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_result.synced',
            'auditable_id' => $result->id,
        ]);
    }

    public function test_seeded_admin_can_manage_internal_psychotest_question_bank_and_battery_template(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $provider = PsychotestProvider::create([
            'company_id' => $company->id,
            'provider_code' => 'PSY-INT-068',
            'provider_name' => 'HRIS Internal Engine',
            'provider_type' => 'internal_engine',
            'auth_method' => 'internal',
            'connection_status' => 'connected',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $package = PsychotestPackage::create([
            'psychotest_provider_id' => $provider->id,
            'package_code' => 'PSY-PKG-068',
            'package_name' => 'Internal Integrity Battery',
            'assessment_category' => 'integrity',
            'target_role_level' => 'staff',
            'estimated_minutes' => 60,
            'competency_mapping' => ['integritas', 'konsistensi'],
            'result_scale' => 'score_0_100',
            'interpretation_method' => 'hr_mapping',
            'auto_pull_result' => true,
            'requires_candidate_consent' => true,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/psychotest-question-bank')
            ->assertOk()
            ->assertSeeText('Bank Soal Psikotest')
            ->assertSeeText('Kategori & Dimensi')
            ->assertSeeText('Soal & Template Battery');

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-question-bank/categories', [
                'category_code' => 'PSY-CAT-068',
                'category_name' => 'Integrity Assessment',
                'assessment_domain' => 'integrity',
                'owner_name' => 'Ayu Prameswari',
                'is_active' => '1',
                'description' => 'Kategori untuk mengukur integritas kandidat.',
            ])
            ->assertRedirect('/recruitment/psychotest-question-bank');

        $category = PsychotestQuestionCategory::where('category_code', 'PSY-CAT-068')->firstOrFail();

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-question-bank/dimensions', [
                'psychotest_question_category_id' => $category->id,
                'dimension_code' => 'PSY-DIM-068',
                'dimension_name' => 'Integritas',
                'scoring_direction' => 'positive',
                'weight_percent' => 80,
                'interpretation_note' => 'Skor tinggi menunjukkan konsistensi nilai dan perilaku.',
                'is_active' => '1',
            ])
            ->assertRedirect('/recruitment/psychotest-question-bank');

        $dimension = PsychotestDimension::where('dimension_code', 'PSY-DIM-068')->firstOrFail();

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-question-bank/questions', [
                'psychotest_dimension_id' => $dimension->id,
                'question_code' => 'PSY-Q-068',
                'question_type' => 'likert',
                'question_text' => 'Saya tetap mengikuti prosedur meskipun tidak diawasi.',
                'instruction' => 'Pilih jawaban yang paling menggambarkan diri Anda.',
                'time_limit_seconds' => 60,
                'difficulty_level' => 'normal',
                'version' => 1,
                'status' => 'active',
                'scoring_key' => 'positive, integrity',
                'option_codes' => ['A', 'B'],
                'option_texts' => ['Tidak setuju', 'Sangat setuju'],
                'option_scores' => [1, 5],
            ])
            ->assertRedirect('/recruitment/psychotest-question-bank');

        $question = PsychotestQuestion::where('question_code', 'PSY-Q-068')->firstOrFail();

        $this->actingAs($admin)
            ->post('/recruitment/psychotest-question-bank/templates', [
                'psychotest_package_id' => $package->id,
                'template_code' => 'BAT-068',
                'template_name' => 'Integrity Staff Battery',
                'target_role_level' => 'staff',
                'total_duration_minutes' => 45,
                'section_config' => 'integrity:20, consistency:10',
                'version' => 1,
                'status' => 'active',
                'instruction' => 'Kerjakan semua section sesuai urutan.',
                'notes' => 'Template awal untuk kandidat staff.',
            ])
            ->assertRedirect('/recruitment/psychotest-question-bank');

        $template = PsychotestBatteryTemplate::where('template_code', 'BAT-068')->firstOrFail();

        $this->assertSame('Integrity Assessment', $dimension->category->category_name);
        $this->assertSame('likert', $question->question_type);
        $this->assertSame(2, $question->options()->count());
        $this->assertSame('Sangat setuju', $question->options()->where('option_code', 'B')->first()->option_text);
        $this->assertSame('integrity:20', $template->section_config[0]);
        $this->assertSame('Internal Integrity Battery', $template->package->package_name);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_question.created',
            'auditable_id' => $question->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'psychotest_battery_template.created',
            'auditable_id' => $template->id,
        ]);
    }

    public function test_seeded_admin_can_convert_hired_candidate_to_employee_master(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('company_id', $company->id)->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'MPR-065',
            'position_title' => 'HRIS Conversion Specialist',
            'department' => 'Human Capital',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'priority' => 'high',
            'request_reason' => 'new_position',
            'status' => 'approved',
            'approval_snapshot' => ['company' => 'BMP'],
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-065',
            'full_name' => 'Converted Kandidat Demo',
            'email' => 'converted.personal@example.com',
            'phone' => '081299990065',
            'source_channel' => 'referral',
            'pipeline_stage' => 'hired',
            'candidate_status' => 'hired',
            'portal_token' => 'portal-token-065',
            'portal_invited_at' => now(),
            'fit_score' => 91,
            'stage_history' => [['stage' => 'hired', 'status' => 'hired']],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/employee-conversions')
            ->assertOk()
            ->assertSeeText('Konversi Employee')
            ->assertSeeText('Buat Konversi')
            ->assertSeeText('Riwayat Konversi');

        $this->actingAs($admin)
            ->post('/recruitment/employee-conversions', [
                'recruitment_candidate_id' => $candidate->id,
                'conversion_number' => 'CONV-065',
                'employee_number' => 'KRM-0065',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employment_status' => 'probation',
                'position' => 'HRIS Conversion Specialist',
                'department' => 'Human Capital',
                'grade' => 'G5',
                'cost_center' => 'HC-001',
                'join_date' => '2026-08-03',
                'work_email' => 'converted@kurmigo.id',
                'conversion_status' => 'converted',
                'notes' => 'Ready untuk onboarding dan payroll starter.',
            ])
            ->assertRedirect('/recruitment/employee-conversions');

        $conversion = RecruitmentEmployeeConversion::where('conversion_number', 'CONV-065')->firstOrFail();
        $employee = EmployeeProfile::where('employee_number', 'KRM-0065')->firstOrFail();

        $this->assertSame($employee->id, $conversion->employee_profile_id);
        $this->assertSame('Converted Kandidat Demo', $employee->full_name);
        $this->assertSame('converted.personal@example.com', $employee->personal_email);
        $this->assertSame('converted@kurmigo.id', $employee->work_email);
        $this->assertSame('converted', $conversion->conversion_status);
        $this->assertTrue($conversion->mapping_snapshot['employee_created']);
        $this->assertSame('hired', $candidate->fresh()->candidate_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_employee_conversion.created',
            'auditable_id' => $conversion->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_profile.created_from_candidate',
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_seeded_admin_can_create_recruitment_welcome_journey_and_generate_onboarding_tasks(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('company_id', $company->id)->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-0066',
            'full_name' => 'Welcome Journey Demo',
            'employment_status' => 'probation',
            'position' => 'People Experience Officer',
            'department' => 'Human Capital',
            'join_date' => '2026-08-05',
            'work_email' => 'welcome@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/onboarding-journeys')
            ->assertOk()
            ->assertSeeText('Welcome Journey')
            ->assertSeeText('Buat Welcome Journey')
            ->assertSeeText('Daftar Welcome Journey');

        $this->actingAs($admin)
            ->post('/recruitment/onboarding-journeys', [
                'employee_profile_id' => $employee->id,
                'journey_number' => 'WELCOME-066',
                'buddy_name' => 'Ayu Prameswari',
                'buddy_role' => 'HR Buddy',
                'buddy_contact' => 'ayu@kurmigo.id',
                'start_date' => '2026-08-05',
                'probation_end_date' => '2026-11-05',
                'probation_reviewer' => 'Raka Mahendra',
                'journey_status' => 'active',
                'checklist_items' => [
                    'Pre-join: verifikasi dokumen dan akun kerja',
                    'Day-1: welcome session dan tour area kerja',
                    'Week-1: review role dan target kerja',
                ],
                'probation_goals' => ['Adaptasi role HRIS', 'Selesaikan 1 improvement onboarding'],
                'welcome_agenda' => ['Meet buddy', 'Intro ke team Human Capital'],
                'notes' => 'Karyawan baru perlu pendampingan intensif minggu pertama.',
            ])
            ->assertRedirect('/recruitment/onboarding-journeys');

        $journey = RecruitmentOnboardingJourney::where('journey_number', 'WELCOME-066')->firstOrFail();

        $this->assertSame('Ayu Prameswari', $journey->buddy_name);
        $this->assertCount(3, $journey->checklist_snapshot);
        $this->assertCount(2, $journey->probation_plan);
        $this->assertSame('active', $journey->journey_status);
        $this->assertSame(3, EmployeeOnboardingTask::where('employee_profile_id', $employee->id)->where('category', 'welcome_journey')->count());
        $this->assertDatabaseHas('employee_onboarding_tasks', [
            'employee_profile_id' => $employee->id,
            'owner_name' => 'Ayu Prameswari',
            'task_code' => 'WELCOME-066-TASK-01',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'recruitment_onboarding_journey.created',
            'auditable_id' => $journey->id,
        ]);
    }

    public function test_seeded_admin_can_create_tax_calculation_audit(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-TAX-015',
            'employee_name' => 'Audit Pajak Demo',
            'nik' => '3175091201010015',
            'npwp' => '09.123.456.7-015.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'klu' => '62090',
            'kpp_code' => 'KPP-015',
            'kpp_name' => 'KPP Demo',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-audits')
            ->assertOk()
            ->assertSeeText('Riwayat Perhitungan PPh 21')
            ->assertSeeText('Buat Simulasi Audit');

        $this->actingAs($admin)
            ->post('/payroll/tax-audits', [
                'tax_profile_id' => $profile->id,
                'period' => '2026-07',
                'gross_income' => 12000000,
                'taxable_allowance' => 1000000,
                'deduction' => 500000,
                'calculation_status' => 'final',
            ])
            ->assertRedirect('/payroll/tax-audits');

        $audit = TaxCalculationAudit::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame('2026-07', $audit->period);
        $this->assertSame('final', $audit->calculation_status);
        $this->assertGreaterThan(0, (float) $audit->pph21_amount);
        $this->assertSame('PKP', $audit->calculation_steps[5]['label']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_calculation.explained',
            'auditable_id' => $audit->id,
        ]);
    }

    public function test_seeded_admin_can_manage_payroll_component_builder(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/payroll/components')
            ->assertOk()
            ->assertSeeText('Builder Komponen Payroll')
            ->assertSeeText('Buat Komponen Payroll')
            ->assertSeeText('Rule Eligibility');

        $this->actingAs($admin)
            ->post('/payroll/components', [
                'company_id' => $company->id,
                'component_code' => 'SITE-031',
                'component_name' => 'Tunjangan Site',
                'component_category' => 'earning',
                'calculation_method' => 'formula',
                'default_amount' => 0,
                'rate_value' => 0,
                'formula_expression' => 'base_salary / working_days * attendance_days',
                'dependencies' => 'base_salary,working_days,attendance_days',
                'prorate_basis' => 'attendance_days',
                'is_taxable' => 1,
                'is_bpjs_basis' => 0,
                'is_payslip_visible' => 1,
                'gl_account' => '5110-031',
                'effective_start' => '2026-07-01',
                'status' => 'active',
                'sort_order' => 31,
                'notes' => 'Tunjangan site prorata berdasarkan hari hadir.',
            ])
            ->assertRedirect('/payroll/components');

        $component = PayrollComponent::where('component_code', 'SITE-031')->firstOrFail();

        $this->assertSame('earning', $component->component_category);
        $this->assertSame('formula', $component->calculation_method);
        $this->assertSame('attendance_days', $component->prorate_basis);
        $this->assertTrue($component->is_taxable);
        $this->assertFalse($component->is_bpjs_basis);
        $this->assertSame(['base_salary', 'working_days', 'attendance_days'], $component->dependencies);

        $this->actingAs($admin)
            ->post('/payroll/components/rules', [
                'payroll_component_id' => $component->id,
                'rule_name' => 'Hanya site BMP-SITE-01',
                'condition_field' => 'branch',
                'operator' => 'equals',
                'condition_value' => 'BMP-SITE-01',
                'action' => 'include',
                'priority' => 10,
                'is_active' => 1,
                'notes' => 'Komponen hanya berlaku untuk site operasional.',
            ])
            ->assertRedirect('/payroll/components');

        $rule = PayrollComponentRule::where('payroll_component_id', $component->id)->firstOrFail();

        $this->assertSame('branch', $rule->condition_field);
        $this->assertSame('include', $rule->action);
        $this->assertTrue($rule->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_component.created',
            'auditable_id' => $component->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_component_rule.created',
            'auditable_id' => $rule->id,
        ]);
    }

    public function test_seeded_admin_can_manage_salary_structure_and_confidential_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $unit = OrganizationUnit::firstOrCreate(
            ['code' => 'PAY-032-UNIT'],
            [
                'company_id' => $company->id,
                'name' => 'Payroll Test Unit',
                'unit_type' => 'Departemen',
                'is_active' => true,
            ]
        );
        $grade = JobGrade::firstOrCreate(
            ['code' => 'G5-032'],
            [
                'company_id' => $company->id,
                'name' => 'Grade 5 Test',
                'level' => 5,
                'minimum_salary' => 8500000,
                'maximum_salary' => 12500000,
                'is_active' => true,
            ]
        );
        $position = JobPosition::firstOrCreate(
            ['code' => 'POS-032'],
            [
                'company_id' => $company->id,
                'organization_unit_id' => $unit->id,
                'job_grade_id' => $grade->id,
                'name' => 'Supervisor Payroll Test',
                'employment_category' => 'Staff',
                'headcount_plan' => 1,
                'is_active' => true,
            ]
        );
        $employee = EmployeeProfile::firstOrCreate(
            ['employee_number' => 'EMP-PAY-032'],
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Payroll Salary Test',
                'employment_status' => 'permanent',
                'position' => $position->name,
                'department' => $unit->name,
                'grade' => $grade->code,
                'cost_center' => 'PAYROLL',
                'join_date' => '2026-07-01',
                'work_email' => 'payroll.salary032@kurmigo.id',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $this->actingAs($admin)
            ->get('/payroll/salary-structures')
            ->assertOk()
            ->assertSeeText('Struktur Gaji')
            ->assertSeeText('Buat Salary Band')
            ->assertSeeText('Policy Akses Kompensasi');

        $this->actingAs($admin)
            ->post('/payroll/salary-structures/bands', [
                'company_id' => $company->id,
                'job_grade_id' => $grade->id,
                'band_code' => 'G5-BMP-032',
                'band_name' => 'Supervisor Site 2026',
                'level' => 5,
                'minimum_salary' => 8500000,
                'midpoint_salary' => 10500000,
                'maximum_salary' => 12500000,
                'currency' => 'IDR',
                'effective_start' => '2026-07-01',
                'status' => 'active',
                'notes' => 'Salary band test untuk PAYROL-032.',
            ])
            ->assertRedirect('/payroll/salary-structures');

        $band = PayrollSalaryBand::where('band_code', 'G5-BMP-032')->firstOrFail();

        $this->actingAs($admin)
            ->post('/payroll/salary-structures/assignments', [
                'employee_profile_id' => $employee->id,
                'payroll_salary_band_id' => $band->id,
                'job_position_id' => $position->id,
                'base_salary' => 10000000,
                'fixed_allowance' => 1500000,
                'variable_allowance' => 500000,
                'payroll_frequency' => 'monthly',
                'effective_start' => '2026-07-01',
                'approval_status' => 'approved',
                'confidentiality_level' => 'payroll_only',
                'notes' => 'Assignment kompensasi rahasia.',
            ])
            ->assertRedirect('/payroll/salary-structures');

        $assignment = EmployeeSalaryAssignment::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->actingAs($admin)
            ->post('/payroll/salary-structures/policies', [
                'policy_name' => 'Payroll Admin Full Access',
                'role_scope' => 'Payroll Admin',
                'access_level' => 'detail',
                'can_view_salary' => 1,
                'can_edit_salary' => 1,
                'can_export_salary' => 1,
                'requires_approval' => 1,
                'status' => 'active',
                'notes' => 'Export salary wajib approval.',
            ])
            ->assertRedirect('/payroll/salary-structures');

        $policy = CompensationAccessPolicy::where('policy_name', 'Payroll Admin Full Access')->firstOrFail();

        $this->assertSame($band->id, $assignment->payroll_salary_band_id);
        $this->assertSame('payroll_only', $assignment->confidentiality_level);
        $this->assertTrue($policy->can_view_salary);
        $this->assertTrue($policy->can_export_salary);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_salary_band.created',
            'auditable_id' => $band->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'employee_salary_assignment.created',
            'auditable_id' => $assignment->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'compensation_access_policy.created',
            'auditable_id' => $policy->id,
        ]);
    }

    public function test_seeded_admin_can_run_payroll_wizard_validation_and_anomaly_detection(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $employee = EmployeeProfile::firstOrCreate(
            ['employee_number' => 'EMP-RUN-035'],
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Payroll Wizard Test',
                'employment_status' => 'permanent',
                'position' => 'Staff Payroll',
                'department' => 'Payroll',
                'grade' => 'G5',
                'cost_center' => 'PAYROLL',
                'join_date' => '2026-07-01',
                'work_email' => 'payroll.run035@kurmigo.id',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );
        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->full_name,
            'nik' => '3175091201010035',
            'npwp' => '09.123.456.7-035.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);
        $employee->forceFill(['tax_profile_id' => $taxProfile->id])->save();
        EmployeeBankAccount::create([
            'employee_profile_id' => $employee->id,
            'bank_name' => 'BCA',
            'account_number' => '035035035',
            'account_holder_name' => $employee->full_name,
            'is_primary' => true,
        ]);
        EmployeeBpjsProfile::create([
            'employee_profile_id' => $employee->id,
            'bpjs_kesehatan_number' => 'KES035',
            'bpjs_ketenagakerjaan_number' => 'TK035',
            'bpjs_class' => 'Kelas 1',
            'registered_at' => '2026-07-01',
            'coverage_snapshot' => ['status' => 'active'],
        ]);
        $band = PayrollSalaryBand::create([
            'company_id' => $company->id,
            'band_code' => 'RUN-G5-035',
            'band_name' => 'Run Wizard Band',
            'level' => 5,
            'minimum_salary' => 8000000,
            'midpoint_salary' => 10000000,
            'maximum_salary' => 12000000,
            'currency' => 'IDR',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        EmployeeSalaryAssignment::create([
            'employee_profile_id' => $employee->id,
            'payroll_salary_band_id' => $band->id,
            'base_salary' => 10000000,
            'fixed_allowance' => 1000000,
            'variable_allowance' => 0,
            'payroll_frequency' => 'monthly',
            'effective_start' => '2026-07-01',
            'approval_status' => 'approved',
            'confidentiality_level' => 'payroll_only',
            'created_by' => $admin->id,
        ]);
        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-202606-' . $employee->employee_number,
            'period' => '2026-06',
            'status' => 'published',
            'gross_pay' => 5000000,
            'total_deduction' => 0,
            'net_pay' => 5000000,
            'earning_snapshot' => ['Gaji Pokok' => 5000000],
            'deduction_snapshot' => [],
            'employee_snapshot' => ['full_name' => $employee->full_name],
            'checksum' => 'run035',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/run-wizard')
            ->assertOk()
            ->assertSeeText('Wizard Proses Payroll')
            ->assertSeeText('Buat Payroll Run');

        $this->actingAs($admin)
            ->post('/payroll/run-wizard', [
                'company_id' => $company->id,
                'period' => '2026-07',
                'cutoff_start' => '2026-07-01',
                'cutoff_end' => '2026-07-31',
                'run_type' => 'regular',
                'notes' => 'Payroll run test PAYROL-035.',
            ])
            ->assertRedirect('/payroll/run-wizard');

        $run = PayrollRun::where('period', '2026-07')->firstOrFail();

        $this->actingAs($admin)
            ->post("/payroll/run-wizard/{$run->id}/validate")
            ->assertRedirect('/payroll/run-wizard');

        $run->refresh();

        $this->assertSame('ready', $run->status);
        $this->assertSame(4, PayrollRunValidation::where('payroll_run_id', $run->id)->where('status', 'passed')->count());
        $this->assertSame(1, PayrollRunAnomaly::where('payroll_run_id', $run->id)->count());
        $this->assertSame(11000000.0, (float) $run->gross_estimate);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_run.created',
            'auditable_id' => $run->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_run.validated',
            'auditable_id' => $run->id,
        ]);
    }

    public function test_seeded_admin_can_generate_bank_h2h_payload_and_approve_disbursement(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $employee = EmployeeProfile::firstOrCreate(
            ['employee_number' => 'EMP-DISB-039'],
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Payroll Disbursement Test',
                'employment_status' => 'permanent',
                'position' => 'Staff Payroll',
                'department' => 'Payroll',
                'grade' => 'G5',
                'cost_center' => 'PAYROLL',
                'join_date' => '2026-07-01',
                'work_email' => 'payroll.disb039@kurmigo.id',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );
        EmployeeBankAccount::create([
            'employee_profile_id' => $employee->id,
            'bank_name' => 'BCA',
            'account_number' => '039039039',
            'account_holder_name' => $employee->full_name,
            'is_primary' => true,
        ]);
        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-202607-' . $employee->employee_number,
            'period' => '2026-07',
            'status' => 'published',
            'gross_pay' => 12000000,
            'total_deduction' => 1000000,
            'net_pay' => 11000000,
            'earning_snapshot' => ['Gaji Pokok' => 12000000],
            'deduction_snapshot' => ['Potongan' => 1000000],
            'employee_snapshot' => ['full_name' => $employee->full_name],
            'checksum' => 'disb039',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/disbursements')
            ->assertOk()
            ->assertSeeText('Pencairan Gaji')
            ->assertSeeText('Buat Batch Pencairan Bank')
            ->assertSeeText('Host-to-host bank API')
            ->assertSeeText('Kontrol Integritas');

        $this->actingAs($admin)
            ->post('/payroll/disbursements', [
                'company_id' => $company->id,
                'period' => '2026-07',
                'bank_channel' => 'host_to_host',
                'source_account' => '1234567890',
                'scheduled_payment_date' => '2026-07-25',
                'file_format' => 'h2h_json',
                'notes' => 'Batch transfer test INTEGR-089.',
            ])
            ->assertRedirect('/payroll/disbursements');

        $batch = PayrollDisbursementBatch::where('period', '2026-07')->firstOrFail();
        $item = PayrollDisbursementItem::where('payroll_disbursement_batch_id', $batch->id)->firstOrFail();

        $this->assertSame('pending_approval', $batch->status);
        $this->assertSame('host_to_host', $batch->bank_channel);
        $this->assertSame('h2h_json', $batch->file_format);
        $this->assertNotEmpty($batch->file_checksum);
        $this->assertSame(11000000.0, (float) $batch->total_amount);
        $this->assertSame('ready', $item->status);
        $this->assertSame('039039039', $item->bank_account_number);
        $this->assertSame(2, PayrollDisbursementApproval::where('payroll_disbursement_batch_id', $batch->id)->count());

        $this->actingAs($admin)
            ->post("/payroll/disbursements/{$batch->id}/approve", ['decision' => 'approved'])
            ->assertRedirect('/payroll/disbursements');
        $this->actingAs($admin)
            ->post("/payroll/disbursements/{$batch->id}/approve", ['decision' => 'approved'])
            ->assertRedirect('/payroll/disbursements');

        $batch->refresh();
        $this->assertSame('approved', $batch->status);

        $this->actingAs($admin)
            ->get("/payroll/disbursements/{$batch->id}/download")
            ->assertOk()
            ->assertSee('transactions')
            ->assertSee('PAY202607-EMP-DISB-039')
            ->assertSee('EMP-DISB-039')
            ->assertSee('039039039');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_disbursement.created',
            'auditable_id' => $batch->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_disbursement.approved',
            'auditable_id' => $batch->id,
        ]);
    }

    public function test_seeded_admin_can_review_reimbursement_ocr_and_mark_ready_for_payroll(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $employee = EmployeeProfile::updateOrCreate(
            ['employee_number' => 'EMP-REIMB-037'],
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Reimbursement OCR Test',
                'employment_status' => 'permanent',
                'position' => 'Staff Finance',
                'department' => 'Finance',
                'grade' => 'G4',
                'cost_center' => 'FINANCE',
                'join_date' => '2026-07-01',
                'work_email' => 'reimb037@kurmigo.id',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $this->actingAs($admin)
            ->get('/payroll/reimbursements')
            ->assertOk()
            ->assertSeeText('Klaim Reimbursement')
            ->assertSeeText('Catat Klaim Manual');

        $this->actingAs($admin)
            ->post('/payroll/reimbursements', [
                'employee_profile_id' => $employee->id,
                'claim_number' => 'REIMB-037-001',
                'claim_date' => '2026-07-17',
                'claim_type' => 'transport',
                'amount' => 175000,
                'merchant_name' => 'Grab',
                'attachment_reference' => 'camera://receipt/reimb-037.jpg',
                'ocr_status' => 'scanned',
                'ocr_confidence' => 82,
                'ocr_vendor_name' => 'Grab',
                'ocr_total_amount' => 175000,
                'duplicate_risk_score' => 10,
                'payroll_treatment' => 'non_taxable_reimbursement',
                'payroll_period' => '2026-07',
                'payroll_posting_status' => 'not_posted',
                'approval_status' => 'submitted',
                'current_approver' => 'Payroll Reviewer',
                'decision_notes' => 'OCR awal cocok dengan nominal klaim.',
            ])
            ->assertRedirect('/payroll/reimbursements');

        $claim = ManagerClaimRequest::where('claim_number', 'REIMB-037-001')->firstOrFail();
        $this->assertSame('scanned', $claim->ocr_status);
        $this->assertSame(175000.0, (float) $claim->ocr_total_amount);

        $this->actingAs($admin)
            ->post("/payroll/reimbursements/{$claim->id}/review", [
                'ocr_status' => 'verified',
                'ocr_confidence' => 95,
                'duplicate_risk_score' => 5,
                'payroll_treatment' => 'non_taxable_reimbursement',
                'payroll_period' => '2026-07',
                'payroll_posting_status' => 'ready_for_payroll',
                'approval_status' => 'approved',
                'decision_notes' => 'Approved untuk payroll Juli.',
            ])
            ->assertRedirect('/payroll/reimbursements');

        $claim->refresh();
        $this->assertSame('approved', $claim->approval_status);
        $this->assertSame('ready_for_payroll', $claim->payroll_posting_status);
        $this->assertSame('verified', $claim->ocr_status);
        $this->assertNotNull($claim->reviewed_at);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_reimbursement.reviewed',
            'auditable_id' => $claim->id,
        ]);
    }

    public function test_seeded_admin_can_create_employee_loan_and_employee_can_view_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $employee = EmployeeProfile::updateOrCreate(
            ['employee_number' => 'EMP-LOAN-038'],
            [
                'user_id' => $employeeUser->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Loan Balance Test',
                'employment_status' => 'permanent',
                'position' => 'Staff Operation',
                'department' => 'Operation',
                'grade' => 'G3',
                'cost_center' => 'OPS',
                'join_date' => '2026-07-01',
                'work_email' => $employeeUser->email,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $this->actingAs($admin)
            ->get('/payroll/loans')
            ->assertOk()
            ->assertSeeText('Pinjaman & Potongan')
            ->assertSeeText('Tambah Pinjaman');

        $this->actingAs($admin)
            ->post('/payroll/loans', [
                'employee_profile_id' => $employee->id,
                'loan_number' => 'LOAN-038-001',
                'loan_type' => 'cooperative_loan',
                'provider' => 'Koperasi Karyawan',
                'loan_date' => '2026-07-17',
                'principal_amount' => 5000000,
                'admin_fee' => 0,
                'interest_amount' => 0,
                'tenor_months' => 10,
                'start_period' => '2026-08-01',
                'deduction_component_code' => 'DED-LOAN',
                'approval_status' => 'approved',
                'payroll_posting_status' => 'active',
                'notes' => 'Potongan koperasi payroll.',
            ])
            ->assertRedirect('/payroll/loans');

        $loan = EmployeeLoan::where('loan_number', 'LOAN-038-001')->firstOrFail();
        $this->assertSame(500000.0, (float) $loan->monthly_installment);
        $this->assertSame(5000000.0, (float) $loan->outstanding_amount);
        $this->assertSame(10, EmployeeLoanInstallment::where('employee_loan_id', $loan->id)->count());

        $installment = EmployeeLoanInstallment::where('employee_loan_id', $loan->id)->orderBy('installment_no')->firstOrFail();
        $this->actingAs($admin)
            ->post("/payroll/loan-installments/{$installment->id}", [
                'paid_amount' => 500000,
                'status' => 'posted',
                'notes' => 'Diposting payroll Agustus.',
            ])
            ->assertRedirect('/payroll/loans');

        $loan->refresh();
        $this->assertSame(4500000.0, (float) $loan->outstanding_amount);

        $this->actingAs($employeeUser)
            ->get('/ess/loans')
            ->assertOk()
            ->assertSeeText('Pinjaman Saya')
            ->assertSeeText('LOAN-038-001');
    }

    public function test_seeded_admin_can_create_retroactive_payroll_adjustment_and_employee_can_view_it(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $employee = EmployeeProfile::updateOrCreate(
            ['employee_number' => 'EMP-ADJ-040'],
            [
                'user_id' => $employeeUser->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Payroll Adjustment Test',
                'employment_status' => 'permanent',
                'position' => 'Staff Payroll Adj',
                'department' => 'Payroll',
                'grade' => 'G4',
                'cost_center' => 'PAYROLL',
                'join_date' => '2026-07-01',
                'work_email' => $employeeUser->email,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $this->actingAs($admin)
            ->get('/payroll/adjustments')
            ->assertOk()
            ->assertSeeText('Penyesuaian Payroll')
            ->assertSeeText('Tambah Adjustment');

        $this->actingAs($admin)
            ->post('/payroll/adjustments', [
                'employee_profile_id' => $employee->id,
                'adjustment_number' => 'ADJ-040-001',
                'adjustment_type' => 'backpay',
                'component_code' => 'EARN-BACKPAY',
                'component_name' => 'Backpay kenaikan gaji',
                'component_direction' => 'earning',
                'period_from' => '2026-05',
                'period_to' => '2026-06',
                'posting_period' => '2026-07',
                'base_amount' => 0,
                'adjustment_amount' => 1500000,
                'tax_treatment' => 'regular_taxable',
                'status' => 'submitted',
                'reason' => 'Kenaikan gaji berlaku mundur.',
                'audit_notes' => 'Approval memo HR-040.',
            ])
            ->assertRedirect('/payroll/adjustments');

        $adjustment = PayrollAdjustment::where('adjustment_number', 'ADJ-040-001')->firstOrFail();
        $this->assertSame(1500000.0, (float) $adjustment->taxable_amount);
        $this->assertSame(1500000.0, (float) $adjustment->impact_snapshot['net_impact']);

        $this->actingAs($admin)
            ->post("/payroll/adjustments/{$adjustment->id}/approve", [
                'decision' => 'posted',
                'audit_notes' => 'Posted ke payroll Juli.',
            ])
            ->assertRedirect('/payroll/adjustments');

        $adjustment->refresh();
        $this->assertSame('posted', $adjustment->status);
        $this->assertNotNull($adjustment->posted_at);

        $this->actingAs($employeeUser)
            ->get('/ess/payroll-adjustments')
            ->assertOk()
            ->assertSeeText('Penyesuaian Payroll Saya')
            ->assertSeeText('Backpay kenaikan gaji');
    }

    public function test_seeded_admin_can_calculate_monthly_pph21_ter(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        EmployeeProfile::updateOrCreate(
            ['employee_number' => 'EMP-SIM-014'],
            [
                'user_id' => $employeeUser->id,
                'company_id' => $company->id,
                'branch_id' => $admin->branch_id,
                'full_name' => 'Simulasi Pajak Demo',
                'employment_status' => 'permanent',
                'position' => 'Staff Tax Simulation',
                'department' => 'Payroll',
                'grade' => 'G4',
                'cost_center' => 'PAYROLL',
                'join_date' => '2026-07-01',
                'work_email' => $employeeUser->email,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-TER-002',
            'employee_name' => 'TER Bulanan Demo',
            'nik' => '3175091201010002',
            'npwp' => '09.123.456.7-002.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-monthly')
            ->assertOk()
            ->assertSeeText('Hitung PPh 21 Bulanan')
            ->assertSeeText('Master Tarif TER')
            ->assertSeeText('Mapping Komponen Pajak')
            ->assertSeeText('Gross-up - tunjangan pajak otomatis')
            ->assertSeeText('Pajak ditanggung perusahaan');

        $this->actingAs($admin)
            ->post('/payroll/tax-monthly/components', [
                'company_id' => $company->id,
                'component_code' => 'MEAL',
                'component_name' => 'Tunjangan Makan Kena Pajak',
                'component_type' => 'earning',
                'tax_treatment' => 'taxable_income',
                'payroll_formula_key' => 'earning.meal_taxable',
                'default_amount' => 500000,
                'is_taxable' => 1,
                'is_recurring' => 1,
                'dependencies' => 'attendance.present_days,payroll.base_salary',
                'sort_order' => 10,
            ])
            ->assertRedirect('/payroll/tax-monthly');

        $this->actingAs($admin)
            ->post('/payroll/tax-monthly/rates', [
                'category' => 'A',
                'income_from' => 0,
                'income_to' => 20000000,
                'rate' => 0.005,
                'source_reference' => 'TER test bracket',
            ])
            ->assertRedirect('/payroll/tax-monthly');

        $this->actingAs($admin)
            ->post('/payroll/tax-monthly/calculate', [
                'tax_profile_id' => $profile->id,
                'period' => '2026-07',
                'gross_income' => 12000000,
                'taxable_allowance' => 1000000,
                'deduction' => 500000,
                'tax_method' => 'company_borne',
                'component_ids' => [PayrollTaxComponent::where('component_code', 'MEAL')->firstOrFail()->id],
                'calculation_status' => 'final',
            ])
            ->assertRedirect('/payroll/tax-monthly');

        $rate = TaxTerRate::where('category', 'A')->firstOrFail();
        $calculation = TaxMonthlyCalculation::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame($rate->id, $calculation->tax_ter_rate_id);
        $this->assertSame('A', $calculation->ter_category);
        $this->assertSame('company_borne', $calculation->tax_method);
        $this->assertSame(65000.0, (float) $calculation->pph21_amount);
        $this->assertSame(65000.0, (float) $calculation->company_tax_borne_amount);
        $this->assertSame(13000000.0, (float) $calculation->take_home_pay);
        $this->assertSame(500000.0, (float) $calculation->source_snapshot['component_taxable_income']);
        $this->assertSame('MEAL', $calculation->source_snapshot['payroll_components'][0]['code']);
        $this->assertContains('attendance.present_days', $calculation->source_snapshot['payroll_components'][0]['dependencies']);
        $this->assertDatabaseHas('payroll_tax_components', [
            'component_code' => 'MEAL',
            'tax_treatment' => 'taxable_income',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_tax_component.created',
            'auditable_id' => PayrollTaxComponent::where('component_code', 'MEAL')->firstOrFail()->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_monthly.calculated',
            'auditable_id' => $calculation->id,
        ]);
    }

    public function test_seeded_admin_can_create_monthly_tax_report_package(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-PKG-008',
            'employee_name' => 'Paket Pajak Demo',
            'nik' => '3175091201010008',
            'npwp' => '09.123.456.7-008.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        TaxMonthlyCalculation::create([
            'tax_profile_id' => $profile->id,
            'company_id' => $company->id,
            'period' => '2026-07',
            'ter_category' => 'A',
            'tax_method' => 'gross',
            'gross_income' => 15000000,
            'taxable_allowance' => 0,
            'deduction' => 0,
            'ter_base_amount' => 15000000,
            'ter_rate' => 0.005,
            'pph21_amount' => 75000,
            'tax_allowance_amount' => 0,
            'company_tax_borne_amount' => 0,
            'take_home_pay' => 14925000,
            'calculation_status' => 'final',
            'calculation_steps' => [],
            'source_snapshot' => ['ptkp_status' => 'TK/0'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-report-packages')
            ->assertOk()
            ->assertSeeText('Laporan Pajak Bulanan')
            ->assertSeeText('Buat Paket Laporan');

        $this->actingAs($admin)
            ->post('/payroll/tax-report-packages', [
                'company_id' => $company->id,
                'period' => '2026-07',
                'status' => 'ready_for_coretax',
                'notes' => 'Siap validasi Coretax.',
            ])
            ->assertRedirect('/payroll/tax-report-packages');

        $package = TaxMonthlyReportPackage::where('period', '2026-07')->firstOrFail();

        $this->assertSame('ready_for_coretax', $package->status);
        $this->assertSame(1, $package->employee_count);
        $this->assertSame(15000000.0, (float) $package->total_gross_income);
        $this->assertSame(15000000.0, (float) $package->total_tax_base);
        $this->assertSame(75000.0, (float) $package->total_pph21);
        $this->assertSame('EMP-PKG-008', $package->detail_snapshot[0]['employee_number']);
        $this->assertSame(1, $package->summary_snapshot['tax_methods']['gross']);

        $this->actingAs($admin)
            ->post("/payroll/tax-report-packages/{$package->id}/templates", [
                'schema_version' => 'coretax-v1.0',
                'format' => 'csv',
                'status' => 'ready_to_export',
            ])
            ->assertRedirect('/payroll/tax-report-packages');

        $template = TaxMonthlyExportTemplate::where('tax_monthly_report_package_id', $package->id)->firstOrFail();

        $this->assertSame('coretax-v1.0', $template->schema_version);
        $this->assertSame('csv', $template->format);
        $this->assertSame('ready_to_export', $template->status);
        $this->assertSame('kode_karyawan', $template->columns_schema[2]['key']);
        $this->assertSame('EMP-PKG-008', $template->payload_snapshot[0]['kode_karyawan']);
        $this->assertNotEmpty($template->checksum);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_monthly_report_package.created',
            'auditable_id' => $package->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_monthly_export_template.generated',
            'auditable_id' => $template->id,
        ]);
    }

    public function test_seeded_admin_can_generate_withholding_document_and_employee_can_view_ess(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-BUPOT-012',
            'employee_name' => $admin->name,
            'nik' => '3175091201010012',
            'npwp' => '09.123.456.7-012.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $package = TaxMonthlyReportPackage::create([
            'company_id' => $company->id,
            'period' => '2026-07',
            'package_number' => 'TAXPKG-202607-012',
            'status' => 'ready_for_coretax',
            'employee_count' => 1,
            'total_gross_income' => 18000000,
            'total_tax_base' => 18000000,
            'total_pph21' => 90000,
            'summary_snapshot' => [],
            'detail_snapshot' => [[
                'employee_number' => $profile->employee_number,
                'employee_name' => $profile->employee_name,
                'company_code' => 'BMP',
                'ptkp_status' => 'TK/0',
                'gross_income' => 18000000,
                'tax_base' => 18000000,
                'pph21_amount' => 90000,
            ]],
            'validation_notes' => [],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-withholding-documents')
            ->assertOk()
            ->assertSeeText('Bukti Potong')
            ->assertSeeText('Generate Bukti Potong');

        $this->actingAs($admin)
            ->post('/payroll/tax-withholding-documents/generate', [
                'tax_monthly_report_package_id' => $package->id,
                'status' => 'published',
            ])
            ->assertRedirect('/payroll/tax-withholding-documents');

        $document = TaxWithholdingDocument::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame('published', $document->status);
        $this->assertSame('monthly_pph21', $document->document_type);
        $this->assertSame(18000000.0, (float) $document->gross_income);
        $this->assertSame(90000.0, (float) $document->pph21_amount);
        $this->assertSame('EMP-BUPOT-012', $document->employee_snapshot['employee_number']);
        $this->assertNotEmpty($document->checksum);

        $this->actingAs($admin)
            ->get('/ess/tax-documents')
            ->assertOk()
            ->assertSeeText('Bukti Potong PPh 21')
            ->assertSeeText($document->document_number);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_withholding_document.generated',
            'auditable_id' => $document->id,
        ]);
    }

    public function test_seeded_admin_can_publish_resend_and_employee_acknowledge_payslip(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $admin->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-SLIP-036',
            'full_name' => $admin->name,
            'employment_status' => 'permanent',
            'position' => 'Payroll Reviewer',
            'department' => 'Finance',
            'work_email' => $admin->email,
            'address_snapshot' => [],
            'document_summary' => [],
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/payslips')
            ->assertOk()
            ->assertSeeText('Slip Gaji')
            ->assertSeeText('Buat / Publish Slip');

        $this->actingAs($admin)
            ->post('/payroll/payslips', [
                'employee_profile_id' => $employee->id,
                'period' => '2026-07',
                'status' => 'published',
                'basic_salary' => 12000000,
                'allowance' => 1500000,
                'overtime' => 500000,
                'bpjs_deduction' => 250000,
                'pph21_deduction' => 75000,
                'other_deduction' => 0,
                'notes' => 'Payroll Juli siap publish.',
            ])
            ->assertRedirect('/payroll/payslips');

        $payslip = PayrollPayslip::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->assertSame('published', $payslip->status);
        $this->assertSame(14000000.0, (float) $payslip->gross_pay);
        $this->assertSame(325000.0, (float) $payslip->total_deduction);
        $this->assertSame(13675000.0, (float) $payslip->net_pay);
        $this->assertSame('EMP-SLIP-036', $payslip->employee_snapshot['employee_number']);
        $this->assertNotEmpty($payslip->checksum);

        $this->actingAs($admin)
            ->post("/payroll/payslips/{$payslip->id}/resend")
            ->assertRedirect('/payroll/payslips');

        $payslip->refresh();
        $this->assertSame(1, $payslip->resend_count);
        $this->assertNotNull($payslip->last_sent_at);

        $this->actingAs($admin)
            ->get('/ess/payslips')
            ->assertOk()
            ->assertSeeText('Slip Gaji')
            ->assertSeeText($payslip->payslip_number);

        $this->actingAs($admin)
            ->get("/ess/payslips/{$payslip->id}/download")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $this->actingAs($admin)
            ->post("/ess/payslips/{$payslip->id}/acknowledge")
            ->assertRedirect('/ess/payslips');

        $payslip->refresh();
        $this->assertNotNull($payslip->acknowledged_at);
        $this->assertSame($admin->id, $payslip->acknowledged_by);

        $this->actingAs($admin)
            ->get('/ess/documents')
            ->assertOk()
            ->assertSeeText($payslip->payslip_number)
            ->assertSeeText('Sudah dikonfirmasi');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_payslip.saved',
            'auditable_id' => $payslip->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_payslip.acknowledged',
            'auditable_id' => $payslip->id,
        ]);
    }

    public function test_seeded_admin_can_create_tax_correction_for_retroactive_payroll(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-COR-013',
            'employee_name' => 'Koreksi Pajak Demo',
            'nik' => '3175091201010013',
            'npwp' => '09.123.456.7-013.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $package = TaxMonthlyReportPackage::create([
            'company_id' => $company->id,
            'period' => '2026-07',
            'package_number' => 'TAXPKG-202607-013',
            'status' => 'ready_for_coretax',
            'employee_count' => 1,
            'total_gross_income' => 15000000,
            'total_tax_base' => 15000000,
            'total_pph21' => 75000,
            'summary_snapshot' => [],
            'detail_snapshot' => [[
                'employee_number' => $profile->employee_number,
                'employee_name' => $profile->employee_name,
                'gross_income' => 15000000,
                'tax_base' => 15000000,
                'pph21_amount' => 75000,
            ]],
            'validation_notes' => [],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-corrections')
            ->assertOk()
            ->assertSeeText('Koreksi Pajak')
            ->assertSeeText('Buat Koreksi');

        $this->actingAs($admin)
            ->post('/payroll/tax-corrections', [
                'tax_profile_id' => $profile->id,
                'tax_monthly_report_package_id' => $package->id,
                'correction_type' => 'retroaktif',
                'period' => '2026-07',
                'payroll_period' => '2026-08',
                'previous_gross_income' => 15000000,
                'corrected_gross_income' => 17000000,
                'previous_tax_base' => 15000000,
                'corrected_tax_base' => 17000000,
                'previous_pph21_amount' => 75000,
                'corrected_pph21_amount' => 85000,
                'payroll_adjustment_type' => 'next_payroll_deduction',
                'status' => 'approved',
                'reason' => 'Koreksi rapel tunjangan site yang masuk setelah payroll Juli final.',
            ])
            ->assertRedirect('/payroll/tax-corrections');

        $correction = TaxCorrection::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame('retroaktif', $correction->correction_type);
        $this->assertSame('approved', $correction->status);
        $this->assertSame(10000.0, (float) $correction->delta_pph21_amount);
        $this->assertSame('kurang_bayar', $correction->workflow_snapshot['impact']);
        $this->assertSame('next_payroll_deduction', $correction->after_snapshot['payroll_adjustment_type']);
        $this->assertSame('TAXPKG-202607-013', $correction->before_snapshot['package_number']);
        $this->assertNotNull($correction->approved_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_correction.created',
            'auditable_id' => $correction->id,
        ]);
    }

    public function test_seeded_admin_can_run_tax_simulation_before_payroll_finalization(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-SIM-014',
            'employee_name' => 'Simulasi Pajak Demo',
            'nik' => '3175091201010014',
            'npwp' => '09.123.456.7-014.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        EmployeeProfile::updateOrCreate(
            ['employee_number' => 'EMP-SIM-014'],
            [
                'user_id' => $employeeUser->id,
                'company_id' => $company->id,
                'tax_profile_id' => $profile->id,
                'full_name' => 'Simulasi Pajak Demo',
                'gender' => 'male',
                'employment_status' => 'permanent',
                'position' => 'Staff',
                'department' => 'HR',
                'join_date' => '2026-01-10',
                'work_email' => $employeeUser->email,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        TaxTerRate::create([
            'category' => 'A',
            'income_from' => 0,
            'income_to' => 20000000,
            'rate' => 0.005,
            'source_reference' => 'TER sandbox test',
        ]);

        TaxMonthlyCalculation::create([
            'tax_profile_id' => $profile->id,
            'company_id' => $company->id,
            'period' => '2026-07',
            'ter_category' => 'A',
            'tax_method' => 'gross',
            'gross_income' => 12000000,
            'taxable_allowance' => 0,
            'deduction' => 0,
            'ter_base_amount' => 12000000,
            'ter_rate' => 0.005,
            'pph21_amount' => 60000,
            'tax_allowance_amount' => 0,
            'company_tax_borne_amount' => 0,
            'take_home_pay' => 11940000,
            'calculation_status' => 'final',
            'calculation_steps' => [],
            'source_snapshot' => [],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-simulations')
            ->assertOk()
            ->assertSeeText('Simulasi PPh 21')
            ->assertSeeText('Buat Scenario');

        $this->actingAs($admin)
            ->post('/payroll/tax-simulations', [
                'tax_profile_id' => $profile->id,
                'scenario_name' => 'Simulasi THR gross-up',
                'scenario_type' => 'thr',
                'period' => '2026-07',
                'tax_method' => 'gross_up',
                'gross_income' => 14000000,
                'taxable_allowance' => 1000000,
                'irregular_income' => 5000000,
                'deduction' => 0,
                'final_tax_amount' => 0,
                'scenario_status' => 'approved_for_payroll',
                'visible_to_employee' => 1,
                'notes' => 'Cek dampak THR sebelum payroll final.',
            ])
            ->assertRedirect('/payroll/tax-simulations');

        $simulation = TaxSimulation::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame('approved_for_payroll', $simulation->scenario_status);
        $this->assertSame('thr', $simulation->scenario_type);
        $this->assertSame('gross_up', $simulation->tax_method);
        $this->assertSame(5000000.0, (float) $simulation->irregular_income);
        $this->assertGreaterThan(20000000, (float) $simulation->tax_base);
        $this->assertGreaterThan(0, (float) $simulation->tax_allowance_amount);
        $this->assertGreaterThan(0, (int) $simulation->gross_up_iterations);
        $this->assertTrue($simulation->visible_to_employee);
        $this->assertSame('Simulasi Pajak Demo', $simulation->input_snapshot['employee_name']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_simulation.created',
            'auditable_id' => $simulation->id,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/tax-simulations')
            ->assertOk()
            ->assertSeeText('Simulasi Pajak Saya')
            ->assertSeeText('Simulasi THR gross-up');
    }

    public function test_seeded_admin_can_create_tax_compliance_calendar_with_sla_escalation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $owner = User::where('email', 'citra@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/payroll/tax-compliance-calendar')
            ->assertOk()
            ->assertSeeText('Kalender Compliance Pajak')
            ->assertSeeText('Tambah Deadline');

        $this->actingAs($admin)
            ->post('/payroll/tax-compliance-calendar', [
                'company_id' => $company->id,
                'event_type' => 'coretax_submission',
                'title' => 'Submit Coretax PPh 21 Juli 2026',
                'period' => '2026-07',
                'due_date' => now()->subDay()->toDateString(),
                'reminder_days_before' => 5,
                'sla_level' => 'critical',
                'status' => 'planned',
                'owner_user_id' => $owner->id,
                'escalation_user_id' => $admin->id,
                'notes' => 'Deadline submission payroll tax bulanan.',
            ])
            ->assertRedirect('/payroll/tax-compliance-calendar');

        $event = TaxComplianceEvent::where('event_type', 'coretax_submission')->firstOrFail();

        $this->assertSame('escalated', $event->status);
        $this->assertSame('critical', $event->sla_level);
        $this->assertSame($owner->id, $event->owner_user_id);
        $this->assertSame($admin->id, $event->escalation_user_id);
        $this->assertSame(now()->subDay()->subDays(5)->toDateString(), $event->reminder_date->toDateString());
        $this->assertContains('Submit Coretax', $event->checklist_snapshot);
        $this->assertSame('overdue', $event->escalation_snapshot['reason']);
        $this->assertNotNull($event->escalated_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_compliance_event.created',
            'auditable_id' => $event->id,
        ]);
    }

    public function test_seeded_admin_can_execute_tax_regression_case_for_pph21_and_coretax(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/payroll/tax-regression-cases')
            ->assertOk()
            ->assertSeeText('Regression Test Pajak')
            ->assertSeeText('Run Test Case');

        $this->actingAs($admin)
            ->post('/payroll/tax-regression-cases', [
                'case_type' => 'coretax_template',
                'case_name' => 'TER A basic payroll Coretax schema',
                'period' => '2026-07',
                'gross_income' => 12000000,
                'taxable_allowance' => 1000000,
                'deduction' => 500000,
                'ter_rate' => 0.005,
                'expected_pph21_amount' => 62500,
                'coretax_schema_version' => 'coretax-v1.0',
                'notes' => 'Regression untuk formula TER dan readiness kolom Coretax.',
            ])
            ->assertRedirect('/payroll/tax-regression-cases');

        $case = TaxRegressionCase::where('case_type', 'coretax_template')->firstOrFail();

        $this->assertSame('passed', $case->run_status);
        $this->assertSame('valid', $case->coretax_validation_status);
        $this->assertSame(12500000.0, (float) $case->input_payload['tax_base']);
        $this->assertSame(62500.0, (float) $case->actual_pph21_amount);
        $this->assertSame(0.0, (float) $case->variance_amount);
        $this->assertTrue($case->assertion_snapshot[0]['passed']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_regression_case.executed',
            'auditable_id' => $case->id,
        ]);
    }

    public function test_seeded_admin_can_configure_coretax_connector_and_queue_template(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $package = TaxMonthlyReportPackage::create([
            'company_id' => $company->id,
            'period' => '2026-07',
            'package_number' => 'TAXPKG-202607-010',
            'status' => 'ready_for_coretax',
            'employee_count' => 1,
            'total_gross_income' => 15000000,
            'total_tax_base' => 15000000,
            'total_pph21' => 75000,
            'summary_snapshot' => [],
            'detail_snapshot' => [],
            'validation_notes' => [],
            'created_by' => $admin->id,
        ]);

        $template = TaxMonthlyExportTemplate::create([
            'tax_monthly_report_package_id' => $package->id,
            'template_number' => 'CORETAX-202607-010',
            'schema_version' => 'coretax-v1.0',
            'format' => 'csv',
            'status' => 'ready_to_export',
            'columns_schema' => [['key' => 'kode_karyawan']],
            'payload_snapshot' => [['kode_karyawan' => 'EMP-010']],
            'checksum' => hash('sha256', 'template-010'),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/coretax-connector')
            ->assertOk()
            ->assertSeeText('Coretax Connector')
            ->assertSeeText('Credential Vault');

        $this->actingAs($admin)
            ->post('/payroll/coretax-connector/settings', [
                'company_id' => $company->id,
                'environment' => 'sandbox',
                'base_url' => 'https://coretax-sandbox.example.test',
                'client_id' => 'bmp-client',
                'client_secret' => 'super-secret-010',
                'timeout_seconds' => 30,
                'max_retry' => 3,
                'is_active' => 1,
            ])
            ->assertRedirect('/payroll/coretax-connector');

        $setting = CoretaxConnectorSetting::where('client_id', 'bmp-client')->firstOrFail();

        $this->assertSame('sandbox', $setting->environment);
        $this->assertSame('************-010', $setting->secret_mask);
        $this->assertNotSame('super-secret-010', $setting->credential_hash);
        $this->assertSame('configured', $setting->last_status);

        $this->actingAs($admin)
            ->post('/payroll/coretax-connector/queue', [
                'coretax_connector_setting_id' => $setting->id,
                'tax_monthly_export_template_id' => $template->id,
                'request_type' => 'validate_template',
            ])
            ->assertRedirect('/payroll/coretax-connector');

        $log = CoretaxRetryLog::where('tax_monthly_export_template_id', $template->id)->firstOrFail();

        $this->assertSame('queued', $log->status);
        $this->assertSame(0, $log->attempt);
        $this->assertSame(3, $log->max_retry);
        $this->assertSame($template->checksum, $log->payload_checksum);
        $this->assertSame('CORETAX-202607-010', $log->request_snapshot['template_number']);

        $this->actingAs($admin)
            ->post("/payroll/coretax-connector/retry-logs/{$log->id}/track", [
                'submission_status' => 'accepted',
                'accepted_rows' => 1,
                'rejected_rows' => 0,
                'acknowledged_total_pph21' => 0,
                'response_message' => 'Accepted by sandbox tracker.',
            ])
            ->assertRedirect('/payroll/coretax-connector');

        $tracker = CoretaxSubmissionTracker::where('coretax_retry_log_id', $log->id)->firstOrFail();

        $this->assertSame('accepted', $tracker->submission_status);
        $this->assertSame(1, $tracker->submitted_rows);
        $this->assertSame(1, $tracker->accepted_rows);
        $this->assertSame(0, $tracker->rejected_rows);
        $this->assertSame(0.0, (float) $tracker->difference_amount);
        $this->assertSame('Accepted by sandbox tracker.', $tracker->reconciliation_snapshot['response_message']);
        $this->assertSame('success', $log->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'coretax_connector.setting_created',
            'auditable_id' => $setting->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'coretax_connector.retry_queued',
            'auditable_id' => $log->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'coretax_submission.tracked',
            'auditable_id' => $tracker->id,
        ]);
    }

    public function test_seeded_admin_can_reconcile_annual_pph21_true_up(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-TRUEUP-003',
            'employee_name' => 'True Up Demo',
            'nik' => '3175091201010003',
            'npwp' => '09.123.456.7-003.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        foreach (range(1, 12) as $month) {
            TaxMonthlyCalculation::create([
                'tax_profile_id' => $profile->id,
                'company_id' => $company->id,
                'period' => sprintf('2026-%02d', $month),
                'ter_category' => 'A',
                'gross_income' => 12000000,
                'taxable_allowance' => 0,
                'deduction' => 0,
                'ter_base_amount' => 12000000,
                'ter_rate' => 0.005,
                'pph21_amount' => 60000,
                'calculation_status' => 'final',
                'calculation_steps' => [],
                'source_snapshot' => [],
            ]);
        }

        $this->actingAs($admin)
            ->get('/payroll/tax-annual')
            ->assertOk()
            ->assertSeeText('True-up PPh 21 Tahunan')
            ->assertSeeText('Hitung True-up');

        $this->actingAs($admin)
            ->post('/payroll/tax-annual/reconcile', [
                'tax_profile_id' => $profile->id,
                'tax_year' => 2026,
                'calculation_status' => 'final',
            ])
            ->assertRedirect('/payroll/tax-annual');

        $reconciliation = TaxAnnualReconciliation::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame(2026, $reconciliation->tax_year);
        $this->assertSame('underpaid', $reconciliation->true_up_status);
        $this->assertGreaterThan(0, (float) $reconciliation->true_up_amount);
        $this->assertCount(12, $reconciliation->monthly_snapshot);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_annual.reconciled',
            'auditable_id' => $reconciliation->id,
        ]);
    }

    public function test_seeded_admin_can_calculate_irregular_income_tax(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $profile = TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-IRR-004',
            'employee_name' => 'THR Bonus Demo',
            'nik' => '3175091201010004',
            'npwp' => '09.123.456.7-004.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-irregular')
            ->assertOk()
            ->assertSeeText('Penghasilan Tidak Teratur')
            ->assertSeeText('Hitung Penghasilan Tidak Teratur');

        $this->actingAs($admin)
            ->post('/payroll/tax-irregular', [
                'tax_profile_id' => $profile->id,
                'period' => '2026-04',
                'income_type' => 'thr',
                'tax_method' => 'gross',
                'gross_amount' => 10000000,
                'tax_rate' => 0.05,
                'calculation_status' => 'final',
            ])
            ->assertRedirect('/payroll/tax-irregular');

        $item = TaxIrregularIncome::where('tax_profile_id', $profile->id)->firstOrFail();

        $this->assertSame('thr', $item->income_type);
        $this->assertSame(500000.0, (float) $item->pph21_amount);
        $this->assertSame(9500000.0, (float) $item->net_amount);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_irregular_income.calculated',
            'auditable_id' => $item->id,
        ]);
    }

    public function test_seeded_admin_can_calculate_non_permanent_worker_pph21(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/payroll/tax-non-permanent')
            ->assertOk()
            ->assertSeeText('PPh 21 Non Permanen')
            ->assertSeeText('Tambah Worker');

        $this->actingAs($admin)
            ->post('/payroll/tax-non-permanent/workers', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'worker_name' => 'Harian Demo',
                'worker_number' => 'NPW-005',
                'worker_type' => 'daily_worker',
                'ptkp_status' => 'TK/0',
                'nik' => '3175091201010005',
                'npwp' => '09.123.456.7-005.000',
                'is_npwp_verified' => 1,
            ])
            ->assertRedirect('/payroll/tax-non-permanent');

        $worker = TaxNonPermanentWorker::where('worker_number', 'NPW-005')->firstOrFail();

        $this->actingAs($admin)
            ->post('/payroll/tax-non-permanent/calculate', [
                'tax_non_permanent_worker_id' => $worker->id,
                'period' => '2026-07',
                'work_days' => 10,
                'daily_rate' => 1000000,
                'tax_rate' => 0.05,
                'calculation_status' => 'final',
            ])
            ->assertRedirect('/payroll/tax-non-permanent');

        $calculation = TaxNonPermanentCalculation::where('tax_non_permanent_worker_id', $worker->id)->firstOrFail();

        $this->assertSame('daily_worker', $worker->worker_type);
        $this->assertTrue($worker->is_npwp_verified);
        $this->assertSame(10000000.0, (float) $calculation->gross_income);
        $this->assertSame(9550000.0, (float) $calculation->taxable_base);
        $this->assertSame(477500.0, (float) $calculation->pph21_amount);
        $this->assertSame('PPh 21', $calculation->calculation_steps[2]['label']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_non_permanent.worker_created',
            'auditable_id' => $worker->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_non_permanent.calculated',
            'auditable_id' => $calculation->id,
        ]);
    }

    public function test_seeded_admin_can_manage_tax_privacy_and_export_log(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        TaxProfile::create([
            'company_id' => $company->id,
            'employee_number' => 'EMP-PRIV-017',
            'employee_name' => 'Privasi Pajak Demo',
            'nik' => '3175091201010017',
            'npwp' => '09.123.456.7-017.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-privacy')
            ->assertOk()
            ->assertSeeText('Privasi Pajak')
            ->assertSeeText('Preview Masking')
            ->assertSee('************0017');

        $this->actingAs($admin)
            ->post('/payroll/tax-privacy/policy', [
                'company_id' => $company->id,
                'name' => 'Kebijakan Privasi BMP',
                'mask_nik' => 1,
                'mask_npwp' => 1,
                'export_requires_approval' => 1,
                'retention_years' => 10,
                'legal_basis' => 'Kepatuhan perpajakan Indonesia',
                'notes' => 'Data pajak hanya untuk payroll dan Coretax.',
            ])
            ->assertRedirect('/payroll/tax-privacy');

        $this->actingAs($admin)
            ->post('/payroll/tax-privacy/exports', [
                'company_id' => $company->id,
                'export_type' => 'coretax_template',
                'scope' => 'period',
                'period' => '2026-07',
                'purpose' => 'Pelaporan Coretax bulanan',
                'notes' => 'Export template dengan masking default.',
            ])
            ->assertRedirect('/payroll/tax-privacy');

        $policy = TaxPrivacyPolicy::where('name', 'Kebijakan Privasi BMP')->firstOrFail();
        $export = TaxExportLog::where('purpose', 'Pelaporan Coretax bulanan')->firstOrFail();

        $this->assertTrue($policy->mask_nik);
        $this->assertSame('pending', $export->status);
        $this->assertContains('nik', $export->masked_fields);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_privacy.policy_created',
            'auditable_id' => $policy->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'tax_export.requested',
            'auditable_id' => $export->id,
        ]);
    }

    public function test_seeded_admin_can_manage_platform_data_privacy_consent_and_retention(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/data-privacy')
            ->assertOk()
            ->assertSeeText('Data Privacy Control')
            ->assertSeeText('Buat Policy Privasi')
            ->assertSeeText('Catat Consent')
            ->assertSeeText('Buat Retention Job')
            ->assertSeeText('Preview Masking');

        $this->actingAs($admin)
            ->post('/settings/data-privacy/policies', [
                'company_id' => $company->id,
                'policy_code' => 'PRIV-EMP-010',
                'policy_name' => 'Masking Data Karyawan BMP',
                'data_domain' => 'employee_master',
                'masked_fields' => ['nik', 'npwp', 'bank_account'],
                'restricted_roles' => ['Manager', 'Employee'],
                'masking_mode' => 'role_based',
                'consent_required' => 1,
                'retention_years' => 7,
                'legal_hold_enabled' => 1,
                'legal_basis' => 'Kepatuhan ketenagakerjaan dan perlindungan data pribadi',
                'status' => 'active',
                'notes' => 'Policy platform untuk PLATFO-010.',
            ])
            ->assertRedirect('/settings/data-privacy');

        $policy = PlatformDataPrivacyPolicy::where('policy_code', 'PRIV-EMP-010')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/data-privacy/consents', [
                'platform_data_privacy_policy_id' => $policy->id,
                'company_id' => $company->id,
                'subject_type' => 'employee',
                'subject_reference' => 'KRM-0002',
                'subject_name' => 'Ayu Prameswari',
                'consent_purpose' => 'Administrasi HR, payroll, ESS, dan dokumen karyawan',
                'consent_channel' => 'mobile_webview',
                'status' => 'granted',
                'granted_at' => '2026-07-21',
                'expires_at' => '2028-07-21',
                'notes' => 'Consent dari ESS mobile WebView.',
            ])
            ->assertRedirect('/settings/data-privacy');

        $this->actingAs($admin)
            ->post('/settings/data-privacy/retention-jobs', [
                'platform_data_privacy_policy_id' => $policy->id,
                'company_id' => $company->id,
                'job_number' => 'RET-EMP-010',
                'data_domain' => 'employee_master',
                'scope_reference' => 'ex-employee before 2019',
                'cutoff_date' => '2019-12-31',
                'action_type' => 'anonymize',
                'legal_hold' => 1,
                'estimated_records' => 120,
                'status' => 'blocked',
                'scheduled_at' => '2026-08-03 10:00:00',
                'notes' => 'Ditahan sampai legal review selesai.',
            ])
            ->assertRedirect('/settings/data-privacy');

        $consent = PlatformDataConsent::where('subject_reference', 'KRM-0002')->firstOrFail();
        $job = PlatformDataRetentionJob::where('job_number', 'RET-EMP-010')->firstOrFail();

        $this->assertSame(['nik', 'npwp', 'bank_account'], $policy->masked_fields);
        $this->assertContains('Manager', $policy->restricted_roles);
        $this->assertTrue($policy->consent_required);
        $this->assertTrue($policy->legal_hold_enabled);
        $this->assertSame('granted', $consent->status);
        $this->assertSame('mobile_webview', $consent->consent_channel);
        $this->assertTrue($job->legal_hold);
        $this->assertSame('blocked', $job->status);
        $this->assertTrue($job->impact_snapshot['requires_approval']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'platform_privacy_policy.created',
            'auditable_id' => $policy->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'platform_data_consent.recorded',
            'auditable_id' => $consent->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'platform_data_retention_job.created',
            'auditable_id' => $job->id,
        ]);

        $this->actingAs($admin)
            ->get('/settings/data-privacy')
            ->assertOk()
            ->assertSeeText('PRIV-EMP-010')
            ->assertSeeText('RET-EMP-010')
            ->assertSeeText('Legal hold');
    }

    public function test_seeded_admin_can_manage_two_factor_and_privileged_action_confirmation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $payrollUser = User::where('email', 'citra@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/two-factor-security')
            ->assertOk()
            ->assertSeeText('2FA & Privileged Action')
            ->assertSeeText('Buat Policy 2FA')
            ->assertSeeText('Enroll User 2FA')
            ->assertSeeText('Konfirmasi Aksi');

        $this->actingAs($admin)
            ->post('/settings/two-factor-security/policies', [
                'company_id' => $company->id,
                'policy_code' => '2FA-HR-093',
                'policy_name' => 'Policy 2FA Admin dan Payroll',
                'enforced_roles' => ['Super Admin', 'HR Admin', 'Payroll Admin'],
                'privileged_actions' => ['payroll_publish', 'role_permission_change', 'privacy_export_approval'],
                'otp_channel' => 'authenticator_app',
                'require_on_login' => 1,
                'require_for_privileged_action' => 1,
                'challenge_ttl_minutes' => 5,
                'remember_device_days' => 0,
                'status' => 'active',
                'notes' => 'Policy step-up untuk COMPLI-093.',
            ])
            ->assertRedirect('/settings/two-factor-security');

        $policy = SecurityTwoFactorPolicy::where('policy_code', '2FA-HR-093')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/two-factor-security/enrollments', [
                'user_id' => $payrollUser->id,
                'security_two_factor_policy_id' => $policy->id,
                'method' => 'authenticator_app',
                'status' => 'enabled',
                'notes' => 'Authenticator payroll admin aktif.',
            ])
            ->assertRedirect('/settings/two-factor-security');

        $this->actingAs($admin)
            ->post('/settings/two-factor-security/confirmations', [
                'user_id' => $payrollUser->id,
                'action_key' => 'payroll_publish',
                'resource_type' => 'payroll_run',
                'resource_reference' => 'RUN-2026-07',
                'confirmation_method' => 'password_and_otp',
                'status' => 'confirmed',
                'notes' => 'Konfirmasi publish payroll dengan OTP.',
            ])
            ->assertRedirect('/settings/two-factor-security');

        $setting = UserTwoFactorSetting::where('user_id', $payrollUser->id)->firstOrFail();
        $confirmation = PrivilegedActionConfirmation::where('resource_reference', 'RUN-2026-07')->firstOrFail();

        $this->assertTrue($policy->require_on_login);
        $this->assertTrue($policy->require_for_privileged_action);
        $this->assertContains('Payroll Admin', $policy->enforced_roles);
        $this->assertContains('payroll_publish', $policy->privileged_actions);
        $this->assertSame('enabled', $setting->status);
        $this->assertNotEmpty($setting->secret_mask);
        $this->assertCount(5, $setting->backup_code_masks);
        $this->assertSame('confirmed', $confirmation->status);
        $this->assertSame('Publish payroll / slip gaji', $confirmation->action_label);
        $this->assertTrue($confirmation->risk_snapshot['requires_2fa']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'security_2fa_policy.created',
            'auditable_id' => $policy->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'security_2fa_user.enrolled',
            'auditable_id' => $setting->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'security_privileged_action.confirmed',
            'auditable_id' => $confirmation->id,
        ]);

        $this->actingAs($admin)
            ->get('/settings/two-factor-security')
            ->assertOk()
            ->assertSeeText('2FA-HR-093')
            ->assertSeeText('RUN-2026-07')
            ->assertSeeText('Confirmed');
    }

    public function test_seeded_admin_can_manage_data_retention_archive_and_legal_hold(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/data-retention-compliance')
            ->assertOk()
            ->assertSeeText('Retensi & Legal Hold')
            ->assertSeeText('Buat Policy Retensi')
            ->assertSeeText('Buat Legal Hold')
            ->assertSeeText('Catat Execution');

        $this->actingAs($admin)
            ->post('/settings/data-retention-compliance/policies', [
                'company_id' => $company->id,
                'policy_code' => 'RET-PAY-095',
                'policy_name' => 'Retensi Payroll dan Pajak BMP',
                'data_domain' => 'payroll',
                'retention_years' => 10,
                'archive_strategy' => 'encrypted_archive',
                'disposal_action' => 'anonymize',
                'requires_approval' => 1,
                'legal_hold_override' => 1,
                'owner_role' => 'Compliance Officer',
                'status' => 'active',
                'notes' => 'Payroll dan bukti pajak mengikuti masa simpan compliance.',
            ])
            ->assertRedirect('/settings/data-retention-compliance');

        $policy = DataRetentionPolicy::where('policy_code', 'RET-PAY-095')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/data-retention-compliance/legal-holds', [
                'data_retention_policy_id' => $policy->id,
                'company_id' => $company->id,
                'case_number' => 'HOLD-095',
                'case_title' => 'Audit payroll tahun 2020',
                'data_domain' => 'payroll',
                'scope_reference' => 'payroll period 2020',
                'reason_category' => 'audit',
                'hold_start_date' => '2026-08-03',
                'hold_end_date' => '2026-12-31',
                'custodian_name' => 'Compliance Officer',
                'approval_status' => 'approved',
                'status' => 'active',
                'notes' => 'Disposal payroll 2020 ditahan sampai audit selesai.',
            ])
            ->assertRedirect('/settings/data-retention-compliance');

        $hold = DataLegalHoldCase::where('case_number', 'HOLD-095')->firstOrFail();

        $this->actingAs($admin)
            ->post('/settings/data-retention-compliance/executions', [
                'data_retention_policy_id' => $policy->id,
                'data_legal_hold_case_id' => $hold->id,
                'company_id' => $company->id,
                'execution_number' => 'EXEC-095',
                'data_domain' => 'payroll',
                'cutoff_date' => '2020-12-31',
                'action_type' => 'anonymize',
                'records_scanned' => 1500,
                'records_affected' => 0,
                'status' => 'blocked_by_legal_hold',
                'notes' => 'Execution tertahan oleh HOLD-095.',
            ])
            ->assertRedirect('/settings/data-retention-compliance');

        $execution = DataRetentionExecutionLog::where('execution_number', 'EXEC-095')->firstOrFail();

        $this->assertSame('encrypted_archive', $policy->archive_strategy);
        $this->assertTrue($policy->requires_approval);
        $this->assertTrue($policy->legal_hold_override);
        $this->assertSame('approved', $hold->approval_status);
        $this->assertSame('active', $hold->status);
        $this->assertSame('blocked_by_legal_hold', $execution->status);
        $this->assertSame(1500, $execution->records_scanned);
        $this->assertSame(0, $execution->records_affected);
        $this->assertTrue($execution->result_snapshot['blocked_by_hold']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'data_retention_policy.created',
            'auditable_id' => $policy->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'data_legal_hold.created',
            'auditable_id' => $hold->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'data_retention_execution.recorded',
            'auditable_id' => $execution->id,
        ]);

        $this->actingAs($admin)
            ->get('/settings/data-retention-compliance')
            ->assertOk()
            ->assertSeeText('RET-PAY-095')
            ->assertSeeText('HOLD-095')
            ->assertSeeText('EXEC-095');
    }

    public function test_seeded_admin_can_manage_holiday_calendar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/attendance/holidays?year=2026')
            ->assertOk()
            ->assertSeeText('Kalender Libur')
            ->assertSeeText('Tambah Kalender Libur');

        $this->actingAs($admin)
            ->post('/attendance/holidays', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Libur Operasional Site',
                'holiday_date' => '2026-08-17',
                'scope' => 'branch',
                'category' => 'regional',
                'region' => 'Morowali',
                'affects_attendance' => 1,
                'affects_leave' => 1,
                'affects_payroll' => 1,
                'notes' => 'Dipakai untuk kalender absensi dan payroll site.',
            ])
            ->assertRedirect('/attendance/holidays?year=2026');

        $holiday = HolidayCalendar::where('name', 'Libur Operasional Site')->firstOrFail();

        $this->assertSame('branch', $holiday->scope);
        $this->assertSame('BMP-SITE-01', $holiday->branch->code);
        $this->assertTrue($holiday->affects_payroll);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'holiday_calendar.created',
            'auditable_id' => $holiday->id,
        ]);
    }

    public function test_seeded_admin_can_run_access_review_campaign(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/settings/access-reviews')
            ->assertOk()
            ->assertSeeText('Access Review')
            ->assertSeeText('Buat Campaign');

        $this->actingAs($admin)
            ->post('/settings/access-reviews', [
                'name' => 'Access Review Juli 2026',
                'period_start' => '2026-07-01',
                'period_end' => '2026-07-31',
                'notes' => 'Review akses awal project HRIS.',
            ])
            ->assertRedirect('/settings/access-reviews');

        $review = AccessReview::where('name', 'Access Review Juli 2026')->firstOrFail();
        $item = AccessReviewItem::where('access_review_id', $review->id)
            ->where('user_email', 'admin@kurmigo.id')
            ->firstOrFail();

        $this->assertSame('launched', $review->status);
        $this->assertSame('pending', $item->review_status);
        $this->assertContains('Akses super admin', $item->risk_flags);

        $this->actingAs($admin)
            ->post("/settings/access-reviews/items/{$item->id}", [
                'review_status' => 'approved',
                'review_note' => 'Akses super admin disetujui untuk setup awal.',
            ])
            ->assertRedirect('/settings/access-reviews');

        $this->assertSame('approved', $item->fresh()->review_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'access_review.launched',
            'auditable_id' => $review->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'access_review.item_reviewed',
            'auditable_id' => $item->id,
        ]);
    }

    public function test_seeded_admin_can_manage_attendance_shift_schedule(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();
        $employee = EmployeeProfile::create([
            'user_id' => User::where('email', 'dimas@kurmigo.id')->firstOrFail()->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'SHIFT-001',
            'full_name' => 'Dimas Shift',
            'employment_status' => 'permanent',
            'position' => 'Operator',
            'department' => 'Operasional',
            'join_date' => '2026-07-01',
            'work_email' => 'dimas.shift@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/shift-schedules')
            ->assertOk()
            ->assertSeeText('Jadwal Shift')
            ->assertSeeText('Buat Pola Shift')
            ->assertSeeText('Pasang Roster Shift');

        $this->actingAs($admin)
            ->post('/attendance/shift-schedules/patterns', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'SHIFT-MALAM',
                'name' => 'Shift Malam Site',
                'shift_type' => 'night',
                'start_time' => '20:00',
                'end_time' => '08:00',
                'break_minutes' => 60,
                'is_cross_day' => 1,
                'planned_hours' => 11,
                'grace_late_minutes' => 10,
                'grace_early_leave_minutes' => 10,
                'notes' => 'Shift malam untuk operasi site.',
            ])
            ->assertRedirect('/attendance/shift-schedules');

        $pattern = AttendanceShiftPattern::where('code', 'SHIFT-MALAM')->firstOrFail();

        $this->actingAs($admin)
            ->post('/attendance/shift-schedules/assignments', [
                'attendance_shift_pattern_id' => $pattern->id,
                'employee_profile_id' => $employee->id,
                'assignment_scope' => 'employee',
                'rotation_cycle' => 'weekly',
                'effective_start' => '2026-07-20',
                'effective_end' => '2026-08-20',
                'work_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'off_days' => ['sat', 'sun'],
                'status' => 'published',
                'notes' => 'Roster rotasi minggu pertama.',
            ])
            ->assertRedirect('/attendance/shift-schedules');

        $assignment = AttendanceShiftAssignment::where('attendance_shift_pattern_id', $pattern->id)->firstOrFail();

        $this->assertTrue($pattern->is_cross_day);
        $this->assertSame('weekly', $assignment->rotation_cycle);
        $this->assertSame(['mon', 'tue', 'wed', 'thu', 'fri'], $assignment->work_days);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_shift_pattern.created',
            'auditable_id' => $pattern->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_shift_assignment.created',
            'auditable_id' => $assignment->id,
        ]);
    }

    public function test_seeded_admin_can_capture_mobile_clock_event_with_device_binding(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();
        $employee = EmployeeProfile::create([
            'user_id' => User::where('email', 'dimas@kurmigo.id')->firstOrFail()->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'CLOCK-EMP-001',
            'full_name' => 'Dimas Clock',
            'employment_status' => 'permanent',
            'position' => 'Operator',
            'department' => 'Operasional',
            'join_date' => '2026-07-01',
            'work_email' => 'dimas.clock@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/clock-events')
            ->assertOk()
            ->assertSeeText('Clock Mobile')
            ->assertSeeText('Catat Clock Event')
            ->assertSeeText('Device Binding');

        $this->actingAs($admin)
            ->post('/attendance/clock-events', [
                'employee_profile_id' => $employee->id,
                'event_number' => 'CLOCK-TEST-001',
                'event_type' => 'clock_in',
                'event_at' => '2026-07-20 08:01:00',
                'source' => 'mobile_webview',
                'latitude' => -6.2000000,
                'longitude' => 106.8166667,
                'gps_accuracy_meter' => 15,
                'location_label' => 'BMP-HO',
                'selfie_reference' => 'selfies/CLOCK-TEST-001.jpg',
                'liveness_score' => 92,
                'device_uid' => 'device-dimas-001',
                'device_name' => 'Android Dimas',
                'platform' => 'webview',
                'app_version' => '1.0.0',
                'bind_device' => 1,
                'status' => 'approved',
                'notes' => 'Clock dari ESS WebView.',
            ])
            ->assertRedirect('/attendance/clock-events');

        $event = AttendanceClockEvent::where('event_number', 'CLOCK-TEST-001')->firstOrFail();
        $device = AttendanceDeviceBinding::where('device_uid', 'device-dimas-001')->firstOrFail();

        $this->assertSame('approved', $event->status);
        $this->assertTrue($event->is_liveness_passed);
        $this->assertTrue($event->is_device_trusted);
        $this->assertSame([], $event->risk_flags);
        $this->assertSame('active', $device->status);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_clock_event.created',
            'auditable_id' => $event->id,
        ]);
    }

    public function test_seeded_admin_can_manage_attendance_geofence_and_location_policy(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $employee = EmployeeProfile::create([
            'user_id' => User::where('email', 'dimas@kurmigo.id')->firstOrFail()->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'GEO-EMP-001',
            'full_name' => 'Dimas Geofence',
            'employment_status' => 'permanent',
            'position' => 'HR Officer',
            'department' => 'Human Capital',
            'join_date' => '2026-07-01',
            'work_email' => 'dimas.geo@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/geofences')
            ->assertOk()
            ->assertSeeText('Geofence Attendance')
            ->assertSeeText('Buat Geofence')
            ->assertSeeText('Buat Policy Lokasi');

        $this->actingAs($admin)
            ->post('/attendance/geofences', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'BMP-HO-GEO',
                'name' => 'Head Office BMP',
                'location_type' => 'office',
                'latitude' => -6.2000000,
                'longitude' => 106.8166667,
                'radius_meter' => 150,
                'address' => 'Jakarta',
                'allow_clock_in' => 1,
                'allow_clock_out' => 1,
                'requires_photo' => 1,
                'requires_liveness' => 1,
                'notes' => 'Geofence kantor pusat.',
            ])
            ->assertRedirect('/attendance/geofences');

        $geofence = AttendanceGeofence::where('code', 'BMP-HO-GEO')->firstOrFail();

        $this->actingAs($admin)
            ->post('/attendance/geofences/policies', [
                'attendance_geofence_id' => $geofence->id,
                'employee_profile_id' => $employee->id,
                'policy_number' => 'GEO-POL-001',
                'policy_type' => 'office',
                'assignment_scope' => 'employee',
                'effective_start' => '2026-07-20',
                'effective_end' => '2026-08-20',
                'approval_status' => 'approved',
                'approved_by' => 'Raka Mahendra',
                'allowed_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'notes' => 'Policy office regular.',
            ])
            ->assertRedirect('/attendance/geofences');

        $policy = AttendanceLocationPolicy::where('policy_number', 'GEO-POL-001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/attendance/geofences?attendance_geofence_id=' . $geofence->id . '&latitude=-6.2000000&longitude=106.8166667')
            ->assertOk()
            ->assertSeeText('Titik clock masuk radius');

        $this->assertSame('approved', $policy->approval_status);
        $this->assertNotNull($policy->approved_at);
        $this->assertSame(['mon', 'tue', 'wed', 'thu', 'fri'], $policy->allowed_days);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_geofence.created',
            'auditable_id' => $geofence->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_location_policy.created',
            'auditable_id' => $policy->id,
        ]);
    }

    public function test_seeded_admin_can_import_biometric_attendance_and_reconcile_anomalies(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-IMPORT-024',
            'full_name' => 'Ayu Import Absensi',
            'nik' => '3174012400010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'join_date' => '2026-07-01',
            'work_email' => 'ayu.import@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/imports')
            ->assertOk()
            ->assertSeeText('Import Absensi')
            ->assertSeeText('Buat Batch Import')
            ->assertSeeText('Tambah Row Import');

        $this->actingAs($admin)
            ->post('/attendance/imports/batches', [
                'batch_number' => 'IMP-ABS-024',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'source_type' => 'biometric_machine',
                'machine_code' => 'BIO-HO-01',
                'machine_name' => 'Fingerprint HO Lobby',
                'import_period_start' => '2026-07-20',
                'import_period_end' => '2026-07-20',
                'file_reference' => 'bio-ho-01-20260720.csv',
                'status' => 'draft',
                'notes' => 'Import awal biometric HO.',
            ])
            ->assertRedirect('/attendance/imports');

        $batch = AttendanceImportBatch::where('batch_number', 'IMP-ABS-024')->firstOrFail();

        $this->actingAs($admin)
            ->post('/attendance/imports/rows', [
                'attendance_import_batch_id' => $batch->id,
                'employee_profile_id' => $employee->id,
                'employee_number' => 'KRM-IMPORT-024',
                'employee_name' => 'Ayu Import Absensi',
                'raw_identifier' => 'KRM-IMPORT-024',
                'event_type' => 'clock_in',
                'event_at' => '2026-07-20 08:00:00',
                'source_device' => 'BIO-HO-01',
                'notes' => 'Clock-in valid dari mesin.',
            ])
            ->assertRedirect('/attendance/imports');

        $this->actingAs($admin)
            ->post('/attendance/imports/rows', [
                'attendance_import_batch_id' => $batch->id,
                'employee_number' => 'UNKNOWN-024',
                'employee_name' => 'Unknown Device User',
                'raw_identifier' => 'PIN-9999',
                'event_type' => 'clock_out',
                'event_at' => '2026-07-20 17:00:00',
                'source_device' => 'BIO-HO-01',
                'notes' => 'Row tidak match karyawan.',
            ])
            ->assertRedirect('/attendance/imports');

        $matched = AttendanceImportRow::where('employee_number', 'KRM-IMPORT-024')->firstOrFail();
        $anomaly = AttendanceImportRow::where('employee_number', 'UNKNOWN-024')->firstOrFail();

        $this->assertSame('matched', $matched->match_status);
        $this->assertSame('needs_review', $anomaly->match_status);
        $this->assertContains('employee_not_found', $anomaly->anomaly_flags);
        $this->assertSame(2, $batch->fresh()->imported_rows);
        $this->assertSame(1, $batch->fresh()->matched_rows);
        $this->assertSame(1, $batch->fresh()->anomaly_rows);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_import_batch.created',
            'auditable_id' => $batch->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_import_row.created',
            'auditable_id' => $matched->id,
        ]);
    }

    public function test_seeded_admin_can_manage_leave_policy_engine(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/leave/policies')
            ->assertOk()
            ->assertSeeText('Kebijakan Cuti')
            ->assertSeeText('Buat Jenis Cuti')
            ->assertSeeText('Aturan Berlaku');

        $this->actingAs($admin)
            ->post('/leave/policies/types', [
                'code' => 'ANNUAL-025',
                'name' => 'Cuti Tahunan Demo',
                'category' => 'annual',
                'unit' => 'day',
                'annual_quota_days' => 12,
                'max_days_per_request' => 5,
                'requires_balance' => 1,
                'is_paid' => 1,
                'approval_required' => 1,
                'carry_forward_allowed' => 1,
                'carry_forward_max_days' => 6,
                'carry_forward_expiry_months' => 3,
                'prorate_on_join' => 1,
                'gender_rule' => 'all',
                'min_service_months' => 3,
                'status' => 'active',
                'description' => 'Policy cuti tahunan dengan carry forward.',
            ])
            ->assertRedirect('/leave/policies');

        $type = LeavePolicyType::where('code', 'ANNUAL-025')->firstOrFail();

        $this->actingAs($admin)
            ->post('/leave/policies/rules', [
                'leave_policy_type_id' => $type->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employment_status' => 'permanent',
                'effective_start' => '2026-01-01',
                'effective_end' => '2026-12-31',
                'accrual_frequency' => 'yearly',
                'accrual_days' => 12,
                'reset_month' => 1,
                'include_holidays' => 0,
                'include_weekends' => 0,
                'status' => 'active',
                'notes' => 'Berlaku untuk HO permanent.',
            ])
            ->assertRedirect('/leave/policies');

        $rule = LeavePolicyRule::where('leave_policy_type_id', $type->id)->firstOrFail();

        $this->assertTrue($type->carry_forward_allowed);
        $this->assertSame('permanent', $rule->employment_status);
        $this->assertSame($branch->id, $rule->branch_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'leave_policy_type.created',
            'auditable_id' => $type->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'leave_policy_rule.created',
            'auditable_id' => $rule->id,
        ]);
    }

    public function test_seeded_admin_can_submit_leave_request_with_balance_simulation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-LEAVE-026',
            'full_name' => 'Dimas Cuti Simulasi',
            'nik' => '3174012600010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas.leave@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $type = LeavePolicyType::create([
            'code' => 'ANNUAL-026',
            'name' => 'Cuti Tahunan Request',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'max_days_per_request' => 5,
            'requires_balance' => true,
            'is_paid' => true,
            'approval_required' => true,
            'carry_forward_allowed' => true,
            'carry_forward_max_days' => 6,
            'carry_forward_expiry_months' => 3,
            'prorate_on_join' => true,
            'gender_rule' => 'all',
            'min_service_months' => 0,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        LeavePolicyRule::create([
            'leave_policy_type_id' => $type->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employment_status' => 'permanent',
            'effective_start' => '2026-01-01',
            'effective_end' => '2026-12-31',
            'accrual_frequency' => 'yearly',
            'accrual_days' => 12,
            'reset_month' => 1,
            'include_holidays' => false,
            'include_weekends' => false,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/leave/requests?employee_profile_id=' . $employee->id . '&leave_policy_type_id=' . $type->id . '&start_date=2026-07-20&end_date=2026-07-21')
            ->assertOk()
            ->assertSeeText('Pengajuan Cuti')
            ->assertSeeText('Hasil Simulasi Saldo')
            ->assertSeeText('Saldo Akhir');

        $this->actingAs($admin)
            ->post('/leave/requests', [
                'request_number' => 'LEAVE-026-001',
                'employee_profile_id' => $employee->id,
                'leave_policy_type_id' => $type->id,
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-21',
                'request_source' => 'ess_webview',
                'approval_status' => 'submitted',
                'current_approver' => 'Manager Operasional',
                'attachment_reference' => 'DOC-LEAVE-026',
                'reason' => 'Keperluan keluarga.',
                'decision_notes' => 'Menunggu approval manager.',
            ])
            ->assertRedirect('/leave/requests');

        $request = LeaveRequest::where('request_number', 'LEAVE-026-001')->firstOrFail();

        $this->assertSame('submitted', $request->approval_status);
        $this->assertEquals(2.0, (float) $request->requested_days);
        $this->assertEquals(12.0, (float) $request->balance_before);
        $this->assertEquals(10.0, (float) $request->balance_after);
        $this->assertSame('ess_webview', $request->request_source);
        $this->assertFalse($request->simulation_snapshot['include_weekends']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'leave_request.created',
            'auditable_id' => $request->id,
        ]);
    }

    public function test_employee_can_submit_own_ess_leave_request_with_balance_simulation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-026',
            'full_name' => 'Dimas ESS Cuti',
            'nik' => '3174012600020001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $otherEmployee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-OTHER-026',
            'full_name' => 'Other Employee',
            'nik' => '3174012600030001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'other.leave@kurmigo.id',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $type = LeavePolicyType::create([
            'code' => 'ESS-ANNUAL-026',
            'name' => 'Cuti Tahunan ESS',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'max_days_per_request' => 5,
            'requires_balance' => true,
            'is_paid' => true,
            'approval_required' => true,
            'carry_forward_max_days' => 0,
            'carry_forward_expiry_months' => 0,
            'gender_rule' => 'all',
            'min_service_months' => 0,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        LeavePolicyRule::create([
            'leave_policy_type_id' => $type->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employment_status' => 'permanent',
            'effective_start' => '2026-01-01',
            'effective_end' => '2026-12-31',
            'accrual_frequency' => 'yearly',
            'accrual_days' => 12,
            'reset_month' => 1,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'OTHER-ESS-026',
            'employee_profile_id' => $otherEmployee->id,
            'leave_policy_type_id' => $type->id,
            'start_date' => '2026-07-20',
            'end_date' => '2026-07-20',
            'requested_days' => 1,
            'balance_before' => 12,
            'balance_after' => 11,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
        ]);

        $this->actingAs($user)
            ->get('/ess/leave-requests?leave_policy_type_id=' . $type->id . '&start_date=2026-07-20&end_date=2026-07-21')
            ->assertOk()
            ->assertSeeText('Cuti & Izin')
            ->assertSeeText('Hasil Simulasi')
            ->assertDontSeeText('OTHER-ESS-026');

        $this->actingAs($user)
            ->post('/ess/leave-requests', [
                'request_number' => 'ESS-LEAVE-026-001',
                'leave_policy_type_id' => $type->id,
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-21',
                'request_source' => 'ess_webview',
                'attachment_reference' => 'ESS-DOC-026',
                'reason' => 'Keperluan keluarga dari ESS.',
            ])
            ->assertRedirect('/ess/leave-requests');

        $request = LeaveRequest::where('request_number', 'ESS-LEAVE-026-001')->firstOrFail();

        $this->assertSame($employee->id, $request->employee_profile_id);
        $this->assertSame('submitted', $request->approval_status);
        $this->assertEquals(2.0, (float) $request->requested_days);
        $this->assertEquals(10.0, (float) $request->balance_after);
        $this->assertSame('Atasan langsung', $request->current_approver);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ess_leave_request.submitted',
            'auditable_id' => $request->id,
        ]);
    }

    public function test_seeded_admin_can_create_overtime_request_with_payroll_handoff(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-OT-027',
            'full_name' => 'Dimas Lembur Payroll',
            'nik' => '3174012700010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas.ot@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/overtime-requests')
            ->assertOk()
            ->assertSeeText('Pengajuan Lembur')
            ->assertSeeText('Buat Pengajuan');

        $this->actingAs($admin)
            ->post('/attendance/overtime-requests', [
                'request_number' => 'OT-027-001',
                'employee_profile_id' => $employee->id,
                'overtime_date' => '2026-07-20',
                'start_at' => '2026-07-20 18:00:00',
                'end_at' => '2026-07-20 21:00:00',
                'overtime_type' => 'regular_day',
                'request_source' => 'web_admin',
                'approval_status' => 'approved',
                'current_approver' => 'Manager Operasional',
                'hourly_rate' => 25000,
                'payroll_component_code' => 'OT',
                'handoff_to_payroll' => 1,
                'reason' => 'Closing produksi.',
                'decision_notes' => 'Approved untuk payroll.',
            ])
            ->assertRedirect('/attendance/overtime-requests');

        $overtime = OvertimeRequest::where('request_number', 'OT-027-001')->firstOrFail();

        $this->assertSame('approved', $overtime->approval_status);
        $this->assertEquals(3.0, (float) $overtime->requested_hours);
        $this->assertEquals(3.0, (float) $overtime->approved_hours);
        $this->assertEquals(1.5, (float) $overtime->payroll_multiplier);
        $this->assertEquals(112500.0, (float) $overtime->estimated_amount);
        $this->assertTrue($overtime->handoff_to_payroll);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'overtime_request.created',
            'auditable_id' => $overtime->id,
        ]);
    }

    public function test_seeded_admin_can_create_attendance_correction_and_generate_clock_event(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-CORR-028',
            'full_name' => 'Ayu Koreksi Absensi',
            'nik' => '3174012800010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'ayu.correction@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/corrections')
            ->assertOk()
            ->assertSeeText('Koreksi Absensi')
            ->assertSeeText('Buat Koreksi');

        $this->actingAs($admin)
            ->post('/attendance/corrections', [
                'correction_number' => 'COR-028-001',
                'employee_profile_id' => $employee->id,
                'attendance_date' => '2026-07-20',
                'correction_type' => 'missing_clock_out',
                'requested_event_at' => '2026-07-20 17:15:00',
                'requested_event_type' => 'clock_out',
                'request_source' => 'web_admin',
                'approval_status' => 'approved',
                'current_approver' => 'Manager Operasional',
                'creates_clock_event' => 1,
                'attachment_reference' => 'DOC-CORR-028',
                'reason' => 'Karyawan lupa clock-out dari mesin.',
                'decision_notes' => 'Disetujui berdasarkan approval manager.',
            ])
            ->assertRedirect('/attendance/corrections');

        $correction = AttendanceCorrection::where('correction_number', 'COR-028-001')->firstOrFail();

        $this->assertSame('approved', $correction->approval_status);
        $this->assertTrue($correction->creates_clock_event);
        $this->assertSame('CORR-COR-028-001', $correction->generated_event_number);
        $this->assertSame('clock_out', $correction->requested_event_type);

        $event = AttendanceClockEvent::where('event_number', 'CORR-COR-028-001')->firstOrFail();

        $this->assertSame($employee->id, $event->employee_profile_id);
        $this->assertSame('approved', $event->status);
        $this->assertContains('attendance_correction', $event->risk_flags);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_correction.created',
            'auditable_id' => $correction->id,
        ]);
    }

    public function test_seeded_admin_can_create_attendance_alert_for_late_and_fatigue_review(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ALERT-030',
            'full_name' => 'Bima Alert Absensi',
            'nik' => '3174013000010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-01',
            'work_email' => 'bima.alert@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $clockEvent = AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLOCK-ALERT-030',
            'event_type' => 'clock_in',
            'event_at' => '2026-07-20 08:35:00',
            'source' => 'mobile_webview',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'gps_accuracy_meter' => 20,
            'location_label' => 'BMP-HO',
            'selfie_reference' => 'selfie-alert-030.jpg',
            'liveness_score' => 92,
            'is_liveness_passed' => true,
            'is_device_trusted' => true,
            'status' => 'approved',
            'risk_flags' => [],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/attendance/alerts')
            ->assertOk()
            ->assertSeeText('Alert Absensi')
            ->assertSeeText('Buat Alert');

        $this->actingAs($admin)
            ->post('/attendance/alerts', [
                'alert_number' => 'ALT-030-001',
                'employee_profile_id' => $employee->id,
                'attendance_clock_event_id' => $clockEvent->id,
                'alert_date' => '2026-07-20',
                'alert_type' => 'late_arrival',
                'severity' => 'high',
                'status' => 'in_review',
                'late_minutes' => 35,
                'worked_hours' => 12.5,
                'fatigue_score' => 78.5,
                'compliance_rule' => 'Grace late 10 menit dan fatigue review di atas 12 jam kerja',
                'owner_name' => 'HR Site',
                'due_date' => '2026-07-21',
                'trigger_reason' => 'Clock-in melewati grace dan jam kerja panjang.',
                'follow_up_notes' => 'Manager diminta review shift dan kondisi karyawan.',
            ])
            ->assertRedirect('/attendance/alerts');

        $alert = AttendanceAlert::where('alert_number', 'ALT-030-001')->firstOrFail();

        $this->assertSame('late_arrival', $alert->alert_type);
        $this->assertSame('high', $alert->severity);
        $this->assertSame('in_review', $alert->status);
        $this->assertSame(35, $alert->late_minutes);
        $this->assertEquals(12.5, (float) $alert->worked_hours);
        $this->assertEquals(78.5, (float) $alert->fatigue_score);
        $this->assertSame($clockEvent->id, $alert->attendance_clock_event_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_alert.created',
            'auditable_id' => $alert->id,
        ]);
    }

    public function test_seeded_admin_can_view_delivery_progress_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        config()->set('database.connections.kanban', config('database.connections.sqlite'));

        $connection = \Illuminate\Support\Facades\DB::connection('kanban');
        $connection->getSchemaBuilder()->create('task_lists', function ($table) {
            $table->id();
            $table->unsignedBigInteger('board_id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedInteger('order')->default(0);
        });
        $connection->getSchemaBuilder()->create('tasks', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('priority')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('task_list_id');
            $table->timestamp('archived_at')->nullable();
        });
        $connection->getSchemaBuilder()->create('wbs_items', function ($table) {
            $table->id();
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('code')->nullable();
            $table->string('title');
        });

        $connection->table('task_lists')->insert(['id' => 1, 'board_id' => 6, 'name' => 'Testing', 'order' => 1]);
        $connection->table('tasks')->insert(['id' => 1, 'title' => '[ADMIN-003] Test delivery page', 'priority' => 'high', 'due_date' => '2026-07-12', 'task_list_id' => 1]);
        $connection->table('wbs_items')->insert(['board_id' => 6, 'task_id' => 1, 'code' => '1.1', 'title' => 'Delivery page']);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($user)
            ->get('/settings/delivery-progress')
            ->assertOk()
            ->assertSee('Progress Delivery')
            ->assertSee('[ADMIN-003] Test delivery page');
    }

    public function test_seeded_admin_can_view_product_roadmap_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        config()->set('database.connections.kanban', config('database.connections.sqlite'));

        $connection = \Illuminate\Support\Facades\DB::connection('kanban');
        $connection->getSchemaBuilder()->create('task_lists', function ($table) {
            $table->id();
            $table->unsignedBigInteger('board_id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedInteger('order')->default(0);
        });
        $connection->getSchemaBuilder()->create('tasks', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('priority')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('progress')->default(0);
            $table->unsignedBigInteger('task_list_id');
            $table->timestamp('archived_at')->nullable();
        });
        $connection->getSchemaBuilder()->create('wbs_items', function ($table) {
            $table->id();
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('code')->nullable();
            $table->string('title');
        });

        $connection->table('task_lists')->insert([
            ['id' => 1, 'board_id' => 6, 'name' => 'Testing', 'order' => 1],
            ['id' => 2, 'board_id' => 6, 'name' => 'Backlog', 'order' => 2],
        ]);
        $connection->table('tasks')->insert([
            ['id' => 1, 'title' => '[ROADMAP-000] Strategi produk HRIS enterprise dan slicing rilis', 'priority' => 'high', 'due_date' => '2026-08-09', 'progress' => 90, 'task_list_id' => 1],
            ['id' => 2, 'title' => '[ATTEND-030] Alert fatigue dan keterlambatan', 'priority' => 'high', 'due_date' => '2026-07-18', 'progress' => 0, 'task_list_id' => 2],
        ]);
        $connection->table('wbs_items')->insert([
            ['board_id' => 6, 'task_id' => 1, 'code' => '200', 'title' => 'Roadmap'],
            ['board_id' => 6, 'task_id' => 2, 'code' => '58', 'title' => 'Attendance alert'],
        ]);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        $this->actingAs($user)
            ->get('/settings/product-roadmap')
            ->assertOk()
            ->assertSee('Product Roadmap')
            ->assertSee('Slicing Rilis 1 Bulan')
            ->assertSee('[ATTEND-030] Alert fatigue');
    }

    public function test_seeded_manager_can_view_team_dashboard_summary(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-051',
            'full_name' => 'Bima Manager Tim',
            'nik' => '3174015100010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Manager Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TEAM-051',
            'full_name' => 'Dimas Team Member',
            'nik' => '3174015100010002',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'birth_date' => now()->addDays(10)->subYears(28)->toDateString(),
            'work_email' => 'dimas.team051@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'ANNUAL-MGR-051',
            'name' => 'Cuti Tahunan',
            'category' => 'annual',
            'annual_quota_days' => 12,
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-MGR-051',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'requested_days' => 1,
            'balance_before' => 12,
            'balance_after' => 11,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Bima Manager Tim',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-MGR-051',
            'employee_profile_id' => $employee->id,
            'overtime_date' => today(),
            'start_at' => now()->setTime(18, 0),
            'end_at' => now()->setTime(20, 0),
            'requested_hours' => 2,
            'overtime_type' => 'regular_day',
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Bima Manager Tim',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLK-MGR-051',
            'event_type' => 'clock_in',
            'event_at' => now()->setTime(7, 55),
            'source' => 'mobile_webview',
            'liveness_score' => 95,
            'is_liveness_passed' => true,
            'is_device_trusted' => true,
            'status' => 'approved',
            'risk_flags' => [],
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/dashboard')
            ->assertOk()
            ->assertSeeText('Dashboard Manager')
            ->assertSeeText('Approval Tim')
            ->assertSeeText('Dimas Team Member')
            ->assertSeeText('LV-MGR-051')
            ->assertSeeText('OT-MGR-051')
            ->assertSeeText('Ulang Tahun Tim');
    }

    public function test_seeded_manager_can_bulk_approve_team_requests_and_delegate_approval(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $delegate = User::where('email', 'ayu@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-052',
            'full_name' => 'Bima Manager Approval',
            'nik' => '3174015200010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Manager Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TEAM-052',
            'full_name' => 'Nadia Approval Team',
            'nik' => '3174015200010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'nadia.team052@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'ANNUAL-MGR-052',
            'name' => 'Cuti Tahunan Approval',
            'category' => 'annual',
            'annual_quota_days' => 12,
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        $leave = LeaveRequest::create([
            'request_number' => 'LV-MGR-052',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'requested_days' => 5,
            'balance_before' => 12,
            'balance_after' => 7,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Bima Manager Approval',
            'submitted_at' => now()->subDays(3),
            'created_by' => $manager->id,
        ]);

        $overtime = OvertimeRequest::create([
            'request_number' => 'OT-MGR-052',
            'employee_profile_id' => $employee->id,
            'overtime_date' => today(),
            'start_at' => now()->setTime(18, 0),
            'end_at' => now()->setTime(21, 0),
            'requested_hours' => 3,
            'estimated_amount' => 350000,
            'overtime_type' => 'regular_day',
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Bima Manager Approval',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        BusinessTripRequest::create([
            'request_number' => 'TRIP-MGR-052',
            'employee_profile_id' => $employee->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'destination_city' => 'Morowali',
            'estimated_budget' => 6500000,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Bima Manager Approval',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/approval-inbox')
            ->assertOk()
            ->assertSeeText('Inbox Approval')
            ->assertSeeText('LV-MGR-052')
            ->assertSeeText('OT-MGR-052')
            ->assertSeeText('TRIP-MGR-052')
            ->assertSeeText('High');

        $this->actingAs($manager)
            ->post('/manager/approval-inbox/bulk', [
                'decision' => 'approved',
                'items' => ['leave:' . $leave->id, 'overtime:' . $overtime->id],
                'decision_notes' => 'Disetujui dari inbox approval massal.',
            ])
            ->assertRedirect('/manager/approval-inbox');

        $this->assertSame('approved', $leave->fresh()->approval_status);
        $this->assertSame('approved', $overtime->fresh()->approval_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_approval.bulk_decision',
        ]);

        $this->actingAs($manager)
            ->post('/manager/approval-inbox/delegations', [
                'delegate_user_id' => $delegate->id,
                'scope_type' => 'all',
                'start_date' => today()->toDateString(),
                'end_date' => today()->addDays(2)->toDateString(),
                'reason' => 'Manager dinas luar kota.',
            ])
            ->assertRedirect('/manager/approval-inbox');

        $delegation = ApprovalDelegation::where('delegator_user_id', $manager->id)->firstOrFail();

        $this->assertSame($delegate->id, $delegation->delegate_user_id);
        $this->assertSame('active', $delegation->status);
    }

    public function test_seeded_manager_can_create_performance_note_and_one_on_one_log(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-054',
            'full_name' => 'Bima Manager Journal',
            'nik' => '3174015400010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Manager Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TEAM-054',
            'full_name' => 'Citra Performance Team',
            'nik' => '3174015400010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'citra.team054@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/performance-journal')
            ->assertOk()
            ->assertSeeText('Jurnal Performa Tim')
            ->assertSeeText('Tambah Catatan Performa')
            ->assertSeeText('Log One-on-One');

        $this->actingAs($manager)
            ->post('/manager/performance-journal/notes', [
                'employee_profile_id' => $employee->id,
                'note_date' => '2026-07-22',
                'category' => 'achievement',
                'sentiment' => 'positive',
                'visibility' => 'hr_visible',
                'title' => 'Closing harian konsisten tepat waktu',
                'note_body' => 'Citra menyelesaikan closing harian tepat waktu selama tiga hari berturut-turut.',
                'tags' => 'discipline,quality',
                'follow_up_date' => '2026-07-29',
                'status' => 'follow_up',
            ])
            ->assertRedirect('/manager/performance-journal');

        $note = ManagerPerformanceNote::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->assertSame($manager->id, $note->manager_user_id);
        $this->assertSame('achievement', $note->category);
        $this->assertSame(['discipline', 'quality'], $note->tags);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_performance_note.created',
            'auditable_id' => $note->id,
        ]);

        $this->actingAs($manager)
            ->post('/manager/performance-journal/one-on-ones', [
                'employee_profile_id' => $employee->id,
                'meeting_date' => '2026-07-22',
                'meeting_type' => 'regular',
                'mood' => 'neutral',
                'topic_summary' => 'Review target mingguan',
                'discussion_notes' => 'Diskusi progress target dan kendala operasional.',
                'employee_feedback' => 'Butuh prioritas yang lebih jelas saat volume tinggi.',
                'manager_commitment' => 'Manager akan bantu prioritas harian.',
                'action_items' => 'buat prioritas pagi,review blocker sore',
                'next_meeting_date' => '2026-07-29',
                'status' => 'follow_up',
            ])
            ->assertRedirect('/manager/performance-journal');

        $log = ManagerOneOnOneLog::where('employee_profile_id', $employee->id)->firstOrFail();

        $this->assertSame($manager->id, $log->manager_user_id);
        $this->assertSame('Review target mingguan', $log->topic_summary);
        $this->assertSame(['buat prioritas pagi', 'review blocker sore'], $log->action_items);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_one_on_one.created',
            'auditable_id' => $log->id,
        ]);
    }

    public function test_seeded_manager_can_create_headcount_plan_and_replacement_requisition(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-055',
            'full_name' => 'Bima Headcount Manager',
            'nik' => '3174015500010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Manager Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TEAM-055',
            'full_name' => 'Dimas Replacement Target',
            'nik' => '3174015500010002',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas.team055@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/headcount-requisitions')
            ->assertOk()
            ->assertSeeText('Headcount & Requisition Tim')
            ->assertSeeText('Ajukan Rencana Headcount')
            ->assertSeeText('Ajukan Requisition Pengganti');

        $this->actingAs($manager)
            ->post('/manager/headcount-requisitions/plans', [
                'plan_number' => 'HC-OPS-055',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'department' => 'Operasional Site',
                'position_title' => 'Staff Operasional Site',
                'request_type' => 'expansion',
                'current_headcount' => 8,
                'requested_headcount' => 2,
                'planning_period' => '2026-07-22',
                'target_join_date' => '2026-08-15',
                'priority' => 'high',
                'budget_estimate' => 13000000,
                'budget_status' => 'needs_finance_review',
                'approval_status' => 'submitted',
                'current_approver' => 'HRBP',
                'business_reason' => 'Volume operasional meningkat dan tim membutuhkan tambahan shift support.',
                'risk_if_not_filled' => 'Overtime meningkat dan SLA operasional berisiko turun.',
            ])
            ->assertRedirect('/manager/headcount-requisitions');

        $plan = ManagerHeadcountPlan::where('plan_number', 'HC-OPS-055')->firstOrFail();

        $this->assertSame($manager->id, $plan->manager_user_id);
        $this->assertSame(2, $plan->requested_headcount);
        $this->assertSame('submitted', $plan->approval_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_headcount_plan.created',
            'auditable_id' => $plan->id,
        ]);

        $this->actingAs($manager)
            ->post('/manager/headcount-requisitions/replacements', [
                'requisition_number' => 'REQ-OPS-055',
                'employee_profile_id' => $employee->id,
                'position_title' => 'Staff Operasional Site',
                'replacement_type' => 'resignation',
                'vacancy_date' => '2026-07-25',
                'target_join_date' => '2026-08-10',
                'employment_type' => 'permanent',
                'priority' => 'critical',
                'approval_status' => 'submitted',
                'current_approver' => 'HRBP',
                'skill_requirements' => 'Excel,HSE dasar,shift operation',
                'replacement_reason' => 'Karyawan existing resign dan posisi perlu segera diganti agar roster tetap aman.',
                'handover_notes' => 'Butuh overlap minimal satu minggu untuk transfer area kerja.',
            ])
            ->assertRedirect('/manager/headcount-requisitions');

        $requisition = ManagerReplacementRequisition::where('requisition_number', 'REQ-OPS-055')->firstOrFail();

        $this->assertSame($manager->id, $requisition->manager_user_id);
        $this->assertSame($employee->id, $requisition->employee_profile_id);
        $this->assertSame(['Excel', 'HSE dasar', 'shift operation'], $requisition->skill_requirements);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_replacement_requisition.created',
            'auditable_id' => $requisition->id,
        ]);
    }

    public function test_seeded_manager_can_request_team_document_and_approve_work_certificate(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-056',
            'full_name' => 'Bima Document Manager',
            'nik' => '3174015600010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Manager Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TEAM-056',
            'full_name' => 'Ayu Document Team',
            'nik' => '3174015600010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'ayu.team056@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/team-documents')
            ->assertOk()
            ->assertSeeText('Dokumen Tim')
            ->assertSeeText('Ajukan Dokumen Tim')
            ->assertSeeText('Approval Surat Keterangan Kerja');

        $this->actingAs($manager)
            ->post('/manager/team-documents/requests', [
                'request_number' => 'DOC-TEAM-056',
                'employee_profile_id' => $employee->id,
                'document_type' => 'contract_copy',
                'purpose' => 'Kebutuhan audit site operasional',
                'needed_date' => '2026-07-26',
                'delivery_channel' => 'portal',
                'approval_status' => 'submitted',
                'current_approver' => 'HR Admin',
                'request_note' => 'Dokumen dibutuhkan untuk audit bulanan site.',
                'hr_note' => 'Menunggu verifikasi arsip.',
            ])
            ->assertRedirect('/manager/team-documents');

        $document = ManagerTeamDocumentRequest::where('request_number', 'DOC-TEAM-056')->firstOrFail();

        $this->assertSame($manager->id, $document->manager_user_id);
        $this->assertSame($employee->id, $document->employee_profile_id);
        $this->assertSame('submitted', $document->approval_status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_team_document_request.created',
            'auditable_id' => $document->id,
        ]);

        $this->actingAs($manager)
            ->post('/manager/team-documents/certificates', [
                'certificate_number' => 'SKK-TEAM-056',
                'employee_profile_id' => $employee->id,
                'certificate_type' => 'employment_statement',
                'recipient_name' => 'Bank Mandiri',
                'recipient_institution' => 'Bank',
                'effective_date' => '2026-07-26',
                'valid_until' => '2026-08-26',
                'approval_status' => 'submitted',
                'manager_decision' => 'approved',
                'certificate_purpose' => 'Pengajuan rekening payroll dan administrasi bank.',
                'decision_note' => 'Disetujui, data jabatan sesuai kondisi kerja.',
            ])
            ->assertRedirect('/manager/team-documents');

        $certificate = ManagerWorkCertificateApproval::where('certificate_number', 'SKK-TEAM-056')->firstOrFail();

        $this->assertSame($manager->id, $certificate->manager_user_id);
        $this->assertSame($employee->id, $certificate->employee_profile_id);
        $this->assertSame('approved', $certificate->manager_decision);
        $this->assertNotNull($certificate->decided_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'manager_work_certificate_approval.created',
            'auditable_id' => $certificate->id,
        ]);
    }

    public function test_seeded_manager_can_review_attendance_exception_with_map_and_photo_evidence(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-057',
            'full_name' => 'Bima Supervisor Review',
            'nik' => '3174015700010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-EXC-057',
            'full_name' => 'Nadia Exception Review',
            'nik' => '3174015700010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'nadia.exc057@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $event = AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLK-EXC-057',
            'event_type' => 'clock_in',
            'event_at' => now()->setTime(8, 15),
            'source' => 'mobile_webview',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'gps_accuracy_meter' => 220,
            'location_label' => 'Area luar geofence',
            'selfie_reference' => 'selfies/CLK-EXC-057.jpg',
            'liveness_score' => 82,
            'is_liveness_passed' => true,
            'is_device_trusted' => false,
            'device_uid' => 'DEV-EXC-057',
            'device_name' => 'Android WebView',
            'status' => 'need_review',
            'review_status' => 'pending',
            'risk_flags' => ['gps_low_accuracy', 'device_not_trusted'],
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/attendance-exceptions')
            ->assertOk()
            ->assertSeeText('Review Pengecualian Absensi')
            ->assertSeeText('Nadia Exception Review')
            ->assertSeeText('CLK-EXC-057')
            ->assertSeeText('gps low accuracy');

        $this->actingAs($manager)
            ->patch('/manager/attendance-exceptions/' . $event->id, [
                'review_decision' => 'approve_exception',
                'review_notes' => 'Foto dan lokasi diterima karena ada tugas luar area.',
            ])
            ->assertRedirect('/manager/attendance-exceptions');

        $event->refresh();

        $this->assertSame('approved', $event->status);
        $this->assertSame('approved', $event->review_status);
        $this->assertSame('approve_exception', $event->review_decision);
        $this->assertSame('Bima Santoso', $event->reviewed_by);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_exception.reviewed',
            'auditable_id' => $event->id,
        ]);
    }

    public function test_seeded_manager_can_view_team_calendar_for_leave_shift_trip_overtime_and_cutoff(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-053',
            'full_name' => 'Bima Manager Calendar',
            'nik' => '3174015300010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TCAL-053',
            'full_name' => 'Nadia Team Calendar',
            'nik' => '3174015300010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'nadia.teamcal053@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'ANNUAL-MGR-053',
            'name' => 'Cuti Tahunan Manager Calendar',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'requires_balance' => true,
            'requires_attachment' => false,
            'approval_required' => true,
            'is_active' => true,
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-MGR-053',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => '2026-07-20',
            'end_date' => '2026-07-21',
            'requested_days' => 2,
            'balance_before' => 12,
            'balance_after' => 10,
            'duration_unit' => 'day',
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        $shiftPattern = AttendanceShiftPattern::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'SH-MGR-053',
            'name' => 'Shift Pagi Manager Calendar',
            'shift_type' => 'regular',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'planned_hours' => 8,
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        AttendanceShiftAssignment::create([
            'attendance_shift_pattern_id' => $shiftPattern->id,
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'assignment_scope' => 'employee',
            'rotation_cycle' => 'fixed',
            'effective_start' => '2026-07-15',
            'effective_end' => '2026-07-31',
            'work_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'off_days' => ['saturday', 'sunday'],
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        BusinessTripRequest::create([
            'request_number' => 'TRIP-MGR-053',
            'employee_profile_id' => $employee->id,
            'start_date' => '2026-07-22',
            'end_date' => '2026-07-23',
            'destination_city' => 'Morowali',
            'trip_type' => 'domestic',
            'transport_mode' => 'Pesawat',
            'estimated_budget' => 1500000,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-MGR-053',
            'employee_profile_id' => $employee->id,
            'overtime_date' => '2026-07-24',
            'start_at' => '2026-07-24 18:00:00',
            'end_at' => '2026-07-24 20:00:00',
            'requested_hours' => 2,
            'approved_hours' => 0,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 150000,
            'overtime_type' => 'regular_day',
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/team-calendar?start_date=2026-07-15&end_date=2026-07-31')
            ->assertOk()
            ->assertSeeText('Kalender Tim')
            ->assertSeeText('Cuti Tahunan Manager Calendar')
            ->assertSeeText('Shift Pagi Manager Calendar')
            ->assertSeeText('Dinas Morowali')
            ->assertSeeText('Lembur regular day')
            ->assertSeeText('Payroll cutoff');
    }

    public function test_seeded_manager_can_view_budget_visibility_for_overtime_claim_and_training(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MBGT-058',
            'full_name' => 'Bima Manager Budget',
            'nik' => '3174015800010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-BGT-058',
            'full_name' => 'Nadia Budget Team',
            'nik' => '3174015800010002',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'nadia.budget058@kurmigo.id',
            'is_active' => true,
            'created_by' => $manager->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-BGT-058',
            'employee_profile_id' => $employee->id,
            'overtime_date' => '2026-07-19',
            'start_at' => '2026-07-19 18:00:00',
            'end_at' => '2026-07-19 21:00:00',
            'requested_hours' => 3,
            'approved_hours' => 3,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 225000,
            'overtime_type' => 'regular_day',
            'request_source' => 'ess_webview',
            'approval_status' => 'approved',
            'submitted_at' => now(),
            'decided_at' => now(),
            'decided_by' => 'Bima Santoso',
            'created_by' => $manager->id,
        ]);

        $course = TrainingCourse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'course_code' => 'TRN-BGT-058',
            'title' => 'Training Budget Safety',
            'category' => 'hse',
            'delivery_mode' => 'classroom',
            'provider' => 'Internal HSE',
            'start_date' => '2026-07-19',
            'end_date' => '2026-07-20',
            'capacity' => 20,
            'status' => 'published',
            'created_by' => $manager->id,
        ]);

        TrainingEnrollment::create([
            'training_course_id' => $course->id,
            'employee_profile_id' => $employee->id,
            'enrollment_number' => 'ENR-BGT-058',
            'enrollment_status' => 'enrolled',
            'attendance_status' => 'scheduled',
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/budget?start_date=2026-07-01&end_date=2026-07-31')
            ->assertOk()
            ->assertSeeText('Budget Tim')
            ->assertSeeText('Lembur')
            ->assertSeeText('Training Budget Safety');

        $this->actingAs($manager)
            ->post('/manager/budget/allocations', [
                'allocation_number' => 'BGT-MGR-058',
                'category' => 'overtime',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'period_start' => '2026-07-01',
                'period_end' => '2026-07-31',
                'allocated_amount' => 1000000,
                'alert_threshold_percent' => 80,
                'status' => 'active',
                'notes' => 'Budget lembur manager bulan Juli.',
            ])
            ->assertRedirect('/manager/budget');

        $this->actingAs($manager)
            ->post('/manager/budget/claims', [
                'claim_number' => 'CLM-MGR-058',
                'employee_profile_id' => $employee->id,
                'claim_date' => '2026-07-19',
                'claim_type' => 'training',
                'amount' => 350000,
                'approval_status' => 'submitted',
                'description' => 'Klaim training safety.',
            ])
            ->assertRedirect('/manager/budget');

        $allocation = ManagerBudgetAllocation::where('allocation_number', 'BGT-MGR-058')->firstOrFail();
        $claim = ManagerClaimRequest::where('claim_number', 'CLM-MGR-058')->firstOrFail();

        $this->assertSame('overtime', $allocation->category);
        $this->assertSame('training', $claim->claim_type);
        $this->assertSame($employee->id, $claim->employee_profile_id);
        $this->assertDatabaseHas('audit_logs', ['event' => 'manager_budget_allocation.created', 'auditable_id' => $allocation->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'manager_claim_request.created', 'auditable_id' => $claim->id]);
    }

    public function test_seeded_admin_can_manage_training_catalog_enrollment_certificate_and_evaluation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TRN-072',
            'full_name' => 'Ayu Peserta Training',
            'nik' => '3174017200010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff HR',
            'department' => 'HR',
            'join_date' => '2026-01-01',
            'work_email' => 'ayu.training072@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/training-learning')
            ->assertOk()
            ->assertSeeText('Learning & Training')
            ->assertSeeText('Buat Katalog Training');

        $this->actingAs($admin)
            ->post('/performance/training-learning/courses', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'course_code' => 'TRN-HSE-072',
                'title' => 'Safety Induction Site',
                'category' => 'hse',
                'delivery_mode' => 'classroom',
                'provider' => 'Internal HSE',
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-21',
                'capacity' => 25,
                'status' => 'published',
                'description' => 'Induksi keselamatan untuk site operasional.',
            ])
            ->assertRedirect('/performance/training-learning');

        $course = TrainingCourse::where('course_code', 'TRN-HSE-072')->firstOrFail();

        $this->actingAs($admin)
            ->post('/performance/training-learning/enrollments', [
                'training_course_id' => $course->id,
                'employee_profile_id' => $employee->id,
                'enrollment_number' => 'ENR-TRN-072',
                'enrollment_status' => 'completed',
                'attendance_status' => 'present',
                'attended_at' => '2026-07-20 09:00:00',
                'certificate_number' => 'CERT-HSE-072',
                'certificate_issued_at' => '2026-07-21 16:00:00',
                'certificate_file_reference' => 'certificates/CERT-HSE-072.pdf',
                'evaluation_score' => 92.5,
                'evaluation_feedback' => 'Peserta lulus dan memahami prosedur dasar HSE.',
                'manager_notes' => 'Eligible untuk penugasan site.',
            ])
            ->assertRedirect('/performance/training-learning');

        $enrollment = TrainingEnrollment::where('enrollment_number', 'ENR-TRN-072')->firstOrFail();

        $this->assertSame($course->id, $enrollment->training_course_id);
        $this->assertSame($employee->id, $enrollment->employee_profile_id);
        $this->assertSame('completed', $enrollment->enrollment_status);
        $this->assertSame('present', $enrollment->attendance_status);
        $this->assertSame('CERT-HSE-072', $enrollment->certificate_number);
        $this->assertEquals(92.5, (float) $enrollment->evaluation_score);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'training_course.created',
            'auditable_id' => $course->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'training_enrollment.created',
            'auditable_id' => $enrollment->id,
        ]);
    }

    public function test_seeded_admin_can_manage_goal_okr_cycle_goal_and_checkin(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-OKR-067',
            'full_name' => 'Ayu OKR Demo',
            'nik' => '3174016700010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'HRIS Specialist',
            'department' => 'Human Capital',
            'join_date' => '2026-01-01',
            'work_email' => 'ayu.okr067@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/goal-okr')
            ->assertOk()
            ->assertSeeText('Goal & OKR')
            ->assertSeeText('Setup Goal/OKR')
            ->assertSeeText('Check-in Progress');

        $this->actingAs($admin)
            ->post('/performance/goal-okr/cycles', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'cycle_code' => 'OKR-Q3-067',
                'cycle_name' => 'OKR Quarter 3 2026',
                'period_type' => 'quarter',
                'period_start' => '2026-07-01',
                'period_end' => '2026-09-30',
                'checkin_deadline' => '2026-08-15',
                'scoring_method' => 'weighted_progress',
                'status' => 'active',
                'calibration_rules' => 'Progress factual, bukti delivery, manager review',
                'description' => 'Siklus OKR Q3 untuk demo HRIS.',
            ])
            ->assertRedirect('/performance/goal-okr');

        $cycle = PerformanceGoalCycle::where('cycle_code', 'OKR-Q3-067')->firstOrFail();
        $this->assertSame(['Progress factual', 'bukti delivery', 'manager review'], $cycle->calibration_rules);

        $this->actingAs($admin)
            ->post('/performance/goal-okr/goals', [
                'performance_goal_cycle_id' => $cycle->id,
                'employee_profile_id' => $employee->id,
                'manager_user_id' => $manager->id,
                'goal_number' => 'GOAL-OKR-067',
                'goal_type' => 'individual',
                'objective_title' => 'Tingkatkan akurasi data payroll',
                'objective_description' => 'Menurunkan selisih data payroll sebelum closing.',
                'key_result_metric' => 'Akurasi payroll minimal 99%',
                'baseline_value' => 80,
                'target_value' => 100,
                'current_value' => 90,
                'weight' => 60,
                'confidence_level' => 'medium',
                'status' => 'active',
                'cascading_snapshot' => 'Company OKR, HR OKR, Payroll OKR',
                'notes' => 'Target dicapai melalui validasi master data dan payroll preview.',
            ])
            ->assertRedirect('/performance/goal-okr');

        $goal = PerformanceGoal::where('goal_number', 'GOAL-OKR-067')->firstOrFail();
        $this->assertSame($employee->id, $goal->employee_profile_id);
        $this->assertEquals(50.0, (float) $goal->progress_percent);
        $this->assertEquals(30.0, (float) $goal->score);

        $this->actingAs($admin)
            ->post('/performance/goal-okr/check-ins', [
                'performance_goal_id' => $goal->id,
                'employee_profile_id' => $employee->id,
                'reviewer_user_id' => $manager->id,
                'checkin_number' => 'CI-OKR-067',
                'checkin_date' => '2026-07-30',
                'new_value' => 96,
                'confidence_level' => 'high',
                'risk_status' => 'on_track',
                'achievement_notes' => 'Payroll preview sudah divalidasi lintas data.',
                'blocker_notes' => 'Menunggu mapping komponen minor.',
                'next_action' => 'Finalisasi mapping komponen dan sample payslip.',
                'status' => 'reviewed',
            ])
            ->assertRedirect('/performance/goal-okr');

        $checkIn = PerformanceGoalCheckIn::where('checkin_number', 'CI-OKR-067')->firstOrFail();
        $goal->refresh();

        $this->assertEquals(80.0, (float) $checkIn->progress_percent);
        $this->assertEquals(80.0, (float) $goal->progress_percent);
        $this->assertEquals(48.0, (float) $goal->score);
        $this->assertSame('on_track', $goal->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_goal_cycle.created', 'auditable_id' => $cycle->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_goal.created', 'auditable_id' => $goal->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_goal_checkin.created', 'auditable_id' => $checkIn->id]);
    }

    public function test_seeded_admin_can_manage_performance_calibration_and_rating_distribution(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-CAL-068',
            'full_name' => 'Citra Calibration Demo',
            'nik' => '3174016800010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'HRIS Analyst',
            'department' => 'Human Capital',
            'join_date' => '2026-01-01',
            'work_email' => 'citra.cal068@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $cycle = PerformanceReviewCycle::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cycle_code' => 'PR-CAL-068',
            'cycle_name' => 'Performance Review Calibration 2026',
            'review_type' => 'manager',
            'period_start' => '2026-07-01',
            'period_end' => '2026-12-31',
            'submission_deadline' => '2026-12-15',
            'status' => 'active',
            'competency_groups' => ['delivery', 'leadership'],
            'description' => 'Demo calibration review.',
            'created_by' => $admin->id,
        ]);

        $form = PerformanceReviewForm::create([
            'performance_review_cycle_id' => $cycle->id,
            'employee_profile_id' => $employee->id,
            'manager_user_id' => $manager->id,
            'form_number' => 'FORM-CAL-068',
            'review_scope' => 'semester',
            'status' => 'in_review',
            'target_score' => 85,
            'final_score' => 82,
            'summary_note' => 'Sebelum kalibrasi.',
            'created_by' => $admin->id,
        ]);

        PerformanceReviewResponse::create([
            'performance_review_form_id' => $form->id,
            'reviewer_user_id' => $manager->id,
            'reviewer_type' => 'manager',
            'score' => 82,
            'competency_scores' => ['delivery' => 84, 'leadership' => 80],
            'strength_feedback' => 'Delivery stabil.',
            'improvement_feedback' => 'Perlu ownership lintas tim.',
            'development_suggestion' => 'Mentoring project.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/calibration')
            ->assertOk()
            ->assertSeeText('Kalibrasi Review')
            ->assertSeeText('Setup Kalibrasi')
            ->assertSeeText('Distribusi Rating');

        $this->actingAs($admin)
            ->post('/performance/calibration/sessions', [
                'performance_review_cycle_id' => $cycle->id,
                'session_number' => 'CAL-068',
                'session_name' => 'Kalibrasi H2 Human Capital',
                'session_date' => '2026-07-30',
                'facilitator_name' => 'Raka Mahendra',
                'calibration_scope' => 'department',
                'target_distribution' => 'balanced',
                'status' => 'active',
                'rating_rules' => 'A:90+, B:80-89, C:70-79',
                'notes' => 'Kalibrasi lintas manager.',
            ])
            ->assertRedirect('/performance/calibration');

        $session = PerformanceCalibrationSession::where('session_number', 'CAL-068')->firstOrFail();
        $this->assertSame(['A:90+', 'B:80-89', 'C:70-79'], $session->rating_rules);

        $this->actingAs($admin)
            ->post('/performance/calibration/items', [
                'performance_calibration_session_id' => $session->id,
                'performance_review_form_id' => $form->id,
                'original_score' => 82,
                'calibrated_score' => 86,
                'original_rating' => 'B',
                'calibrated_rating' => 'B+',
                'rating_bucket' => 'meets',
                'calibration_decision' => 'raise',
                'decision_reason' => 'Evidence goal delivery lebih kuat dibanding skor awal manager.',
                'evidence_snapshot' => 'Goal achievement, manager note, peer feedback',
                'status' => 'reviewed',
            ])
            ->assertRedirect('/performance/calibration');

        $item = PerformanceCalibrationItem::where('performance_calibration_session_id', $session->id)->firstOrFail();
        $form->refresh();

        $this->assertSame($employee->id, $item->employee_profile_id);
        $this->assertSame('raise', $item->calibration_decision);
        $this->assertSame('calibrated', $form->status);
        $this->assertEquals(86.0, (float) $form->final_score);
        $this->assertStringContainsString('B+', $form->summary_note);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_calibration_session.created', 'auditable_id' => $session->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_calibration_item.created', 'auditable_id' => $item->id]);
    }

    public function test_seeded_admin_can_manage_competency_matrix_for_role_grade_and_career_path(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $grade = JobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G-COMP-070'],
            ['name' => 'Specialist', 'level' => 4, 'minimum_salary' => 8000000, 'maximum_salary' => 16000000, 'is_active' => true]
        );

        $position = JobPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-COMP-070'],
            ['job_grade_id' => $grade->id, 'name' => 'HRIS Specialist', 'employment_category' => 'staff', 'headcount_plan' => 2, 'is_active' => true]
        );

        $course = TrainingCourse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'course_code' => 'TRN-COMP-070',
            'title' => 'HRIS Data Quality Workshop',
            'category' => 'technical',
            'delivery_mode' => 'classroom',
            'provider' => 'Internal HRIS',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'capacity' => 20,
            'status' => 'planned',
            'description' => 'Training untuk gap kompetensi data HRIS.',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/competency-matrix')
            ->assertOk()
            ->assertSeeText('Matriks Kompetensi')
            ->assertSeeText('Setup Matriks')
            ->assertSeeText('Mapping Role / Grade');

        $this->actingAs($admin)
            ->post('/performance/competency-matrix/competencies', [
                'company_id' => $company->id,
                'competency_code' => 'COMP-070',
                'competency_name' => 'HRIS Data Accuracy',
                'category' => 'technical',
                'description' => 'Kemampuan menjaga kualitas dan akurasi data HRIS.',
                'proficiency_descriptors' => 'Level 1 basic, Level 3 independent, Level 5 expert',
                'is_core' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect('/performance/competency-matrix');

        $competency = Competency::where('competency_code', 'COMP-070')->firstOrFail();
        $this->assertTrue($competency->is_core);
        $this->assertSame('Level 3 independent', $competency->proficiency_descriptors[1]);

        $this->actingAs($admin)
            ->post('/performance/competency-matrix/requirements', [
                'competency_id' => $competency->id,
                'job_position_id' => $position->id,
                'job_grade_id' => $grade->id,
                'training_course_id' => $course->id,
                'requirement_number' => 'REQ-COMP-070',
                'career_path' => 'HRIS Specialist -> HRIS Lead',
                'required_level' => 3,
                'target_level' => 4,
                'weight' => 80,
                'criticality' => 'high',
                'assessment_method' => 'manager_review',
                'evidence_examples' => 'Data audit, payroll sample, master data correction',
                'development_action' => 'Ikuti workshop data quality dan review hasil audit bulanan.',
                'status' => 'active',
            ])
            ->assertRedirect('/performance/competency-matrix');

        $requirement = CompetencyRoleRequirement::where('requirement_number', 'REQ-COMP-070')->firstOrFail();

        $this->assertSame($position->id, $requirement->job_position_id);
        $this->assertSame($grade->id, $requirement->job_grade_id);
        $this->assertSame($course->id, $requirement->training_course_id);
        $this->assertSame('high', $requirement->criticality);
        $this->assertSame('payroll sample', $requirement->evidence_examples[1]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'competency.created', 'auditable_id' => $competency->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'competency_role_requirement.created', 'auditable_id' => $requirement->id]);
    }

    public function test_seeded_admin_can_manage_individual_development_plan_and_learning_recommendation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-IDP-071',
            'full_name' => 'Ayu IDP Career',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'grade' => 'G4',
            'join_date' => '2026-01-10',
            'work_email' => 'ayu.idp071@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $course = TrainingCourse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'course_code' => 'TRN-IDP-071',
            'title' => 'Supervisor Readiness Program',
            'category' => 'leadership',
            'delivery_mode' => 'hybrid',
            'provider' => 'Internal Academy',
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-05',
            'capacity' => 24,
            'status' => 'published',
            'description' => 'Program readiness untuk calon supervisor.',
            'created_by' => $admin->id,
        ]);

        $competency = Competency::create([
            'company_id' => $company->id,
            'competency_code' => 'COMP-IDP-071',
            'competency_name' => 'Supervisory Leadership',
            'category' => 'leadership',
            'description' => 'Kemampuan memimpin shift dan melakukan coaching.',
            'proficiency_descriptors' => ['Basic', 'Working', 'Advanced'],
            'is_core' => true,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $requirement = CompetencyRoleRequirement::create([
            'competency_id' => $competency->id,
            'training_course_id' => $course->id,
            'requirement_number' => 'REQ-IDP-071',
            'career_path' => 'Staff Operasional -> Supervisor Operasional',
            'required_level' => 2,
            'target_level' => 4,
            'weight' => 90,
            'criticality' => 'high',
            'assessment_method' => 'manager_review',
            'evidence_examples' => ['Coaching log', 'Shift handover', 'Team productivity'],
            'development_action' => 'Ikuti readiness program dan weekly coaching.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/idp')
            ->assertOk()
            ->assertSeeText('Individual Development Plan')
            ->assertSeeText('Buat IDP Karyawan');

        $this->actingAs($admin)
            ->post('/performance/idp', [
                'employee_profile_id' => $employee->id,
                'competency_role_requirement_id' => $requirement->id,
                'training_course_id' => $course->id,
                'plan_number' => 'IDP-071',
                'development_area' => 'Leadership shift supervisor',
                'career_goal' => 'Siap promosi ke Supervisor Operasional',
                'current_level' => 2,
                'target_level' => 4,
                'priority' => 'high',
                'learning_recommendation' => 'Coaching weekly dengan mentor site',
                'action_plan' => 'Ikut readiness program, OJT 30 hari, dan review mingguan.',
                'start_date' => '2026-08-03',
                'target_date' => '2026-09-03',
                'mentor_name' => 'Bima Santoso',
                'reviewer_name' => 'Raka Mahendra',
                'progress_percent' => 25,
                'status' => 'in_progress',
                'notes' => 'Fokus readiness untuk promosi internal.',
            ])
            ->assertRedirect('/performance/idp');

        $plan = IndividualDevelopmentPlan::where('plan_number', 'IDP-071')->firstOrFail();

        $this->assertSame($employee->id, $plan->employee_profile_id);
        $this->assertSame($requirement->id, $plan->competency_role_requirement_id);
        $this->assertSame($course->id, $plan->training_course_id);
        $this->assertSame(2, $plan->current_level);
        $this->assertSame(4, $plan->target_level);
        $this->assertSame(25, $plan->progress_percent);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'individual_development_plan.created',
            'auditable_id' => $plan->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/idp')
            ->assertOk()
            ->assertSeeText('Leadership shift supervisor')
            ->assertSeeText('Supervisor Readiness Program')
            ->assertSeeText('Ayu IDP Career');
    }

    public function test_seeded_admin_can_manage_succession_planning_talent_pool_and_readiness_matrix(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $grade = JobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G-SUCC-073'],
            ['name' => 'Supervisor', 'level' => 5, 'minimum_salary' => 9000000, 'maximum_salary' => 18000000, 'is_active' => true]
        );

        $position = JobPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-SUCC-073'],
            ['job_grade_id' => $grade->id, 'name' => 'Supervisor Operasional Site', 'employment_category' => 'staff', 'headcount_plan' => 2, 'is_active' => true]
        );

        $incumbent = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-INC-073',
            'full_name' => 'Bima Incumbent',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional Site',
            'department' => 'Operasional Site',
            'grade' => 'G5',
            'join_date' => '2025-01-01',
            'work_email' => 'bima.inc073@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $candidateEmployee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TAL-073',
            'full_name' => 'Citra Talent Pool',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Senior Staff Operasional',
            'department' => 'Operasional Site',
            'grade' => 'G4',
            'join_date' => '2025-06-01',
            'work_email' => 'citra.tal073@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $idp = IndividualDevelopmentPlan::create([
            'employee_profile_id' => $candidateEmployee->id,
            'plan_number' => 'IDP-SUCC-073',
            'development_area' => 'Readiness supervisor operasional',
            'career_goal' => 'Promosi ke Supervisor Operasional Site',
            'current_level' => 3,
            'target_level' => 4,
            'priority' => 'high',
            'learning_recommendation' => 'Shadowing incumbent dan coaching bulanan',
            'action_plan' => 'OJT lintas shift dan review readiness.',
            'start_date' => '2026-08-01',
            'target_date' => '2026-10-01',
            'mentor_name' => 'Bima Incumbent',
            'reviewer_name' => 'Raka Mahendra',
            'progress_percent' => 40,
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/succession')
            ->assertOk()
            ->assertSeeText('Succession Planning')
            ->assertSeeText('Buat Succession Plan');

        $this->actingAs($admin)
            ->post('/performance/succession/plans', [
                'job_position_id' => $position->id,
                'incumbent_employee_profile_id' => $incumbent->id,
                'plan_number' => 'SUCC-073',
                'criticality' => 'critical',
                'vacancy_risk' => 'high',
                'target_ready_by' => '2026-10-01',
                'business_impact' => 'Operasional site terganggu jika posisi kosong.',
                'status' => 'active',
                'notes' => 'Posisi wajib punya minimal dua kandidat internal.',
            ])
            ->assertRedirect('/performance/succession');

        $plan = SuccessionPlan::where('plan_number', 'SUCC-073')->firstOrFail();

        $this->assertSame($position->id, $plan->job_position_id);
        $this->assertSame($incumbent->id, $plan->incumbent_employee_profile_id);
        $this->assertSame('critical', $plan->criticality);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'succession_plan.created',
            'auditable_id' => $plan->id,
        ]);

        $this->actingAs($admin)
            ->post('/performance/succession/candidates', [
                'succession_plan_id' => $plan->id,
                'employee_profile_id' => $candidateEmployee->id,
                'individual_development_plan_id' => $idp->id,
                'candidate_number' => 'TAL-073',
                'readiness_level' => 'ready_12_months',
                'readiness_score' => 78,
                'performance_rating' => 'exceeds',
                'potential_rating' => 'high',
                'mobility_preference' => 'cross_site',
                'retention_risk' => 'medium',
                'development_gap' => 'Perlu exposure budgeting dan people handling.',
                'next_action' => 'Lanjut IDP, shadowing incumbent, dan talent committee review.',
                'owner_name' => 'HRBP Site',
                'review_date' => '2026-08-30',
                'status' => 'in_development',
            ])
            ->assertRedirect('/performance/succession');

        $candidate = SuccessionCandidate::where('candidate_number', 'TAL-073')->firstOrFail();

        $this->assertSame($plan->id, $candidate->succession_plan_id);
        $this->assertSame($candidateEmployee->id, $candidate->employee_profile_id);
        $this->assertSame($idp->id, $candidate->individual_development_plan_id);
        $this->assertSame(78, $candidate->readiness_score);
        $this->assertSame('high', $candidate->potential_rating);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'succession_candidate.created',
            'auditable_id' => $candidate->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/succession')
            ->assertOk()
            ->assertSeeText('Supervisor Operasional Site')
            ->assertSeeText('Citra Talent Pool')
            ->assertSeeText('Readiness Matrix');
    }

    public function test_seeded_admin_can_manage_internal_mobility_career_marketplace(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $grade = JobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G-MOB-075'],
            ['name' => 'Lead', 'level' => 5, 'minimum_salary' => 10000000, 'maximum_salary' => 20000000, 'is_active' => true]
        );

        $position = JobPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-MOB-075'],
            ['job_grade_id' => $grade->id, 'name' => 'Lead Operasional Lintas Site', 'employment_category' => 'staff', 'headcount_plan' => 1, 'is_active' => true]
        );

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MOB-075',
            'full_name' => 'Dimas Mobility',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'grade' => 'G4',
            'join_date' => '2025-05-01',
            'work_email' => 'dimas.mob075@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $idp = IndividualDevelopmentPlan::create([
            'employee_profile_id' => $employee->id,
            'plan_number' => 'IDP-MOB-075',
            'development_area' => 'Cross-site leadership',
            'career_goal' => 'Siap movement ke Lead Operasional Lintas Site',
            'current_level' => 3,
            'target_level' => 4,
            'priority' => 'high',
            'learning_recommendation' => 'Project assignment lintas site',
            'action_plan' => 'Shadowing lead dan assignment lintas site.',
            'start_date' => '2026-08-01',
            'target_date' => '2026-09-15',
            'mentor_name' => 'Raka Mahendra',
            'reviewer_name' => 'HRBP Site',
            'progress_percent' => 55,
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $successionPlan = SuccessionPlan::create([
            'job_position_id' => $position->id,
            'plan_number' => 'SUCC-MOB-075',
            'criticality' => 'high',
            'vacancy_risk' => 'medium',
            'target_ready_by' => '2026-09-15',
            'business_impact' => 'Pipeline lead lintas site.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $successionCandidate = SuccessionCandidate::create([
            'succession_plan_id' => $successionPlan->id,
            'employee_profile_id' => $employee->id,
            'individual_development_plan_id' => $idp->id,
            'candidate_number' => 'TAL-MOB-075',
            'readiness_level' => 'ready_6_months',
            'readiness_score' => 82,
            'performance_rating' => 'exceeds',
            'potential_rating' => 'high',
            'mobility_preference' => 'cross_site',
            'retention_risk' => 'low',
            'development_gap' => 'Exposure lintas site.',
            'next_action' => 'Masukkan marketplace opportunity.',
            'owner_name' => 'HRBP Site',
            'review_date' => '2026-08-20',
            'status' => 'validated',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/internal-mobility')
            ->assertOk()
            ->assertSeeText('Career Marketplace')
            ->assertSeeText('Buka Opportunity Internal');

        $this->actingAs($admin)
            ->post('/performance/internal-mobility/opportunities', [
                'job_position_id' => $position->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'opportunity_number' => 'MOB-075',
                'title' => 'Lead Operasional Lintas Site - Internal Mobility',
                'career_track' => 'Operations Leadership',
                'movement_type' => 'promotion',
                'readiness_requirement' => 'ready_6_months',
                'open_slots' => 1,
                'open_date' => '2026-08-01',
                'close_date' => '2026-08-20',
                'eligibility_note' => 'Minimal performance meets, readiness 6 bulan, dan bersedia cross-site.',
                'development_benefit' => 'Exposure multi-site dan leadership scope lebih luas.',
                'owner_name' => 'HRBP Site',
                'status' => 'published',
            ])
            ->assertRedirect('/performance/internal-mobility');

        $opportunity = InternalMobilityOpportunity::where('opportunity_number', 'MOB-075')->firstOrFail();

        $this->assertSame($position->id, $opportunity->job_position_id);
        $this->assertSame('promotion', $opportunity->movement_type);
        $this->assertSame('published', $opportunity->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'internal_mobility_opportunity.created',
            'auditable_id' => $opportunity->id,
        ]);

        $this->actingAs($admin)
            ->post('/performance/internal-mobility/applications', [
                'internal_mobility_opportunity_id' => $opportunity->id,
                'employee_profile_id' => $employee->id,
                'succession_candidate_id' => $successionCandidate->id,
                'individual_development_plan_id' => $idp->id,
                'application_number' => 'MOB-APP-075',
                'source' => 'succession_pool',
                'match_score' => 86,
                'readiness_level' => 'ready_6_months',
                'manager_support' => 'supported',
                'mobility_constraint' => 'Siap cross-site setelah handover 30 hari.',
                'motivation_note' => 'Ingin mengambil scope leadership lintas site.',
                'screening_note' => 'Shortlist untuk komite talent.',
                'status' => 'shortlisted',
                'target_movement_date' => '2026-09-01',
                'owner_name' => 'HRBP Site',
            ])
            ->assertRedirect('/performance/internal-mobility');

        $application = InternalMobilityApplication::where('application_number', 'MOB-APP-075')->firstOrFail();

        $this->assertSame($opportunity->id, $application->internal_mobility_opportunity_id);
        $this->assertSame($employee->id, $application->employee_profile_id);
        $this->assertSame($successionCandidate->id, $application->succession_candidate_id);
        $this->assertSame(86, $application->match_score);
        $this->assertSame('shortlisted', $application->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'internal_mobility_application.created',
            'auditable_id' => $application->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/internal-mobility')
            ->assertOk()
            ->assertSeeText('Lead Operasional Lintas Site - Internal Mobility')
            ->assertSeeText('Dimas Mobility')
            ->assertSeeText('Pipeline Mobilitas Internal');
    }

    public function test_seeded_admin_can_manage_engagement_survey_response_and_action_plan(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ENG-074',
            'full_name' => 'Nadia Engagement',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'grade' => 'G3',
            'join_date' => '2026-01-15',
            'work_email' => 'nadia.eng074@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/engagement')
            ->assertOk()
            ->assertSeeText('Engagement Survey')
            ->assertSeeText('Buat Survey');

        $this->actingAs($admin)
            ->post('/performance/engagement/surveys', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'survey_number' => 'ENG-074',
                'title' => 'Pulse Check Operasional Q3',
                'survey_type' => 'pulse',
                'audience_scope' => 'branch',
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-07',
                'question_bank' => 'Atasan memberi arahan jelas, Beban kerja masih sehat, Saya punya peluang berkembang',
                'is_anonymous' => '1',
                'owner_name' => 'People Team',
                'status' => 'running',
                'notes' => 'Target response rate 80 persen.',
            ])
            ->assertRedirect('/performance/engagement');

        $survey = EngagementSurvey::where('survey_number', 'ENG-074')->firstOrFail();

        $this->assertSame($company->id, $survey->company_id);
        $this->assertTrue($survey->is_anonymous);
        $this->assertSame('Beban kerja masih sehat', $survey->question_bank[1]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'engagement_survey.created',
            'auditable_id' => $survey->id,
        ]);

        $this->actingAs($admin)
            ->post('/performance/engagement/responses', [
                'engagement_survey_id' => $survey->id,
                'employee_profile_id' => $employee->id,
                'response_number' => 'RESP-ENG-074',
                'engagement_score' => 72,
                'enps_score' => 7,
                'sentiment' => 'mixed',
                'dimension_scores' => 'leadership:80, workload:60, career:70',
                'comment' => 'Beban kerja perlu ditata ulang pada shift malam.',
                'status' => 'submitted',
            ])
            ->assertRedirect('/performance/engagement');

        $response = EngagementSurveyResponse::where('response_number', 'RESP-ENG-074')->firstOrFail();

        $this->assertSame($survey->id, $response->engagement_survey_id);
        $this->assertSame($employee->id, $response->employee_profile_id);
        $this->assertSame(72, $response->engagement_score);
        $this->assertSame('workload:60', $response->dimension_scores[1]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'engagement_survey_response.created',
            'auditable_id' => $response->id,
        ]);

        $this->actingAs($admin)
            ->post('/performance/engagement/actions', [
                'engagement_survey_id' => $survey->id,
                'action_number' => 'ACT-ENG-074',
                'focus_area' => 'Workload shift malam',
                'root_cause' => 'Distribusi manpower belum seimbang',
                'action_plan' => 'Review roster, tambah backup shift, dan weekly check-in selama 4 minggu.',
                'owner_name' => 'HRBP Site',
                'target_date' => '2026-08-31',
                'progress_percent' => 20,
                'impact_metric' => 'Skor workload naik ke 75',
                'priority' => 'high',
                'status' => 'in_progress',
                'notes' => 'Follow-up dengan operasional site.',
            ])
            ->assertRedirect('/performance/engagement');

        $action = EngagementActionPlan::where('action_number', 'ACT-ENG-074')->firstOrFail();

        $this->assertSame($survey->id, $action->engagement_survey_id);
        $this->assertSame('high', $action->priority);
        $this->assertSame(20, $action->progress_percent);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'engagement_action_plan.created',
            'auditable_id' => $action->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/engagement')
            ->assertOk()
            ->assertSeeText('Pulse Check Operasional Q3')
            ->assertSeeText('Workload shift malam')
            ->assertSeeText('Monitoring Action Plan');
    }

    public function test_seeded_admin_can_manage_360_feedback_cycle_form_and_response(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-PRF-069',
            'full_name' => 'Ayu Feedback Review',
            'nik' => '3174016900010001',
            'gender' => 'female',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'ayu.perfor069@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $reviewer = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-REV-069',
            'full_name' => 'Bima Peer Reviewer',
            'nik' => '3174016900010002',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'join_date' => '2026-01-01',
            'work_email' => 'bima.reviewer069@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/performance/review-360')
            ->assertOk()
            ->assertSeeText('360 Feedback')
            ->assertSeeText('Buat Siklus Review')
            ->assertSeeText('Submit Response');

        $this->actingAs($admin)
            ->post('/performance/review-360/cycles', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'cycle_code' => 'PR-360-069',
                'cycle_name' => 'Review 360 Semester 2',
                'review_type' => '360',
                'period_start' => '2026-07-01',
                'period_end' => '2026-12-31',
                'submission_deadline' => '2026-12-15',
                'status' => 'active',
                'competency_groups' => 'leadership,collaboration,delivery',
                'description' => 'Siklus review 360 untuk semester 2.',
            ])
            ->assertRedirect('/performance/review-360');

        $cycle = PerformanceReviewCycle::where('cycle_code', 'PR-360-069')->firstOrFail();
        $this->assertSame(['leadership', 'collaboration', 'delivery'], $cycle->competency_groups);

        $this->actingAs($admin)
            ->post('/performance/review-360/forms', [
                'performance_review_cycle_id' => $cycle->id,
                'employee_profile_id' => $employee->id,
                'manager_user_id' => $manager->id,
                'form_number' => 'FORM-360-069',
                'review_scope' => 'semester',
                'status' => 'assigned',
                'target_score' => 85,
                'summary_note' => 'Review semester untuk readiness promosi.',
            ])
            ->assertRedirect('/performance/review-360');

        $form = PerformanceReviewForm::where('form_number', 'FORM-360-069')->firstOrFail();
        $this->assertSame($employee->id, $form->employee_profile_id);

        $this->actingAs($admin)
            ->post('/performance/review-360/responses', [
                'performance_review_form_id' => $form->id,
                'reviewer_user_id' => $manager->id,
                'reviewer_employee_profile_id' => $reviewer->id,
                'reviewer_type' => 'peer',
                'score' => 88,
                'competency_scores' => 'leadership:86,delivery:90',
                'strength_feedback' => 'Konsisten menjaga koordinasi tim dan follow-up pekerjaan.',
                'improvement_feedback' => 'Perlu meningkatkan dokumentasi keputusan operasional.',
                'development_suggestion' => 'Ikut program leadership shift dan coaching komunikasi.',
                'status' => 'submitted',
            ])
            ->assertRedirect('/performance/review-360');

        $response = PerformanceReviewResponse::where('performance_review_form_id', $form->id)->firstOrFail();
        $this->assertSame('peer', $response->reviewer_type);
        $this->assertSame(['leadership' => 86, 'delivery' => 90], $response->competency_scores);
        $this->assertNotNull($response->submitted_at);
        $this->assertSame('88.00', $form->fresh()->final_score);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_review_cycle.created', 'auditable_id' => $cycle->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_review_form.created', 'auditable_id' => $form->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'performance_review_response.created', 'auditable_id' => $response->id]);
    }

    public function test_seeded_admin_can_create_workforce_planning_simulation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/analytics/workforce-planning')
            ->assertOk()
            ->assertSeeText('Workforce Planning')
            ->assertSeeText('Buat Simulasi');

        $this->actingAs($admin)
            ->post('/analytics/workforce-planning', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'simulation_number' => 'WFP-ANL-080',
                'scenario_name' => 'Peak season operasional',
                'department' => 'Operasional',
                'current_headcount' => 10,
                'required_headcount' => 14,
                'hire_headcount' => 2,
                'monthly_salary_budget' => 7000000,
                'overtime_hourly_rate' => 50000,
                'overtime_hours_per_week' => 10,
                'planning_months' => 3,
                'hiring_cost' => 1000000,
                'training_cost' => 1500000,
                'notes' => 'Simulasi peak season untuk kapasitas site.',
            ])
            ->assertRedirect('/analytics/workforce-planning');

        $simulation = WorkforcePlanningSimulation::where('simulation_number', 'WFP-ANL-080')->firstOrFail();

        $this->assertSame(4, $simulation->assumption_snapshot['gap_headcount']);
        $this->assertSame('overtime', $simulation->recommended_scenario);
        $this->assertEquals(94000000, (float) $simulation->hire_scenario_cost);
        $this->assertEquals(25980000, (float) $simulation->overtime_scenario_cost);
        $this->assertEquals(59990000, (float) $simulation->hybrid_scenario_cost);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'workforce_planning_simulation.created',
            'auditable_id' => $simulation->id,
        ]);
    }

    public function test_seeded_admin_can_create_leave_overtime_capacity_prediction(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/analytics/leave-overtime-capacity')
            ->assertOk()
            ->assertSeeText('Prediksi Kapasitas Cuti & Lembur')
            ->assertSeeText('Buat Prediksi');

        $this->actingAs($admin)
            ->post('/analytics/leave-overtime-capacity', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'prediction_number' => 'CAP-ANL-083',
                'prediction_name' => 'Long weekend operasional',
                'department' => 'Operasional',
                'period_start' => '2026-08-03',
                'period_end' => '2026-08-07',
                'planned_headcount' => 10,
                'minimum_required_headcount' => 9,
                'forecast_leave_days' => 6,
                'forecast_overtime_hours' => 12,
                'notes' => 'Forecast cuti long weekend.',
            ])
            ->assertRedirect('/analytics/leave-overtime-capacity');

        $prediction = LeaveOvertimeCapacityPrediction::where('prediction_number', 'CAP-ANL-083')->firstOrFail();

        $this->assertSame('low', $prediction->risk_level);
        $this->assertEquals(50, (float) $prediction->estimated_capacity_days);
        $this->assertEquals(44, (float) $prediction->projected_available_days);
        $this->assertEquals(1, (float) $prediction->capacity_gap_days);
        $this->assertEquals(8, (float) $prediction->overtime_needed_hours);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'leave_overtime_capacity_prediction.created',
            'auditable_id' => $prediction->id,
        ]);
    }

    public function test_seeded_admin_can_scan_attendance_anomalies_for_repeated_late_and_ghost_punch(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANM-079',
            'full_name' => 'Dimas Anomali Absensi',
            'nik' => '3174017900010001',
            'gender' => 'male',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional',
            'join_date' => '2026-01-01',
            'work_email' => 'dimas.anomaly079@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        foreach ([1, 2, 3] as $day) {
            AttendanceClockEvent::create([
                'employee_profile_id' => $employee->id,
                'event_number' => 'CLK-ANM-079-' . $day,
                'event_type' => 'clock_in',
                'event_at' => now()->subDays($day)->setTime(8, 35),
                'source' => 'mobile_webview',
                'latitude' => -6.2,
                'longitude' => 106.8,
                'gps_accuracy_meter' => 30,
                'selfie_reference' => $day === 1 ? null : 'selfies/CLK-ANM-079-' . $day . '.jpg',
                'liveness_score' => $day === 1 ? 55 : 88,
                'is_liveness_passed' => $day !== 1,
                'is_device_trusted' => $day !== 1,
                'device_uid' => 'DEV-ANM-079',
                'device_name' => 'Android WebView',
                'status' => $day === 1 ? 'need_review' : 'approved',
                'risk_flags' => $day === 1 ? ['selfie_missing', 'liveness_low_score', 'device_not_trusted'] : [],
                'created_by' => $admin->id,
            ]);
        }

        $this->actingAs($admin)
            ->get('/analytics/attendance-anomalies')
            ->assertOk()
            ->assertSeeText('Detektor Anomali Absensi');

        $this->actingAs($admin)
            ->post('/analytics/attendance-anomalies/scan', [
                'period_start' => now()->subDays(7)->toDateString(),
                'period_end' => now()->toDateString(),
                'late_after_time' => '08:15',
                'min_repeated_late' => 3,
            ])
            ->assertRedirect('/analytics/attendance-anomalies');

        $this->assertDatabaseHas('attendance_anomaly_detections', [
            'employee_profile_id' => $employee->id,
            'anomaly_type' => 'ghost_punch',
            'severity' => 'critical',
        ]);
        $this->assertDatabaseHas('attendance_anomaly_detections', [
            'employee_profile_id' => $employee->id,
            'anomaly_type' => 'repeated_late',
            'severity' => 'high',
        ]);
        $this->assertGreaterThanOrEqual(4, AttendanceAnomalyDetection::where('employee_profile_id', $employee->id)->count());
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'attendance_anomaly.detected',
        ]);
    }

    public function test_seeded_admin_can_manage_biometric_connector_and_sync_to_attendance_import(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/integrations/biometric-connectors')
            ->assertOk()
            ->assertSeeText('Biometric Connector')
            ->assertSeeText('Tambah Connector');

        $this->actingAs($admin)
            ->post('/integrations/biometric-connectors', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'connector_code' => 'BIO-CON-085',
                'connector_name' => 'Fingerprint Site 01',
                'vendor' => 'zkteco',
                'connection_type' => 'api_pull',
                'endpoint_url' => 'https://vendor.example.test/attendance',
                'auth_mode' => 'api_key',
                'machine_code' => 'BIO-SITE-01',
                'machine_location' => 'Gate Site 01',
                'sync_interval_minutes' => 30,
                'status' => 'active',
                'mapping_employee_field' => 'employee_number',
                'mapping_time_field' => 'event_time',
                'mapping_event_field' => 'event_type',
                'notes' => 'Connector simulasi untuk site operasional.',
            ])
            ->assertRedirect('/integrations/biometric-connectors');

        $connector = BiometricConnector::where('connector_code', 'BIO-CON-085')->firstOrFail();

        $this->assertSame('active', $connector->status);
        $this->assertNotNull($connector->next_sync_at);
        $this->assertSame('employee_number', $connector->mapping_config['employee_field']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'biometric_connector.created',
            'auditable_id' => $connector->id,
        ]);

        $this->actingAs($admin)
            ->post('/integrations/biometric-connectors/' . $connector->id . '/sync', [
                'period_start' => '2026-07-15',
                'period_end' => '2026-07-15',
                'pulled_rows' => 12,
                'matched_rows' => 10,
                'failed_rows' => 2,
            ])
            ->assertRedirect('/integrations/biometric-connectors');

        $log = BiometricSyncLog::where('biometric_connector_id', $connector->id)->firstOrFail();
        $batch = AttendanceImportBatch::where('id', $log->attendance_import_batch_id)->firstOrFail();

        $this->assertSame('partial_success', $log->status);
        $this->assertSame(12, $log->pulled_rows);
        $this->assertSame(10, $log->matched_rows);
        $this->assertSame(2, $log->failed_rows);
        $this->assertSame('api_sync', $batch->source_type);
        $this->assertSame(12, $batch->imported_rows);
        $this->assertSame(2, $batch->anomaly_rows);
        $this->assertSame('validated', $batch->status);
        $this->assertSame('error', $connector->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'biometric_connector.synced',
            'auditable_id' => $log->id,
        ]);
    }

    public function test_seeded_admin_can_configure_local_database_attendance_adapter(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/integrations/biometric-connectors')
            ->assertOk()
            ->assertSeeText('Adapter Database Lokal');

        $this->actingAs($admin)
            ->post('/integrations/biometric-connectors', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'connector_code' => 'BIO-DB-095',
                'connector_name' => 'Attendance Management Local DB',
                'vendor' => 'attendance_management',
                'connection_type' => 'local_db',
                'endpoint_url' => 'LOCAL-ATTENDANCE-PC',
                'auth_mode' => 'db_credentials',
                'agent_token' => 'db-agent-095',
                'agent_config_note' => 'Agent Windows di PC attendance HO',
                'db_driver' => 'mysql',
                'db_host' => '127.0.0.1',
                'db_port' => 3306,
                'db_name' => 'attendance_management',
                'db_table' => 'att_log',
                'db_username' => 'attendance_ro',
                'db_password' => 'secret-local-db',
                'db_query_template' => 'SELECT emp_code, punch_time, punch_state FROM att_log WHERE punch_time BETWEEN :from AND :to',
                'db_employee_column' => 'emp_code',
                'db_time_column' => 'punch_time',
                'db_event_column' => 'punch_state',
                'db_device_column' => 'terminal_sn',
                'db_increment_column' => 'log_id',
                'db_timezone' => 'Asia/Jakarta',
                'db_poll_mode' => 'timestamp_window',
                'machine_code' => 'BIO-DB-HO',
                'machine_location' => 'PC Attendance HO',
                'sync_interval_minutes' => 15,
                'status' => 'active',
                'mapping_employee_field' => 'employee_number',
                'mapping_time_field' => 'punch_time',
                'mapping_event_field' => 'punch_state',
                'notes' => 'Adapter DB lokal untuk attendance management existing.',
            ])
            ->assertRedirect('/integrations/biometric-connectors');

        $connector = BiometricConnector::where('connector_code', 'BIO-DB-095')->firstOrFail();

        $this->assertSame('local_db', $connector->connection_type);
        $this->assertSame('configured', $connector->adapter_status);
        $this->assertSame('local_database', $connector->agent_config['source_mode']);
        $this->assertSame('mysql', $connector->database_adapter_config['driver']);
        $this->assertSame('att_log', $connector->database_adapter_config['table']);
        $this->assertTrue($connector->database_adapter_config['password_saved']);
        $this->assertArrayNotHasKey('db_password', $connector->database_adapter_config);
        $this->assertTrue(Hash::check('db-agent-095', $connector->agent_token_hash));
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'biometric_connector.created',
            'auditable_id' => $connector->id,
        ]);

        $this->actingAs($admin)
            ->post('/integrations/biometric-connectors/' . $connector->id . '/adapter-test', [
                'preview_rows' => 25,
                'matched_rows' => 24,
                'failed_rows' => 1,
                'sample_employee' => 'KRM-0002',
                'sample_time' => '2026-07-26 08:01:00',
                'sample_event' => 'clock_in',
            ])
            ->assertRedirect('/integrations/biometric-connectors');

        $connector->refresh();
        $log = BiometricSyncLog::where('biometric_connector_id', $connector->id)
            ->where('sync_number', 'like', 'ADAPTER-TEST-%')
            ->firstOrFail();

        $this->assertSame('needs_mapping_review', $connector->adapter_status);
        $this->assertSame(25, $connector->adapter_last_preview['preview_rows']);
        $this->assertSame(24, $connector->adapter_last_preview['matched_rows']);
        $this->assertSame('partial_success', $log->status);
        $this->assertSame('KRM-0002', $log->payload_summary['sample']['employee']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'biometric_connector.adapter_tested',
            'auditable_id' => $log->id,
        ]);

        $this->actingAs($admin)
            ->get('/integrations/biometric-connectors')
            ->assertOk()
            ->assertSeeText('Attendance Management Local DB')
            ->assertSeeText('Adapter DB Lokal')
            ->assertSeeText('Catat Preview Adapter');
    }

    public function test_seeded_admin_can_manage_email_calendar_connector_and_queue_event(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $this->actingAs($admin)
            ->get('/integrations/email-calendar')
            ->assertOk()
            ->assertSeeText('Email & Kalender')
            ->assertSeeText('Tambah Connector');

        $this->actingAs($admin)
            ->post('/integrations/email-calendar/connectors', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'connector_code' => 'CAL-INT-087',
                'connector_name' => 'HR Calendar Integration',
                'provider' => 'smtp_ics',
                'connection_type' => 'smtp_ics',
                'sender_email' => 'hr@kurmigo.id',
                'calendar_owner' => 'HR Calendar',
                'endpoint_url' => 'https://calendar.example.test/webhook',
                'auth_mode' => 'smtp_auth',
                'default_timezone' => 'Asia/Jakarta',
                'enabled_modules' => ['interview', 'training', 'leave'],
                'status' => 'active',
                'notes' => 'Connector test email dan kalender.',
            ])
            ->assertRedirect('/integrations/email-calendar');

        $connector = EmailCalendarConnector::where('connector_code', 'CAL-INT-087')->firstOrFail();

        $this->actingAs($admin)
            ->post('/integrations/email-calendar/events', [
                'email_calendar_connector_id' => $connector->id,
                'source_type' => 'manual',
                'event_number' => 'EVT-INT-087',
                'event_title' => 'Manual HR briefing',
                'recipient_name' => 'Ayu Prameswari',
                'recipient_email' => 'ayu@kurmigo.id',
                'scheduled_start' => '2026-08-10 09:00:00',
                'scheduled_end' => '2026-08-10 10:00:00',
                'location_or_link' => 'Meeting Room HR',
                'send_email' => '1',
                'create_calendar_event' => '1',
            ])
            ->assertRedirect('/integrations/email-calendar');

        $event = EmailCalendarEvent::where('event_number', 'EVT-INT-087')->firstOrFail();

        $this->assertSame('queued', $event->email_status);
        $this->assertSame('queued', $event->calendar_status);
        $this->assertSame('manual', $event->source_type);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'email_calendar_event.queued',
            'auditable_id' => $event->id,
        ]);
    }

    public function test_password_reset_flow_can_update_password(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $token = Password::createToken($user);

        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Reset Password');

        $this->get('/reset-password/' . $token . '?email=' . urlencode($user->email))
            ->assertOk()
            ->assertSee('Password Baru');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_employee_can_request_profile_update_and_hr_can_approve_it(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $hrUser = User::where('email', 'ayu@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-043',
            'full_name' => 'Dimas Wirawan',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'work_email' => 'dimas@kurmigo.id',
            'personal_email' => 'old.dimas@example.test',
            'phone' => '0811000000',
            'emergency_contact_name' => 'Kontak Lama',
            'emergency_contact_phone' => '0811222333',
            'address_snapshot' => ['address' => 'Alamat lama'],
            'is_active' => true,
            'created_by' => $hrUser->id,
        ]);

        $employee->bankAccounts()->create([
            'bank_name' => 'Bank Lama',
            'account_number' => '000111222',
            'account_holder_name' => 'Dimas Lama',
            'is_primary' => true,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/profile-updates')
            ->assertOk()
            ->assertSeeText('Update Profil')
            ->assertSeeText('Ajukan Update Profil');

        $this->actingAs($employeeUser)
            ->post('/ess/profile-updates', [
                'request_number' => 'ESS-PROFILE-043',
                'request_source' => 'mobile_webview',
                'personal_email' => 'dimas.new@example.test',
                'phone' => '0812999000',
                'emergency_contact_name' => 'Siti Dimas',
                'emergency_contact_phone' => '0812888777',
                'address' => 'Alamat baru Morowali',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'Dimas Wirawan',
                'evidence_reference' => 'KTP-ESS-043.pdf',
                'employee_note' => 'Update data kontak dan rekening payroll.',
            ])
            ->assertRedirect('/ess/profile-updates');

        $profileRequest = EssProfileUpdateRequest::where('request_number', 'ESS-PROFILE-043')->firstOrFail();
        $this->assertSame('submitted', $profileRequest->approval_status);
        $this->assertSame($employee->id, $profileRequest->employee_profile_id);
        $this->assertSame('0812999000', $profileRequest->requested_snapshot['profile']['phone']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ess_profile_update.submitted',
            'auditable_id' => $profileRequest->id,
        ]);

        $this->actingAs($hrUser)
            ->get('/employees/profile-updates')
            ->assertOk()
            ->assertSeeText('Approval Update Profil')
            ->assertSeeText('ESS-PROFILE-043');

        $this->actingAs($hrUser)
            ->post("/employees/profile-updates/{$profileRequest->id}/review", [
                'approval_status' => 'approved',
                'review_note' => 'Bukti sesuai.',
            ])
            ->assertRedirect('/employees/profile-updates');

        $employee->refresh();
        $bank = EmployeeBankAccount::where('employee_profile_id', $employee->id)->where('is_primary', true)->firstOrFail();

        $this->assertSame('dimas.new@example.test', $employee->personal_email);
        $this->assertSame('0812999000', $employee->phone);
        $this->assertSame('Alamat baru Morowali', $employee->address_snapshot['address']);
        $this->assertSame('BCA', $bank->bank_name);
        $this->assertSame('1234567890', $bank->account_number);
        $this->assertDatabaseHas('ess_profile_update_requests', [
            'request_number' => 'ESS-PROFILE-043',
            'approval_status' => 'approved',
            'reviewed_by' => $hrUser->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ess_profile_update.reviewed',
            'auditable_id' => $profileRequest->id,
        ]);
    }

    public function test_employee_can_submit_ess_reimbursement_with_camera_receipt(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $managerUser = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $managerUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MGR-046',
            'full_name' => 'Bima Santoso',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-046',
            'full_name' => 'Dimas Wirawan',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/reimbursements')
            ->assertOk()
            ->assertSeeText('Klaim Reimbursement')
            ->assertSeeText('Ajukan Klaim');

        $this->actingAs($employeeUser)
            ->post('/ess/reimbursements', [
                'claim_number' => 'ESS-REIMB-046',
                'claim_date' => '2026-07-21',
                'claim_type' => 'meal',
                'merchant_name' => 'Restoran Site',
                'amount' => 125000,
                'attachment_reference' => 'camera://receipt/ESS-REIMB-046.jpg',
                'receipt_capture_method' => 'camera',
                'receipt_captured_at' => '2026-07-21 12:30:00',
                'description' => 'Makan siang meeting operasional.',
            ])
            ->assertRedirect('/ess/reimbursements');

        $claim = ManagerClaimRequest::where('claim_number', 'ESS-REIMB-046')->firstOrFail();

        $this->assertSame($employee->id, $claim->employee_profile_id);
        $this->assertSame('ess_webview', $claim->request_source);
        $this->assertSame('submitted', $claim->approval_status);
        $this->assertSame('camera', $claim->receipt_capture_method);
        $this->assertSame('camera://receipt/ESS-REIMB-046.jpg', $claim->attachment_reference);
        $this->assertFalse($claim->receipt_metadata['requires_original_check']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ess_reimbursement.submitted',
            'auditable_id' => $claim->id,
        ]);

        $this->actingAs($managerUser)
            ->get('/manager/budget?start_date=2026-07-01&end_date=2026-07-31')
            ->assertOk()
            ->assertSeeText('ESS-REIMB-046')
            ->assertSeeText('Klaim');
    }

    public function test_employee_can_submit_hr_helpdesk_ticket_and_hr_can_reply(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $adminUser = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-047',
            'full_name' => 'Dimas Wirawan',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/helpdesk')
            ->assertOk()
            ->assertSeeText('Helpdesk HR')
            ->assertSeeText('Buat Tiket');

        $this->actingAs($employeeUser)
            ->post('/ess/helpdesk', [
                'ticket_number' => 'HRD-HELP-047',
                'category' => 'absensi',
                'priority' => 'high',
                'subject' => 'Clock-in tidak masuk',
                'description' => 'Clock-in tanggal 21 Juli tidak masuk di ESS.',
                'attachment_reference' => 'camera://helpdesk/HRD-HELP-047.jpg',
            ])
            ->assertRedirect('/ess/helpdesk');

        $ticket = HrHelpdeskTicket::where('ticket_number', 'HRD-HELP-047')->firstOrFail();

        $this->assertSame($employee->id, $ticket->employee_profile_id);
        $this->assertSame('submitted', $ticket->status);
        $this->assertSame('high', $ticket->priority);
        $this->assertNotNull($ticket->first_response_due_at);
        $this->assertNotNull($ticket->sla_due_at);
        $this->assertDatabaseHas('hr_helpdesk_messages', [
            'hr_helpdesk_ticket_id' => $ticket->id,
            'sender_type' => 'employee',
            'message_body' => 'Clock-in tanggal 21 Juli tidak masuk di ESS.',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'hr_helpdesk_ticket.submitted',
            'auditable_id' => $ticket->id,
        ]);

        $this->actingAs($adminUser)
            ->get('/employees/helpdesk')
            ->assertOk()
            ->assertSeeText('HRD-HELP-047')
            ->assertSeeText('Clock-in tidak masuk');

        $this->actingAs($adminUser)
            ->post("/employees/helpdesk/{$ticket->id}", [
                'status' => 'waiting_employee',
                'assigned_to' => $adminUser->id,
                'hr_reply' => 'Tolong kirim screenshot lokasi clock-in.',
                'internal_note' => 'Cek log biometric jika karyawan sudah kirim bukti.',
            ])
            ->assertRedirect('/employees/helpdesk');

        $ticket->refresh();

        $this->assertSame('waiting_employee', $ticket->status);
        $this->assertSame($adminUser->id, $ticket->assigned_to);
        $this->assertNotNull($ticket->responded_at);
        $this->assertDatabaseHas('hr_helpdesk_messages', [
            'hr_helpdesk_ticket_id' => $ticket->id,
            'sender_type' => 'hr',
            'message_body' => 'Tolong kirim screenshot lokasi clock-in.',
            'is_internal' => false,
        ]);
        $this->assertDatabaseHas('hr_helpdesk_messages', [
            'hr_helpdesk_ticket_id' => $ticket->id,
            'sender_type' => 'hr',
            'message_body' => 'Cek log biometric jika karyawan sudah kirim bukti.',
            'is_internal' => true,
        ]);

        $this->actingAs($employeeUser)
            ->post("/ess/helpdesk/{$ticket->id}/reply", [
                'message_body' => 'Screenshot sudah saya lampirkan.',
                'attachment_reference' => 'camera://helpdesk/HRD-HELP-047-followup.jpg',
            ])
            ->assertRedirect('/ess/helpdesk');

        $ticket->refresh();

        $this->assertSame('in_progress', $ticket->status);
        $this->assertSame(4, HrHelpdeskMessage::where('hr_helpdesk_ticket_id', $ticket->id)->count());
    }

    public function test_seeded_admin_can_manage_whatsapp_gateway_queue_and_webhook(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $this->actingAs($admin)
            ->get('/integrations/whatsapp-gateway')
            ->assertOk()
            ->assertSeeText('WhatsApp Gateway')
            ->assertSeeText('Tambah Gateway');

        $this->actingAs($admin)
            ->post('/integrations/whatsapp-gateway/settings', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'gateway_code' => 'WA-HO-086',
                'gateway_name' => 'WhatsApp HR HO',
                'provider' => 'generic_webhook',
                'connection_type' => 'webhook_queue',
                'endpoint_url' => 'https://wa-gateway.example/send',
                'sender_id' => '6281200000000',
                'auth_mode' => 'api_key',
                'status' => 'active',
                'enabled_events' => ['approval_request', 'approval_reminder', 'payslip_published'],
                'masked_token' => 'wa_xxx_****',
                'notes' => 'Gateway smoke HRIS.',
            ])
            ->assertRedirect('/integrations/whatsapp-gateway');

        $gateway = WhatsappGatewaySetting::where('gateway_code', 'WA-HO-086')->firstOrFail();

        $this->assertSame('active', $gateway->status);
        $this->assertContains('payslip_published', $gateway->enabled_events);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'whatsapp_gateway.created',
            'auditable_id' => $gateway->id,
        ]);

        $this->actingAs($admin)
            ->post('/integrations/whatsapp-gateway/messages', [
                'whatsapp_gateway_setting_id' => $gateway->id,
                'message_number' => 'WA-MSG-086',
                'event_key' => 'payslip_published',
                'source_type' => 'payslip',
                'source_id' => 86,
                'recipient_name' => 'Dimas Wirawan',
                'recipient_phone' => '6281234567890',
                'template_name' => 'payslip_publish',
                'message_body' => 'Slip gaji periode Juli sudah tersedia di ESS.',
                'priority' => 'high',
                'scheduled_at' => '2026-07-21 08:00:00',
            ])
            ->assertRedirect('/integrations/whatsapp-gateway');

        $message = WhatsappMessageQueue::where('message_number', 'WA-MSG-086')->firstOrFail();

        $this->assertSame('queued', $message->status);
        $this->assertSame('payslip_published', $message->event_key);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'whatsapp_message.queued',
            'auditable_id' => $message->id,
        ]);

        $this->actingAs($admin)
            ->post("/integrations/whatsapp-gateway/messages/{$message->id}/webhook", [
                'delivery_status' => 'delivered',
                'provider_message_id' => 'PROV-WA-086',
            ])
            ->assertRedirect('/integrations/whatsapp-gateway');

        $message->refresh();

        $this->assertSame('delivered', $message->status);
        $this->assertSame(1, $message->attempt_count);
        $this->assertSame('PROV-WA-086', $message->provider_response['provider_message_id']);
        $this->assertSame(1, WhatsappWebhookLog::where('whatsapp_message_queue_id', $message->id)->count());
    }

    public function test_seeded_admin_can_manage_external_workforce_vendor_and_contract(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $costCenter = CostCenter::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'EXT-OPS-096',
            'name' => 'External Workforce Operation',
            'owner_name' => 'HR Operation',
            'is_billable' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/vendors')
            ->assertOk()
            ->assertSeeText('Vendor Outsourcing')
            ->assertSeeText('Tambah Vendor')
            ->assertSeeText('Tambah Kontrak Jasa SDM');

        $this->actingAs($admin)
            ->post('/external-workforce/vendors', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'vendor_code' => 'VND-EXT-096',
                'vendor_name' => 'PT Mitra Tenaga Site',
                'vendor_type' => 'outsourcing',
                'npwp' => '09.096.096.0-096.000',
                'nib' => '9120009600960',
                'pic_name' => 'Hasan Vendor',
                'pic_phone' => '0812096096',
                'pic_email' => 'pic.vendor096@example.test',
                'address' => 'Kantor vendor Jakarta',
                'has_npwp' => '1',
                'has_nib' => '1',
                'has_service_agreement' => '1',
                'has_hse_commitment' => '1',
                'status' => 'active',
                'notes' => 'Vendor outsourcing untuk site operasional.',
            ])
            ->assertRedirect('/external-workforce/vendors');

        $vendor = ExternalWorkforceVendor::where('vendor_code', 'VND-EXT-096')->firstOrFail();

        $this->assertSame('active', $vendor->status);
        $this->assertSame('outsourcing', $vendor->vendor_type);
        $this->assertTrue($vendor->compliance_snapshot['service_agreement']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_vendor.created',
            'auditable_id' => $vendor->id,
        ]);

        $this->actingAs($admin)
            ->post('/external-workforce/contracts', [
                'external_workforce_vendor_id' => $vendor->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'cost_center_id' => $costCenter->id,
                'contract_number' => 'OS/BMP/2026/096',
                'contract_title' => 'Kontrak helper site HO',
                'workforce_scope' => 'outsourced_manpower',
                'worker_type_scope' => 'Helper, security, cleaning',
                'start_date' => '2026-07-25',
                'end_date' => '2027-07-24',
                'renewal_reminder_date' => '2027-06-10',
                'headcount_quota' => 35,
                'monthly_service_fee' => 3500000,
                'daily_rate' => 175000,
                'overtime_rate' => 25000,
                'sla_summary' => 'Replacement maksimal 2 hari, absensi harian, invoice bulanan.',
                'document_reference' => 'DOC-OS-096',
                'location_scope_note' => 'Head Office dan area gudang.',
                'tax_responsibility' => 'vendor',
                'bpjs_responsibility' => 'vendor',
                'hse_responsibility' => 'shared',
                'access_responsibility' => 'company',
                'status' => 'active',
                'notes' => 'Dasar untuk pencatatan worker outsource berikutnya.',
            ])
            ->assertRedirect('/external-workforce/vendors');

        $contract = ExternalWorkforceContract::where('contract_number', 'OS/BMP/2026/096')->firstOrFail();

        $this->assertSame($vendor->id, $contract->external_workforce_vendor_id);
        $this->assertSame(35, $contract->headcount_quota);
        $this->assertSame('vendor', $contract->responsibility_matrix['tax']);
        $this->assertSame('company', $contract->responsibility_matrix['access_area']);
        $this->assertSame('Head Office dan area gudang.', $contract->location_scope['note']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_contract.created',
            'auditable_id' => $contract->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/vendors')
            ->assertOk()
            ->assertSeeText('PT Mitra Tenaga Site')
            ->assertSeeText('Kontrak helper site HO')
            ->assertSeeText('Dokumen Kurang');
    }

    public function test_seeded_admin_can_manage_external_workforce_compliance_register(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $vendor = ExternalWorkforceVendor::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_code' => 'VND-COMP-101',
            'vendor_name' => 'PT Compliance Vendor',
            'vendor_type' => 'outsourcing',
            'status' => 'active',
            'compliance_snapshot' => ['npwp' => true, 'nib' => true, 'service_agreement' => true],
            'created_by' => $admin->id,
        ]);

        $contract = ExternalWorkforceContract::create([
            'external_workforce_vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'contract_number' => 'OS/BMP/2026/101',
            'contract_title' => 'Kontrak compliance outsourcing',
            'workforce_scope' => 'outsourced_manpower',
            'worker_type_scope' => 'Helper',
            'start_date' => '2026-07-30',
            'end_date' => '2027-07-29',
            'headcount_quota' => 30,
            'responsibility_matrix' => ['tax' => 'vendor', 'bpjs' => 'vendor', 'hse' => 'shared', 'access_area' => 'company'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/compliance')
            ->assertOk()
            ->assertSeeText('Compliance Outsourcing')
            ->assertSeeText('Tambah Item Compliance');

        $this->actingAs($admin)
            ->post('/external-workforce/compliance', [
                'external_workforce_vendor_id' => $vendor->id,
                'external_workforce_contract_id' => $contract->id,
                'item_number' => 'EXT-COMP-101',
                'compliance_area' => 'bpjs',
                'requirement_name' => 'Bukti pembayaran BPJS tenaga outsource Juli 2026',
                'responsible_party' => 'vendor',
                'document_reference' => 'BPJS-VND-101',
                'issued_date' => '2026-07-30',
                'expiry_date' => '2026-08-30',
                'verification_status' => 'submitted',
                'risk_level' => 'high',
                'owner_name' => 'HR Ops',
                'next_follow_up_date' => '2026-08-05',
                'notes' => 'Menunggu validasi bukti iuran dari vendor.',
            ])
            ->assertRedirect('/external-workforce/compliance');

        $item = ExternalWorkforceComplianceItem::where('item_number', 'EXT-COMP-101')->firstOrFail();

        $this->assertSame($vendor->id, $item->external_workforce_vendor_id);
        $this->assertSame($contract->id, $item->external_workforce_contract_id);
        $this->assertSame('bpjs', $item->compliance_area);
        $this->assertSame('vendor', $item->responsible_party);
        $this->assertSame('submitted', $item->verification_status);
        $this->assertSame('high', $item->risk_level);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_compliance.created',
            'auditable_id' => $item->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/compliance')
            ->assertOk()
            ->assertSeeText('Bukti pembayaran BPJS tenaga outsource Juli 2026')
            ->assertSeeText('PT Compliance Vendor')
            ->assertSeeText('High');
    }

    public function test_seeded_admin_can_manage_external_workforce_worker_master(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $costCenter = CostCenter::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'EXT-WRK-097',
            'name' => 'External Worker Cost Center',
            'owner_name' => 'HR Operation',
            'is_billable' => true,
            'is_active' => true,
        ]);

        $vendor = ExternalWorkforceVendor::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_code' => 'VND-WRK-097',
            'vendor_name' => 'PT Worker Vendor',
            'vendor_type' => 'outsourcing',
            'status' => 'active',
            'compliance_snapshot' => ['npwp' => true, 'nib' => true],
            'created_by' => $admin->id,
        ]);

        $contract = ExternalWorkforceContract::create([
            'external_workforce_vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'contract_number' => 'OS/BMP/2026/097',
            'contract_title' => 'Kontrak worker outsource',
            'workforce_scope' => 'outsourced_manpower',
            'worker_type_scope' => 'Helper',
            'start_date' => '2026-07-26',
            'end_date' => '2027-07-25',
            'headcount_quota' => 20,
            'daily_rate' => 180000,
            'responsibility_matrix' => ['tax' => 'vendor', 'bpjs' => 'vendor', 'hse' => 'shared', 'access_area' => 'company'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/workers')
            ->assertOk()
            ->assertSeeText('Worker Non-Employee')
            ->assertSeeText('Tambah Worker');

        $this->actingAs($admin)
            ->post('/external-workforce/workers', [
                'external_workforce_contract_id' => $contract->id,
                'worker_number' => 'EXT-WRK-0001',
                'full_name' => 'Rudi Outsource',
                'worker_type' => 'outsource',
                'identity_number' => '3174099700000001',
                'phone' => '0812970001',
                'position_title' => 'Helper Site',
                'supervisor_name' => 'Supervisor HO',
                'start_date' => '2026-07-26',
                'payment_handling' => 'vendor_invoice',
                'daily_rate' => 0,
                'piece_rate' => 0,
                'has_ktp' => '1',
                'has_vendor_contract' => '1',
                'has_hse_induction' => '1',
                'has_area_access' => '1',
                'access_area' => 'Gudang HO',
                'access_valid_until' => '2027-07-25',
                'status' => 'active',
                'notes' => 'Worker outsource untuk mandays dan invoice vendor.',
            ])
            ->assertRedirect('/external-workforce/workers');

        $worker = ExternalWorkforceWorker::where('worker_number', 'EXT-WRK-0001')->firstOrFail();

        $this->assertSame($vendor->id, $worker->external_workforce_vendor_id);
        $this->assertSame($contract->id, $worker->external_workforce_contract_id);
        $this->assertSame($company->id, $worker->company_id);
        $this->assertSame('vendor_invoice', $worker->payment_handling);
        $this->assertSame(180000.0, (float) $worker->daily_rate);
        $this->assertTrue($worker->document_status['hse_induction']);
        $this->assertSame('Gudang HO', $worker->access_scope['area']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_worker.created',
            'auditable_id' => $worker->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/workers?worker_type=outsource&status=active')
            ->assertOk()
            ->assertSeeText('Rudi Outsource')
            ->assertSeeText('Vendor invoice');
    }

    public function test_seeded_admin_can_record_external_workforce_attendance_mandays_and_approval(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $costCenter = CostCenter::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'EXT-ATT-098',
            'name' => 'External Attendance Cost Center',
            'owner_name' => 'HR Operation',
            'is_billable' => true,
            'is_active' => true,
        ]);

        $vendor = ExternalWorkforceVendor::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_code' => 'VND-ATT-098',
            'vendor_name' => 'PT Attendance Vendor',
            'vendor_type' => 'outsourcing',
            'status' => 'active',
            'compliance_snapshot' => ['npwp' => true, 'nib' => true],
            'created_by' => $admin->id,
        ]);

        $contract = ExternalWorkforceContract::create([
            'external_workforce_vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'contract_number' => 'OS/BMP/2026/098',
            'contract_title' => 'Kontrak attendance harian',
            'workforce_scope' => 'outsourced_manpower',
            'worker_type_scope' => 'Harian dan helper',
            'start_date' => '2026-07-27',
            'end_date' => '2027-07-26',
            'headcount_quota' => 15,
            'daily_rate' => 190000,
            'overtime_rate' => 30000,
            'responsibility_matrix' => ['tax' => 'vendor', 'bpjs' => 'vendor', 'hse' => 'shared', 'access_area' => 'company'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $worker = ExternalWorkforceWorker::create([
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'worker_number' => 'EXT-ATT-WRK-098',
            'full_name' => 'Dedi Mandays',
            'worker_type' => 'daily_worker',
            'position_title' => 'Helper Harian',
            'supervisor_name' => 'Supervisor HO',
            'start_date' => '2026-07-27',
            'payment_handling' => 'vendor_invoice',
            'daily_rate' => 190000,
            'piece_rate' => 0,
            'document_status' => ['ktp' => true, 'vendor_contract' => true, 'hse_induction' => true, 'area_access' => true],
            'access_scope' => ['area' => 'Gudang HO', 'valid_until' => '2027-07-26'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/attendance')
            ->assertOk()
            ->assertSeeText('Attendance & Mandays')
            ->assertSeeText('Tambah Attendance')
            ->assertSeeText('Approval & Handoff');

        $this->actingAs($admin)
            ->post('/external-workforce/attendance', [
                'external_workforce_worker_id' => $worker->id,
                'attendance_number' => 'EXT-ATT-098',
                'attendance_date' => '2026-07-27',
                'shift_code' => 'SHIFT-DAY',
                'clock_in_at' => '2026-07-27 08:00:00',
                'clock_out_at' => '2026-07-27 17:00:00',
                'attendance_status' => 'present',
                'mandays' => 1,
                'overtime_hours' => 2,
                'daily_rate' => 0,
                'overtime_rate' => 0,
                'approval_status' => 'submitted',
                'source_type' => 'manual',
                'evidence_reference' => 'TIMESHEET-098',
                'notes' => 'Masuk penuh dan lembur dua jam.',
            ])
            ->assertRedirect('/external-workforce/attendance');

        $attendance = ExternalWorkforceAttendance::where('attendance_number', 'EXT-ATT-098')->firstOrFail();

        $this->assertSame($worker->id, $attendance->external_workforce_worker_id);
        $this->assertSame($vendor->id, $attendance->external_workforce_vendor_id);
        $this->assertSame($contract->id, $attendance->external_workforce_contract_id);
        $this->assertSame('submitted', $attendance->approval_status);
        $this->assertSame('vendor_invoice', $attendance->handoff_target);
        $this->assertSame('pending', $attendance->handoff_status);
        $this->assertSame(250000.0, (float) $attendance->gross_amount);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_attendance.created',
            'auditable_id' => $attendance->id,
        ]);

        $this->actingAs($admin)
            ->post("/external-workforce/attendance/{$attendance->id}/review", [
                'decision' => 'approved',
                'approved_by' => 'Supervisor HO',
                'notes' => 'Approved untuk invoice vendor.',
            ])
            ->assertRedirect('/external-workforce/attendance');

        $attendance->refresh();

        $this->assertSame('approved', $attendance->approval_status);
        $this->assertSame('ready', $attendance->handoff_status);
        $this->assertNotNull($attendance->approved_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_attendance.reviewed',
            'auditable_id' => $attendance->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/attendance?approval_status=approved&handoff_status=ready')
            ->assertOk()
            ->assertSeeText('Dedi Mandays')
            ->assertSeeText('Ready');
    }

    public function test_seeded_admin_can_manage_piecework_output_rate_and_foreman_approval(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $costCenter = CostCenter::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'EXT-BOR-099',
            'name' => 'External Borongan Cost Center',
            'owner_name' => 'HR Operation',
            'is_billable' => true,
            'is_active' => true,
        ]);

        $vendor = ExternalWorkforceVendor::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_code' => 'VND-BOR-099',
            'vendor_name' => 'PT Borongan Vendor',
            'vendor_type' => 'outsourcing',
            'status' => 'active',
            'compliance_snapshot' => ['npwp' => true, 'nib' => true],
            'created_by' => $admin->id,
        ]);

        $contract = ExternalWorkforceContract::create([
            'external_workforce_vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'contract_number' => 'OS/BMP/2026/099',
            'contract_title' => 'Kontrak borongan output',
            'workforce_scope' => 'piecework',
            'worker_type_scope' => 'Borongan loading',
            'start_date' => '2026-07-28',
            'end_date' => '2027-07-27',
            'headcount_quota' => 12,
            'daily_rate' => 0,
            'overtime_rate' => 0,
            'responsibility_matrix' => ['tax' => 'vendor', 'bpjs' => 'vendor', 'hse' => 'shared', 'access_area' => 'company'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $worker = ExternalWorkforceWorker::create([
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'worker_number' => 'EXT-BOR-WRK-099',
            'full_name' => 'Joko Borongan',
            'worker_type' => 'piece_rate',
            'position_title' => 'Loader Borongan',
            'supervisor_name' => 'Mandor HO',
            'start_date' => '2026-07-28',
            'payment_handling' => 'vendor_invoice',
            'daily_rate' => 0,
            'piece_rate' => 2500,
            'document_status' => ['ktp' => true, 'vendor_contract' => true, 'hse_induction' => true, 'area_access' => true],
            'access_scope' => ['area' => 'Gudang HO', 'valid_until' => '2027-07-27'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/piecework')
            ->assertOk()
            ->assertSeeText('Output Borongan')
            ->assertSeeText('Master Tarif Pekerjaan')
            ->assertSeeText('Input Output Borongan');

        $this->actingAs($admin)
            ->post('/external-workforce/piecework/rates', [
                'external_workforce_contract_id' => $contract->id,
                'rate_code' => 'BOR-LOAD-099',
                'work_name' => 'Loading barang ke truk',
                'work_category' => 'borongan',
                'unit_name' => 'box',
                'rate_per_unit' => 2500,
                'minimum_output' => 20,
                'quality_rule' => 'Hanya box lolos QC yang dibayar.',
                'status' => 'active',
                'notes' => 'Tarif borongan untuk loading gudang HO.',
            ])
            ->assertRedirect('/external-workforce/piecework');

        $rate = ExternalWorkforcePieceRate::where('rate_code', 'BOR-LOAD-099')->firstOrFail();

        $this->assertSame($vendor->id, $rate->external_workforce_vendor_id);
        $this->assertSame($contract->id, $rate->external_workforce_contract_id);
        $this->assertSame(2500.0, (float) $rate->rate_per_unit);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_piece_rate.created',
            'auditable_id' => $rate->id,
        ]);

        $this->actingAs($admin)
            ->post('/external-workforce/piecework/outputs', [
                'external_workforce_piece_rate_id' => $rate->id,
                'external_workforce_worker_id' => $worker->id,
                'output_number' => 'OUT-BOR-099',
                'work_date' => '2026-07-28',
                'team_name' => 'Team Mandor HO',
                'quantity' => 120,
                'accepted_quantity' => 115,
                'rejected_quantity' => 5,
                'rate_per_unit' => 0,
                'foreman_name' => 'Mandor HO',
                'approval_status' => 'submitted',
                'evidence_reference' => 'QC-OUT-099',
                'notes' => '5 box reject karena rusak.',
            ])
            ->assertRedirect('/external-workforce/piecework');

        $output = ExternalWorkforcePieceOutput::where('output_number', 'OUT-BOR-099')->firstOrFail();

        $this->assertSame($worker->id, $output->external_workforce_worker_id);
        $this->assertSame($vendor->id, $output->external_workforce_vendor_id);
        $this->assertSame('pending', $output->handoff_status);
        $this->assertSame(287500.0, (float) $output->gross_amount);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_piece_output.created',
            'auditable_id' => $output->id,
        ]);

        $this->actingAs($admin)
            ->post("/external-workforce/piecework/outputs/{$output->id}/review", [
                'decision' => 'approved',
                'foreman_name' => 'Mandor HO',
                'notes' => 'Approved untuk invoice vendor.',
            ])
            ->assertRedirect('/external-workforce/piecework');

        $output->refresh();

        $this->assertSame('approved', $output->approval_status);
        $this->assertSame('ready', $output->handoff_status);
        $this->assertNotNull($output->approved_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_piece_output.reviewed',
            'auditable_id' => $output->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/piecework?approval_status=approved')
            ->assertOk()
            ->assertSeeText('Loading barang ke truk')
            ->assertSeeText('Ready');
    }

    public function test_seeded_admin_can_reconcile_vendor_invoice_from_mandays_and_piecework(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();
        $costCenter = CostCenter::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'EXT-INV-100',
            'name' => 'External Vendor Invoice Cost Center',
            'owner_name' => 'HR Operation',
            'is_billable' => true,
            'is_active' => true,
        ]);

        $vendor = ExternalWorkforceVendor::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_code' => 'VND-INV-100',
            'vendor_name' => 'PT Rekonsiliasi Vendor',
            'vendor_type' => 'outsourcing',
            'status' => 'active',
            'compliance_snapshot' => ['npwp' => true, 'nib' => true],
            'created_by' => $admin->id,
        ]);

        $contract = ExternalWorkforceContract::create([
            'external_workforce_vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'contract_number' => 'OS/BMP/2026/100',
            'contract_title' => 'Kontrak rekonsiliasi vendor',
            'workforce_scope' => 'mixed',
            'worker_type_scope' => 'Harian dan borongan',
            'start_date' => '2026-07-29',
            'end_date' => '2027-07-28',
            'headcount_quota' => 20,
            'daily_rate' => 200000,
            'overtime_rate' => 35000,
            'responsibility_matrix' => ['tax' => 'vendor', 'bpjs' => 'vendor', 'hse' => 'shared', 'access_area' => 'company'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $worker = ExternalWorkforceWorker::create([
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'worker_number' => 'EXT-INV-WRK-100',
            'full_name' => 'Rian Rekonsiliasi',
            'worker_type' => 'outsource',
            'position_title' => 'Helper Mixed',
            'supervisor_name' => 'Supervisor HO',
            'start_date' => '2026-07-29',
            'payment_handling' => 'vendor_invoice',
            'daily_rate' => 200000,
            'piece_rate' => 3000,
            'document_status' => ['ktp' => true, 'vendor_contract' => true, 'hse_induction' => true, 'area_access' => true],
            'access_scope' => ['area' => 'Gudang HO', 'valid_until' => '2027-07-28'],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $attendance = ExternalWorkforceAttendance::create([
            'external_workforce_worker_id' => $worker->id,
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'attendance_number' => 'EXT-ATT-INV-100',
            'attendance_date' => '2026-07-29',
            'attendance_status' => 'present',
            'mandays' => 1,
            'overtime_hours' => 1,
            'daily_rate' => 200000,
            'overtime_rate' => 35000,
            'gross_amount' => 235000,
            'approval_status' => 'approved',
            'approved_by' => 'Supervisor HO',
            'approved_at' => now(),
            'handoff_target' => 'vendor_invoice',
            'handoff_status' => 'ready',
            'source_type' => 'manual',
            'created_by' => $admin->id,
        ]);

        $rate = ExternalWorkforcePieceRate::create([
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'rate_code' => 'BOR-INV-100',
            'work_name' => 'Packing invoice',
            'work_category' => 'borongan',
            'unit_name' => 'box',
            'rate_per_unit' => 3000,
            'minimum_output' => 0,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $pieceOutput = ExternalWorkforcePieceOutput::create([
            'external_workforce_piece_rate_id' => $rate->id,
            'external_workforce_worker_id' => $worker->id,
            'external_workforce_vendor_id' => $vendor->id,
            'external_workforce_contract_id' => $contract->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cost_center_id' => $costCenter->id,
            'output_number' => 'OUT-INV-100',
            'work_date' => '2026-07-29',
            'quantity' => 50,
            'accepted_quantity' => 50,
            'rejected_quantity' => 0,
            'rate_per_unit' => 3000,
            'gross_amount' => 150000,
            'foreman_name' => 'Mandor HO',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'handoff_target' => 'vendor_invoice',
            'handoff_status' => 'ready',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/vendor-invoices')
            ->assertOk()
            ->assertSeeText('Rekonsiliasi Tagihan Vendor')
            ->assertSeeText('Buat Invoice Rekonsiliasi');

        $this->actingAs($admin)
            ->post('/external-workforce/vendor-invoices', [
                'external_workforce_vendor_id' => $vendor->id,
                'external_workforce_contract_id' => $contract->id,
                'invoice_number' => 'EXT-INV-100',
                'vendor_invoice_number' => 'VENDOR/INV/100',
                'period_start' => '2026-07-29',
                'period_end' => '2026-07-29',
                'invoice_date' => '2026-07-30',
                'due_date' => '2026-08-14',
                'source_scope' => 'mixed',
                'tax_amount' => 0,
                'notes' => 'Invoice mixed mandays dan output.',
            ])
            ->assertRedirect('/external-workforce/vendor-invoices');

        $invoice = ExternalWorkforceVendorInvoice::where('invoice_number', 'EXT-INV-100')->firstOrFail();

        $this->assertSame($vendor->id, $invoice->external_workforce_vendor_id);
        $this->assertSame($contract->id, $invoice->external_workforce_contract_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_vendor_invoice.created',
            'auditable_id' => $invoice->id,
        ]);

        $this->actingAs($admin)
            ->post("/external-workforce/vendor-invoices/{$invoice->id}/lines", [
                'source_type' => 'attendance',
                'source_id' => $attendance->id,
                'claimed_amount' => 235000,
                'notes' => 'Mandays match.',
            ])
            ->assertRedirect('/external-workforce/vendor-invoices');

        $this->actingAs($admin)
            ->post("/external-workforce/vendor-invoices/{$invoice->id}/lines", [
                'source_type' => 'piece_output',
                'source_id' => $pieceOutput->id,
                'claimed_amount' => 155000,
                'notes' => 'Vendor claim lebih 5000.',
            ])
            ->assertRedirect('/external-workforce/vendor-invoices');

        $invoice->refresh();

        $this->assertSame(2, ExternalWorkforceVendorInvoiceLine::where('external_workforce_vendor_invoice_id', $invoice->id)->count());
        $this->assertSame(385000.0, (float) $invoice->system_amount);
        $this->assertSame(390000.0, (float) $invoice->claimed_amount);
        $this->assertSame(5000.0, (float) $invoice->variance_amount);
        $this->assertSame('variance', $invoice->reconciliation_status);
        $this->assertSame('submitted', $invoice->approval_status);

        $this->actingAs($admin)
            ->post("/external-workforce/vendor-invoices/{$invoice->id}/review", [
                'decision' => 'approved',
                'reviewed_by' => 'Finance HO',
                'notes' => 'Variance disetujui.',
            ])
            ->assertRedirect('/external-workforce/vendor-invoices');

        $invoice->refresh();

        $this->assertSame('approved', $invoice->approval_status);
        $this->assertSame('ready', $invoice->payment_status);
        $this->assertSame('variance', $invoice->reconciliation_status);
        $this->assertNotNull($invoice->reviewed_at);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'external_workforce_vendor_invoice.reviewed',
            'auditable_id' => $invoice->id,
        ]);

        $this->actingAs($admin)
            ->get('/external-workforce/vendor-invoices?date_from=2026-07-29&date_to=2026-07-29')
            ->assertOk()
            ->assertSeeText('PT Rekonsiliasi Vendor')
            ->assertSeeText('Ready');
    }

    public function test_local_attendance_agent_can_push_clock_events_to_import_batch(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-AGENT-093',
            'nik' => '3174093007010009',
            'full_name' => 'Agent Smoke Employee',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'work_email' => 'agent093@kurmigo.id',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/integrations/biometric-connectors', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'connector_code' => 'BIO-AGENT-093',
                'connector_name' => 'Local Agent HO',
                'vendor' => 'generic',
                'connection_type' => 'local_agent',
                'endpoint_url' => 'C:\\AttendanceExport',
                'auth_mode' => 'local_agent',
                'agent_token' => 'agent-secret-093',
                'agent_config_note' => 'Windows attendance PC HO',
                'machine_code' => 'BIO-HO-AGENT',
                'machine_location' => 'Head Office',
                'sync_interval_minutes' => 15,
                'status' => 'active',
                'mapping_employee_field' => 'employee_number',
                'mapping_time_field' => 'event_time',
                'mapping_event_field' => 'event_type',
            ])
            ->assertRedirect('/integrations/biometric-connectors');

        $connector = BiometricConnector::where('connector_code', 'BIO-AGENT-093')->firstOrFail();

        $this->assertSame('waiting_first_sync', $connector->agent_status);
        $this->assertTrue(Hash::check('agent-secret-093', $connector->agent_token_hash));
        $this->assertSame('BIO-AGENT-093-PAIR', $connector->agent_pairing_code);
        $this->assertSame(url('/api/attendance-agent/heartbeat'), $connector->agent_config['heartbeat_endpoint']);

        $this->postJson('/api/attendance-agent/heartbeat', [
            'connector_code' => 'BIO-AGENT-093',
            'agent_token' => 'wrong-token',
            'agent_version' => '0.1.0-test',
        ])->assertUnauthorized();

        $this->postJson('/api/attendance-agent/heartbeat', [
            'connector_code' => 'BIO-AGENT-093',
            'agent_token' => 'agent-secret-093',
            'agent_version' => '0.1.0-test',
            'machine_code' => 'BIO-HO-AGENT',
            'status' => 'idle',
            'source_type' => 'csv_folder',
            'source_reference' => 'C:\\AttendanceExport',
            'local_time' => '2026-07-21 07:55:00',
            'metrics' => ['pending_files' => 1, 'last_read_rows' => 2],
        ])
            ->assertOk()
            ->assertJsonPath('agent_status', 'idle');

        $connector->refresh();

        $this->assertSame('idle', $connector->agent_status);
        $this->assertSame('0.1.0-test', $connector->agent_version);
        $this->assertSame(1, $connector->agent_heartbeat_count);
        $this->assertSame('heartbeat', $connector->agent_last_payload['type']);
        $this->assertSame(1, BiometricSyncLog::where('biometric_connector_id', $connector->id)->where('status', 'heartbeat')->count());

        $this->postJson('/api/attendance-agent/clock-events', [
            'connector_code' => 'BIO-AGENT-093',
            'agent_token' => 'agent-secret-093',
            'agent_version' => '0.1.0-test',
            'batch_id' => 'CSV-093-20260721-0800',
            'machine_code' => 'BIO-HO-AGENT',
            'source_type' => 'csv_folder',
            'source_reference' => 'C:\\AttendanceExport\\clock-20260721.csv',
            'events' => [
                [
                    'employee_identifier' => $employee->employee_number,
                    'employee_name' => $employee->full_name,
                    'event_time' => '2026-07-21 08:00:00',
                    'event_type' => 'clock-in',
                    'source_device' => 'BIO-HO-AGENT',
                    'raw_payload' => ['pin' => $employee->employee_number],
                ],
                [
                    'employee_identifier' => 'UNKNOWN-093',
                    'employee_name' => 'Unknown Employee',
                    'event_time' => '2026-07-21 08:05:00',
                    'event_type' => 'clock-in',
                    'source_device' => 'BIO-HO-AGENT',
                    'raw_payload' => ['pin' => 'UNKNOWN-093'],
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('batch_number', 'AGENT-CSV-093-20260721-0800')
            ->assertJsonPath('created_rows', 2)
            ->assertJsonPath('matched_rows', 1)
            ->assertJsonPath('failed_rows', 1);

        $batch = AttendanceImportBatch::where('batch_number', 'AGENT-CSV-093-20260721-0800')->firstOrFail();

        $this->assertSame('local_agent', $batch->source_type);
        $this->assertSame(2, $batch->imported_rows);
        $this->assertSame(1, $batch->matched_rows);
        $this->assertSame(1, $batch->anomaly_rows);
        $this->assertDatabaseHas('attendance_import_rows', [
            'attendance_import_batch_id' => $batch->id,
            'employee_profile_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'event_type' => 'clock_in',
            'match_status' => 'matched',
        ]);
        $this->assertDatabaseHas('attendance_import_rows', [
            'attendance_import_batch_id' => $batch->id,
            'raw_identifier' => 'UNKNOWN-093',
            'match_status' => 'needs_review',
        ]);

        $connector->refresh();

        $this->assertSame('synced_with_anomaly', $connector->agent_status);
        $this->assertSame('0.1.0-test', $connector->agent_version);
        $this->assertNotNull($connector->agent_last_seen_at);
        $this->assertSame(2, $connector->agent_heartbeat_count);
        $this->assertSame('ingest', $connector->agent_last_payload['type']);
        $this->assertSame(1, BiometricSyncLog::where('attendance_import_batch_id', $batch->id)->count());
    }

    public function test_employee_can_access_ess_flow_center_with_employee_only_menu(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ESS-012',
            'nik' => '3174092107010012',
            'full_name' => 'Dimas Wirawan',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional',
            'work_email' => 'dimas@kurmigo.id',
            'is_active' => true,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/flow-center')
            ->assertOk()
            ->assertSeeText('Alur ESS Saya')
            ->assertSeeText('Flow Karyawan')
            ->assertSeeText('Pusat Pengajuan')
            ->assertSeeText('KRM-ESS-012 - Dimas Wirawan')
            ->assertDontSeeText('Data Karyawan')
            ->assertDontSeeText('Administration Panel');
    }

    public function test_manager_can_access_manager_self_service_hub(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        EmployeeProfile::create([
            'user_id' => $manager->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MSS-013',
            'nik' => '3174092207010013',
            'full_name' => 'Bima Santoso',
            'employment_status' => 'permanent',
            'position' => 'Supervisor Operasional',
            'department' => 'Operasional Site',
            'work_email' => 'bima@kurmigo.id',
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-MSS-TEAM-013',
            'nik' => '3174092207011013',
            'full_name' => 'Team Member MSS',
            'employment_status' => 'permanent',
            'position' => 'Staff Operasional',
            'department' => 'Operasional Site',
            'work_email' => 'team.mss013@kurmigo.id',
            'is_active' => true,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'ANNUAL-MSS-013',
            'name' => 'Cuti Tahunan MSS',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'requires_balance' => true,
            'requires_attachment' => false,
            'approval_required' => true,
            'is_active' => true,
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-MSS-013',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => '2026-07-22',
            'end_date' => '2026-07-22',
            'requested_days' => 1,
            'balance_before' => 12,
            'balance_after' => 11,
            'duration_unit' => 'day',
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get('/manager/self-service')
            ->assertOk()
            ->assertSeeText('Manager Self Service')
            ->assertSeeText('Antrian Keputusan')
            ->assertSeeText('LV-MSS-013')
            ->assertSeeText('Team Member MSS')
            ->assertSeeText('Beranda MSS')
            ->assertSeeText('Approval Inbox')
            ->assertDontSeeText('Employee Self Service');
    }

    public function test_seeded_admin_can_view_payroll_foundation_readiness(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-PAYFOUND-014',
            'nik' => '3174092307010014',
            'full_name' => 'Payroll Foundation Demo',
            'employment_status' => 'permanent',
            'position' => 'Staff Payroll',
            'department' => 'Finance',
            'work_email' => 'payfound014@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $component = PayrollComponent::create([
            'company_id' => $company->id,
            'component_code' => 'BASIC-PAYFOUND',
            'component_name' => 'Gaji Pokok Foundation',
            'component_category' => 'earning',
            'calculation_method' => 'fixed',
            'default_amount' => 0,
            'rate_value' => 0,
            'prorate_basis' => 'calendar_day',
            'is_taxable' => true,
            'is_bpjs_basis' => true,
            'is_payslip_visible' => true,
            'effective_start' => '2026-07-01',
            'status' => 'active',
            'sort_order' => 1,
            'created_by' => $admin->id,
        ]);

        $band = PayrollSalaryBand::create([
            'company_id' => $company->id,
            'band_code' => 'PAYFOUND-G4',
            'band_name' => 'Payroll Foundation G4',
            'level' => 4,
            'minimum_salary' => 5000000,
            'midpoint_salary' => 6500000,
            'maximum_salary' => 8000000,
            'currency' => 'IDR',
            'effective_start' => '2026-07-01',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        EmployeeSalaryAssignment::create([
            'employee_profile_id' => $employee->id,
            'payroll_salary_band_id' => $band->id,
            'base_salary' => 6500000,
            'fixed_allowance' => 500000,
            'variable_allowance' => 0,
            'payroll_frequency' => 'monthly',
            'effective_start' => '2026-07-01',
            'approval_status' => 'approved',
            'confidentiality_level' => 'payroll_only',
            'created_by' => $admin->id,
        ]);

        TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->full_name,
            'nik' => '3174092307010014',
            'npwp' => '09.014.014.0-014.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        PayrollBpjsProfile::create([
            'employee_profile_id' => $employee->id,
            'bpjs_kesehatan_number' => 'BPJSKES-PAYFOUND',
            'bpjs_ketenagakerjaan_number' => 'BPJSTK-PAYFOUND',
            'bpjs_class' => '1',
            'effective_date' => '2026-07-01',
            'health_contribution_base' => 6500000,
            'employment_contribution_base' => 6500000,
            'health_employee_rate' => 1,
            'health_employer_rate' => 4,
            'jht_employee_rate' => 2,
            'jht_employer_rate' => 3.7,
            'jp_employee_rate' => 1,
            'jp_employer_rate' => 2,
            'is_active' => true,
            'verification_status' => 'verified',
            'created_by' => $admin->id,
        ]);

        PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-PAYFOUND-014',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'validated',
            'employee_count' => 1,
            'validation_passed' => 1,
            'validation_failed' => 0,
            'anomaly_count' => 0,
            'gross_estimate' => 7000000,
            'deduction_estimate' => 500000,
            'net_estimate' => 6500000,
            'source_snapshot' => ['component' => $component->component_code],
            'validated_at' => now(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll')
            ->assertOk()
            ->assertSeeText('Fondasi Payroll')
            ->assertSeeText('Checklist Kesiapan Payroll')
            ->assertSeeText('Workflow Payroll')
            ->assertSeeText('RUN-PAYFOUND-014')
            ->assertSeeText('Struktur Gaji')
            ->assertSeeText('Proses Payroll');
    }

    public function test_seeded_admin_can_calculate_payroll_run_preview(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-PAYCALC-015',
            'employee_name' => 'Payroll Calculation Demo',
            'nik' => '3174092307010015',
            'npwp' => '09.015.015.0-015.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-PAYCALC-015',
            'full_name' => 'Payroll Calculation Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Analyst',
            'department' => 'Finance',
            'work_email' => 'paycalc015@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $band = PayrollSalaryBand::create([
            'company_id' => $company->id,
            'band_code' => 'PAYCALC-G5',
            'band_name' => 'Payroll Calculation G5',
            'level' => 5,
            'minimum_salary' => 7000000,
            'midpoint_salary' => 9000000,
            'maximum_salary' => 11000000,
            'currency' => 'IDR',
            'effective_start' => '2026-07-01',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        EmployeeSalaryAssignment::create([
            'employee_profile_id' => $employee->id,
            'payroll_salary_band_id' => $band->id,
            'base_salary' => 9000000,
            'fixed_allowance' => 1000000,
            'variable_allowance' => 500000,
            'payroll_frequency' => 'monthly',
            'effective_start' => '2026-07-01',
            'approval_status' => 'approved',
            'confidentiality_level' => 'payroll_only',
            'created_by' => $admin->id,
        ]);

        PayrollBpjsProfile::create([
            'employee_profile_id' => $employee->id,
            'tax_profile_id' => $taxProfile->id,
            'bpjs_kesehatan_number' => 'BPJSKES-PAYCALC',
            'bpjs_ketenagakerjaan_number' => 'BPJSTK-PAYCALC',
            'bpjs_class' => '1',
            'effective_date' => '2026-07-01',
            'health_contribution_base' => 9000000,
            'employment_contribution_base' => 9000000,
            'health_employee_rate' => 1,
            'health_employer_rate' => 4,
            'jht_employee_rate' => 2,
            'jht_employer_rate' => 3.7,
            'jp_employee_rate' => 1,
            'jp_employer_rate' => 2,
            'is_active' => true,
            'verification_status' => 'verified',
            'created_by' => $admin->id,
        ]);

        PayrollAdjustment::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'adjustment_number' => 'ADJ-PAYCALC-015',
            'adjustment_type' => 'bonus',
            'component_code' => 'BONUS-PAYCALC',
            'component_name' => 'Bonus Calculation Demo',
            'component_direction' => 'earning',
            'period_from' => '2026-07-01',
            'period_to' => '2026-07-31',
            'posting_period' => '2026-07',
            'base_amount' => 0,
            'adjustment_amount' => 500000,
            'taxable_amount' => 500000,
            'tax_treatment' => 'taxable',
            'status' => 'approved',
            'reason' => 'Demo adjustment payroll calculation',
            'created_by' => $admin->id,
        ]);

        $run = PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-PAYCALC-015',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'ready',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/calculation?run_id='.$run->id)
            ->assertOk()
            ->assertSeeText('Perhitungan Payroll')
            ->assertSeeText('Payroll Calculation Demo')
            ->assertSeeText('Hitung Payroll');

        $this->actingAs($admin)
            ->post('/payroll/calculation/'.$run->id)
            ->assertRedirect('/payroll/calculation?run_id='.$run->id);

        $run->refresh();

        $this->assertSame('calculated', $run->status);
        $this->assertEquals(1, $run->employee_count);
        $this->assertEquals(11000000, (float) $run->gross_estimate);
        $this->assertGreaterThan(0, (float) $run->deduction_estimate);
        $this->assertGreaterThan(0, (float) $run->net_estimate);
    }

    public function test_seeded_admin_can_view_tax_foundation_control_tower(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TAXFOUND-016',
            'employee_name' => 'Tax Foundation Demo',
            'nik' => '3174092307010016',
            'npwp' => '09.016.016.0-016.000',
            'ptkp_status' => 'K/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-TAXFOUND-016',
            'full_name' => 'Tax Foundation Demo',
            'employment_status' => 'permanent',
            'position' => 'Tax Analyst',
            'department' => 'Finance',
            'work_email' => 'taxfound016@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $rate = TaxTerRate::create([
            'category' => 'A',
            'income_from' => 0,
            'income_to' => 10000000,
            'rate' => 0.005,
            'source_reference' => 'Demo TER',
            'is_active' => true,
        ]);

        PayrollTaxComponent::create([
            'company_id' => $company->id,
            'component_code' => 'TAXFOUND-BASIC',
            'component_name' => 'Gaji Dasar Pajak',
            'component_type' => 'earning',
            'tax_treatment' => 'taxable_income',
            'payroll_formula_key' => 'base_salary',
            'default_amount' => 5000000,
            'is_taxable' => true,
            'is_recurring' => true,
            'dependencies' => ['salary_assignment'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        CoretaxConnectorSetting::create([
            'company_id' => $company->id,
            'environment' => 'sandbox',
            'base_url' => 'https://coretax.example.test',
            'client_id' => 'taxfound-client',
            'secret_mask' => '****016',
            'credential_hash' => 'hash-taxfound',
            'timeout_seconds' => 30,
            'max_retry' => 3,
            'is_active' => true,
            'last_status' => 'ready',
        ]);

        TaxMonthlyCalculation::create([
            'tax_profile_id' => $taxProfile->id,
            'tax_ter_rate_id' => $rate->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'period' => '2026-07',
            'ter_category' => 'A',
            'tax_method' => 'gross',
            'gross_income' => 8000000,
            'taxable_allowance' => 0,
            'deduction' => 0,
            'ter_base_amount' => 8000000,
            'ter_rate' => 0.005,
            'pph21_amount' => 40000,
            'take_home_pay' => 7960000,
            'calculation_status' => 'final',
            'calculation_steps' => [['label' => 'TER', 'amount' => 40000]],
            'source_snapshot' => ['employee_name' => $taxProfile->employee_name],
            'created_by' => $admin->id,
        ]);

        TaxWithholdingDocument::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'document_number' => 'BUPOT-TAXFOUND-016',
            'document_type' => 'monthly',
            'period' => '2026-07',
            'status' => 'draft',
            'gross_income' => 8000000,
            'tax_base' => 8000000,
            'pph21_amount' => 40000,
            'employee_snapshot' => ['name' => $taxProfile->employee_name],
            'document_payload' => ['source' => 'tax-foundation-test'],
            'checksum' => 'taxfound016',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-foundation')
            ->assertOk()
            ->assertSeeText('Fondasi PPh 21')
            ->assertSeeText('Checklist Kesiapan')
            ->assertSeeText('Workflow Pajak')
            ->assertSeeText('Tax Foundation Demo')
            ->assertSeeText('BUPOT-TAXFOUND-016')
            ->assertSeeText('Coretax')
            ->assertSeeText('Master PPh 21');
    }

    public function test_seeded_admin_can_view_advanced_tax_control_board(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-TAXADV-017',
            'employee_name' => 'Tax Advanced Demo',
            'nik' => '3174092307010017',
            'npwp' => '09.017.017.0-017.000',
            'ptkp_status' => 'K/1',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $rate = TaxTerRate::create([
            'category' => 'B',
            'income_from' => 0,
            'income_to' => 15000000,
            'rate' => 0.01,
            'source_reference' => 'Demo TER Advanced',
            'is_active' => true,
        ]);

        TaxMonthlyCalculation::create([
            'tax_profile_id' => $taxProfile->id,
            'tax_ter_rate_id' => $rate->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'period' => '2026-07',
            'ter_category' => 'B',
            'tax_method' => 'gross',
            'gross_income' => 12000000,
            'taxable_allowance' => 0,
            'deduction' => 0,
            'ter_base_amount' => 12000000,
            'ter_rate' => 0.01,
            'pph21_amount' => 120000,
            'take_home_pay' => 11880000,
            'calculation_status' => 'final',
            'calculation_steps' => [['label' => 'TER', 'amount' => 120000]],
            'source_snapshot' => ['employee_name' => $taxProfile->employee_name],
            'created_by' => $admin->id,
        ]);

        TaxCorrection::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'correction_number' => 'COR-TAXADV-017',
            'correction_type' => 'gross_income',
            'period' => '2026-07',
            'payroll_period' => '2026-07',
            'previous_gross_income' => 12000000,
            'corrected_gross_income' => 12500000,
            'previous_tax_base' => 12000000,
            'corrected_tax_base' => 12500000,
            'previous_pph21_amount' => 120000,
            'corrected_pph21_amount' => 125000,
            'delta_pph21_amount' => 5000,
            'payroll_adjustment_type' => 'next_period',
            'status' => 'submitted',
            'reason' => 'Demo koreksi PPh 21 lanjutan',
            'before_snapshot' => ['gross' => 12000000],
            'after_snapshot' => ['gross' => 12500000],
            'workflow_snapshot' => ['step' => 'submitted'],
            'created_by' => $admin->id,
        ]);

        TaxAnnualReconciliation::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_year' => 2026,
            'annual_gross_income' => 144000000,
            'annual_taxable_allowance' => 0,
            'annual_deduction' => 6000000,
            'annual_net_income' => 138000000,
            'ptkp_amount' => 63000000,
            'pkp_amount' => 75000000,
            'annual_pph21_due' => 5250000,
            'monthly_pph21_paid' => 4800000,
            'true_up_amount' => 450000,
            'true_up_status' => 'underpaid',
            'calculation_status' => 'draft',
            'monthly_snapshot' => [['period' => '2026-07', 'pph21' => 120000]],
            'calculation_steps' => [['label' => 'PKP', 'amount' => 75000000]],
            'created_by' => $admin->id,
        ]);

        TaxSimulation::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'simulation_number' => 'SIM-TAXADV-017',
            'scenario_name' => 'Simulasi Bonus Tahunan',
            'scenario_type' => 'bonus',
            'period' => '2026-07',
            'tax_method' => 'gross_up',
            'gross_income' => 12000000,
            'taxable_allowance' => 0,
            'irregular_income' => 5000000,
            'deduction' => 0,
            'tax_base' => 17000000,
            'ter_rate' => 0.01,
            'pph21_amount' => 170000,
            'tax_allowance_amount' => 171717,
            'company_tax_borne_amount' => 0,
            'final_tax_amount' => 170000,
            'gross_up_iterations' => 2,
            'take_home_pay' => 17001717,
            'scenario_status' => 'draft',
            'visible_to_employee' => false,
            'input_snapshot' => ['scenario' => 'bonus'],
            'result_snapshot' => ['pph21' => 170000],
            'comparison_snapshot' => ['base' => 120000],
            'notes' => 'Demo simulasi lanjutan',
            'created_by' => $admin->id,
        ]);

        TaxRegressionCase::create([
            'case_number' => 'REG-TAXADV-017',
            'case_type' => 'monthly_ter',
            'case_name' => 'Regression TER K1',
            'period' => '2026-07',
            'input_payload' => ['ptkp' => 'K/1', 'gross' => 12000000],
            'expected_payload' => ['pph21' => 120000],
            'actual_payload' => ['pph21' => 119000],
            'expected_pph21_amount' => 120000,
            'actual_pph21_amount' => 119000,
            'variance_amount' => -1000,
            'coretax_schema_version' => '2026.07',
            'coretax_validation_status' => 'valid',
            'run_status' => 'failed',
            'assertion_snapshot' => ['variance' => -1000],
            'last_run_at' => now(),
            'notes' => 'Demo regression mismatch',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/tax-advanced-control')
            ->assertOk()
            ->assertSeeText('Kontrol Lanjutan PPh 21')
            ->assertSeeText('Control Board')
            ->assertSeeText('COR-TAXADV-017')
            ->assertSeeText('Simulasi Bonus Tahunan')
            ->assertSeeText('REG-TAXADV-017')
            ->assertSeeText('Fondasi')
            ->assertSeeText('Koreksi Pajak');
    }

    public function test_seeded_admin_can_view_coretax_integration_command_center(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $setting = CoretaxConnectorSetting::create([
            'company_id' => $company->id,
            'environment' => 'sandbox',
            'base_url' => 'https://coretax.integration.test',
            'client_id' => 'coretax-day18-client',
            'secret_mask' => '**********1818',
            'credential_hash' => 'hash-day18',
            'timeout_seconds' => 30,
            'max_retry' => 3,
            'is_active' => true,
            'last_checked_at' => now(),
            'last_status' => 'configured',
        ]);

        $package = TaxMonthlyReportPackage::create([
            'company_id' => $company->id,
            'period' => '2026-07',
            'package_number' => 'PKG-CORETAX-DAY18',
            'status' => 'ready',
            'employee_count' => 2,
            'total_gross_income' => 20000000,
            'total_tax_base' => 19000000,
            'total_pph21' => 250000,
            'summary_snapshot' => ['source' => 'day18'],
            'detail_snapshot' => [],
            'validation_notes' => [],
            'created_by' => $admin->id,
        ]);

        $template = TaxMonthlyExportTemplate::create([
            'tax_monthly_report_package_id' => $package->id,
            'template_number' => 'TPL-CORETAX-DAY18',
            'schema_version' => '2026.07',
            'format' => 'json',
            'status' => 'ready',
            'columns_schema' => ['employee_number', 'pph21'],
            'payload_snapshot' => [
                ['employee_number' => 'KRM-001', 'pph21' => 100000],
                ['employee_number' => 'KRM-002', 'pph21' => 150000],
            ],
            'checksum' => 'checksum-day18-coretax',
            'created_by' => $admin->id,
        ]);

        $retryLog = CoretaxRetryLog::create([
            'coretax_connector_setting_id' => $setting->id,
            'tax_monthly_export_template_id' => $template->id,
            'request_type' => 'submit_template',
            'status' => 'queued',
            'attempt' => 1,
            'max_retry' => 3,
            'next_retry_at' => now()->addMinutes(10),
            'endpoint' => 'https://coretax.integration.test/api/coretax/submit_template',
            'payload_checksum' => $template->checksum,
            'response_code' => 202,
            'response_message' => 'Queued for submit',
            'request_snapshot' => ['template_number' => $template->template_number],
            'response_snapshot' => [],
            'created_by' => $admin->id,
        ]);

        CoretaxSubmissionTracker::create([
            'coretax_retry_log_id' => $retryLog->id,
            'tax_monthly_export_template_id' => $template->id,
            'submission_number' => 'CT-SUB-DAY18',
            'submission_status' => 'accepted',
            'submitted_rows' => 2,
            'accepted_rows' => 2,
            'rejected_rows' => 0,
            'template_total_pph21' => 250000,
            'acknowledged_total_pph21' => 250000,
            'difference_amount' => 0,
            'reconciliation_snapshot' => ['status' => 'accepted'],
            'status_history' => [['status' => 'accepted', 'at' => now()->toDateTimeString()]],
            'submitted_at' => now(),
            'last_checked_at' => now(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/coretax-integration')
            ->assertOk()
            ->assertSeeText('Integrasi Coretax')
            ->assertSeeText('Checklist Integrasi')
            ->assertSeeText('Pipeline Status')
            ->assertSeeText('TPL-CORETAX-DAY18')
            ->assertSeeText('CT-SUB-DAY18')
            ->assertSeeText('Connector')
            ->assertSeeText('Laporan Pajak');
    }

    public function test_seeded_admin_can_view_payroll_document_center(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $taxProfile = TaxProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-DOC-019',
            'employee_name' => 'Payroll Document Demo',
            'nik' => '3174092307010019',
            'npwp' => '09.019.019.0-019.000',
            'ptkp_status' => 'TK/0',
            'tax_method' => 'gross',
            'is_npwp_verified' => true,
            'is_active' => true,
        ]);

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'tax_profile_id' => $taxProfile->id,
            'employee_number' => 'KRM-DOC-019',
            'full_name' => 'Payroll Document Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Staff',
            'department' => 'Finance',
            'work_email' => 'paydoc019@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-DOC-019',
            'period' => '2026-07',
            'status' => 'published',
            'gross_pay' => 10000000,
            'total_deduction' => 1000000,
            'net_pay' => 9000000,
            'earning_snapshot' => ['Gaji Pokok' => 9000000, 'Tunjangan' => 1000000],
            'deduction_snapshot' => ['PPh 21' => 500000, 'BPJS' => 500000],
            'employee_snapshot' => ['employee_number' => $employee->employee_number, 'full_name' => $employee->full_name],
            'checksum' => 'checksum-slip-doc-019',
            'published_at' => now(),
            'last_sent_at' => now(),
            'resend_count' => 1,
            'acknowledged_at' => now(),
            'acknowledged_by' => $admin->id,
            'created_by' => $admin->id,
        ]);

        TaxWithholdingDocument::create([
            'tax_profile_id' => $taxProfile->id,
            'company_id' => $company->id,
            'document_number' => 'BUPOT-DOC-019',
            'document_type' => 'monthly_pph21',
            'period' => '2026-07',
            'status' => 'published',
            'gross_income' => 10000000,
            'tax_base' => 9500000,
            'pph21_amount' => 500000,
            'employee_snapshot' => ['employee_number' => $taxProfile->employee_number, 'employee_name' => $taxProfile->employee_name],
            'document_payload' => ['period' => '2026-07'],
            'checksum' => 'checksum-bupot-doc-019',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/payroll/document-center')
            ->assertOk()
            ->assertSeeText('Pusat Dokumen Payroll')
            ->assertSeeText('Checklist Publish')
            ->assertSeeText('Workflow Dokumen')
            ->assertSeeText('SLIP-DOC-019')
            ->assertSeeText('BUPOT-DOC-019')
            ->assertSeeText('Payroll Document Demo')
            ->assertSeeText('Bukti Potong');
    }

    public function test_seeded_admin_can_view_recruitment_command_center(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $manpower = RecruitmentManpowerRequest::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'MPR-DAY20',
            'position_title' => 'Talent Acquisition Lead',
            'department' => 'Human Capital',
            'employment_type' => 'permanent',
            'headcount' => 1,
            'target_join_date' => '2026-08-12',
            'priority' => 'high',
            'request_reason' => 'Memperkuat pipeline hiring HRIS.',
            'budget_min' => 12000000,
            'budget_max' => 18000000,
            'job_summary' => 'Mengelola rekrutmen end-to-end.',
            'requirements' => [['item' => 'Interview berbasis kompetensi']],
            'status' => 'approved',
            'requested_by' => 'Raka Mahendra',
            'requested_at' => '2026-07-19',
            'created_by' => $admin->id,
        ]);

        $candidate = RecruitmentCandidate::create([
            'recruitment_manpower_request_id' => $manpower->id,
            'candidate_number' => 'CAND-DAY20',
            'full_name' => 'Command Center Kandidat',
            'email' => 'candidate.day20@example.com',
            'phone' => '081200002020',
            'source_channel' => 'direct',
            'current_position' => 'Recruiter',
            'current_company' => 'Vendor HR',
            'expected_salary' => 15000000,
            'pipeline_stage' => 'interview',
            'candidate_status' => 'active',
            'portal_token' => 'portal-day20-token',
            'portal_invited_at' => now(),
            'fit_score' => 82,
            'owner_recruiter' => 'Ayu Prameswari',
            'applied_at' => '2026-07-19',
            'last_touch_at' => '2026-07-19',
            'stage_history' => [['stage' => 'screening', 'at' => '2026-07-19']],
            'tags' => ['TA', 'HRIS'],
            'notes' => 'Demo command center recruitment.',
            'created_by' => $admin->id,
        ]);

        RecruitmentCandidateDocument::create([
            'recruitment_candidate_id' => $candidate->id,
            'document_type' => 'cv',
            'document_title' => 'CV Command Center',
            'file_reference' => 'candidate/day20-cv.pdf',
            'verification_status' => 'submitted',
            'submitted_at' => '2026-07-19',
            'notes' => 'Menunggu review HR.',
            'created_by' => $admin->id,
        ]);

        RecruitmentInterview::create([
            'recruitment_candidate_id' => $candidate->id,
            'interview_number' => 'INTV-DAY20',
            'interview_stage' => 'user',
            'scheduled_at' => '2026-07-29 10:00:00',
            'duration_minutes' => 60,
            'interview_mode' => 'online',
            'location_or_link' => 'https://meet.example/day20',
            'interviewer_name' => 'Bima Santoso',
            'interviewer_role' => 'User',
            'status' => 'scheduled',
            'communication_score' => 80,
            'technical_score' => 82,
            'culture_score' => 84,
            'experience_score' => 82,
            'overall_score' => 82,
            'recommendation' => 'proceed',
            'feedback_notes' => 'Lanjut user interview.',
            'scorecard_snapshot' => ['candidate_number' => 'CAND-DAY20'],
            'created_by' => $admin->id,
        ]);

        RecruitmentOfferLetter::create([
            'recruitment_candidate_id' => $candidate->id,
            'offer_number' => 'OFFER-DAY20',
            'position_title' => 'Talent Acquisition Lead',
            'join_date' => '2026-08-12',
            'salary_offer' => 17000000,
            'employment_type' => 'permanent',
            'template_name' => 'Offer HRIS',
            'status' => 'sent',
            'sent_at' => '2026-07-19',
            'expires_at' => '2026-07-26',
            'esign_provider' => 'Privy',
            'esign_reference' => 'PRV-DAY20',
            'signer_email' => 'candidate.day20@example.com',
            'offer_summary' => 'Salary offer Rp 17.000.000 dan join date 12 Aug 2026.',
            'tracking_events' => [['status' => 'sent']],
            'created_by' => $admin->id,
        ]);

        RecruitmentCandidateCheck::create([
            'recruitment_candidate_id' => $candidate->id,
            'check_number' => 'CHECK-DAY20',
            'check_type' => 'psychotest',
            'vendor_name' => 'Psikotest Partner',
            'requested_at' => '2026-07-19',
            'due_at' => '2026-07-25',
            'status' => 'in_progress',
            'result' => 'pending',
            'is_blocking' => true,
            'findings' => 'Menunggu hasil psikotes.',
            'evidence_links' => ['https://assessment.example/day20'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/recruitment/command-center')
            ->assertOk()
            ->assertSeeText('Pusat Rekrutmen')
            ->assertSeeText('Checklist Rekrutmen')
            ->assertSeeText('Alur Kerja')
            ->assertSeeText('Command Center Kandidat')
            ->assertSeeText('MPR-DAY20')
            ->assertSeeText('INTV-DAY20')
            ->assertSeeText('OFFER-DAY20')
            ->assertSeeText('Pengecekan blocking')
            ->assertSeeText('ATS Pipeline');
    }

    public function test_employee_can_submit_ess_anonymous_pulse_survey(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $employeeUser = User::where('email', 'dimas@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::updateOrCreate(
            ['employee_number' => 'KRM-ESS-SURVEY-049'],
            [
                'user_id' => $employeeUser->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'full_name' => 'Dimas Wirawan',
                'employment_status' => 'permanent',
                'position' => 'Operator',
                'department' => 'Operasional',
                'work_email' => 'dimas@kurmigo.id',
                'is_active' => true,
                'created_by' => $employeeUser->id,
            ],
        );

        $survey = EngagementSurvey::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'survey_number' => 'ESS-PULSE-049',
            'title' => 'Pulse Check ESS Mobile',
            'survey_type' => 'pulse',
            'audience_scope' => 'branch',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'question_bank' => ['Saya punya alat kerja yang cukup', 'Atasan memberi arahan jelas'],
            'is_anonymous' => true,
            'owner_name' => 'People Team',
            'status' => 'running',
            'notes' => 'Pulse check operasional site.',
            'created_by' => $employeeUser->id,
        ]);

        $this->actingAs($employeeUser)
            ->get('/ess/surveys')
            ->assertOk()
            ->assertSeeText('Survey Saya')
            ->assertSeeText('Pulse Check ESS Mobile')
            ->assertSeeText('Anonim');

        $this->actingAs($employeeUser)
            ->post("/ess/surveys/{$survey->id}/responses", [
                'engagement_score' => 82,
                'enps_score' => 9,
                'sentiment' => 'positive',
                'dimension_scores' => 'workload:80, atasan:85',
                'comment' => 'Tools kerja makin jelas.',
            ])
            ->assertRedirect('/ess/surveys');

        $response = EngagementSurveyResponse::where('engagement_survey_id', $survey->id)->firstOrFail();

        $this->assertNull($response->employee_profile_id);
        $this->assertNull($response->created_by);
        $this->assertSame(82, $response->engagement_score);
        $this->assertSame(9, $response->enps_score);
        $this->assertDatabaseHas('audit_logs', ['event' => 'ess_survey_response.submitted']);
        $this->assertSame($employee->branch_id, $branch->id);
    }

    public function test_seeded_admin_can_view_people_analytics_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANALYT-076',
            'full_name' => 'People Analytics Demo',
            'employment_status' => 'permanent',
            'position' => 'Operator',
            'department' => 'Operasional',
            'join_date' => '2026-07-01',
            'work_email' => 'analytics076@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLK-ANALYT-076',
            'event_type' => 'clock_in',
            'event_at' => '2026-07-20 08:45:00',
            'source' => 'mobile_webview',
            'is_liveness_passed' => true,
            'is_device_trusted' => true,
            'status' => 'valid',
            'created_by' => $admin->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'ANALYT-CT',
            'name' => 'Cuti Analytics',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'max_days_per_request' => 5,
            'requires_balance' => true,
            'is_paid' => true,
            'requires_attachment' => false,
            'approval_required' => true,
            'carry_forward_allowed' => false,
            'carry_forward_max_days' => 0,
            'carry_forward_expiry_months' => 0,
            'negative_balance_allowed' => false,
            'prorate_on_join' => true,
            'gender_rule' => 'all',
            'min_service_months' => 0,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-ANALYT-076',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => '2026-07-21',
            'end_date' => '2026-07-21',
            'requested_days' => 1,
            'balance_before' => 12,
            'balance_after' => 11,
            'duration_unit' => 'day',
            'request_source' => 'ess_webview',
            'approval_status' => 'approved',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'created_by' => $admin->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-ANALYT-076',
            'employee_profile_id' => $employee->id,
            'overtime_date' => '2026-07-22',
            'start_at' => '2026-07-22 18:00:00',
            'end_at' => '2026-07-22 20:00:00',
            'requested_hours' => 2,
            'approved_hours' => 2,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 150000,
            'overtime_type' => 'weekday',
            'request_source' => 'ess_webview',
            'approval_status' => 'approved',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'created_by' => $admin->id,
        ]);

        PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-ANALYT-076',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'validated',
            'employee_count' => 1,
            'validation_passed' => 1,
            'validation_failed' => 0,
            'anomaly_count' => 0,
            'gross_estimate' => 10000000,
            'deduction_estimate' => 1000000,
            'net_estimate' => 9000000,
            'source_snapshot' => ['source' => 'people analytics test'],
            'created_by' => $admin->id,
        ]);

        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-ANALYT-076',
            'period' => '2026-07',
            'status' => 'published',
            'gross_pay' => 10000000,
            'total_deduction' => 1000000,
            'net_pay' => 9000000,
            'earning_snapshot' => ['Gaji Pokok' => 10000000],
            'deduction_snapshot' => ['PPh 21' => 1000000],
            'employee_snapshot' => ['employee_number' => $employee->employee_number],
            'checksum' => 'checksum-analyt-076',
            'published_at' => '2026-07-20 10:00:00',
            'created_by' => $admin->id,
        ]);

        ManagerClaimRequest::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'claim_number' => 'CLM-ANALYT-076',
            'claim_date' => '2026-07-20',
            'claim_type' => 'transport',
            'request_source' => 'ess_webview',
            'amount' => 250000,
            'approval_status' => 'approved',
            'created_by' => $admin->id,
        ]);

        ExternalWorkforceWorker::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'worker_number' => 'EXT-ANALYT-076',
            'full_name' => 'Worker Analytics Demo',
            'worker_type' => 'daily_worker',
            'position_title' => 'Helper',
            'payment_handling' => 'vendor_invoice',
            'daily_rate' => 250000,
            'document_status' => ['ktp' => true],
            'access_scope' => ['site' => $branch->code],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/analytics/people-dashboard?start_date=2026-07-01&end_date=2026-07-31')
            ->assertOk()
            ->assertSeeText('People Analytics')
            ->assertSeeText('Headcount Aktif')
            ->assertSeeText('Late Rate')
            ->assertSeeText('Net Payroll')
            ->assertSeeText('Operasional')
            ->assertSeeText('RUN-ANALYT-076')
            ->assertSeeText('Worker Eksternal')
            ->assertSeeText('Insight HR');
    }

    public function test_seeded_admin_can_review_payroll_variance_assistant_anomaly(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANALYT-077',
            'full_name' => 'Payroll Variance Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Analyst',
            'department' => 'Finance',
            'join_date' => '2026-07-01',
            'work_email' => 'analytics077@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $run = PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-ANALYT-077',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'blocked',
            'employee_count' => 1,
            'validation_passed' => 2,
            'validation_failed' => 1,
            'anomaly_count' => 1,
            'gross_estimate' => 15000000,
            'deduction_estimate' => 2500000,
            'net_estimate' => 12500000,
            'source_snapshot' => ['source' => 'variance assistant test'],
            'created_by' => $admin->id,
        ]);

        PayrollRunValidation::create([
            'payroll_run_id' => $run->id,
            'check_code' => 'BANK_MISSING',
            'check_name' => 'Rekening bank belum lengkap',
            'severity' => 'high',
            'status' => 'failed',
            'affected_count' => 1,
            'message' => 'Satu karyawan belum memiliki rekening bank aktif.',
            'sample_payload' => ['employee_number' => $employee->employee_number],
        ]);

        $anomaly = PayrollRunAnomaly::create([
            'payroll_run_id' => $run->id,
            'employee_profile_id' => $employee->id,
            'anomaly_code' => 'GROSS_SPIKE',
            'anomaly_name' => 'Gross pay naik signifikan',
            'severity' => 'high',
            'previous_amount' => 10000000,
            'current_amount' => 15000000,
            'variance_percent' => 50,
            'status' => 'open',
            'message' => 'Gross pay naik di atas threshold payroll.',
            'evidence_payload' => ['component' => 'allowance'],
        ]);

        $this->actingAs($admin)
            ->get('/analytics/payroll-variance-assistant?period=2026-07')
            ->assertOk()
            ->assertSeeText('Asisten Varians Payroll')
            ->assertSeeText('Gross pay naik signifikan')
            ->assertSeeText('Payroll Variance Demo')
            ->assertSeeText('Rekomendasi')
            ->assertSeeText('Rekening bank belum lengkap');

        $this->actingAs($admin)
            ->post("/analytics/payroll-variance-assistant/anomalies/{$anomaly->id}/review", [
                'status' => 'resolved',
                'review_note' => 'Allowance sudah dikonfirmasi payroll lead.',
            ])
            ->assertRedirect('/analytics/payroll-variance-assistant');

        $this->assertSame('resolved', $anomaly->fresh()->status);
        $this->assertSame('Allowance sudah dikonfirmasi payroll lead.', $anomaly->fresh()->evidence_payload['review_note']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'payroll_variance_anomaly.reviewed']);
    }

    public function test_seeded_admin_can_view_transparent_attrition_risk_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-SITE-01')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANALYT-078',
            'full_name' => 'Attrition Risk Demo',
            'employment_status' => 'contract',
            'position' => 'Operator',
            'department' => 'Operasional',
            'join_date' => now()->subMonths(4)->toDateString(),
            'contract_end_date' => now()->addDays(30)->toDateString(),
            'work_email' => 'analytics078@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        foreach (range(1, 5) as $day) {
            AttendanceClockEvent::create([
                'employee_profile_id' => $employee->id,
                'event_number' => 'CLK-ATTR-'.$day,
                'event_type' => 'clock_in',
                'event_at' => now()->subDays($day)->setTime(8, 45),
                'source' => 'mobile_webview',
                'is_liveness_passed' => true,
                'is_device_trusted' => true,
                'status' => 'valid',
                'created_by' => $admin->id,
            ]);
        }

        EngagementSurveyResponse::create([
            'engagement_survey_id' => EngagementSurvey::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'survey_number' => 'ATTR-SVY-078',
                'title' => 'Attrition Risk Survey',
                'survey_type' => 'pulse',
                'audience_scope' => 'branch',
                'question_bank' => ['Saya nyaman bekerja di tim ini'],
                'is_anonymous' => false,
                'status' => 'running',
                'created_by' => $admin->id,
            ])->id,
            'employee_profile_id' => $employee->id,
            'response_number' => 'ATTR-RESP-078',
            'engagement_score' => 52,
            'enps_score' => 5,
            'sentiment' => 'negative',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $admin->id,
        ]);

        EmployeeDisciplinaryRecord::create([
            'employee_profile_id' => $employee->id,
            'case_number' => 'DISC-ATTR-078',
            'case_type' => 'coaching',
            'risk_level' => 'medium',
            'incident_date' => now()->subDays(10)->toDateString(),
            'issued_date' => now()->subDays(9)->toDateString(),
            'status' => 'open',
            'reported_by' => 'Supervisor',
            'handled_by' => 'HRBP',
            'summary' => 'Coaching attendance pattern.',
            'created_by' => $admin->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-ATTR-078',
            'employee_profile_id' => $employee->id,
            'overtime_date' => now()->subDays(3)->toDateString(),
            'start_at' => now()->subDays(3)->setTime(18, 0),
            'end_at' => now()->subDays(3)->setTime(22, 0),
            'requested_hours' => 42,
            'approved_hours' => 42,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 3150000,
            'overtime_type' => 'weekday',
            'request_source' => 'ess_webview',
            'approval_status' => 'approved',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/analytics/attrition-risk')
            ->assertOk()
            ->assertSeeText('Risiko Attrition')
            ->assertSeeText('Attrition Risk Demo')
            ->assertSeeText('Engagement rendah')
            ->assertSeeText('Absensi terlambat')
            ->assertSeeText('Rekomendasi')
            ->assertSeeText('1:1 prioritas HRBP + atasan');
    }

    public function test_seeded_admin_can_use_hr_document_assistant_for_policy_qa_and_letter_draft(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANALYT-081',
            'full_name' => 'Document Assistant Demo',
            'employment_status' => 'permanent',
            'position' => 'HR Officer',
            'department' => 'Human Resource',
            'join_date' => '2026-07-01',
            'work_email' => 'analytics081@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'CUTI-ANALYT-081',
            'name' => 'Cuti Tahunan Demo',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'max_days_per_request' => 5,
            'requires_balance' => true,
            'is_paid' => true,
            'requires_attachment' => false,
            'approval_required' => true,
            'status' => 'active',
            'description' => 'Policy cuti tahunan untuk demo assistant dokumen HR.',
            'created_by' => $admin->id,
        ]);

        LeavePolicyRule::create([
            'leave_policy_type_id' => $leaveType->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employment_status' => 'permanent',
            'effective_start' => '2026-01-01',
            'accrual_frequency' => 'yearly',
            'accrual_days' => 12,
            'reset_month' => 1,
            'status' => 'active',
            'notes' => 'Demo rule untuk pertanyaan cuti.',
            'created_by' => $admin->id,
        ]);

        EmployeeDocument::create([
            'employee_profile_id' => $employee->id,
            'document_number' => 'DOC-ANALYT-081',
            'document_type' => 'ktp',
            'title' => 'KTP Document Assistant Demo',
            'version' => 1,
            'status' => 'verified',
            'issued_at' => '2026-07-01',
            'expired_at' => now()->addDays(30)->toDateString(),
            'file_reference' => 'demo-document.pdf',
            'notes' => 'Demo dokumen untuk assistant.',
            'created_by' => $admin->id,
        ]);

        EmployeeContract::create([
            'employee_profile_id' => $employee->id,
            'contract_number' => 'CTR-ANALYT-081',
            'contract_type' => 'pkwtt',
            'start_date' => '2026-07-01',
            'signed_at' => '2026-07-01',
            'status' => 'active',
            'document_reference' => 'contract-demo.pdf',
            'notes' => 'Kontrak demo untuk assistant.',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/analytics/hr-document-assistant?question=cuti+tahunan+dan+dokumen+kontrak&employee_id='.$employee->id.'&draft_type=work_certificate')
            ->assertOk()
            ->assertSeeText('Asisten Dokumen HR')
            ->assertSeeText('Cuti Tahunan Demo')
            ->assertSeeText('Document Assistant Demo')
            ->assertSeeText('Draft Surat Keterangan Kerja')
            ->assertSeeText('Jawaban sementara')
            ->assertSeeText('Checklist Sebelum Dipakai');
    }

    public function test_seeded_admin_can_build_natural_language_hr_report_for_leader(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-ANALYT-082',
            'full_name' => 'Report Builder Demo',
            'employment_status' => 'permanent',
            'position' => 'HR Business Partner',
            'department' => 'Human Resource',
            'join_date' => '2026-07-05',
            'contract_end_date' => now()->addDays(45)->toDateString(),
            'work_email' => 'analytics082@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'CUTI-ANALYT-082',
            'name' => 'Cuti Tahunan Builder Demo',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'requires_balance' => true,
            'is_paid' => true,
            'approval_required' => true,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLK-ANALYT-082',
            'event_type' => 'clock_in',
            'event_at' => '2026-07-10 08:47:00',
            'source' => 'mobile_webview',
            'is_liveness_passed' => true,
            'is_device_trusted' => true,
            'status' => 'valid',
            'created_by' => $admin->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-ANALYT-082',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => '2026-07-14',
            'end_date' => '2026-07-15',
            'requested_days' => 2,
            'balance_before' => 12,
            'balance_after' => 10,
            'duration_unit' => 'day',
            'request_source' => 'ess_webview',
            'approval_status' => 'pending',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => '2026-07-12 09:00:00',
            'reason' => 'Cuti keluarga.',
            'created_by' => $admin->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-ANALYT-082',
            'employee_profile_id' => $employee->id,
            'overtime_date' => '2026-07-11',
            'start_at' => '2026-07-11 18:00:00',
            'end_at' => '2026-07-11 22:00:00',
            'requested_hours' => 8,
            'approved_hours' => 6,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 450000,
            'overtime_type' => 'weekday',
            'request_source' => 'ess_webview',
            'approval_status' => 'approved',
            'current_approver' => 'Payroll',
            'submitted_at' => '2026-07-11 10:00:00',
            'created_by' => $admin->id,
        ]);

        PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-ANALYT-082',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'blocked',
            'employee_count' => 1,
            'validation_passed' => 5,
            'validation_failed' => 1,
            'anomaly_count' => 2,
            'gross_estimate' => 12000000,
            'deduction_estimate' => 2000000,
            'net_estimate' => 10000000,
            'source_snapshot' => ['source' => 'report builder test'],
            'created_by' => $admin->id,
        ]);

        EngagementSurveyResponse::create([
            'engagement_survey_id' => EngagementSurvey::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'survey_number' => 'RPT-SVY-082',
                'title' => 'Report Builder Survey',
                'survey_type' => 'pulse',
                'audience_scope' => 'branch',
                'question_bank' => ['Saya memahami prioritas kerja bulan ini'],
                'is_anonymous' => false,
                'status' => 'running',
                'created_by' => $admin->id,
            ])->id,
            'employee_profile_id' => $employee->id,
            'response_number' => 'RPT-RESP-082',
            'engagement_score' => 64,
            'enps_score' => 6,
            'sentiment' => 'neutral',
            'status' => 'submitted',
            'submitted_at' => '2026-07-15 08:00:00',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/analytics/hr-report-builder?prompt=Buat+laporan+payroll+dan+absensi+untuk+leader&start_date=2026-07-01&end_date=2026-07-31&company_id='.$company->id.'&branch_id='.$branch->id)
            ->assertOk()
            ->assertSeeText('Builder Laporan HR')
            ->assertSeeText('Payroll')
            ->assertSeeText('Sinyal Risiko')
            ->assertSeeText('Action Item')
            ->assertSeeText('Metrik Pendukung')
            ->assertSeeText('Karyawan baru')
            ->assertSeeText('Rp 10.000.000');
    }

    public function test_seeded_admin_can_monitor_integration_health_sla_and_queue(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-COMPLI-099',
            'full_name' => 'Integration Health Demo',
            'employment_status' => 'permanent',
            'position' => 'HR Ops',
            'department' => 'Human Resource',
            'join_date' => '2026-07-01',
            'work_email' => 'compli099@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        DB::table('jobs')->insert([
            'queue' => 'notifications',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->subHour()->timestamp,
            'created_at' => now()->subHour()->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => 'failed-compli-099',
            'connection' => 'database',
            'queue' => 'integrations',
            'payload' => '{}',
            'exception' => 'Demo failed integration job',
            'failed_at' => now(),
        ]);

        $leaveType = LeavePolicyType::create([
            'code' => 'CUTI-COMPLI-099',
            'name' => 'Cuti Compliance Demo',
            'category' => 'annual',
            'unit' => 'day',
            'annual_quota_days' => 12,
            'requires_balance' => true,
            'is_paid' => true,
            'approval_required' => true,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-COMPLI-099',
            'employee_profile_id' => $employee->id,
            'leave_policy_type_id' => $leaveType->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'requested_days' => 2,
            'balance_before' => 12,
            'balance_after' => 10,
            'duration_unit' => 'day',
            'request_source' => 'ess_webview',
            'approval_status' => 'pending',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now()->subHours(30),
            'created_by' => $admin->id,
        ]);

        OvertimeRequest::create([
            'request_number' => 'OT-COMPLI-099',
            'employee_profile_id' => $employee->id,
            'overtime_date' => now()->toDateString(),
            'start_at' => now()->setTime(18, 0),
            'end_at' => now()->setTime(21, 0),
            'requested_hours' => 3,
            'approved_hours' => 0,
            'payroll_multiplier' => 1.5,
            'hourly_rate' => 50000,
            'estimated_amount' => 225000,
            'overtime_type' => 'weekday',
            'request_source' => 'ess_webview',
            'approval_status' => 'pending',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now()->subHours(14),
            'created_by' => $admin->id,
        ]);

        NotificationDelivery::create([
            'user_id' => $admin->id,
            'event_key' => 'approval.reminder',
            'recipient_name' => 'Admin Demo',
            'recipient_email' => 'admin@kurmigo.id',
            'channels' => ['email'],
            'priority' => 'high',
            'status' => 'queued',
            'subject' => 'Demo Reminder',
            'rendered_message' => 'Reminder queued demo.',
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        $biometric = BiometricConnector::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'connector_code' => 'BIO-COMPLI-099',
            'connector_name' => 'Biometric Compliance Demo',
            'vendor' => 'generic',
            'connection_type' => 'local_agent',
            'auth_mode' => 'api_key',
            'machine_code' => 'BIO-099',
            'sync_interval_minutes' => 15,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        BiometricSyncLog::create([
            'biometric_connector_id' => $biometric->id,
            'sync_number' => 'SYNC-COMPLI-099',
            'started_at' => now()->subMinutes(20),
            'finished_at' => now()->subMinutes(18),
            'status' => 'failed',
            'pulled_rows' => 10,
            'matched_rows' => 8,
            'failed_rows' => 2,
            'message' => 'Demo failed sync.',
            'created_by' => $admin->id,
        ]);

        CoretaxConnectorSetting::create([
            'company_id' => $company->id,
            'environment' => 'sandbox',
            'base_url' => 'https://coretax.example.test',
            'client_id' => 'compli099-client',
            'secret_mask' => '***099',
            'credential_hash' => 'hash-compli-099',
            'is_active' => true,
            'last_checked_at' => now()->subMinutes(10),
            'last_status' => 'error',
        ]);

        WhatsappGatewaySetting::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'gateway_code' => 'WA-COMPLI-099',
            'gateway_name' => 'WA Compliance Demo',
            'provider' => 'generic',
            'connection_type' => 'api',
            'auth_mode' => 'token',
            'status' => 'active',
            'last_ping_at' => now()->subMinutes(5),
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/settings/integration-health')
            ->assertOk()
            ->assertSeeText('Health Integrasi & SLA')
            ->assertSeeText('Queue & SLA Operasional')
            ->assertSeeText('Failed Jobs')
            ->assertSeeText('Health Connector')
            ->assertSeeText('Biometric / Attendance Agent')
            ->assertSeeText('Action Item')
            ->assertSeeText('Review failed_jobs');
    }

    public function test_seeded_admin_can_manage_sso_identity_provider_configuration(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();

        $this->actingAs($admin)
            ->get('/integrations/identity-providers')
            ->assertOk()
            ->assertSeeText('SSO & Identity Provider')
            ->assertSeeText('Tambah Identity Provider')
            ->assertSeeText('Claim Mapping')
            ->assertSeeText('Auto Provision');

        $this->actingAs($admin)
            ->post('/integrations/identity-providers', [
                'company_id' => $company->id,
                'provider_code' => 'BMP-AZUREAD-084',
                'provider_name' => 'Azure AD BMP',
                'protocol' => 'oidc',
                'domain_hint' => 'kurmigo.id',
                'issuer_url' => 'https://login.microsoftonline.com/bmp/v2.0',
                'sso_login_url' => 'https://login.microsoftonline.com/bmp/oauth2/v2.0/authorize',
                'metadata_url' => 'https://login.microsoftonline.com/bmp/v2.0/.well-known/openid-configuration',
                'client_id' => 'client-integr-084',
                'claim_email' => 'preferred_username',
                'claim_name' => 'name',
                'claim_employee_number' => 'employeeid',
                'default_role' => 'Employee',
                'auto_provision' => '1',
                'jit_update_profile' => '1',
                'enforce_sso' => '1',
                'status' => 'testing',
                'notes' => 'Pilot SSO untuk INTEGR-084.',
            ])
            ->assertRedirect('/integrations/identity-providers');

        $provider = IdentityProviderSetting::where('provider_code', 'BMP-AZUREAD-084')->firstOrFail();

        $this->assertSame('oidc', $provider->protocol);
        $this->assertSame('preferred_username', $provider->claim_mapping['email']);
        $this->assertSame('employeeid', $provider->claim_mapping['employee_number']);
        $this->assertTrue($provider->auto_provision);
        $this->assertTrue($provider->enforce_sso);

        $this->actingAs($admin)
            ->get('/integrations/identity-providers')
            ->assertOk()
            ->assertSeeText('Azure AD BMP')
            ->assertSeeText('BMP-AZUREAD-084')
            ->assertSeeText('SSO wajib');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'identity_provider.created',
            'auditable_id' => $provider->id,
        ]);
    }

    public function test_seeded_admin_can_generate_payroll_accounting_erp_journal_by_cost_center(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-INTEGR-088',
            'full_name' => 'ERP Journal Demo',
            'employment_status' => 'permanent',
            'position' => 'Payroll Analyst',
            'department' => 'Human Resource',
            'cost_center' => 'OPS-HO',
            'join_date' => '2026-07-01',
            'work_email' => 'integr088@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        PayrollPayslip::create([
            'employee_profile_id' => $employee->id,
            'company_id' => $company->id,
            'payslip_number' => 'SLIP-INTEGR-088',
            'period' => '2026-07',
            'status' => 'published',
            'gross_pay' => 15000000,
            'total_deduction' => 3000000,
            'net_pay' => 12000000,
            'earning_snapshot' => ['Gaji Pokok' => 15000000],
            'deduction_snapshot' => ['PPh 21 dan BPJS' => 3000000],
            'employee_snapshot' => ['full_name' => $employee->full_name],
            'checksum' => 'integr088',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/integrations/accounting-erp')
            ->assertOk()
            ->assertSeeText('Accounting & ERP')
            ->assertSeeText('Setup Connector ERP')
            ->assertSeeText('Generate Journal Payroll');

        $this->actingAs($admin)
            ->post('/integrations/accounting-erp/connectors', [
                'company_id' => $company->id,
                'connector_code' => 'ERP-BMP-088',
                'connector_name' => 'ERP BMP Finance',
                'erp_system' => 'odoo',
                'connection_type' => 'api_push',
                'base_url' => 'https://erp.example.test/api',
                'auth_mode' => 'api_key',
                'credential_reference' => 'vault:erp-bmp-088',
                'salary_expense_gl' => '6100-088',
                'bpjs_expense_gl' => '6100-021',
                'tax_payable_gl' => '2100-088',
                'net_payable_gl' => '2100-101',
                'default_cost_center' => 'OPS-HO',
                'journal_mode' => 'summary_by_cost_center',
                'status' => 'active',
                'notes' => 'Pilot jurnal ERP untuk payroll Juli.',
            ])
            ->assertRedirect('/integrations/accounting-erp');

        $connector = AccountingErpConnector::where('connector_code', 'ERP-BMP-088')->firstOrFail();

        $this->actingAs($admin)
            ->post('/integrations/accounting-erp/journals', [
                'accounting_erp_connector_id' => $connector->id,
                'company_id' => $company->id,
                'period' => '2026-07',
                'posting_date' => '2026-07-31',
                'journal_type' => 'payroll_accrual',
                'notes' => 'Generate jurnal payroll INTEGR-088.',
            ])
            ->assertRedirect('/integrations/accounting-erp');

        $journal = PayrollAccountingJournalBatch::where('period', '2026-07')
            ->where('accounting_erp_connector_id', $connector->id)
            ->firstOrFail();

        $this->assertSame('ready_to_post', $journal->status);
        $this->assertSame(3, $journal->line_count);
        $this->assertSame(15000000.0, (float) $journal->debit_total);
        $this->assertSame(15000000.0, (float) $journal->credit_total);
        $this->assertNotEmpty($journal->payload_checksum);
        $this->assertDatabaseHas('payroll_accounting_journal_lines', [
            'payroll_accounting_journal_batch_id' => $journal->id,
            'cost_center_code' => 'OPS-HO',
            'gl_account' => '6100-088',
            'debit_credit' => 'debit',
            'amount' => 15000000,
        ]);
        $this->assertDatabaseHas('payroll_accounting_journal_lines', [
            'payroll_accounting_journal_batch_id' => $journal->id,
            'gl_account' => '2100-088',
            'debit_credit' => 'credit',
            'amount' => 3000000,
        ]);
        $this->assertDatabaseHas('payroll_accounting_journal_lines', [
            'payroll_accounting_journal_batch_id' => $journal->id,
            'gl_account' => '2100-101',
            'debit_credit' => 'credit',
            'amount' => 12000000,
        ]);
        $this->assertSame(3, PayrollAccountingJournalLine::where('payroll_accounting_journal_batch_id', $journal->id)->count());

        $this->actingAs($admin)
            ->get("/integrations/accounting-erp/journals/{$journal->id}/download")
            ->assertOk()
            ->assertSee($journal->journal_number)
            ->assertSee('OPS-HO')
            ->assertSee('6100-088');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'accounting_erp_connector.created',
            'auditable_id' => $connector->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payroll_accounting_journal.generated',
            'auditable_id' => $journal->id,
        ]);
    }

    public function test_seeded_admin_can_prepare_data_warehouse_export_snapshot_manifest(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();
        $company = Company::where('code', 'BMP')->firstOrFail();
        $branch = Branch::where('code', 'BMP-HO')->firstOrFail();

        $employee = EmployeeProfile::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'KRM-INTEGR-092',
            'full_name' => 'Warehouse Export Demo',
            'employment_status' => 'permanent',
            'position' => 'BI Analyst',
            'department' => 'Human Resource',
            'join_date' => '2026-07-01',
            'work_email' => 'integr092@kurmigo.id',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        AttendanceClockEvent::create([
            'employee_profile_id' => $employee->id,
            'event_number' => 'CLK-INTEGR-092',
            'event_type' => 'clock_in',
            'event_at' => '2026-07-10 08:00:00',
            'source' => 'mobile_webview',
            'is_liveness_passed' => true,
            'is_device_trusted' => true,
            'status' => 'valid',
            'created_by' => $admin->id,
        ]);

        PayrollRun::create([
            'company_id' => $company->id,
            'run_number' => 'RUN-INTEGR-092',
            'period' => '2026-07',
            'cutoff_start' => '2026-07-01',
            'cutoff_end' => '2026-07-31',
            'run_type' => 'regular',
            'status' => 'finalized',
            'employee_count' => 1,
            'validation_passed' => 5,
            'validation_failed' => 0,
            'anomaly_count' => 0,
            'gross_estimate' => 9000000,
            'deduction_estimate' => 1000000,
            'net_estimate' => 8000000,
            'source_snapshot' => ['source' => 'warehouse export test'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/integrations/data-warehouse-export?period=2026-07&format=csv&company_id='.$company->id.'&branch_id='.$branch->id)
            ->assertOk()
            ->assertSeeText('Data Warehouse Export')
            ->assertSeeText('Dataset Catalog')
            ->assertSeeText('Snapshot Plan')
            ->assertSeeText('Quality Gate')
            ->assertSeeText('dim_employee')
            ->assertSeeText('fact_attendance')
            ->assertSeeText('HRIS-BI-2026-07')
            ->assertSeeText('CSV');
    }

    public function test_seeded_admin_can_view_public_api_webhook_catalog_and_integration_logs(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationSeeder::class);
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@kurmigo.id')->firstOrFail();

        AuditLog::create([
            'actor_id' => $admin->id,
            'event' => 'integration.webhook.delivered',
            'summary' => 'Webhook leave.approved delivered to partner endpoint.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'FeatureTest',
        ]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'event' => 'api.key.rotated',
            'summary' => 'API key partner psikotest rotated by admin.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'FeatureTest',
        ]);

        $this->actingAs($admin)
            ->get('/integrations/public-api-webhooks')
            ->assertOk()
            ->assertSeeText('Public API & Webhook')
            ->assertSeeText('Endpoint Catalog')
            ->assertSeeText('/api/v1/employees')
            ->assertSeeText('Webhook Event')
            ->assertSeeText('Rate Limit & Auth Policy')
            ->assertSeeText('Log Integrasi Terbaru')
            ->assertSeeText('integration.webhook.delivered')
            ->assertSeeText('API key partner psikotest rotated');
    }
}
