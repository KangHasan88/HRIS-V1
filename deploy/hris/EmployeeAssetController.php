<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAsset;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeAssetController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-assets', [
            'active' => 'employee-assets',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'assets' => EmployeeAsset::with(['employeeProfile.company', 'employeeProfile.branch'])->latest()->limit(150)->get(),
            'summary' => [
                'assigned' => EmployeeAsset::where('assignment_status', 'assigned')->count(),
                'returned' => EmployeeAsset::where('assignment_status', 'returned')->count(),
                'overdue' => EmployeeAsset::where('assignment_status', 'assigned')->whereNotNull('due_return_at')->whereDate('due_return_at', '<', now())->count(),
                'clearance_pending' => EmployeeAsset::where('requires_clearance', true)->where('clearance_completed', false)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'asset_code' => ['required', 'string', 'max:80', 'unique:employee_assets,asset_code'],
            'asset_type' => ['required', Rule::in(['laptop', 'phone', 'id_card', 'uniform', 'vehicle', 'tool', 'other'])],
            'asset_name' => ['required', 'string', 'max:180'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'asset_value' => ['nullable', 'numeric', 'min:0'],
            'assigned_at' => ['required', 'date'],
            'due_return_at' => ['nullable', 'date'],
            'condition_out' => ['required', Rule::in(['new', 'good', 'fair', 'damaged'])],
            'requires_clearance' => ['nullable', 'boolean'],
            'handover_by' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);

        $asset = EmployeeAsset::create([
            ...$data,
            'assignment_status' => 'assigned',
            'requires_clearance' => $request->boolean('requires_clearance', true),
            'clearance_completed' => false,
            'asset_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'position' => $employee->position,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('employee_asset.assigned', "Aset {$asset->asset_code} diberikan ke {$employee->employee_number}.", $asset, [], $asset->toArray(), $request);

        return redirect()->route('hris.employee-assets')->with('success', 'Aset karyawan berhasil ditugaskan.');
    }

    public function returnAsset(Request $request, EmployeeAsset $asset): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'returned_at' => ['required', 'date'],
            'condition_in' => ['required', Rule::in(['good', 'fair', 'damaged', 'lost'])],
            'received_by' => ['nullable', 'string', 'max:160'],
            'clearance_completed' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = $asset->only(['assignment_status', 'returned_at', 'condition_in', 'clearance_completed']);

        $asset->update([
            'returned_at' => $data['returned_at'],
            'condition_in' => $data['condition_in'],
            'received_by' => $data['received_by'] ?? null,
            'clearance_completed' => $request->boolean('clearance_completed'),
            'assignment_status' => $data['condition_in'] === 'lost' ? 'lost' : 'returned',
            'notes' => $data['notes'] ?? $asset->notes,
        ]);

        AuditLogger::record('employee_asset.returned', "Aset {$asset->asset_code} dikembalikan.", $asset, $before, $asset->fresh()->toArray(), $request);

        return redirect()->route('hris.employee-assets')->with('success', 'Pengembalian aset berhasil dicatat.');
    }
}
