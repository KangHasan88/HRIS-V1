@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Inbox Approval</h1>
            <p class="mt-1 text-sm text-gray-500">Review pengajuan tim, proses approval massal, cek risiko, dan atur delegasi saat berhalangan.</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-chart-line"></i> Dashboard Manager
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Pending Approval', 'value' => $summary['pending'], 'color' => 'text-blue-600'],
                ['label' => 'High Risk', 'value' => $summary['high_risk'], 'color' => 'text-red-600'],
                ['label' => 'Medium Risk', 'value' => $summary['medium_risk'], 'color' => 'text-amber-600'],
                ['label' => 'Delegasi Aktif', 'value' => $summary['delegations'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Approval Tim</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai inbox approval',
                        'description' => 'Pilih beberapa pengajuan yang sudah dicek, isi catatan keputusan, lalu proses approve/reject sekaligus.',
                        'steps' => [
                            'Prioritaskan item High Risk karena biasanya berdampak ke payroll, absensi, atau budget.',
                            'Buka detail modul asal jika perlu validasi lampiran atau histori karyawan.',
                            'Gunakan bulk action hanya untuk item yang memang sudah layak diputuskan bersama.',
                        ],
                        'notes' => [
                            'Semua keputusan disimpan ke audit trail.',
                            'Delegasi dipakai saat manager berhalangan agar approval tidak tertahan.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Inbox ini menggabungkan cuti, lembur, dinas, koreksi absensi, reimbursement, dan update profil.</p>
            </div>

            <form action="{{ route('manager.approval-inbox.bulk') }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3"><input type="checkbox" onclick="document.querySelectorAll('.approval-check').forEach(cb => cb.checked = this.checked)" class="rounded border-gray-300 text-blue-600"></th>
                                <th class="px-5 py-3">Pengajuan</th>
                                <th class="px-5 py-3">Karyawan</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Risiko</th>
                                <th class="px-5 py-3">Approver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($items as $item)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <input type="checkbox" name="items[]" value="{{ $item['token'] }}" class="approval-check rounded border-gray-300 text-blue-600">
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $item['label'] }}</div>
                                        <div class="font-mono text-xs text-gray-500">{{ $item['number'] }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($item['status']) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $item['employee'] }}</div>
                                        <div class="font-mono text-xs text-gray-500">{{ $item['employee_number'] }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-xs font-semibold text-gray-700">{{ $item['date_range'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Submit: {{ $item['submitted_at']?->format('d M Y H:i') ?: '-' }}</div>
                                        @if($item['amount'])
                                            <div class="mt-1 text-xs text-gray-500">Nilai: Rp {{ number_format((float) $item['amount'], 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $item['risk_level'] === 'high' ? 'bg-red-50 text-red-700' : ($item['risk_level'] === 'medium' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700') }}">{{ ucfirst($item['risk_level']) }}</span>
                                        <div class="mt-2 max-w-xs text-xs text-gray-500">{{ $item['risk_reason'] }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-xs text-gray-700">{{ $item['current_approver'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada approval pending untuk tim ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-3 border-t border-gray-100 p-5 lg:grid-cols-[180px_1fr_240px]">
                    <select name="decision" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                    <input name="decision_notes" placeholder="Catatan keputusan, contoh: disetujui sesuai jadwal tim." class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-check-double"></i> Proses Terpilih
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="panel rounded-2xl p-5">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Delegasi Approval</h2>
                    <p class="text-sm text-gray-500">Alihkan approval sementara saat cuti, dinas, atau unavailable.</p>
                </div>
                <form action="{{ route('manager.approval-inbox.delegations') }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Delegasikan ke</label>
                        <select name="delegate_user_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih user</option>
                            @foreach($delegates as $delegate)
                                <option value="{{ $delegate->id }}">{{ $delegate->name }} - {{ $delegate->role?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope</label>
                        <select name="scope_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="all">Semua approval</option>
                            <option value="leave">Cuti</option>
                            <option value="overtime">Lembur</option>
                            <option value="business_trip">Dinas</option>
                            <option value="attendance_correction">Koreksi Absensi</option>
                            <option value="reimbursement">Reimbursement</option>
                            <option value="profile_update">Update Profil</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai</label>
                            <input type="date" name="start_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai</label>
                            <input type="date" name="end_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <textarea name="reason" rows="3" placeholder="Alasan delegasi." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-user-clock"></i> Simpan Delegasi
                    </button>
                </form>
            </section>

            <section class="panel rounded-2xl">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Delegasi</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($delegations as $delegation)
                        <div class="px-5 py-4 text-sm">
                            <div class="font-semibold text-gray-900">{{ $delegation->delegate?->name }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($delegation->scope_type)) }} · {{ $delegation->start_date->format('d M Y') }} - {{ $delegation->end_date->format('d M Y') }}</div>
                            <span class="mt-2 inline-flex rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-700">{{ ucfirst($delegation->status) }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada delegasi.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
