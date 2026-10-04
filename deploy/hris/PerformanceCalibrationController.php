<?php

namespace App\Http\Controllers;

use App\Models\PerformanceCalibrationItem;
use App\Models\PerformanceCalibrationSession;
use App\Models\PerformanceReviewCycle;
use App\Models\PerformanceReviewForm;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerformanceCalibrationController extends Controller
{
    public function index(): View
    {
        $this->authorizePerformance();

        $sessions = PerformanceCalibrationSession::with('cycle')->withCount('items')->latest('session_date')->limit(80)->get();
        $items = PerformanceCalibrationItem::with(['session.cycle', 'form.employeeProfile', 'employeeProfile'])->latest()->limit(120)->get();
        $forms = PerformanceReviewForm::with(['cycle', 'employeeProfile'])->whereIn('status', ['in_review', 'calibrated', 'completed', 'assigned'])->latest()->limit(150)->get();

        $distribution = $items->groupBy('rating_bucket')->map->count();

        return view('performance.calibration', [
            'active' => 'performance-calibration',
            'cycles' => PerformanceReviewCycle::latest('period_start')->limit(80)->get(),
            'forms' => $forms,
            'sessions' => $sessions,
            'items' => $items,
            'distribution' => [
                'exceeds' => $distribution->get('exceeds', 0),
                'meets' => $distribution->get('meets', 0),
                'below' => $distribution->get('below', 0),
                'critical' => $distribution->get('critical', 0),
            ],
            'summary' => [
                'sessions' => $sessions->count(),
                'active_sessions' => $sessions->whereIn('status', ['active', 'in_progress'])->count(),
                'items' => $items->count(),
                'adjusted' => $items->whereIn('calibration_decision', ['raise', 'lower'])->count(),
                'avg_original' => round((float) $items->avg('original_score'), 1),
                'avg_calibrated' => round((float) $items->avg('calibrated_score'), 1),
            ],
        ]);
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_review_cycle_id' => ['required', 'exists:performance_review_cycles,id'],
            'session_number' => ['required', 'string', 'max:80', 'unique:performance_calibration_sessions,session_number'],
            'session_name' => ['required', 'string', 'max:180'],
            'session_date' => ['required', 'date'],
            'facilitator_name' => ['nullable', 'string', 'max:160'],
            'calibration_scope' => ['required', Rule::in(['company', 'department', 'team', 'grade', 'site'])],
            'target_distribution' => ['required', Rule::in(['balanced', 'strict', 'growth', 'custom'])],
            'status' => ['required', Rule::in(['draft', 'active', 'in_progress', 'locked', 'completed', 'cancelled'])],
            'rating_rules' => ['nullable', 'string', 'max:1200'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $session = PerformanceCalibrationSession::create([
            ...collect($validated)->except('rating_rules')->all(),
            'rating_rules' => $this->splitList($validated['rating_rules'] ?? null),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('performance_calibration_session.created', "Sesi kalibrasi {$session->session_number} dibuat.", $session, [], $session->toArray(), $request);

        return redirect()->route('performance.calibration')->with('success', 'Sesi kalibrasi berhasil dibuat.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_calibration_session_id' => ['required', 'exists:performance_calibration_sessions,id'],
            'performance_review_form_id' => ['required', 'exists:performance_review_forms,id'],
            'original_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'calibrated_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'original_rating' => ['nullable', 'string', 'max:40'],
            'calibrated_rating' => ['required', 'string', 'max:40'],
            'rating_bucket' => ['required', Rule::in(['exceeds', 'meets', 'below', 'critical'])],
            'calibration_decision' => ['required', Rule::in(['keep', 'raise', 'lower', 'defer'])],
            'decision_reason' => ['nullable', 'string', 'max:2400'],
            'evidence_snapshot' => ['nullable', 'string', 'max:1200'],
            'status' => ['required', Rule::in(['draft', 'reviewed', 'locked', 'returned'])],
        ]);

        $form = PerformanceReviewForm::with('employeeProfile')->findOrFail($validated['performance_review_form_id']);

        $item = PerformanceCalibrationItem::create([
            ...collect($validated)->except('evidence_snapshot')->all(),
            'employee_profile_id' => $form->employee_profile_id,
            'evidence_snapshot' => $this->splitList($validated['evidence_snapshot'] ?? null),
            'created_by' => auth()->id(),
        ]);

        if (in_array($validated['status'], ['reviewed', 'locked'], true)) {
            $form->forceFill([
                'final_score' => $validated['calibrated_score'],
                'status' => 'calibrated',
                'summary_note' => trim(($form->summary_note ? $form->summary_note."\n" : '').'Kalibrasi: '.$validated['calibrated_rating'].' - '.($validated['decision_reason'] ?? 'tanpa catatan')),
            ])->save();
        }

        AuditLogger::record('performance_calibration_item.created', "Item kalibrasi {$form->form_number} dibuat.", $item, [], $item->toArray(), $request);

        return redirect()->route('performance.calibration')->with('success', 'Item kalibrasi berhasil disimpan.');
    }

    private function authorizePerformance(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage recruitment') || $user?->hasPermission('manage modules') || $user?->hasPermission('view attendance'), 403);
    }

    private function splitList(?string $value): array
    {
        return $value ? collect(explode(',', $value))->map(fn ($item) => trim($item))->filter()->values()->all() : [];
    }
}
