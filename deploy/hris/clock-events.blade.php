@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Clock Mobile</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau clock-in/out mobile WebView dengan GPS, selfie, device binding, dan liveness.</p>
        </div>
        <a href="{{ route('attendance.shift-schedules') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-business-time"></i>
            Jadwal Shift
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
                ['label' => 'Clock Event', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Need Review', 'value' => $summary['need_review'], 'color' => 'text-amber-600'],
                ['label' => 'Trusted Device', 'value' => $summary['trusted_device'], 'color' => 'text-indigo-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Catat Clock Event</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Clock Mobile',
                        'description' => 'Form ini adalah simulasi/admin capture untuk data clock-in/out dari ESS Mobile WebView.',
                        'steps' => [
                            'Pilih karyawan, tipe clock, waktu event, dan roster shift jika tersedia.',
                            'Isi koordinat GPS, akurasi, dan label lokasi untuk validasi lokasi.',
                            'Isi referensi selfie dan skor liveness untuk memastikan clock dilakukan orang yang benar.',
                            'Isi device UID dan aktifkan bind device jika perangkat boleh dipercaya untuk karyawan tersebut.',
                        ],
                        'notes' => [
                            'Jika GPS, selfie, liveness, atau device bermasalah, status approved akan otomatis turun menjadi Need Review.',
                            'Data ini nanti menjadi kontrak untuk ESS WebView Android/iOS dan handoff ke anomali absensi/payroll.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Dipakai untuk validasi flow mobile sebelum endpoint ESS dibuat penuh.</p>
            </div>

            <form action="{{ route('attendance.clock-events.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Employee</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Roster Shift</label>
                            <select name="attendance_shift_assignment_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional, link ke roster</option>
                                @foreach($shiftAssignments as $assignment)
                                    <option value="{{ $assignment->id }}">{{ $assignment->shiftPattern?->code }} - {{ $assignment->employeeProfile?->full_name ?: ucfirst($assignment->assignment_scope) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Clock Event</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Event</label>
                            <input name="event_number" required placeholder="Contoh: CLOCK-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Clock</label>
                            <select name="event_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="clock_in">Clock In</option>
                                <option value="clock_out">Clock Out</option>
                                <option value="break_start">Mulai Istirahat</option>
                                <option value="break_end">Selesai Istirahat</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu Event</label>
                            <input type="datetime-local" name="event_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber</label>
                            <select name="source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="mobile_webview">Mobile WebView</option>
                                <option value="admin_manual">Admin Manual</option>
                                <option value="biometric_import">Biometric Import</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">GPS & Lokasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Latitude</label>
                            <input type="number" step="0.0000001" name="latitude" placeholder="Contoh: -6.2000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Longitude</label>
                            <input type="number" step="0.0000001" name="longitude" placeholder="Contoh: 106.8166667" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akurasi GPS</label>
                            <input type="number" min="0" max="5000" name="gps_accuracy_meter" placeholder="Meter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Label Lokasi</label>
                        <input name="location_label" placeholder="Contoh: BMP-HO / Site Morowali / WFH" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Selfie & Liveness</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Selfie</label>
                            <input name="selfie_reference" placeholder="Path file / object storage / URL" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skor Liveness (0-100)</label>
                            <input type="number" min="0" max="100" name="liveness_score" required value="90" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Device Binding</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Device UID</label>
                            <input name="device_uid" placeholder="Unique ID dari WebView/native wrapper" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Device</label>
                            <input name="device_name" placeholder="Contoh: iPhone Hasan / Android Site 01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Platform</label>
                            <input name="platform" value="webview" placeholder="webview / android / ios" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">App Version</label>
                            <input name="app_version" placeholder="Contoh: 1.0.0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <label class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="bind_device" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Jadikan device ini trusted untuk karyawan
                    </label>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Review HR</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Event</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="submitted">Submitted</option>
                                <option value="approved">Approved</option>
                                <option value="need_review">Need Review</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <input name="notes" placeholder="Catatan koreksi, approval, atau risk review." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-mobile-screen-button"></i>
                    Simpan Clock Event
                </button>
            </form>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Log Clock Mobile</h2>
                    <p class="text-sm text-gray-500">Data clock-in/out beserta risiko GPS, selfie, liveness, dan device.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Event</th>
                                <th class="px-5 py-3">Employee</th>
                                <th class="px-5 py-3">GPS</th>
                                <th class="px-5 py-3">Liveness</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($events as $event)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $event->event_number }}</div>
                                        <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $event->event_type)) }}</div>
                                        <div class="text-xs text-gray-500">{{ $event->event_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $event->employeeProfile?->full_name }}</div>
                                        <div class="font-mono text-xs text-gray-500">{{ $event->employeeProfile?->employee_number }}</div>
                                        <div class="text-xs text-gray-500">{{ $event->shiftAssignment?->shiftPattern?->code ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $event->location_label ?: '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $event->latitude ?: '-' }}, {{ $event->longitude ?: '-' }}</div>
                                        <div class="text-xs text-gray-500">Accuracy: {{ $event->gps_accuracy_meter ?? '-' }}m</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold {{ $event->is_liveness_passed ? 'text-green-600' : 'text-red-600' }}">{{ $event->liveness_score }}%</div>
                                        <div class="text-xs text-gray-500">{{ $event->selfie_reference ?: 'Selfie kosong' }}</div>
                                        <div class="text-xs {{ $event->is_device_trusted ? 'text-green-600' : 'text-amber-600' }}">{{ $event->is_device_trusted ? 'Trusted device' : 'Device perlu review' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $event->status === 'approved' ? 'bg-green-50 text-green-600' : ($event->status === 'need_review' ? 'bg-amber-50 text-amber-600' : ($event->status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600')) }} px-3 py-1 text-xs font-semibold">{{ ucwords(str_replace('_', ' ', $event->status)) }}</span>
                                        @if(!empty($event->risk_flags))
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                @foreach($event->risk_flags as $flag)
                                                    <span class="rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600">{{ str_replace('_', ' ', $flag) }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada clock event.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Device Binding</h2>
                    <p class="text-sm text-gray-500">Perangkat yang pernah dipakai clock mobile oleh karyawan.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($devices as $device)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $device->device_name ?: $device->device_uid }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $device->device_uid }}</div>
                                    <div class="text-xs text-gray-500">{{ $device->employeeProfile?->employee_number }} - {{ $device->employeeProfile?->full_name }}</div>
                                </div>
                                <span class="rounded-full {{ $device->status === 'active' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }} px-3 py-1 text-xs font-semibold">{{ ucfirst($device->status) }}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">{{ $device->platform }} {{ $device->app_version ? '/ '.$device->app_version : '' }} | Last seen: {{ $device->last_seen_at?->format('d M Y H:i') ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada device binding.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
