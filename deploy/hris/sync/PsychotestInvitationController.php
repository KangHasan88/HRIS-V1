<?php

namespace App\Http\Controllers;

use App\Models\PsychotestInvitation;
use App\Models\PsychotestPackage;
use App\Models\PsychotestResult;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCandidateCheck;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PsychotestInvitationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $invitations = PsychotestInvitation::with(['candidate.manpowerRequest', 'package.provider', 'result'])
            ->orderByRaw("case when status in ('sent','opened','in_progress') then 0 else 1 end")
            ->latest('sent_at')
            ->latest()
            ->limit(160)
            ->get();

        $results = PsychotestResult::with(['candidate.manpowerRequest', 'package.provider', 'invitation'])
            ->latest()
            ->limit(160)
            ->get();

        return view('recruitment-psychotest-invitations', [
            'active' => 'recruitment-psychotest-invitations',
            'candidates' => RecruitmentCandidate::with('manpowerRequest')
                ->whereIn('candidate_status', ['active', 'hold', 'hired'])
                ->orderBy('full_name')
                ->limit(220)
                ->get(),
            'packages' => PsychotestPackage::with('provider')
                ->whereIn('status', ['active', 'testing'])
                ->orderBy('package_code')
                ->get(),
            'invitations' => $invitations,
            'results' => $results,
            'summary' => [
                'total' => $invitations->count(),
                'sent' => $invitations->whereIn('status', ['sent', 'opened', 'in_progress'])->count(),
                'completed' => $invitations->where('status', 'completed')->count(),
                'synced' => $invitations->where('result_sync_status', 'synced')->count(),
                'avg_score' => (int) round($results->avg('overall_score') ?? 0),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'psychotest_package_id' => ['required', 'exists:psychotest_packages,id'],
            'invitation_number' => ['required', 'string', 'max:100', 'unique:psychotest_invitations,invitation_number'],
            'delivery_channel' => ['required', Rule::in(['email', 'whatsapp', 'manual_link', 'candidate_portal'])],
            'sent_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:sent_at'],
            'status' => ['required', Rule::in(['draft', 'sent', 'opened', 'in_progress', 'completed', 'expired', 'cancelled'])],
            'instruction' => ['nullable', 'string', 'max:1500'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $invitation = PsychotestInvitation::create([
            ...$data,
            'token' => Str::random(48),
            'sent_at' => $data['sent_at'] ?? ($data['status'] === 'sent' ? now() : null),
            'result_sync_status' => 'not_started',
            'created_by' => auth()->id(),
        ]);

        $invitation->candidate?->update([
            'pipeline_stage' => 'assessment',
            'last_touch_at' => now()->toDateString(),
        ]);

        AuditLogger::record('psychotest_invitation.created', "Undangan psikotest {$invitation->invitation_number} dibuat.", $invitation, [], $invitation->toArray(), $request);

        return redirect()->route('recruitment.psychotest-invitations')->with('success', 'Undangan psikotest berhasil dibuat.');
    }

    public function runner(string $token): View
    {
        $invitation = PsychotestInvitation::with(['candidate.manpowerRequest', 'package.provider', 'result'])
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->opened_at) {
            $invitation->update([
                'opened_at' => now(),
                'status' => in_array($invitation->status, ['draft', 'sent'], true) ? 'opened' : $invitation->status,
            ]);
        }

        return view('public-psychotest-runner', [
            'invitation' => $invitation,
            'isExpired' => $invitation->expires_at && $invitation->expires_at->isPast(),
        ]);
    }

    public function consent(Request $request, string $token): RedirectResponse
    {
        $invitation = PsychotestInvitation::where('token', $token)->firstOrFail();

        $request->validate([
            'candidate_consent' => ['accepted'],
        ]);

        $invitation->update([
            'consented_at' => now(),
            'started_at' => $invitation->started_at ?: now(),
            'status' => 'in_progress',
        ]);

        AuditLogger::record('psychotest_invitation.consented', "Consent psikotest {$invitation->invitation_number} diterima.", $invitation, [], $invitation->toArray(), $request);

        return redirect()->route('psychotest.runner', $invitation->token)->with('success', 'Consent diterima. Test internal siap dikerjakan.');
    }

    public function submitResult(Request $request, string $token): RedirectResponse
    {
        $invitation = PsychotestInvitation::with(['candidate', 'package'])->where('token', $token)->firstOrFail();

        $data = $request->validate([
            'overall_score' => ['required', 'integer', 'min:0', 'max:100'],
            'fit_recommendation' => ['required', Rule::in(['strong_fit', 'fit', 'conditional_fit', 'need_review', 'not_fit'])],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'dimension_scores' => ['nullable', 'string', 'max:1000'],
            'summary' => ['nullable', 'string', 'max:1500'],
        ]);

        $result = PsychotestResult::updateOrCreate(
            ['psychotest_invitation_id' => $invitation->id],
            [
                'recruitment_candidate_id' => $invitation->recruitment_candidate_id,
                'psychotest_package_id' => $invitation->psychotest_package_id,
                'result_number' => 'PSY-RES-'.$invitation->id,
                'overall_score' => $data['overall_score'],
                'fit_recommendation' => $data['fit_recommendation'],
                'risk_level' => $data['risk_level'],
                'dimension_scores' => collect(explode(',', (string) ($data['dimension_scores'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
                'raw_result' => [
                    'source' => 'internal_engine_preview',
                    'package' => $invitation->package?->package_code,
                    'submitted_at' => now()->toDateTimeString(),
                ],
                'summary' => $data['summary'] ?? null,
                'status' => 'computed',
                'created_by' => $invitation->created_by,
            ]
        );

        $invitation->update([
            'completed_at' => now(),
            'status' => 'completed',
            'result_sync_status' => 'synced',
        ]);

        $invitation->candidate?->update([
            'fit_score' => $result->overall_score,
            'pipeline_stage' => 'assessment',
            'last_touch_at' => now()->toDateString(),
        ]);

        RecruitmentCandidateCheck::firstOrCreate(
            ['check_number' => 'PSY-CHECK-'.$invitation->id],
            [
                'recruitment_candidate_id' => $invitation->recruitment_candidate_id,
                'check_type' => 'psychotest',
                'vendor_name' => $invitation->package?->provider?->provider_name ?: 'Internal Engine',
                'requested_at' => $invitation->sent_at?->toDateString(),
                'completed_at' => now()->toDateString(),
                'status' => 'completed',
                'result' => in_array($result->fit_recommendation, ['strong_fit', 'fit'], true) ? 'passed' : 'need_review',
                'is_blocking' => in_array($result->risk_level, ['high', 'critical'], true),
                'findings' => $result->summary,
                'evidence_links' => [route('psychotest.runner', $invitation->token)],
                'created_by' => $invitation->created_by,
            ]
        );

        AuditLogger::record('psychotest_result.synced', "Hasil psikotest {$result->result_number} tersinkron.", $result, [], $result->toArray(), $request);

        return redirect()->route('psychotest.runner', $invitation->token)->with('success', 'Hasil psikotest berhasil disubmit dan tersinkron ke ATS.');
    }
}
