@extends('layouts.sidebar')

@section('title', 'Pusat Rekrutmen')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $stageLabel = [
        'sourcing' => 'Sourcing',
        'screening' => 'Screening',
        'interview' => 'Interview',
        'offer' => 'Offer',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pusat Rekrutmen</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau kebutuhan manpower, pipeline kandidat, portal dokumen, interview, offer, pengecekan, konversi employee, dan onboarding.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('recruitment.manpower-requests') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-user-plus mr-2"></i> Manpower
            </a>
            <a href="{{ route('recruitment.candidates') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-filter-circle-dollar mr-2"></i> ATS Pipeline
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['readiness'].'%', 'color' => $summary['readiness'] >= 75 ? 'text-green-600' : 'text-amber-600', 'sub' => 'Kontrol proses'],
                ['label' => 'Manpower Aktif', 'value' => $summary['open_manpower'].'/'.$summary['manpower'], 'color' => 'text-blue-600', 'sub' => 'Request'],
                ['label' => 'Kandidat Aktif', 'value' => $summary['active_candidates'].'/'.$summary['candidates'], 'color' => 'text-blue-600', 'sub' => 'Pipeline'],
                ['label' => 'Dokumen Review', 'value' => $summary['portal_needs_review'], 'color' => $summary['portal_needs_review'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Portal kandidat'],
                ['label' => 'Handoff Employee', 'value' => $summary['conversions'].'/'.$summary['accepted_offers'], 'color' => 'text-green-600', 'sub' => 'Konversi'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Checklist Rekrutmen</h2>
                    <p class="text-sm text-gray-500">Kontrol agar proses dari kebutuhan posisi sampai onboarding tidak putus.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Halaman ini adalah command center rekrutmen. HR tetap input detail di sub-menu, sedangkan halaman ini dipakai untuk melihat proses yang macet: manpower belum terpenuhi, dokumen kandidat belum diverifikasi, interview belum selesai, offer belum final, pengecekan kandidat masih blocking, atau kandidat hired belum dikonversi menjadi employee.
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($checks as $check)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $check['label'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $check['note'] }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">{{ $check['value'] }}</div>
                        <div class="flex items-center gap-2 md:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ in_array($check['status'], ['Aktif', 'Berjalan', 'Terkontrol', 'Terjadwal', 'Aman', 'Sinkron'], true) ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $check['status'] }}</span>
                            <a href="{{ route($check['route']) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Alur Kerja</h2>
                <p class="text-sm text-gray-500">Urutan kerja yang disarankan supaya rekrutmen rapi.</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach([
                    ['step' => '1', 'title' => 'Ajukan kebutuhan manpower', 'route' => 'recruitment.manpower-requests', 'desc' => 'Pastikan posisi, headcount, budget, dan target join date jelas.'],
                    ['step' => '2', 'title' => 'Masukkan kandidat ke pipeline', 'route' => 'recruitment.candidates', 'desc' => 'Catat sumber kandidat dan skor screening awal sebelum interview.'],
                    ['step' => '3', 'title' => 'Kirim portal kandidat', 'route' => 'recruitment.candidate-portal', 'desc' => 'Kandidat submit CV, portofolio, KTP, atau dokumen lain lewat portal.'],
                    ['step' => '4', 'title' => 'Jalankan interview & scorecard', 'route' => 'recruitment.interviews', 'desc' => 'Gunakan scorecard agar keputusan user lebih objektif.'],
                    ['step' => '5', 'title' => 'Offer, check, dan konversi', 'route' => 'recruitment.employee-conversions', 'desc' => 'Setelah offer accepted dan pengecekan aman, buat employee profile.'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $item['step'] }}</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ $item['title'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $item['desc'] }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Pipeline Kandidat</h2>
                <p class="text-sm text-gray-500">Kandidat terbaru beserta dokumen, interview, offer, dan pengecekan.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($candidates as $candidate)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $candidate->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $candidate->candidate_number }} · {{ $candidate->manpowerRequest?->position_title ?? 'Tanpa manpower' }} · {{ $stageLabel[$candidate->pipeline_stage] ?? ucfirst((string) $candidate->pipeline_stage) }}</div>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ $candidate->documents_count ?? $candidate->documents->count() }} dokumen</span>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ $candidate->interviews_count ?? $candidate->interviews->count() }} interview</span>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1">{{ $candidate->checks_count ?? $candidate->checks->count() }} check</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Skor Screening</div>
                            <div class="text-xl font-semibold text-blue-600">{{ $candidate->fit_score ?? 0 }}</div>
                            <div class="text-xs text-gray-500">{{ $candidate->owner_recruiter ?: 'Belum ada owner' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kandidat.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Manpower Terbaru</h2>
                <p class="text-sm text-gray-500">Kebutuhan posisi yang menjadi sumber pipeline kandidat.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($manpowerRequests as $request)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $request->request_number }}</div>
                            <div class="text-sm text-gray-500">{{ $request->position_title }} · {{ $request->department ?: '-' }} · {{ $request->company?->code ?? '-' }}</div>
                            <div class="mt-1 text-xs text-gray-500">Target join: {{ $request->target_join_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ $request->headcount }} orang</div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ in_array($request->status, ['approved', 'open', 'in_progress'], true) ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst((string) $request->status) }}</span>
                            <div class="mt-1 text-xs text-gray-500">{{ $money($request->budget_min) }} - {{ $money($request->budget_max) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada manpower request.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Interview Terdekat</h2>
                <p class="text-sm text-gray-500">Jadwal interview dan keputusan scorecard terbaru.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($interviews as $interview)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $interview->interview_number }}</div>
                            <div class="text-sm text-gray-500">{{ $interview->candidate?->full_name ?? '-' }} · {{ ucfirst((string) $interview->interview_stage) }} · {{ ucfirst((string) $interview->interview_mode) }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $interview->scheduled_at?->format('d M Y H:i') ?? '-' }} · {{ $interview->interviewer_name ?: '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-semibold text-blue-600">{{ number_format((float) $interview->overall_score, 1) }}</div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-blue-50 text-blue-700">{{ ucfirst((string) $interview->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada interview.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Offer Terbaru</h2>
                <p class="text-sm text-gray-500">Status offer letter dan readiness tanda tangan elektronik.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($offers as $offer)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $offer->offer_number }}</div>
                            <div class="text-sm text-gray-500">{{ $offer->candidate?->full_name ?? '-' }} · {{ $offer->position_title }} · {{ $offer->esign_provider ?: 'Tanpa e-sign' }}</div>
                            <div class="mt-1 text-xs text-gray-500">Join: {{ $offer->join_date?->format('d M Y') ?? '-' }} · Expired: {{ $offer->expires_at?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ $money($offer->salary_offer) }}</div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ in_array($offer->status, ['accepted', 'signed'], true) ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst((string) $offer->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada offer letter.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
