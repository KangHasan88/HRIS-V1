@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pengajuan Cuti</h1>
            <p class="mt-1 text-sm text-gray-500">Buat pengajuan cuti, cek simulasi saldo, dan pantau approval HR/manager.</p>
        </div>
        <a href="{{ route('leave.policies') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-scale-balanced"></i>
            Kebijakan Cuti
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
                ['label' => 'Total Pengajuan', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Rejected', 'value' => $summary['rejected'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.98fr_1.02fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Pengajuan</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Pengajuan Cuti',
                        'description' => 'Form ini dipakai HR/admin untuk mencatat pengajuan cuti dari ESS, mobile WebView, atau input manual.',
                        'steps' => [
                            'Pilih karyawan, jenis cuti, tanggal mulai, dan tanggal akhir.',
                            'Klik Cek Simulasi agar sistem menghitung saldo sebelum pengajuan disimpan.',
                            'Isi nomor request, sumber pengajuan, approver, alasan, dan status approval.',
                        ],
                        'notes' => [
                            'Simulasi mengikuti policy cuti aktif, termasuk weekend dan kalender libur.',
                            'Saldo minus tetap disimpan sebagai warning untuk proses approval berikutnya.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Simulasi saldo dihitung sebelum request disimpan.</p>
            </div>

            <form method="GET" action="{{ route('leave.requests') }}" class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-gray-900">Cek Simulasi Saldo</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                        <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(($filters['employee_profile_id'] ?? null) == $employee->id)>{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Cuti</label>
                        <select name="leave_policy_type_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih jenis cuti</option>
                            @foreach($policyTypes as $type)
                                <option value="{{ $type->id }}" @selected(($filters['leave_policy_type_id'] ?? null) == $type->id)>{{ $type->code }} - {{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Mulai</label>
                        <input type="date" name="start_date" required value="{{ $filters['start_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Akhir</label>
                        <input type="date" name="end_date" required value="{{ $filters['end_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        <i class="fas fa-calculator"></i>
                        Cek Simulasi
                    </button>
                </div>
            </form>

            @if($simulation)
                <div class="mt-4 rounded-xl border {{ $simulation['is_over_balance'] ? 'border-red-200 bg-red-50' : 'border-blue-100 bg-blue-50' }} p-4">
                    <div class="text-sm font-semibold {{ $simulation['is_over_balance'] ? 'text-red-800' : 'text-blue-800' }}">Hasil Simulasi Saldo</div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-4">
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Kuota</div>
                            <div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['quota_days'], 1) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Diminta</div>
                            <div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['requested_days'], 1) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Saldo Awal</div>
                            <div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['balance_before'], 1) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Saldo Akhir</div>
                            <div class="text-lg font-semibold {{ $simulation['is_over_balance'] ? 'text-red-700' : 'text-green-700' }}">{{ number_format($simulation['balance_after'], 1) }}</div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">Weekend: {{ $simulation['include_weekends'] ? 'dihitung' : 'tidak dihitung' }} · Libur: {{ $simulation['include_holidays'] ? 'dihitung' : 'tidak dihitung' }} · Rule ID: {{ $simulation['rule_id'] ?: 'fallback master' }}</p>
                </div>
            @endif

            <form action="{{ route('leave.requests.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Request</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Request</label>
                            <input name="request_number" required placeholder="Contoh: LEAVE-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Pengajuan</label>
                            <select name="request_source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="web_admin">Web Admin</option>
                                <option value="ess_webview">ESS WebView</option>
                                <option value="mobile_webview">Mobile WebView</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(($filters['employee_profile_id'] ?? null) == $employee->id)>{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Cuti</label>
                            <select name="leave_policy_type_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih jenis cuti</option>
                                @foreach($policyTypes as $type)
                                    <option value="{{ $type->id }}" @selected(($filters['leave_policy_type_id'] ?? null) == $type->id)>{{ $type->code }} - {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Mulai</label>
                            <input type="date" name="start_date" required value="{{ $filters['start_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Akhir</label>
                            <input type="date" name="end_date" required value="{{ $filters['end_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval & Catatan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="submitted">Submitted</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: Manager Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Lampiran</label>
                            <input name="attachment_reference" placeholder="Nomor dokumen, URL, atau file reference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Cuti</label>
                            <textarea name="reason" rows="3" placeholder="Alasan pengajuan cuti dari karyawan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Keputusan</label>
                            <textarea name="decision_notes" rows="3" placeholder="Catatan approval, revisi, atau alasan penolakan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                    Simpan Pengajuan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Pengajuan</h2>
                <p class="text-sm text-gray-500">Status approval dan saldo hasil simulasi tersimpan per request.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Request</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">Saldo</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($requests as $leave)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $leave->request_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $leave->policyType?->code }} · {{ $leave->request_source }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $leave->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $leave->employeeProfile?->employee_number }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $leave->start_date?->format('d M Y') }} - {{ $leave->end_date?->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format((float) $leave->requested_days, 1) }} hari</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ number_format((float) $leave->balance_before, 1) }} → {{ number_format((float) $leave->balance_after, 1) }}</div>
                                    <div class="text-xs text-gray-500">Approver: {{ $leave->current_approver ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{
                                        $leave->approval_status === 'approved' ? 'bg-green-50 text-green-700' :
                                        ($leave->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')
                                    }}">{{ ucfirst($leave->approval_status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
