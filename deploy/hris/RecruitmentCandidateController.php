<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentManpowerRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentCandidateController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $stages = ['sourcing', 'screening', 'interview', 'offer', 'hired'];
        $candidates = RecruitmentCandidate::with('manpowerRequest.company')->latest()->limit(200)->get();

        return view('recruitment-candidates', [
            'active' => 'recruitment-candidates',
            'manpowerRequests' => RecruitmentManpowerRequest::with(['company', 'branch'])
                ->whereIn('status', ['submitted', 'approved'])
                ->latest()
                ->limit(100)
                ->get(),
            'candidates' => $candidates,
            'stages' => $stages,
            'summary' => [
                'total' => $candidates->count(),
                'active' => $candidates->where('candidate_status', 'active')->count(),
                'interview' => $candidates->where('pipeline_stage', 'interview')->count(),
                'hired' => $candidates->where('pipeline_stage', 'hired')->count(),
            ],
            'stageCounts' => collect($stages)->mapWithKeys(fn (string $stage) => [
                $stage => $candidates->where('pipeline_stage', $stage)->count(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_manpower_request_id' => ['nullable', 'exists:recruitment_manpower_requests,id'],
            'candidate_number' => ['required', 'string', 'max:80', 'unique:recruitment_candidates,candidate_number'],
            'full_name' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'source_channel' => ['required', Rule::in(['direct', 'job_portal', 'referral', 'headhunter', 'social_media', 'campus', 'internal'])],
            'current_position' => ['nullable', 'string', 'max:160'],
            'current_company' => ['nullable', 'string', 'max:160'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'pipeline_stage' => ['required', Rule::in(['sourcing', 'screening', 'interview', 'offer', 'hired'])],
            'candidate_status' => ['required', Rule::in(['active', 'hold', 'rejected', 'withdrawn', 'hired'])],
            'fit_score' => ['required', 'integer', 'min:0', 'max:100'],
            'owner_recruiter' => ['nullable', 'string', 'max:160'],
            'applied_at' => ['nullable', 'date'],
            'last_touch_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $candidate = RecruitmentCandidate::create([
            ...collect($data)->except('tags')->all(),
            'tags' => collect($data['tags'] ?? [])->filter()->values()->all(),
            'portal_token' => Str::uuid()->toString(),
            'portal_invited_at' => now(),
            'stage_history' => [[
                'stage' => $data['pipeline_stage'],
                'status' => $data['candidate_status'],
                'changed_at' => now()->toDateTimeString(),
                'changed_by' => auth()->user()?->name,
            ]],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('recruitment_candidate.created', "Kandidat ATS {$candidate->candidate_number} dibuat.", $candidate, [], $candidate->toArray(), $request);

        return redirect()->route('recruitment.candidates')->with('success', 'Kandidat berhasil masuk pipeline ATS.');
    }
}
