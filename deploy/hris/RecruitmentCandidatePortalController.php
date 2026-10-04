<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCandidateDocument;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentCandidatePortalController extends Controller
{
    public function admin(): View
    {
        Gate::authorize('manage recruitment');

        $candidates = RecruitmentCandidate::with(['manpowerRequest', 'documents'])->latest()->limit(150)->get();
        $documents = RecruitmentCandidateDocument::with('candidate.manpowerRequest')->latest()->limit(200)->get();

        return view('recruitment-candidate-portal-admin', [
            'active' => 'recruitment-candidate-portal',
            'candidates' => $candidates,
            'documents' => $documents,
            'summary' => [
                'portal_ready' => $candidates->whereNotNull('portal_token')->count(),
                'submitted' => $documents->where('verification_status', 'submitted')->count(),
                'verified' => $documents->where('verification_status', 'verified')->count(),
                'need_revision' => $documents->where('verification_status', 'need_revision')->count(),
            ],
        ]);
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'recruitment_candidate_id' => ['required', 'exists:recruitment_candidates,id'],
            'document_type' => ['required', Rule::in(['cv', 'ktp', 'ijazah', 'transkrip', 'sertifikat', 'portfolio', 'lainnya'])],
            'document_title' => ['required', 'string', 'max:180'],
            'file_reference' => ['nullable', 'string', 'max:255'],
            'verification_status' => ['required', Rule::in(['submitted', 'verified', 'need_revision', 'rejected'])],
            'submitted_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $document = RecruitmentCandidateDocument::create([
            ...$data,
            'verified_at' => $data['verification_status'] === 'verified' ? now() : null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('recruitment_candidate_document.created', "Dokumen kandidat {$document->document_title} dicatat.", $document, [], $document->toArray(), $request);

        return redirect()->route('recruitment.candidate-portal')->with('success', 'Dokumen kandidat berhasil dicatat.');
    }

    public function candidateSubmitDocument(Request $request, string $token): RedirectResponse
    {
        $candidate = RecruitmentCandidate::where('portal_token', $token)->firstOrFail();

        $data = $request->validate([
            'document_type' => ['required', Rule::in(['cv', 'ktp', 'ijazah', 'transkrip', 'sertifikat', 'portfolio', 'lainnya'])],
            'document_title' => ['required', 'string', 'max:180'],
            'file_reference' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $document = RecruitmentCandidateDocument::create([
            'recruitment_candidate_id' => $candidate->id,
            ...$data,
            'verification_status' => 'submitted',
            'submitted_at' => now()->toDateString(),
        ]);

        AuditLogger::record('recruitment_candidate_document.submitted', "Dokumen kandidat {$document->document_title} disubmit melalui portal.", $document, [], $document->toArray(), $request);

        return redirect()->route('candidate.portal.tracking', $token)->with('success', 'Dokumen berhasil disubmit dan menunggu review HR.');
    }

    public function publicTracking(string $token): View
    {
        $candidate = RecruitmentCandidate::with(['manpowerRequest.company', 'documents'])
            ->where('portal_token', $token)
            ->firstOrFail();

        $stageOrder = ['sourcing', 'screening', 'interview', 'offer', 'hired'];
        $currentIndex = array_search($candidate->pipeline_stage, $stageOrder, true);

        return view('candidate-portal-tracking', [
            'candidate' => $candidate,
            'stageOrder' => $stageOrder,
            'currentIndex' => $currentIndex === false ? 0 : $currentIndex,
        ]);
    }
}
