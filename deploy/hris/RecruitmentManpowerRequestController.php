<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\RecruitmentManpowerRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentManpowerRequestController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        return view('recruitment-manpower-requests', [
            'active' => 'recruitment-manpower',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'requests' => RecruitmentManpowerRequest::with(['company', 'branch'])->latest()->limit(150)->get(),
            'summary' => [
                'submitted' => RecruitmentManpowerRequest::where('status', 'submitted')->count(),
                'approved' => RecruitmentManpowerRequest::where('status', 'approved')->count(),
                'open_headcount' => RecruitmentManpowerRequest::whereIn('status', ['submitted', 'approved'])->sum('headcount'),
                'urgent' => RecruitmentManpowerRequest::where('priority', 'urgent')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'request_number' => ['required', 'string', 'max:80', 'unique:recruitment_manpower_requests,request_number'],
            'position_title' => ['required', 'string', 'max:180'],
            'department' => ['nullable', 'string', 'max:120'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'internship', 'outsourcing', 'daily_worker'])],
            'headcount' => ['required', 'integer', 'min:1', 'max:500'],
            'target_join_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'request_reason' => ['required', Rule::in(['replacement', 'new_position', 'expansion', 'project', 'succession'])],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'job_summary' => ['nullable', 'string', 'max:1200'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['nullable', 'string', 'max:220'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'requested_by' => ['nullable', 'string', 'max:160'],
            'requested_at' => ['nullable', 'date'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'approval_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $company = Company::findOrFail($data['company_id']);
        $branch = ! empty($data['branch_id']) ? Branch::find($data['branch_id']) : null;

        $requestModel = RecruitmentManpowerRequest::create([
            ...collect($data)->except('requirements')->all(),
            'requirements' => collect($data['requirements'] ?? [])->filter()->values()->map(fn (string $item) => [
                'item' => $item,
                'category' => 'requirement',
            ])->all(),
            'approved_at' => $data['status'] === 'approved' ? now() : null,
            'approval_snapshot' => [
                'company' => $company->code,
                'branch' => $branch?->code,
                'requested_headcount' => (int) $data['headcount'],
                'budget_range' => [$data['budget_min'] ?? null, $data['budget_max'] ?? null],
                'approval_status' => $data['status'],
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('recruitment_manpower_request.created', "Pengajuan manpower {$requestModel->request_number} dibuat.", $requestModel, [], $requestModel->toArray(), $request);

        return redirect()->route('recruitment.manpower-requests')->with('success', 'Pengajuan manpower berhasil dibuat.');
    }
}
