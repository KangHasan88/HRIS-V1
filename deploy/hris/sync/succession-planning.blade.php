@extends('layouts.sidebar')

@section('title', 'Succession Planning')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Succession Planning</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola posisi kritikal, talent pool, readiness matrix, risiko vacancy, dan rencana pengganti internal.</p>
        </div>
        <a href="{{ route('performance.idp') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-route"></i> Review IDP
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Plan Posisi', 'value' => $summary['plans'], 'color' => 'text-blue-600', 'hint' => 'Role kritikal'],
                ['label' => 'Critical Role', 'value' => $summary['critical'], 'color' => 'text-red-600', 'hint' => 'High / critical'],
                ['label' => 'Ready Now', 'value' => $summary['ready_now'], 'color' => 'text-green-600', 'hint' => 'Siap isi posisi'],
                ['label' => 'Ready 12 Bulan', 'value' => $summary['ready_12'], 'color' => 'text-amber-600', 'hint' => 'Butuh IDP'],
                ['label' => 'Avg Readiness', 'value' => $summary['avg_score'], 'color' => 'text-blue-600', 'hint' => 'Skor kandidat'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Succession Plan</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Succession Plan',
                        'description' => 'Form ini dipakai untuk menandai posisi kritikal yang perlu kandidat pengganti dan readiness plan.',
                        'steps' => [
                            'Pilih posisi kritikal dan incumbent jika posisi sudah ditempati.',
                            'Tentukan criticality dan risiko vacancy untuk membaca prioritas bisnis.',
                            'Isi target ready-by, impact bisnis, status, dan catatan agar HR bisa monitor cadence review.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu plan mewakili satu posisi yang butuh pipeline pengganti.</p>
            </div>
            <form action="{{ route('performance.succession.plans') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Posisi & Risiko</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi Kritikal</label>
                            <select name="job_position_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih posisi</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->code }} - {{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Incumbent</label>
                            <select name="incumbent_employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, pemegang posisi saat ini</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Plan</label>
                            <input name="plan_number" required placeholder="Contoh: SUCC-2026-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Ready By</label>
                            <input type="date" name="target_ready_by" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Criticality</label>
                            <select name="criticality" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Risiko Vacancy</label>
                            <select name="vacancy_risk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="monitoring">Monitoring</option>
                                <option value="paused">Paused</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Impact Bisnis</label>
                            <input name="business_impact" placeholder="Contoh: Operasional site terganggu jika kosong" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Plan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan risiko, trigger replacement, atau arahan komite talent." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-sitemap"></i> Simpan Plan
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Kandidat Talent Pool</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Talent Pool',
                        'description' => 'Form ini mencatat kandidat internal untuk posisi succession beserta readiness, performance/potential, mobilitas, dan next action.',
                        'steps' => [
                            'Pilih succession plan dan karyawan kandidat.',
                            'Link IDP jika kandidat masih butuh pengembangan terstruktur.',
                            'Isi readiness score, readiness level, performance, potential, dan retention risk.',
                            'Tulis development gap dan next action supaya follow-up tidak berhenti di nominasi.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Kandidat bisa ready now atau masih perlu IDP sebelum promosi/mutasi.</p>
            </div>
            <form action="{{ route('performance.succession.candidates') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kandidat & Readiness</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Succession Plan</label>
                            <select name="succession_plan_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->plan_number }} - {{ $plan->jobPosition?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Kandidat</label>
                            <input name="candidate_number" required placeholder="Contoh: TAL-2026-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link IDP</label>
                            <select name="individual_development_plan_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, jika ada IDP aktif</option>
                                @foreach($idps as $idp)
                                    <option value="{{ $idp->id }}">{{ $idp->plan_number }} - {{ $idp->employeeProfile?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Readiness Level</label>
                            <select name="readiness_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="ready_12_months">Ready 12 bulan</option>
                                <option value="ready_now">Ready now</option>
                                <option value="ready_6_months">Ready 6 bulan</option>
                                <option value="ready_24_months">Ready 24 bulan</option>
                                <option value="needs_development">Needs development</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Readiness Score</label>
                            <input type="number" name="readiness_score" value="70" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Performance</label>
                            <select name="performance_rating" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="meets">Meets</option>
                                <option value="exceeds">Exceeds</option>
                                <option value="outstanding">Outstanding</option>
                                <option value="below">Below</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Potential</label>
                            <select name="potential_rating" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Follow-up Talent</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mobilitas</label>
                            <select name="mobility_preference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="same_site">Same site</option>
                                <option value="cross_site">Cross site</option>
                                <option value="cross_company">Cross company</option>
                                <option value="remote">Remote</option>
                                <option value="not_mobile">Tidak mobile</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Retention Risk</label>
                            <select name="retention_risk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                            <input name="owner_name" placeholder="HRBP / atasan / mentor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Review</label>
                            <input type="date" name="review_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Kandidat</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="nominated">Nominated</option>
                                <option value="validated">Validated</option>
                                <option value="in_development">In development</option>
                                <option value="ready">Ready</option>
                                <option value="moved">Moved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Development Gap</label>
                            <input name="development_gap" placeholder="Contoh: leadership, exposure budgeting" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Next Action</label>
                            <textarea name="next_action" rows="2" placeholder="Contoh: Masukkan ke IDP, mentoring 60 hari, shadowing incumbent, review komite talent." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-user-check"></i> Simpan Kandidat
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Readiness Matrix</h2>
            <p class="text-sm text-gray-500">Pantau kandidat pengganti, readiness, performance/potential, dan next action.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Posisi</th>
                        <th class="px-5 py-3">Kandidat</th>
                        <th class="px-5 py-3">Readiness</th>
                        <th class="px-5 py-3">9-Box Signal</th>
                        <th class="px-5 py-3">Risk & Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($candidates as $candidate)
                        @php
                            $readyClass = in_array($candidate->readiness_level, ['ready_now', 'ready_6_months'], true) ? 'text-green-600' : ($candidate->readiness_level === 'needs_development' ? 'text-red-600' : 'text-amber-600');
                            $riskClass = in_array($candidate->retention_risk, ['high', 'critical'], true) ? 'text-red-600' : ($candidate->retention_risk === 'medium' ? 'text-amber-600' : 'text-green-600');
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $candidate->successionPlan?->jobPosition?->name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $candidate->successionPlan?->plan_number }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $candidate->employeeProfile?->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $candidate->candidate_number }}</div>
                                <div class="text-xs text-gray-500">{{ $candidate->employeeProfile?->position ?: '-' }} / {{ $candidate->employeeProfile?->grade ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="font-semibold {{ $readyClass }}">{{ ucfirst(str_replace('_', ' ', $candidate->readiness_level)) }}</span>
                                    <span>{{ $candidate->readiness_score }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $candidate->readiness_score }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>Performance: <span class="font-semibold text-gray-900">{{ ucfirst($candidate->performance_rating) }}</span></div>
                                <div class="text-xs">Potential: <span class="font-semibold text-gray-900">{{ ucfirst($candidate->potential_rating) }}</span></div>
                                <div class="text-xs">Mobilitas: {{ ucfirst(str_replace('_', ' ', $candidate->mobility_preference)) }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>Retention: <span class="font-semibold {{ $riskClass }}">{{ ucfirst($candidate->retention_risk) }}</span></div>
                                <div class="text-xs">Owner: {{ $candidate->owner_name ?: '-' }}</div>
                                <div class="text-xs">Next: {{ $candidate->next_action ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada kandidat succession.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
