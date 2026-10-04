<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCandidateCheck;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentCandidateCheckController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $checks = RecruitmentCandidateCheck::with('candidate.manpowerRequest')
            ->orderByRaw("case when status in ('pending','in_progress') then 0 else 1 end")
            ->orderBy('due_at')
            ->latest('id')
            ->limit(200)
            ->get();

        return view('recruitment-candidate-checks', [
            'active' => 'recruitment-candidate-checks',
            'candidates' => RecruitmentCandidate::with('manpowerRequest')
                ->whereIn('candidate_status', ['active', 'hold', 'hired'])
                ->orderBy('full_name')
                ->limit(200)
                ->get(),
            'checks' => $checks,
            'summary' => [
                'total' => $checks->count(),
                'open' => $checks->whereIn('status', ['pending', 'in_progress'])->count(),
                'passed' => $checks->where('result', 'passed')->count(),
                'blocking' => $checks->where('is_blocking', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'check_number' => ['required', 'string', 'max:80', 'unique:recruitment_candidate_checks,check_number'],
            'check_type' => ['required', Rule::in(['medical_checkup', 'background_check', 'reference_check'])],
            'vendor_name' => ['nullable', 'string', 'max:160'],
            'reference_name' => ['nullable', 'string', 'max:160'],
            'reference_contact' => ['nullable', 'string', 'max:160'],
            'requested_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'result' => ['required', Rule::in(['waiting', 'passed', 'passed_with_note', 'failed', 'need_review'])],
            'is_blocking' => ['nullable', 'boolean'],
            'findings' => ['nullable', 'string', 'max:1600'],
            'evidence_links' => ['nullable', 'array'],
            'evidence_links.*' => ['nullable', 'string', 'max:255'],
        ]);

        $check = RecruitmentCandidateCheck::create([
            ...collect($data)->except('evidence_links', 'is_blocking')->all(),
            'is_blocking' => (bool) ($data['is_blocking'] ?? false),
            'evidence_links' => collect($data['evidence_links'] ?? [])->filter()->values()->all(),
            'created_by' => auth()->id(),
        ]);

        $candidate = $check->candidate;
        if ($candidate && in_array($check->status, ['in_progress', 'completed'], true)) {
            $candidate->update([
                'last_touch_at' => now()->toDateString(),
            ]);
        }

        AuditLogger::record('recruitment_candidate_check.created', "Candidate check {$check->check_number} dibuat.", $check, [], $check->toArray(), $request);

        return redirect()->route('recruitment.candidate-checks')->with('success', 'Tracker pengecekan kandidat berhasil disimpan.');
    }
}
