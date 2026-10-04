<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCandidateCheck;
use App\Models\RecruitmentCandidateDocument;
use App\Models\RecruitmentEmployeeConversion;
use App\Models\RecruitmentInterview;
use App\Models\RecruitmentManpowerRequest;
use App\Models\RecruitmentOfferLetter;
use App\Models\RecruitmentOnboardingJourney;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecruitmentCommandCenterController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $openManpower = RecruitmentManpowerRequest::whereIn('status', ['approved', 'open', 'in_progress'])->count();
        $activeCandidates = RecruitmentCandidate::where('candidate_status', 'active')->count();
        $portalNeedsReview = RecruitmentCandidateDocument::whereIn('verification_status', ['submitted', 'need_revision'])->count();
        $scheduledInterviews = RecruitmentInterview::whereIn('status', ['scheduled', 'rescheduled'])->count();
        $pendingOffers = RecruitmentOfferLetter::whereIn('status', ['draft', 'sent', 'negotiation'])->count();
        $blockingChecks = RecruitmentCandidateCheck::where('is_blocking', true)->whereIn('status', ['requested', 'in_progress'])->count();
        $acceptedOffers = RecruitmentOfferLetter::whereIn('status', ['accepted', 'signed'])->count();
        $convertedEmployees = RecruitmentEmployeeConversion::whereIn('conversion_status', ['converted', 'completed'])->count();
        $activeJourneys = RecruitmentOnboardingJourney::whereIn('journey_status', ['planned', 'active', 'in_progress'])->count();

        $checks = collect([
            [
                'label' => 'Permintaan tenaga kerja',
                'value' => $openManpower,
                'status' => $openManpower > 0 ? 'Aktif' : 'Belum ada',
                'note' => 'Pastikan kebutuhan posisi sudah disetujui sebelum sourcing kandidat.',
                'route' => 'recruitment.manpower-requests',
            ],
            [
                'label' => 'Kandidat aktif',
                'value' => $activeCandidates,
                'status' => $activeCandidates > 0 ? 'Berjalan' : 'Kosong',
                'note' => 'Pipeline kandidat harus punya stage, owner recruiter, dan skor screening awal.',
                'route' => 'recruitment.candidates',
            ],
            [
                'label' => 'Dokumen portal',
                'value' => $portalNeedsReview,
                'status' => $portalNeedsReview === 0 ? 'Terkontrol' : 'Perlu review',
                'note' => 'Dokumen yang dikirim kandidat dari portal perlu diverifikasi HR.',
                'route' => 'recruitment.candidate-portal',
            ],
            [
                'label' => 'Interview terjadwal',
                'value' => $scheduledInterviews,
                'status' => $scheduledInterviews > 0 ? 'Terjadwal' : 'Belum ada',
                'note' => 'Gunakan scorecard supaya keputusan interview konsisten.',
                'route' => 'recruitment.interviews',
            ],
            [
                'label' => 'Offer belum final',
                'value' => $pendingOffers,
                'status' => $pendingOffers === 0 ? 'Terkontrol' : 'Follow-up',
                'note' => 'Pantau offer yang masih draft, sent, atau negosiasi.',
                'route' => 'recruitment.offer-letters',
            ],
            [
                'label' => 'Pengecekan blocking',
                'value' => $blockingChecks,
                'status' => $blockingChecks === 0 ? 'Aman' : 'Ada risiko',
                'note' => 'Termasuk background check, referensi, medis, dan integrasi psikotes.',
                'route' => 'recruitment.candidate-checks',
            ],
            [
                'label' => 'Konversi ke employee',
                'value' => $convertedEmployees,
                'status' => $acceptedOffers > 0 && $convertedEmployees >= $acceptedOffers ? 'Sinkron' : 'Perlu handoff',
                'note' => 'Kandidat hired perlu dibuatkan employee profile sebelum onboarding.',
                'route' => 'recruitment.employee-conversions',
            ],
            [
                'label' => 'Welcome journey',
                'value' => $activeJourneys,
                'status' => $activeJourneys > 0 ? 'Berjalan' : 'Belum mulai',
                'note' => 'Onboarding awal dan probation plan dibuat setelah employee profile siap.',
                'route' => 'recruitment.onboarding-journeys',
            ],
        ]);

        $healthy = $checks->whereIn('status', ['Aktif', 'Berjalan', 'Terkontrol', 'Terjadwal', 'Aman', 'Sinkron'])->count();

        return view('recruitment.command-center', [
            'active' => 'recruitment-command-center',
            'summary' => [
                'readiness' => $checks->count() > 0 ? round(($healthy / $checks->count()) * 100) : 0,
                'manpower' => RecruitmentManpowerRequest::count(),
                'open_manpower' => $openManpower,
                'candidates' => RecruitmentCandidate::count(),
                'active_candidates' => $activeCandidates,
                'documents' => RecruitmentCandidateDocument::count(),
                'portal_needs_review' => $portalNeedsReview,
                'interviews' => RecruitmentInterview::count(),
                'scheduled_interviews' => $scheduledInterviews,
                'offers' => RecruitmentOfferLetter::count(),
                'accepted_offers' => $acceptedOffers,
                'checks' => RecruitmentCandidateCheck::count(),
                'blocking_checks' => $blockingChecks,
                'conversions' => RecruitmentEmployeeConversion::count(),
                'journeys' => RecruitmentOnboardingJourney::count(),
            ],
            'checks' => $checks,
            'manpowerRequests' => RecruitmentManpowerRequest::with(['company', 'branch'])->latest()->limit(8)->get(),
            'candidates' => RecruitmentCandidate::with(['manpowerRequest', 'documents', 'interviews', 'offerLetters', 'checks'])->latest()->limit(10)->get(),
            'interviews' => RecruitmentInterview::with('candidate')->orderByDesc('scheduled_at')->limit(8)->get(),
            'offers' => RecruitmentOfferLetter::with('candidate')->latest()->limit(8)->get(),
        ]);
    }
}
