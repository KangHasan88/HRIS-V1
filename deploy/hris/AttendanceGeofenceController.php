<?php

namespace App\Http\Controllers;

use App\Models\AttendanceGeofence;
use App\Models\AttendanceLocationPolicy;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceGeofenceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view attendance');

        $geofences = AttendanceGeofence::with(['company', 'branch'])->latest()->limit(150)->get();
        $policies = AttendanceLocationPolicy::with(['geofence', 'employeeProfile', 'company', 'branch'])->latest()->limit(150)->get();
        $simulation = null;

        if ($request->filled(['latitude', 'longitude', 'attendance_geofence_id'])) {
            $geofence = AttendanceGeofence::find($request->integer('attendance_geofence_id'));
            if ($geofence) {
                $distance = $this->distanceMeter(
                    (float) $request->query('latitude'),
                    (float) $request->query('longitude'),
                    (float) $geofence->latitude,
                    (float) $geofence->longitude,
                );

                $simulation = [
                    'geofence' => $geofence,
                    'distance' => round($distance, 2),
                    'inside' => $distance <= $geofence->radius_meter,
                ];
            }
        }

        return view('attendance.geofences', [
            'active' => 'attendance-geofences',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'geofences' => $geofences,
            'policies' => $policies,
            'simulation' => $simulation,
            'summary' => [
                'geofences' => $geofences->count(),
                'active' => $geofences->where('is_active', true)->count(),
                'policies' => $policies->count(),
                'approved' => $policies->where('approval_status', 'approved')->count(),
            ],
            'locationTypes' => [
                'office' => 'Kantor',
                'site' => 'Site Operasional',
                'wfh' => 'WFH',
                'client_site' => 'Client Site',
                'business_trip' => 'Perjalanan Dinas',
                'temporary' => 'Lokasi Temporary',
            ],
            'policyTypes' => [
                'office' => 'Office/site regular',
                'wfh' => 'WFH',
                'client_site' => 'Client site',
                'business_trip' => 'Perjalanan dinas',
            ],
            'scopeOptions' => [
                'employee' => 'Per karyawan',
                'company' => 'Perusahaan',
                'branch' => 'Cabang / Site',
            ],
            'dayOptions' => [
                'mon' => 'Senin',
                'tue' => 'Selasa',
                'wed' => 'Rabu',
                'thu' => 'Kamis',
                'fri' => 'Jumat',
                'sat' => 'Sabtu',
                'sun' => 'Minggu',
            ],
        ]);
    }

    public function storeGeofence(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:80', 'unique:attendance_geofences,code'],
            'name' => ['required', 'string', 'max:180'],
            'location_type' => ['required', Rule::in(['office', 'site', 'wfh', 'client_site', 'business_trip', 'temporary'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:100000'],
            'address' => ['nullable', 'string', 'max:255'],
            'allow_clock_in' => ['nullable', 'boolean'],
            'allow_clock_out' => ['nullable', 'boolean'],
            'requires_photo' => ['nullable', 'boolean'],
            'requires_liveness' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $geofence = AttendanceGeofence::create([
            ...$validated,
            'allow_clock_in' => $request->boolean('allow_clock_in', true),
            'allow_clock_out' => $request->boolean('allow_clock_out', true),
            'requires_photo' => $request->boolean('requires_photo', true),
            'requires_liveness' => $request->boolean('requires_liveness', true),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('attendance_geofence.created', "Geofence {$geofence->code} dibuat.", $geofence, [], $geofence->toArray(), $request);

        return redirect()->route('attendance.geofences')->with('success', 'Geofence berhasil dibuat.');
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'attendance_geofence_id' => ['required', 'exists:attendance_geofences,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'policy_number' => ['required', 'string', 'max:80', 'unique:attendance_location_policies,policy_number'],
            'policy_type' => ['required', Rule::in(['office', 'wfh', 'client_site', 'business_trip'])],
            'assignment_scope' => ['required', Rule::in(['employee', 'company', 'branch'])],
            'effective_start' => ['required', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'expired'])],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'allowed_days' => ['nullable', 'array'],
            'allowed_days.*' => ['string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($validated['assignment_scope'] === 'employee') {
            $validated['company_id'] = null;
            $validated['branch_id'] = null;
        }
        if ($validated['assignment_scope'] === 'company') {
            $validated['employee_profile_id'] = null;
            $validated['branch_id'] = null;
        }
        if ($validated['assignment_scope'] === 'branch') {
            $validated['employee_profile_id'] = null;
        }

        $policy = AttendanceLocationPolicy::create([
            ...$validated,
            'approved_at' => $validated['approval_status'] === 'approved' ? now() : null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('attendance_location_policy.created', "Policy lokasi {$policy->policy_number} dibuat.", $policy, [], $policy->toArray(), $request);

        return redirect()->route('attendance.geofences')->with('success', 'Policy lokasi berhasil dibuat.');
    }

    private function distanceMeter(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
