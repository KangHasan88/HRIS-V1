<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDisciplinaryRecord;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDisciplinaryRecordController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-disciplinary-records', [
            'active' => 'employee-disciplinary-records',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'records' => EmployeeDisciplinaryRecord::with(['employeeProfile.company', 'employeeProfile.branch'])
                ->latest('incident_date')
                ->latest()
                ->limit(150)
                ->get(),
            'summary' => [
                'open' => EmployeeDisciplinaryRecord::where('status', 'open')->count(),
                'monitoring' => EmployeeDisciplinaryRecord::where('status', 'monitoring')->count(),
                'closed' => EmployeeDisciplinaryRecord::where('status', 'closed')->count(),
                'high_risk' => EmployeeDisciplinaryRecord::whereIn('risk_level', ['high', 'critical'])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'case_number' => ['required', 'string', 'max:80', 'unique:employee_disciplinary_records,case_number'],
            'case_type' => ['required', Rule::in(['coaching', 'verbal_warning', 'sp1', 'sp2', 'sp3', 'investigation'])],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'incident_date' => ['required', 'date'],
            'issued_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'follow_up_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['open', 'monitoring', 'closed', 'cancelled'])],
            'reported_by' => ['nullable', 'string', 'max:160'],
            'handled_by' => ['nullable', 'string', 'max:160'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:1500'],
            'corrective_action' => ['nullable', 'string', 'max:1500'],
            'employee_response' => ['nullable', 'string', 'max:1500'],
            'follow_up_result' => ['nullable', 'string', 'max:1500'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);

        $record = EmployeeDisciplinaryRecord::create([
            ...$data,
            'case_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'position' => $employee->position,
                'department' => $employee->department,
                'grade' => $employee->grade,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('employee_disciplinary_record.created', "Case {$record->case_number} dibuat untuk {$employee->employee_number}.", $record, [], $record->toArray(), $request);

        return redirect()->route('hris.employee-disciplinary-records')->with('success', 'Riwayat disiplin dan coaching berhasil dicatat.');
    }
}
