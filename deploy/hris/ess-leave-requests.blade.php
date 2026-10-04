@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Cuti & Izin</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan cuti, cek simulasi saldo, dan pantau status approval dari ESS.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif
    @unless ($employee)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            <div class="font-semibold">Akun login belum terhubung ke profil karyawan.</div>
            <div class="mt-1">Halaman sudah siap, tapi pengajuan cuti baru aktif setelah HR/admin menghubungkan akun ini ke profil karyawan.</div>
        </div>
    @endunless

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

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Ajukan Cuti</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Cuti ESS',
                        'description' => 'Form ini dipakai karyawan untuk mengajukan cuti sendiri melalui ESS atau mobile WebView.',
                        'steps' => [
                            'Pilih jenis cuti, tanggal mulai, dan tanggal akhir.',
                            'Klik Cek Simulasi untuk melihat estimasi saldo sebelum submit.',
                            'Isi nomor request, alasan, dan referensi lampiran jika diperlukan.',
                        ],
                        'notes' => [
                            'Pengajuan yang dikirim dari ESS otomatis masuk status menunggu approval.',
                            'Karyawan hanya melihat pengajuan miliknya sendiri.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">{{ $employee ? "{$employee->employee_number} - {$employee->full_name}" : 'Belum ada profil karyawan yang terhubung.' }}</p>
            </div>

            @if ($employee)
            <form method="GET" action="{{ route('ess.leave-requests') }}" class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-gray-900">Simulasi Saldo</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
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
                    <div class="text-sm font-semibold {{ $simulation['is_over_balance'] ? 'text-red-800' : 'text-blue-800' }}">Hasil Simulasi</div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-4">
                        <div><div class="text-xs font-semibold uppercase text-gray-500">Kuota</div><div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['quota_days'], 1) }}</div></div>
                        <div><div class="text-xs font-semibold uppercase text-gray-500">Diminta</div><div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['requested_days'], 1) }}</div></div>
                        <div><div class="text-xs font-semibold uppercase text-gray-500">Saldo Awal</div><div class="text-lg font-semibold text-gray-900">{{ number_format($simulation['balance_before'], 1) }}</div></div>
                        <div><div class="text-xs font-semibold uppercase text-gray-500">Saldo Akhir</div><div class="text-lg font-semibold {{ $simulation['is_over_balance'] ? 'text-red-700' : 'text-green-700' }}">{{ number_format($simulation['balance_after'], 1) }}</div></div>
                    </div>
                </div>
            @endif

            <form action="{{ route('ess.leave-requests.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Pengajuan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Request</label>
                            <input name="request_number" required placeholder="Contoh: ESS-LEAVE-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber</label>
                            <select name="request_source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="ess_webview">ESS WebView</option>
                                <option value="mobile_webview">Mobile WebView</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
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
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Lampiran</label>
                            <input name="attachment_reference" placeholder="Nomor dokumen, URL, atau file reference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Cuti</label>
                            <textarea name="reason" rows="3" placeholder="Jelaskan alasan pengajuan cuti." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                    Kirim Pengajuan
                </button>
            </form>
            @else
                <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                    Form pengajuan akan muncul setelah akun login terhubung ke profil karyawan.
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Cuti & Izin</h2>
                <p class="text-sm text-gray-500">Hanya menampilkan pengajuan milik akun login.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Request</th>
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
                                <td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
