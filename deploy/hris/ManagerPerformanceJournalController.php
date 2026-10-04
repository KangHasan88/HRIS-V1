<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\ManagerOneOnOneLog;
use App\Models\ManagerPerformanceNote;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerPerformanceJournalController extends Controller
{
    public function index(): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');

        $notes = ManagerPerformanceNote::with('employeeProfile')
            ->where('manager_user_id', $manager->id)
            ->whereIn('employee_profile_id', $teamIds)
            ->latest('note_date')
            ->limit(80)
            ->get();

        $oneOnOnes = ManagerOneOnOneLog::with('employeeProfile')
            ->where('manager_user_id', $manager->id)
            ->whereIn('employee_profile_id', $teamIds)
            ->latest('meeting_date')
            ->limit(80)
            ->get();

        return view('manager.performance-journal', [
            'active' => 'manager-performance-journal',
            'team' => $team,
            'notes' => $notes,
            'oneOnOnes' => $oneOnOnes,
            'summary' => [
                'team_total' => $team->count(),
                'notes' => $notes->count(),
                'one_on_ones' => $oneOnOnes->count(),
                'follow_up' => $notes->where('status', 'open')->whereNotNull('follow_up_date')->count() + $oneOnOnes->where('status', 'open')->whereNotNull('next_meeting_date')->count(),
            ],
        ]);
    }

    public function storeNote(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $teamIds = $this->teamFor($manager)->pluck('id');
        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'note_date' => ['required', 'date'],
            'category' => ['required', Rule::in(['performance', 'behavior', 'achievement', 'coaching', 'risk', 'recognition'])],
            'sentiment' => ['required', Rule::in(['positive', 'neutral', 'concern'])],
            'visibility' => ['required', Rule::in(['manager_only', 'hr_visible', 'employee_visible'])],
            'title' => ['required', 'string', 'max:255'],
            'note_body' => ['required', 'string', 'max:4000'],
            'tags' => ['nullable', 'string', 'max:255'],
            'follow_up_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['open', 'follow_up', 'closed'])],
        ]);

        abort_unless($teamIds->contains((int) $validated['employee_profile_id']), 403);

        $note = ManagerPerformanceNote::create([
            ...collect($validated)->except('tags')->all(),
            'manager_user_id' => $manager->id,
            'tags' => $this->splitTags($validated['tags'] ?? null),
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_performance_note.created',
            "Catatan performa {$note->title} dibuat.",
            $note,
            [],
            $note->toArray(),
            $request,
        );

        return redirect()->route('manager.performance-journal')->with('success', 'Catatan performa berhasil disimpan.');
    }

    public function storeOneOnOne(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $teamIds = $this->teamFor($manager)->pluck('id');
        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'meeting_date' => ['required', 'date'],
            'meeting_type' => ['required', Rule::in(['regular', 'probation', 'performance', 'career', 'issue_follow_up'])],
            'mood' => ['required', Rule::in(['positive', 'neutral', 'concern'])],
            'topic_summary' => ['required', 'string', 'max:255'],
            'discussion_notes' => ['nullable', 'string', 'max:4000'],
            'employee_feedback' => ['nullable', 'string', 'max:4000'],
            'manager_commitment' => ['nullable', 'string', 'max:4000'],
            'action_items' => ['nullable', 'string', 'max:1200'],
            'next_meeting_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['open', 'follow_up', 'closed'])],
        ]);

        abort_unless($teamIds->contains((int) $validated['employee_profile_id']), 403);

        $log = ManagerOneOnOneLog::create([
            ...collect($validated)->except('action_items')->all(),
            'manager_user_id' => $manager->id,
            'action_items' => $this->splitTags($validated['action_items'] ?? null),
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_one_on_one.created',
            "Log one-on-one {$log->topic_summary} dibuat.",
            $log,
            [],
            $log->toArray(),
            $request,
        );

        return redirect()->route('manager.performance-journal')->with('success', 'Log one-on-one berhasil disimpan.');
    }

    private function teamFor($manager): Collection
    {
        $managerEmployee = EmployeeProfile::where('user_id', $manager?->id)
            ->orWhere('work_email', $manager?->email)
            ->first();

        $query = EmployeeProfile::with(['branch', 'company'])
            ->where('is_active', true)
            ->orderBy('full_name');

        if ($managerEmployee?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $managerEmployee->branch_id);
        } elseif ($manager?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $manager->branch_id);
        }

        return $query->limit(150)->get();
    }

    private function splitTags(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
