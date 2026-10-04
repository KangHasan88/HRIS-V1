<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentOfferLetter;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentOfferLetterController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $offers = RecruitmentOfferLetter::with('candidate.manpowerRequest')->latest()->limit(200)->get();

        return view('recruitment-offer-letters', [
            'active' => 'recruitment-offer-letters',
            'candidates' => RecruitmentCandidate::with('manpowerRequest')
                ->whereIn('candidate_status', ['active', 'hold', 'hired'])
                ->orderBy('full_name')
                ->limit(200)
                ->get(),
            'offers' => $offers,
            'summary' => [
                'draft' => $offers->where('status', 'draft')->count(),
                'sent' => $offers->where('status', 'sent')->count(),
                'signed' => $offers->where('status', 'signed')->count(),
                'pending' => $offers->whereIn('status', ['sent', 'viewed'])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'offer_number' => ['required', 'string', 'max:80', 'unique:recruitment_offer_letters,offer_number'],
            'position_title' => ['required', 'string', 'max:180'],
            'join_date' => ['nullable', 'date'],
            'salary_offer' => ['required', 'numeric', 'min:0'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'probation', 'internship'])],
            'template_name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'sent', 'viewed', 'signed', 'declined', 'expired', 'cancelled'])],
            'sent_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'esign_provider' => ['nullable', 'string', 'max:80'],
            'esign_reference' => ['nullable', 'string', 'max:160'],
            'signer_email' => ['nullable', 'email', 'max:180'],
            'offer_summary' => ['nullable', 'string', 'max:1600'],
        ]);

        $candidate = RecruitmentCandidate::findOrFail($data['recruitment_candidate_id']);
        $offer = RecruitmentOfferLetter::create([
            ...$data,
            'tracking_events' => [[
                'status' => $data['status'],
                'event_at' => now()->toDateTimeString(),
                'actor' => auth()->user()?->name,
                'note' => 'Offer letter dibuat dari HRIS.',
            ]],
            'created_by' => auth()->id(),
        ]);

        if (in_array($data['status'], ['sent', 'viewed', 'signed'], true)) {
            $candidate->update([
                'pipeline_stage' => $data['status'] === 'signed' ? 'hired' : 'offer',
                'candidate_status' => $data['status'] === 'signed' ? 'hired' : $candidate->candidate_status,
                'last_touch_at' => now()->toDateString(),
            ]);
        }

        AuditLogger::record('recruitment_offer_letter.created', "Offer letter {$offer->offer_number} dibuat.", $offer, [], $offer->toArray(), $request);

        return redirect()->route('recruitment.offer-letters')->with('success', 'Offer letter dan tracking tanda tangan berhasil disimpan.');
    }
}
