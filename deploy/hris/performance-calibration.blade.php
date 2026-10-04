@extends('layouts.sidebar')

@section('title', 'Kalibrasi Review')

@php
    $tone = fn ($value) => match ($value) {
        'exceeds', 'completed', 'locked', 'reviewed' => 'bg-green-50 text-green-700',
        'below', 'draft', 'defer' => 'bg-amber-50 text-amber-700',
        'critical', 'returned', 'cancelled' => 'bg-red-50 text-red-700',
        default => 'bg-blue-50 text-blue-700',
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kalibrasi Review</h1>
            <p class="mt-1 text-sm text-gray-500">Normalisasi skor performance review, distribusi rating, keputusan kalibrasi, dan catatan alasan perubahan.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('performance.goal-okr') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-bullseye mr-2"></i>Goal & OKR</a>
            <a href="{{ route('performance.review-360') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-people-arrows mr-2"></i>360 Feedback</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Sesi', 'value' => $summary['sessions'], 'color' => 'text-blue-600', 'sub' => $summary['active_sessions'].' aktif'],
                ['label' => 'Item Kalibrasi', 'value' => $summary['items'], 'color' => 'text-gray-900', 'sub' => 'Form review'],
                ['label' => 'Adjusted', 'value' => $summary['adjusted'], 'color' => 'text-amber-600', 'sub' => 'Naik/turun'],
                ['label' => 'Avg Original', 'value' => $summary['avg_original'], 'color' => 'text-blue-600', 'sub' => 'Sebelum'],
                ['label' => 'Avg Calibrated', 'value' => $summary['avg_calibrated'], 'color' => 'text-green-600', 'sub' => 'Sesudah'],
                ['label' => 'Top Rating', 'value' => $distribution['exceeds'], 'color' => 'text-green-600', 'sub' => 'Exceeds'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Setup Kalibrasi</h2>
                    <p class="text-sm text-gray-500">Buat sesi, lalu masukkan form review yang akan dikalibrasi.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Kalibrasi dipakai setelah response review masuk. HR/management membandingkan skor antar tim, melihat bukti, lalu menetapkan rating final. Jika skor/rating berubah, alasan perubahan wajib dicatat supaya audit trail jelas.
                    </div>
                </div>
            </div>
            <div class="grid gap-5 p-5 xl:grid-cols-2">
                <form method="POST" action="{{ route('performance.calibration.sessions') }}" class="rounded-xl border border-gray-200 p-5">
                    @csrf
                    <h3 class="font-semibold text-gray-900">Sesi Kalibrasi</h3>
                    <p class="mb-4 text-sm text-gray-500">Forum review final untuk satu siklus.</p>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Siklus Review</span><select name="performance_review_cycle_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih siklus review</option>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}">{{ $cycle->cycle_code }} - {{ $cycle->cycle_name }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nomor Sesi</span><input name="session_number" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="CAL-2026-H2-01" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Tanggal</span><input type="date" name="session_date" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nama Sesi</span><input name="session_name" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Kalibrasi Performance H2 2026" required></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Scope</span><select name="calibration_scope" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="company">Company</option><option value="department">Department</option><option value="team">Team</option><option value="grade">Grade</option><option value="site">Site</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Distribusi Target</span><select name="target_distribution" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="balanced">Balanced</option><option value="strict">Strict</option><option value="growth">Growth</option><option value="custom">Custom</option></select></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Facilitator</span><input name="facilitator_name" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Nama HR/Komite"></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="draft">Draft</option><option value="active">Active</option><option value="in_progress">In progress</option><option value="locked">Locked</option><option value="completed">Completed</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Aturan Rating</span><input name="rating_rules" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="A:90+, B:80-89, C:70-79"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Catatan Sesi</span><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan scope dan aturan diskusi."></textarea></label>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white"><i class="fas fa-save mr-2"></i>Simpan Sesi</button></div>
                    </div>
                </form>

                <form method="POST" action="{{ route('performance.calibration.items') }}" class="rounded-xl border border-gray-200 p-5">
                    @csrf
                    <h3 class="font-semibold text-gray-900">Item Kalibrasi</h3>
                    <p class="mb-4 text-sm text-gray-500">Tetapkan rating final untuk form review.</p>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Sesi Kalibrasi</span><select name="performance_calibration_session_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih sesi</option>@foreach($sessions as $session)<option value="{{ $session->id }}">{{ $session->session_number }} - {{ $session->session_name }}</option>@endforeach</select></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Form Review</span><select name="performance_review_form_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih form review</option>@foreach($forms as $form)<option value="{{ $form->id }}">{{ $form->form_number }} - {{ $form->employeeProfile?->full_name }} - skor {{ $form->final_score }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Skor Awal</span><input type="number" step="0.01" name="original_score" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="82" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Skor Kalibrasi</span><input type="number" step="0.01" name="calibrated_score" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="85" required></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Rating Awal</span><input name="original_rating" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="B"></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Rating Final</span><input name="calibrated_rating" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="B+" required></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Bucket</span><select name="rating_bucket" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="exceeds">Exceeds</option><option value="meets">Meets</option><option value="below">Below</option><option value="critical">Critical</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Keputusan</span><select name="calibration_decision" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="keep">Keep</option><option value="raise">Raise</option><option value="lower">Lower</option><option value="defer">Defer</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="draft">Draft</option><option value="reviewed">Reviewed</option><option value="locked">Locked</option><option value="returned">Returned</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Evidence</span><input name="evidence_snapshot" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Goal achievement, 360 feedback, manager note"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Alasan Keputusan</span><textarea name="decision_reason" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Wajib isi jika rating berubah."></textarea></label>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white"><i class="fas fa-check mr-2"></i>Simpan Kalibrasi</button></div>
                    </div>
                </form>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Distribusi Rating</h2>
                <p class="text-sm text-gray-500">Pantau sebaran rating final setelah kalibrasi.</p>
                <div class="mt-4 space-y-3">
                    @foreach([
                        ['label' => 'Exceeds', 'value' => $distribution['exceeds'], 'color' => 'bg-green-500'],
                        ['label' => 'Meets', 'value' => $distribution['meets'], 'color' => 'bg-blue-500'],
                        ['label' => 'Below', 'value' => $distribution['below'], 'color' => 'bg-amber-500'],
                        ['label' => 'Critical', 'value' => $distribution['critical'], 'color' => 'bg-red-500'],
                    ] as $item)
                        <div>
                            <div class="mb-1 flex justify-between text-sm"><span class="text-gray-600">{{ $item['label'] }}</span><span class="font-semibold text-gray-900">{{ $item['value'] }}</span></div>
                            <div class="h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full {{ $item['color'] }}" style="width: {{ min(100, $summary['items'] ? ($item['value'] / $summary['items']) * 100 : 0) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                <h2 class="text-sm font-semibold text-blue-900">Prinsip Kalibrasi</h2>
                <p class="mt-2 text-sm leading-6 text-blue-900/75">Kalibrasi bukan untuk memaksa rating turun, tapi memastikan standar rating konsisten antar manager, grade, dan tim. Perubahan rating harus punya evidence dan alasan yang bisa diaudit.</p>
            </section>
        </aside>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Hasil Kalibrasi</h2>
            <p class="text-sm text-gray-500">Daftar rating final dan keputusan kalibrasi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr><th class="px-5 py-3 text-left">Employee</th><th class="px-5 py-3 text-left">Sesi</th><th class="px-5 py-3 text-left">Skor</th><th class="px-5 py-3 text-left">Rating</th><th class="px-5 py-3 text-left">Keputusan</th><th class="px-5 py-3 text-left">Alasan</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $item->employeeProfile?->full_name ?? $item->form?->employeeProfile?->full_name ?? '-' }}</div><div class="text-xs text-gray-500">{{ $item->form?->form_number ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $item->session?->session_number }}<div class="text-xs text-gray-400">{{ $item->session?->cycle?->cycle_code }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $item->original_score }} → <span class="font-semibold text-blue-600">{{ $item->calibrated_score }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tone($item->rating_bucket) }}">{{ $item->calibrated_rating }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tone($item->calibration_decision) }}">{{ ucfirst($item->calibration_decision) }}</span></td>
                            <td class="px-5 py-4 max-w-md text-gray-500">{{ $item->decision_reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Belum ada item kalibrasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
