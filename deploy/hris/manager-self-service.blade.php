@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manager Self Service</h1>
            <p class="mt-1 text-sm text-gray-500">Pusat kerja atasan untuk approval, tim, budget, dokumen, headcount, dan coaching.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('manager.approval-inbox') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                <i class="fas fa-inbox"></i> Buka Approval
            </a>
            <a href="{{ route('manager.team-calendar') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-days"></i> Kalender Tim
            </a>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-7">
            @foreach([
                ['label' => 'Tim Aktif', 'value' => $summary['team_total'], 'color' => 'text-gray-900'],
                ['label' => 'Approval Pending', 'value' => $summary['approval_pending'], 'color' => 'text-amber-600'],
                ['label' => 'High Risk', 'value' => $summary['high_risk'], 'color' => 'text-red-600'],
                ['label' => 'Budget Aktif', 'value' => $summary['budget_active'], 'color' => 'text-green-600'],
                ['label' => 'Coaching', 'value' => $summary['performance_items'], 'color' => 'text-blue-600'],
                ['label' => 'Headcount', 'value' => $summary['headcount_items'], 'color' => 'text-indigo-600'],
                ['label' => 'Dokumen', 'value' => $summary['document_items'], 'color' => 'text-cyan-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="panel rounded-2xl xl:col-span-2">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Antrian Keputusan</h2>
                    <p class="text-sm text-gray-500">Request tim yang butuh keputusan manager atau atasan.</p>
                </div>
                @include('partials.form-help', [
                    'title' => 'Panduan Manager Self Service',
                    'description' => 'Halaman ini menjadi pintu awal atasan untuk melihat pekerjaan managerial harian tanpa membuka banyak menu satu per satu.',
                    'steps' => [
                        'Mulai dari Antrian Keputusan untuk approval cuti, lembur, dinas, koreksi absensi, reimbursement, dan update profil.',
                        'Gunakan pintasan workflow untuk membuka modul manager terkait.',
                        'Pantau scope tim, budget, headcount, dan dokumen dari ringkasan di halaman ini.',
                    ],
                ])
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($approvals->take(8) as $approval)
                    <div class="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $approval['type'] }}</span>
                                @if($approval['risk'] === 'high')
                                    <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">High Risk</span>
                                @endif
                            </div>
                            <div class="mt-2 font-semibold text-gray-900">{{ $approval['employee'] }}</div>
                            <div class="text-sm text-gray-500">{{ $approval['number'] }} - {{ ucfirst(str_replace('_', ' ', $approval['status'])) }}</div>
                        </div>
                        <a href="{{ $approval['route'] }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Review</a>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">Tidak ada approval pending untuk scope manager ini.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Scope Manager</h2>
                <p class="text-sm text-gray-500">Identitas atasan dan cakupan tim aktif.</p>
            </div>
            <div class="space-y-4 p-5">
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Atasan</div>
                    <div class="mt-1 font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ $managerEmployee?->employee_number ?: 'Belum link employee profile' }}</div>
                </div>
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Cabang / Site</div>
                    <div class="mt-1 font-semibold text-gray-900">{{ $managerEmployee?->branch?->code ?: auth()->user()->branch?->code ?: '-' }}</div>
                    <div class="text-sm text-gray-500">{{ $managerEmployee?->branch?->name ?: auth()->user()->branch?->name ?: 'Scope mengikuti role user' }}</div>
                </div>
                <a href="{{ route('manager.dashboard') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-chart-line"></i> Dashboard Tim
                </a>
            </div>
        </section>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['title' => 'Approval Inbox', 'desc' => 'Bulk approve, reject, dan delegasi approval.', 'route' => route('manager.approval-inbox'), 'icon' => 'fas fa-inbox'],
            ['title' => 'Jurnal Performa', 'desc' => 'Catatan coaching dan one-on-one anggota tim.', 'route' => route('manager.performance-journal'), 'icon' => 'fas fa-clipboard-user'],
            ['title' => 'Headcount Tim', 'desc' => 'Rencana manpower dan replacement request.', 'route' => route('manager.headcount-requisitions'), 'icon' => 'fas fa-user-plus'],
            ['title' => 'Budget Tim', 'desc' => 'Pantau budget overtime, claim, dan trip.', 'route' => route('manager.budget'), 'icon' => 'fas fa-chart-pie'],
            ['title' => 'Dokumen Tim', 'desc' => 'Request dokumen dan surat keterangan kerja.', 'route' => route('manager.team-documents'), 'icon' => 'fas fa-folder-open'],
            ['title' => 'Kalender Tim', 'desc' => 'Cuti, shift, lembur, dan dinas dalam satu kalender.', 'route' => route('manager.team-calendar'), 'icon' => 'fas fa-calendar-days'],
            ['title' => 'Pengecualian Absensi', 'desc' => 'Review clock event dengan map dan bukti.', 'route' => route('manager.attendance-exceptions'), 'icon' => 'fas fa-map-location-dot'],
            ['title' => 'Review Cuti Tim', 'desc' => 'Akses cepat ke daftar cuti operasional.', 'route' => route('leave.requests'), 'icon' => 'fas fa-calendar-check'],
        ] as $shortcut)
            <a href="{{ $shortcut['route'] }}" class="panel rounded-2xl p-5 hover:border-blue-200">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="{{ $shortcut['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $shortcut['title'] }}</h3>
                        <p class="mt-1 text-sm leading-5 text-gray-500">{{ $shortcut['desc'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Anggota Tim</h2>
                <p class="text-sm text-gray-500">Daftar ringkas karyawan dalam scope manager.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($team->take(8) as $employee)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $employee->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $employee->employee_number }} - {{ $employee->position ?: 'No position' }}</div>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <div>{{ $employee->branch?->code ?: '-' }}</div>
                            <div>{{ $employee->department ?: '-' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">Belum ada anggota tim dalam scope manager.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Manager</h2>
                <p class="text-sm text-gray-500">Ringkasan pekerjaan manager yang sudah tercatat.</p>
            </div>
            <div class="grid grid-cols-2 gap-4 p-5">
                @foreach([
                    ['label' => 'Performance Notes', 'value' => $performanceNotes->count()],
                    ['label' => 'One-on-One', 'value' => $oneOnOnes->count()],
                    ['label' => 'Headcount Plan', 'value' => $headcountPlans->count()],
                    ['label' => 'Replacement', 'value' => $replacementRequests->count()],
                    ['label' => 'Request Dokumen', 'value' => $teamDocuments->count()],
                    ['label' => 'Surat Kerja', 'value' => $certificates->count()],
                ] as $item)
                    <div class="rounded-xl border border-gray-100 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                        <div class="mt-1 text-xl font-semibold text-gray-900">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
