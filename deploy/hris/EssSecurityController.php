<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EssTrustedDevice;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssSecurityController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        $devices = EssTrustedDevice::with(['user', 'employeeProfile'])->latest()->limit(150)->get();

        return view('ess.security', [
            'active' => 'ess-security',
            'users' => User::with('role')->orderBy('name')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'devices' => $devices,
            'summary' => [
                'total' => $devices->count(),
                'trusted' => $devices->where('trust_status', 'trusted')->count(),
                'pending' => $devices->where('trust_status', 'pending')->count(),
                'biometric' => $devices->where('biometric_enabled', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage modules');

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'device_uid' => ['required', 'string', 'max:160'],
            'device_name' => ['nullable', 'string', 'max:160'],
            'platform' => ['required', Rule::in(['webview', 'android', 'ios', 'browser'])],
            'app_version' => ['nullable', 'string', 'max:40'],
            'biometric_enabled' => ['nullable', 'boolean'],
            'biometric_type' => ['nullable', Rule::in(['fingerprint', 'face_id', 'device_biometric', 'none'])],
            'fallback_pin' => ['nullable', 'string', 'min:4', 'max:12'],
            'trust_status' => ['required', Rule::in(['pending', 'trusted', 'blocked', 'revoked'])],
            'failed_attempts' => ['nullable', 'integer', 'min:0', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $biometricEnabled = $request->boolean('biometric_enabled');
        $fallbackPin = $validated['fallback_pin'] ?? null;
        $riskFlags = $this->riskFlags($validated, $biometricEnabled, $fallbackPin);
        $isTrusted = $validated['trust_status'] === 'trusted';

        $device = EssTrustedDevice::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'device_uid' => $validated['device_uid'],
            ],
            [
                'employee_profile_id' => $validated['employee_profile_id'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'platform' => $validated['platform'],
                'app_version' => $validated['app_version'] ?? null,
                'biometric_enabled' => $biometricEnabled,
                'biometric_type' => $biometricEnabled ? ($validated['biometric_type'] ?? 'device_biometric') : 'none',
                'fallback_pin_hash' => $fallbackPin ? Hash::make($fallbackPin) : null,
                'trust_status' => $validated['trust_status'],
                'trusted_at' => $isTrusted ? now() : null,
                'last_authenticated_at' => $isTrusted ? now() : null,
                'failed_attempts' => $validated['failed_attempts'] ?? 0,
                'risk_flags' => $riskFlags,
                'capability_snapshot' => [
                    'bridge_capability' => 'biometric.verify',
                    'pin_fallback' => (bool) $fallbackPin,
                    'platform' => $validated['platform'],
                    'app_version' => $validated['app_version'] ?? null,
                    'biometric_enabled' => $biometricEnabled,
                    'biometric_type' => $biometricEnabled ? ($validated['biometric_type'] ?? 'device_biometric') : 'none',
                ],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ],
        );

        AuditLogger::record(
            'ess_trusted_device.saved',
            "ESS trusted device {$device->device_uid} disimpan.",
            $device,
            [],
            $device->toArray(),
            $request,
        );

        return redirect()->route('settings.ess-security')->with('success', 'Trusted device ESS berhasil disimpan.');
    }

    private function riskFlags(array $data, bool $biometricEnabled, ?string $fallbackPin): array
    {
        $flags = [];

        if (! $biometricEnabled) {
            $flags[] = 'biometric_disabled';
        }

        if (! $fallbackPin) {
            $flags[] = 'pin_missing';
        }

        if (($data['trust_status'] ?? null) === 'trusted' && (($data['platform'] ?? 'browser') === 'browser')) {
            $flags[] = 'browser_trusted_device';
        }

        if ((int) ($data['failed_attempts'] ?? 0) >= 3) {
            $flags[] = 'multiple_failed_attempts';
        }

        return $flags;
    }
}
