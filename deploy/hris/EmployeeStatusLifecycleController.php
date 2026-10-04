<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeStatusHistory;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeStatusLifecycleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $employees = EmployeeProfile::with(['company', 'branch', 'statusHistories' => fn ($query) => $query->latest()->limit(3)])
            ->orderBy('full_name')
            ->get();

        return view('employee-status-lifecycle', [
            'active' => 'employee-status',
            'employees' => $employees,
            'histories' => EmployeeStatusHistory::with('employeeProfile.company')->latest()->limit(100)->get(),
            'summary' => [
                'probation' => EmployeeProfile::where('employment_status', 'probation')->count(),
                'contract' => EmployeeProfile::where('employment_status', 'contract')->count(),
                'permanent' => EmployeeProfile::where('employment_status', 'permanent')->count(),
                'alumni' => EmployeeProfile::where('employment_status', 'alumni')->count(),
                'due_review' => EmployeeStatusHistory::whereNotNull('review_date')->whereDate('review_date', '<=', now()->addDays(30))->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'to_status' => ['required', 'in:probation,contract,permanent,intern,alumni'],
            'effective_date' => ['required', 'date'],
            'review_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:160'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = EmployeeProfile::findOrFail($data['employee_profile_id']);
        $before = $employee->only(['employment_status', 'contract_end_date', 'is_active']);

        $history = $employee->statusHistories()->create([
            'from_status' => $employee->employment_status,
            'to_status' => $data['to_status'],
            'effective_date' => $data['effective_date'],
            'review_date' => $data['review_date'] ?? null,
            'contract_end_date' => $data['contract_end_date'] ?? null,
            'reason' => $data['reason'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'position' => $employee->position,
            ],
            'created_by' => auth()->id(),
        ]);

        $employee->employment_status = $data['to_status'];
        $employee->contract_end_date = $data['contract_end_date'] ?? $employee->contract_end_date;
        $employee->is_active = $data['to_status'] !== 'alumni';
        $employee->save();

        AuditLogger::record(
            'employee_status.changed',
            "Status kerja {$employee->employee_number} berubah dari {$history->from_status} ke {$history->to_status}.",
            $history,
            $before,
            $employee->fresh()->only(['employment_status', 'contract_end_date', 'is_active']),
            $request
        );

        return redirect()->route('hris.employee-status')->with('success', 'Status kerja karyawan berhasil diperbarui.');
    }
}
