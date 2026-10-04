@extends('layouts.sidebar')

@section('title', '2FA & Privileged Action')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">2FA & Privileged Action</h1>
            <p class="mt-1 text-sm text-gray-500">Atur two-factor authentication, enrollment user, dan konfirmasi untuk aksi sensitif.</p>
        </div>
        <a href="{{ route('settings.access-reviews') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-clipboard-check"></i>
            Access Review
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Policy', 'value' => $summary['policies'], 'color' => 'text-blue-600', 'hint' => 'Total'],
                ['label' => 'Aktif', 'value' => $summary['active_policies'], 'color' => 'text-green-600', 'hint' => 'Enforced'],
                ['label' => 'User 2FA', 'value' => $summary['enrolled_users'], 'color' => 'text-blue-600', 'hint' => 'Enabled'],
                ['label' => 'Pending', 'value' => $summary['pending_users'], 'color' => 'text-amber-600', 'hint' => 'Enrollment'],
                ['label' => 'Confirmed', 'value' => $summary['confirmed_actions'], 'color' => 'text-green-600', 'hint' => 'Action'],
                ['label' => 'Action Pending', 'value' => $summary['pending_actions'], 'color' => 'text-red-600', 'hint' => 'Butuh confirm'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Buat Policy 2FA</h2>
                <p class="text-sm text-gray-500">Tentukan role, channel OTP, remember device, dan aksi yang wajib step-up confirmation.</p>
            </div>
            <form method="POST" action="{{ route('settings.two-factor-security.policies') }}" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Policy</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <input name="policy_code" required placeholder="Kode policy, contoh: 2FA-HR-001" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="policy_name" required placeholder="Nama policy" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Global semua company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Enforcement</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Role Wajib 2FA</div>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($roleCatalog as $role)
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="enforced_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-blue-600">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Aksi Privileged</div>
                            <div class="mt-2 space-y-2">
                                @foreach($actionCatalog as $key => $label)
                                    <label class="flex items-start gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="privileged_actions[]" value="{{ $key }}" class="mt-1 rounded border-gray-300 text-blue-600">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-4">
                        <select name="otp_channel" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="authenticator_app">Authenticator app</option>
                            <option value="email_otp">Email OTP</option>
                            <option value="whatsapp_otp">WhatsApp OTP</option>
                            <option value="hardware_key">Hardware key</option>
                        </select>
                        <input type="number" name="challenge_ttl_minutes" min="1" max="30" value="5" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input type="number" name="remember_device_days" min="0" max="90" value="0" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="notes" placeholder="Catatan policy" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                            <input type="checkbox" name="require_on_login" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Wajib challenge saat login
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                            <input type="checkbox" name="require_for_privileged_action" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Wajib step-up untuk aksi sensitif
                        </label>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-shield-halved mr-2"></i>Simpan Policy</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Enroll User 2FA</h2>
                <p class="mt-1 text-sm text-gray-500">Siapkan user untuk authenticator/OTP tanpa menampilkan secret asli.</p>
                <form method="POST" action="{{ route('settings.two-factor-security.enrollments') }}" class="mt-5 space-y-3">
                    @csrf
                    <select name="user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }} ({{ $user->role?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                    <select name="security_two_factor_policy_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Tanpa link policy</option>
                        @foreach($policies as $policy)
                            <option value="{{ $policy->id }}">{{ $policy->policy_code }} - {{ $policy->policy_name }}</option>
                        @endforeach
                    </select>
                    <div class="grid gap-3 md:grid-cols-2">
                        <select name="method" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="authenticator_app">Authenticator app</option>
                            <option value="email_otp">Email OTP</option>
                            <option value="whatsapp_otp">WhatsApp OTP</option>
                            <option value="hardware_key">Hardware key</option>
                        </select>
                        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="pending">Pending setup</option>
                            <option value="enabled">Enabled</option>
                            <option value="reset_required">Reset required</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                    <textarea name="notes" rows="3" placeholder="Catatan enrollment, device, atau reset." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-user-shield mr-2"></i>Simpan Enrollment</button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Aksi</h2>
                <p class="mt-1 text-sm text-gray-500">Simulasi/rekam approval step-up sebelum aksi sensitif diproses.</p>
                <form method="POST" action="{{ route('settings.two-factor-security.confirmations') }}" class="mt-5 space-y-3">
                    @csrf
                    <select name="user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih user pemohon</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </select>
                    <select name="action_key" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($actionCatalog as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="grid gap-3 md:grid-cols-2">
                        <input name="resource_type" placeholder="Resource, contoh: payroll_run" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="resource_reference" placeholder="ID/nomor dokumen" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <select name="confirmation_method" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="password_and_otp">Password + OTP</option>
                            <option value="otp_only">OTP only</option>
                            <option value="sso_step_up">SSO step-up</option>
                            <option value="admin_dual_control">Dual control admin</option>
                        </select>
                        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <textarea name="notes" rows="3" placeholder="Alasan, risiko, atau approval note." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-key mr-2"></i>Catat Konfirmasi</button>
                </form>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">User 2FA</h2>
                <p class="text-sm text-gray-500">Status enrollment dan backup code mask.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($settings as $setting)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $setting->user?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $setting->user?->email }} / {{ $setting->secret_mask ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $setting->method)) }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst(str_replace('_', ' ', $setting->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada user 2FA.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Log Aksi Privileged</h2>
                <p class="text-sm text-gray-500">Konfirmasi aksi sensitif terbaru.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Aksi</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($confirmations as $confirmation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $confirmation->action_label }}</div>
                                    <div class="text-xs text-gray-500">{{ $confirmation->resource_type ?: '-' }} / {{ $confirmation->resource_reference ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500">{{ $confirmation->user?->email ?: '-' }}</td>
                                <td class="px-5 py-4"><span class="rounded-full {{ $confirmation->status === 'confirmed' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($confirmation->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada konfirmasi aksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
