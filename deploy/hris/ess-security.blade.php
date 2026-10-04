@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">ESS Security</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola biometric login, fallback PIN, dan trusted device untuk ESS WebView.</p>
        </div>
        <a href="{{ route('settings.mobile-bridge') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i> Mobile Bridge
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Device Terdaftar', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Trusted', 'value' => $summary['trusted'], 'color' => 'text-green-600'],
                ['label' => 'Pending Review', 'value' => $summary['pending'], 'color' => 'text-amber-600'],
                ['label' => 'Biometric Aktif', 'value' => $summary['biometric'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Daftarkan Device</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan ESS Security',
                        'description' => 'Form ini mencatat device WebView yang boleh dipakai karyawan untuk login ESS dengan biometric atau PIN cadangan.',
                        'steps' => [
                            'Pilih user dan employee yang memakai device.',
                            'Isi device UID dari native wrapper Android/iOS atau browser WebView.',
                            'Aktifkan biometric jika device mendukung fingerprint/Face ID.',
                            'Isi fallback PIN agar karyawan tetap bisa login jika biometric gagal.',
                            'Set trust status trusted hanya untuk device yang sudah diverifikasi.',
                        ],
                        'notes' => [
                            'PIN disimpan sebagai hash, bukan plain text.',
                            'Risk flag muncul jika biometric mati, PIN kosong, browser ditandai trusted, atau gagal login berulang.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Dipakai sebagai registry keamanan ESS mobile Android/iOS full WebView.</p>
            </div>

            <form action="{{ route('settings.ess-security.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas User</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">User Login</label>
                            <select name="user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Employee Link</label>
                            <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional jika belum link employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Device WebView</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Device UID</label>
                            <input name="device_uid" required placeholder="Contoh: android-uuid-hash-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Device</label>
                            <input name="device_name" placeholder="Contoh: Android Dimas / iPhone Ayu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Platform</label>
                            <select name="platform" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="android">Android WebView</option>
                                <option value="ios">iOS WebView</option>
                                <option value="webview">Generic WebView</option>
                                <option value="browser">Browser</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">App Version</label>
                            <input name="app_version" placeholder="Contoh: 1.0.0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Biometric & PIN</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="biometric_enabled" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                            Biometric aktif di device
                        </label>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Biometric</label>
                            <select name="biometric_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="device_biometric">Device biometric</option>
                                <option value="fingerprint">Fingerprint</option>
                                <option value="face_id">Face ID</option>
                                <option value="none">Tidak ada</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Fallback PIN</label>
                            <input name="fallback_pin" type="password" minlength="4" maxlength="12" placeholder="4-12 digit/karakter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Failed Attempt</label>
                            <input name="failed_attempts" type="number" min="0" max="99" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Trust Review</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Trust Status</label>
                            <select name="trust_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="pending">Pending review</option>
                                <option value="trusted">Trusted</option>
                                <option value="blocked">Blocked</option>
                                <option value="revoked">Revoked</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Security</label>
                            <input name="notes" placeholder="Catatan verifikasi device / reset PIN / revoke." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-shield-halved"></i> Simpan Device
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Trusted Device Registry</h2>
                <p class="text-sm text-gray-500">Device ESS yang terdaftar untuk biometric login dan fallback PIN.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Device</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Auth</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($devices as $device)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $device->device_name ?: $device->device_uid }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $device->device_uid }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper($device->platform) }} {{ $device->app_version ? '/ '.$device->app_version : '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $device->user?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $device->user?->email }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $device->employeeProfile?->employee_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold {{ $device->biometric_enabled ? 'text-green-600' : 'text-amber-600' }}">{{ $device->biometric_enabled ? 'Biometric aktif' : 'Biometric mati' }}</div>
                                    <div class="text-xs text-gray-500">{{ $device->biometric_type ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Failed: {{ $device->failed_attempts }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $device->trust_status === 'trusted' ? 'bg-green-50 text-green-600' : ($device->trust_status === 'blocked' || $device->trust_status === 'revoked' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }} px-3 py-1 text-xs font-semibold">{{ ucfirst($device->trust_status) }}</span>
                                    @if(!empty($device->risk_flags))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach($device->risk_flags as $flag)
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600">{{ str_replace('_', ' ', $flag) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-2 text-xs text-gray-500">Last auth: {{ $device->last_authenticated_at?->format('d M Y H:i') ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada trusted device ESS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
