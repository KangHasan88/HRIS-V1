<?php

namespace App\Http\Controllers;

use App\Models\AttendanceImportBatch;
use App\Models\AttendanceImportRow;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceImportController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $batches = AttendanceImportBatch::with(['company', 'branch'])
            ->latest()
            ->limit(40)
            ->get();

        $rows = AttendanceImportRow::with(['batch', 'employeeProfile'])
            ->latest('event_at')
            ->limit(120)
            ->get();

        return view('attendance.imports', [
            'active' => 'attendance-imports',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'batches' => $batches,
            'rows' => $rows,
            'summary' => [
                'batches' => $batches->count(),
                'imported' => $batches->sum('imported_rows'),
                'matched' => $batches->sum('matched_rows'),
                'anomaly' => $batches->sum('anomaly_rows'),
            ],
        ]);
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'batch_number' => ['required', 'string', 'max:80', 'unique:attendance_import_batches,batch_number'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'source_type' => ['required', Rule::in(['biometric_machine', 'csv_import', 'api_sync', 'admin_manual'])],
            'machine_code' => ['nullable', 'string', 'max:80'],
            'machine_name' => ['nullable', 'string', 'max:255'],
            'import_period_start' => ['nullable', 'date'],
            'import_period_end' => ['nullable', 'date', 'after_or_equal:import_period_start'],
            'file_reference' => ['nullable', 'string', 'max:255'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
            'status' => ['required', Rule::in(['draft', 'validated', 'reconciled', 'failed'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->store('attendance-imports');
            $validated['file_reference'] = $path;
        }

        unset($validated['csv_file']);

        $batch = AttendanceImportBatch::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'attendance_import_batch.created',
            "Batch import absensi {$batch->batch_number} dibuat.",
            $batch,
            [],
            $batch->toArray(),
            $request,
        );

        return redirect()->route('attendance.imports')->with('success', 'Batch import absensi berhasil dibuat.');
    }

    public function storeRow(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'attendance_import_batch_id' => ['required', 'exists:attendance_import_batches,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'employee_number' => ['nullable', 'string', 'max:80'],
            'employee_name' => ['nullable', 'string', 'max:255'],
            'raw_identifier' => ['nullable', 'string', 'max:120'],
            'event_type' => ['required', Rule::in(['clock_in', 'clock_out', 'break_start', 'break_end'])],
            'event_at' => ['required', 'date'],
            'source_device' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = $this->resolveEmployee($validated);
        $flags = $this->anomalyFlags($validated, $employee);

        $row = AttendanceImportRow::create([
            ...$validated,
            'employee_profile_id' => $employee?->id,
            'employee_number' => $validated['employee_number'] ?: $employee?->employee_number,
            'employee_name' => $validated['employee_name'] ?: $employee?->full_name,
            'match_status' => $flags === [] ? 'matched' : 'needs_review',
            'anomaly_flags' => $flags,
            'normalized_payload' => [
                'employee_number' => $validated['employee_number'] ?? null,
                'raw_identifier' => $validated['raw_identifier'] ?? null,
                'event_type' => $validated['event_type'],
                'event_at' => $validated['event_at'],
                'source_device' => $validated['source_device'] ?? null,
            ],
        ]);

        $this->refreshBatchCounts($row->batch);

        AuditLogger::record(
            'attendance_import_row.created',
            "Row import absensi {$row->employee_number} {$row->event_type} dibuat.",
            $row,
            [],
            $row->toArray(),
            $request,
        );

        return redirect()->route('attendance.imports')->with('success', 'Row import absensi berhasil diproses.');
    }

    private function resolveEmployee(array $data): ?EmployeeProfile
    {
        if (! empty($data['employee_profile_id'])) {
            return EmployeeProfile::find($data['employee_profile_id']);
        }

        $identifier = $data['employee_number'] ?: $data['raw_identifier'] ?: null;

        if (! $identifier) {
            return null;
        }

        return EmployeeProfile::where('employee_number', $identifier)
            ->orWhere('nik', $identifier)
            ->first();
    }

    private function anomalyFlags(array $data, ?EmployeeProfile $employee): array
    {
        $flags = [];

        if (! $employee) {
            $flags[] = 'employee_not_found';
        }

        if ($employee && AttendanceImportRow::where('employee_profile_id', $employee->id)
            ->where('event_at', $data['event_at'])
            ->where('event_type', $data['event_type'])
            ->exists()) {
            $flags[] = 'duplicate_event';
        }

        if ($employee && $data['event_type'] === 'clock_out') {
            $hasClockIn = AttendanceImportRow::where('employee_profile_id', $employee->id)
                ->whereDate('event_at', date('Y-m-d', strtotime($data['event_at'])))
                ->where('event_type', 'clock_in')
                ->exists();

            if (! $hasClockIn) {
                $flags[] = 'missing_clock_in';
            }
        }

        return $flags;
    }

    private function refreshBatchCounts(AttendanceImportBatch $batch): void
    {
        $rows = $batch->rows();

        $batch->forceFill([
            'imported_rows' => (clone $rows)->count(),
            'matched_rows' => (clone $rows)->where('match_status', 'matched')->count(),
            'anomaly_rows' => (clone $rows)->where('match_status', '!=', 'matched')->count(),
            'status' => (clone $rows)->where('match_status', '!=', 'matched')->exists() ? 'validated' : 'reconciled',
        ])->save();
    }
}
