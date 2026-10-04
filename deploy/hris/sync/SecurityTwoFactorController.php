<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PrivilegedActionConfirmation;
use App\Models\SecurityTwoFactorPolicy;
use App\Models\User;
use App\Models\UserTwoFactorSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SecurityTwoFactorController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $policies = SecurityTwoFactorPolicy::with(['company', 'userSettings'])->latest()->limit(50)->get();
        $settings = UserTwoFactorSetting::with(['user.role', 'policy'])->latest()->limit(80)->get();
        $confirmations = PrivilegedActionConfirmation::with('user')->latest()->limit(80)->get();

        return view('settings.two-factor-security', [
            'active' => 'two-factor-security',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'users' => User::with('role')->orderBy('name')->limit(120)->get(),
            'policies' => $policies,
            'settings' => $settings,
            'confirmations' => $confirmations,
            'roleCatalog' => ['Super Admin', 'HR Admin', 'Payroll Admin', 'Manager', 'Employee', 'Auditor'],
            'actionCatalog' => $this->actionCatalog(),
            'summary' => [
                'policies' => $policies->count(),
                'active_policies' => $policies->where('status', 'active')->count(),
                'enrolled_users' => $settings->where('status', 'enabled')->count(),
                'pending_users' => $settings->where('status', 'pending')->count(),
                'confirmed_actions' => $confirmations->where('status', 'confirmed')->count(),
                'pending_actions' => $confirmations->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'policy_code' => ['required', 'string', 'max:100', 'unique:security_two_factor_policies,policy_code'],
            'policy_name' => ['required', 'string', 'max:255'],
            'enforced_roles' => ['nullable', 'array'],
            'enforced_roles.*' => ['string', 'max:80'],
            'privileged_actions' => ['nullable', 'array'],
            'privileged_actions.*' => ['string', 'max:120'],
            'otp_channel' => ['required', Rule::in(['authenticator_app', 'email_otp', 'whatsapp_otp', 'hardware_key'])],
            'challenge_ttl_minutes' => ['required', 'integer', 'min:1', 'max:30'],
            'remember_device_days' => ['required', 'integer', 'min:0', 'max:90'],
            'status' => ['required', Rule::in(['draft', 'active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $policy = SecurityTwoFactorPolicy::create([
            ...$data,
            'policy_code' => strtoupper($data['policy_code']),
            'enforced_roles' => array_values($data['enforced_roles'] ?? []),
            'privileged_actions' => array_values($data['privileged_actions'] ?? []),
            'require_on_login' => $request->boolean('require_on_login'),
            'require_for_privileged_action' => $request->boolean('require_for_privileged_action'),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('security_2fa_policy.created', "Policy 2FA {$policy->policy_code} dibuat.", $policy, [], $policy->toArray(), $request);

        return redirect()->route('settings.two-factor-security')->with('success', 'Policy 2FA berhasil disimpan.');
    }

    public function enrollUser(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'security_two_factor_policy_id' => ['nullable', 'exists:security_two_factor_policies,id'],
            'method' => ['required', Rule::in(['authenticator_app', 'email_otp', 'whatsapp_otp', 'hardware_key'])],
            'status' => ['required', Rule::in(['pending', 'enabled', 'disabled', 'reset_required'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $secret = '2fa_'.Str::upper(Str::random(24));
        $backupMasks = collect(range(1, 5))
            ->map(fn ($index) => 'BACKUP-'.$index.'-****'.Str::upper(Str::random(4)))
            ->all();

        $setting = UserTwoFactorSetting::updateOrCreate(
            ['user_id' => $data['user_id']],
            [
                ...$data,
                'secret_reference' => 'vault:'.hash('sha256', $secret),
                'secret_mask' => substr($secret, 0, 6).'********'.substr($secret, -4),
                'backup_code_masks' => $backupMasks,
                'enabled_at' => $data['status'] === 'enabled' ? now() : null,
                'verified_at' => $data['status'] === 'enabled' ? now() : null,
                'last_challenged_at' => null,
                'created_by' => auth()->id(),
            ]
        );

        AuditLogger::record('security_2fa_user.enrolled', "2FA user {$setting->user?->email} disiapkan.", $setting, [], $setting->fresh()->toArray(), $request);

        return redirect()->route('settings.two-factor-security')->with('success', 'Enrollment 2FA user berhasil disimpan.');
    }

    public function confirmAction(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'action_key' => ['required', Rule::in(array_keys($this->actionCatalog()))],
            'resource_type' => ['nullable', 'string', 'max:120'],
            'resource_reference' => ['nullable', 'string', 'max:160'],
            'confirmation_method' => ['required', Rule::in(['password_and_otp', 'otp_only', 'sso_step_up', 'admin_dual_control'])],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'expired', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $confirmation = PrivilegedActionConfirmation::create([
            ...$data,
            'action_label' => $this->actionCatalog()[$data['action_key']],
            'requested_at' => now(),
            'confirmed_at' => $data['status'] === 'confirmed' ? now() : null,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'risk_snapshot' => [
                'requires_2fa' => true,
                'step_up' => in_array($data['confirmation_method'], ['password_and_otp', 'sso_step_up', 'admin_dual_control'], true),
                'resource' => $data['resource_reference'] ?? '-',
            ],
        ]);

        AuditLogger::record('security_privileged_action.confirmed', "Konfirmasi aksi {$confirmation->action_key} dibuat.", $confirmation, [], $confirmation->toArray(), $request);

        return redirect()->route('settings.two-factor-security')->with('success', 'Konfirmasi aksi privileged berhasil dicatat.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(Gate::allows('manage users') || Gate::allows('manage modules') || Gate::allows('view delivery progress'), 403);
    }

    private function actionCatalog(): array
    {
        return [
            'payroll_publish' => 'Publish payroll / slip gaji',
            'role_permission_change' => 'Ubah role dan permission',
            'bank_disbursement_release' => 'Release pembayaran gaji',
            'privacy_export_approval' => 'Approve ekspor data sensitif',
            'employee_delete' => 'Hapus/nonaktifkan data karyawan',
            'integration_secret_rotate' => 'Rotasi secret integrasi/API',
        ];
    }
}
