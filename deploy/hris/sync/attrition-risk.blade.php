@extends('layouts.sidebar')

@section('title', 'Risiko Attrition')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Risiko Attrition</h1>
            <p class="mt-1 text-sm text-gray-500">Sinyal risiko resign/keluar dengan faktor transparan dan rekomendasi aksi HR.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('performance.engagement') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-chart-simple"></i> Engagement
            </a>
            <a href="{{ route('analytics.people-dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-chart-pie"></i> People Analytics
            </a>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <form method="GET" action="{{ route('analytics.attrition-risk') }}" class="grid gap-3 xl:grid-cols-[minmax(180px,1fr)_minmax(180px,1fr)_150px_150px_150px_auto] xl:items-end">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Company</label>
                <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected((int) $filters['company_id'] === $company->id)>{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua site</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) $filters['branch_id'] === $branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Risk Level</label>
                <select name="risk_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach(['high', 'medium', 'low'] as $level)
                        <option value="{{ $level }}" @selected($filters['risk_level'] === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Karyawan Terbaca', 'value' => $summary['employees'], 'hint' => 'Scope aktif', 'color' => 'text-blue-600'],
                ['label' => 'High Risk', 'value' => $summary['high'], 'hint' => 'Prioritas 1:1', 'color' => $summary['high'] > 0 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Medium Risk', 'value' => $summary['medium'], 'hint' => 'Monitor 30 hari', 'color' => 'text-amber-600'],
                ['label' => 'Low Risk', 'value' => $summary['low'], 'hint' => 'Engagement rutin', 'color' => 'text-green-600'],
                ['label' => 'Avg Score', 'value' => $summary['avg_score'], 'hint' => '0 rendah - 100 tinggi', 'color' => $summary['avg_score'] >= 60 ? 'text-red-600' : 'text-blue-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,.85fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Faktor Dominan</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara membaca risiko attrition',
                        'description' => 'Skor risiko dihitung dari faktor yang terlihat agar HR bisa mengecek bukti dan mengambil aksi, bukan membuat keputusan otomatis.',
                        'steps' => [
                            'Mulai dari High Risk, lalu baca faktor dominan dan evidence per karyawan.',
                            'Gunakan rekomendasi sebagai arahan 1:1, bukan vonis resign.',
                            'Cocokkan dengan data real: absensi, engagement, performa, workload, kontrak, dan coaching.',
                            'Update action plan lewat HRBP/atasan setelah diskusi manusia.',
                        ],
                        'notes' => [
                            'Skor ini bersifat sinyal awal dan harus diverifikasi.',
                            'Jaga kerahasiaan data karena informasi ini sensitif.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Faktor yang paling sering menyumbang skor risiko pada scope terpilih.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($topFactors as $factor)
                    @php $width = $topFactors->max('score') > 0 ? max(6, (int) round(($factor['score'] / $topFactors->max('score')) * 100)) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">{{ $factor['label'] }}</span>
                            <span class="text-gray-500">{{ $factor['count'] }} karyawan</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-8 text-center text-sm text-gray-500">Belum ada faktor risiko dominan.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Rencana Aksi</h2>
                <p class="text-sm text-gray-500">Bucket tindakan yang disarankan dari level risiko.</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach([
                    ['label' => '1:1 prioritas HRBP + atasan', 'value' => $recommendationBuckets['one_on_one'], 'color' => 'bg-red-50 text-red-700'],
                    ['label' => 'Monitor ulang dalam 30 hari', 'value' => $recommendationBuckets['monitor_30_days'], 'color' => 'bg-amber-50 text-amber-700'],
                    ['label' => 'Engagement rutin', 'value' => $recommendationBuckets['routine_engagement'], 'color' => 'bg-green-50 text-green-700'],
                ] as $bucket)
                    <div class="flex items-center justify-between rounded-xl {{ $bucket['color'] }} px-4 py-3">
                        <div class="text-sm font-semibold">{{ $bucket['label'] }}</div>
                        <div class="text-xl font-semibold">{{ $bucket['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Risiko Karyawan</h2>
            <p class="text-sm text-gray-500">Transparansi faktor, evidence, dan rekomendasi tindakan per karyawan.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($profiles as $profile)
                @php
                    $levelClass = [
                        'high' => 'bg-red-50 text-red-700',
                        'medium' => 'bg-amber-50 text-amber-700',
                        'low' => 'bg-green-50 text-green-700',
                    ][$profile['risk_level']];
                @endphp
                <article class="p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-gray-900">{{ $profile['employee']->full_name }}</h3>
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $levelClass }}">{{ ucfirst($profile['risk_level']) }}</span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $profile['employee']->employee_number }} · {{ $profile['employee']->department ?: '-' }} · {{ $profile['employee']->branch?->code ?: '-' }}
                            </div>
                        </div>
                        <div class="w-full lg:w-64">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Risk score</span>
                                <span class="font-semibold text-gray-900">{{ $profile['score'] }}/100</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full {{ $profile['risk_level'] === 'high' ? 'bg-red-600' : ($profile['risk_level'] === 'medium' ? 'bg-amber-500' : 'bg-green-600') }}" style="width: {{ $profile['score'] }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 xl:grid-cols-3">
                        @forelse($profile['dominant_factors']->take(3) as $factor)
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $factor['label'] }}</div>
                                    <span class="rounded-full bg-white px-2 py-1 text-xs font-semibold text-blue-600">+{{ $factor['score'] }}</span>
                                </div>
                                <p class="mt-2 text-xs leading-5 text-gray-500">{{ $factor['evidence'] }}</p>
                                <p class="mt-2 text-xs font-medium leading-5 text-gray-700">{{ $factor['action'] }}</p>
                            </div>
                        @empty
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500 xl:col-span-3">Tidak ada faktor risiko dominan pada periode ini.</div>
                        @endforelse
                    </div>

                    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        <span class="font-semibold">Rekomendasi:</span> {{ $profile['recommendation'] }}
                    </div>
                </article>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada karyawan pada filter ini.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
