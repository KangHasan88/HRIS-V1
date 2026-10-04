<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentEmployeeConversion;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentEmployeeConversionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $conversions = RecruitmentEmployeeConversion::with(['candidate.manpowerRequest', 'employeeProfile', 'company', 'branch'])
            ->latest()
            ->limit(200)
            ->get();

        return view('recruitment-employee-conversions', [
            'active' => 'recruitment-employee-conversions',
            'candidates' => RecruitmentCandidate::with('manpowerRequest.company')
                ->whereIn('pipeline_stage', ['offer', 'hired'])
                ->whereIn('candidate_status', ['active', 'hired'])
                ->orderBy('full_name')
                ->limit(200)
                ->get(),
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'conversions' => $conversions,
            'summary' => [
                'total' => $conversions->count(),
                'converted' => $conversions->where('conversion_status', 'converted')->count(),
                'draft' => $conversions->where('conversion_status', 'draft')->count(),
                'employees' => $conversions->whereNotNull('employee_profile_id')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'conversion_number' => ['required', 'string', 'max:80', 'unique:recruitment_employee_conversions,conversion_number'],
            'employee_number' => ['required', 'string', 'max:80', 'unique:recruitment_employee_conversions,employee_number', 'unique:employee_profiles,employee_number'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employment_status' => ['required', Rule::in(['probation', 'contract', 'permanent', 'intern'])],
            'position' => ['nullable', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:160'],
            'grade' => ['nullable', 'string', 'max:80'],
            'cost_center' => ['nullable', 'string', 'max:120'],
            'join_date' => ['nullable', 'date'],
            'work_email' => ['nullable', 'email', 'max:180'],
            'conversion_status' => ['required', Rule::in(['draft', 'converted'])],
            'notes' => ['nullable', 'string', 'max:1600'],
        ]);

        $candidate = RecruitmentCandidate::with('manpowerRequest')->findOrFail($data['recruitment_candidate_id']);

        $conversion = DB::transaction(function () use ($data, $candidate, $request) {
            $employee = null;

            if ($data['conversion_status'] === 'converted') {
                $employee = EmployeeProfile::create([
                    'company_id' => $data['company_id'] ?? $candidate->manpowerRequest?->company_id,
                    'branch_id' => $data['branch_id'] ?? $candidate->manpowerRequest?->branch_id,
                    'employee_number' => $data['employee_number'],
                    'full_name' => $candidate->full_name,
                    'employment_status' => $data['employment_status'],
                    'position' => $data['position'] ?: $candidate->manpowerRequest?->position_title ?: $candidate->current_position,
                    'department' => $data['department'] ?? null,
                    'grade' => $data['grade'] ?? null,
                    'cost_center' => $data['cost_center'] ?? null,
                    'join_date' => $data['join_date'] ?? null,
                    'work_email' => $data['work_email'] ?? null,
                    'personal_email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'address_snapshot' => ['source' => 'recruitment_conversion', 'candidate_number' => $candidate->candidate_number],
                    'document_summary' => [
                        'converted_from_candidate' => true,
                        'candidate_number' => $candidate->candidate_number,
                        'offer_stage' => $candidate->pipeline_stage,
                    ],
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);

                $candidate->update([
                    'pipeline_stage' => 'hired',
                    'candidate_status' => 'hired',
                    'last_touch_at' => now()->toDateString(),
                ]);
            }

            $conversion = RecruitmentEmployeeConversion::create([
                ...$data,
                'company_id' => $data['company_id'] ?? $candidate->manpowerRequest?->company_id,
                'branch_id' => $data['branch_id'] ?? $candidate->manpowerRequest?->branch_id,
                'employee_profile_id' => $employee?->id,
                'mapping_snapshot' => [
                    'candidate_number' => $candidate->candidate_number,
                    'candidate_name' => $candidate->full_name,
                    'candidate_email' => $candidate->email,
                    'manpower_request' => $candidate->manpowerRequest?->request_number,
                    'target_position' => $data['position'] ?: $candidate->manpowerRequest?->position_title,
                    'employee_created' => (bool) $employee,
                ],
                'created_by' => auth()->id(),
            ]);

            AuditLogger::record('recruitment_employee_conversion.created', "Konversi kandidat {$conversion->conversion_number} dibuat.", $conversion, [], $conversion->toArray(), $request);

            if ($employee) {
                AuditLogger::record('employee_profile.created_from_candidate', "Employee {$employee->employee_number} dibuat dari kandidat {$candidate->candidate_number}.", $employee, [], $employee->toArray(), $request);
            }

            return $conversion;
        });

        return redirect()->route('recruitment.employee-conversions')->with('success', $conversion->conversion_status === 'converted'
            ? 'Kandidat berhasil dikonversi menjadi employee master.'
            : 'Draft konversi kandidat berhasil disimpan.');
    }
}
