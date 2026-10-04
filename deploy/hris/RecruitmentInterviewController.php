<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentInterview;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentInterviewController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $interviews = RecruitmentInterview::with('candidate.manpowerRequest')
            ->orderBy('scheduled_at')
            ->limit(200)
            ->get();

        return view('recruitment-interviews', [
            'active' => 'recruitment-interviews',
            'candidates' => RecruitmentCandidate::with('manpowerRequest')
                ->whereIn('candidate_status', ['active', 'hold'])
                ->orderBy('full_name')
                ->limit(200)
                ->get(),
            'interviews' => $interviews,
            'summary' => [
                'scheduled' => $interviews->where('status', 'scheduled')->count(),
                'completed' => $interviews->where('status', 'completed')->count(),
                'average_score' => round((float) $interviews->where('overall_score', '>', 0)->avg('overall_score'), 1),
                'recommended' => $interviews->whereIn('recommendation', ['strong_hire', 'hire'])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'interview_number' => ['required', 'string', 'max:80', 'unique:recruitment_interviews,interview_number'],
            'interview_stage' => ['required', Rule::in(['hr', 'user', 'technical', 'final', 'offering'])],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'interview_mode' => ['required', Rule::in(['online', 'onsite', 'phone'])],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'interviewer_name' => ['required', 'string', 'max:160'],
            'interviewer_role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled', 'rescheduled', 'no_show'])],
            'communication_score' => ['required', 'integer', 'min:0', 'max:100'],
            'technical_score' => ['required', 'integer', 'min:0', 'max:100'],
            'culture_score' => ['required', 'integer', 'min:0', 'max:100'],
            'experience_score' => ['required', 'integer', 'min:0', 'max:100'],
            'recommendation' => ['required', Rule::in(['strong_hire', 'hire', 'hold', 'reject'])],
            'feedback_notes' => ['nullable', 'string', 'max:1600'],
        ]);

        $scoreFields = collect([
            $data['communication_score'],
            $data['technical_score'],
            $data['culture_score'],
            $data['experience_score'],
        ]);
        $overallScore = round($scoreFields->avg(), 2);

        $candidate = RecruitmentCandidate::findOrFail($data['recruitment_candidate_id']);
        $interview = RecruitmentInterview::create([
            ...$data,
            'overall_score' => $overallScore,
            'scorecard_snapshot' => [
                'candidate_number' => $candidate->candidate_number,
                'candidate_name' => $candidate->full_name,
                'position' => $candidate->manpowerRequest?->position_title ?: $candidate->current_position,
                'scores' => [
                    'communication' => $data['communication_score'],
                    'technical' => $data['technical_score'],
                    'culture' => $data['culture_score'],
                    'experience' => $data['experience_score'],
                ],
                'overall_score' => $overallScore,
            ],
            'created_by' => auth()->id(),
        ]);

        if (in_array($data['status'], ['scheduled', 'completed'], true)) {
            $candidate->update([
                'pipeline_stage' => 'interview',
                'last_touch_at' => $interview->scheduled_at->toDateString(),
            ]);
        }

        AuditLogger::record('recruitment_interview.scheduled', "Interview {$interview->interview_number} dibuat.", $interview, [], $interview->toArray(), $request);

        return redirect()->route('recruitment.interviews')->with('success', 'Jadwal interview dan scorecard berhasil disimpan.');
    }
}
