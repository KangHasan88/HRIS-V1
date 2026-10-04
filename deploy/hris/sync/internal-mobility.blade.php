@extends('layouts.sidebar')

@section('title', 'Career Marketplace')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Career Marketplace</h1>
            <p class="mt-1 text-sm text-gray-500">Publikasikan peluang role internal, rotasi, promosi, project assignment, dan screening kandidat mobilitas.</p>
        </div>
        <a href="{{ route('performance.succession') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-sitemap"></i> Talent Pool
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
                ['label' => 'Opportunity', 'value' => $summary['opportunities'], 'color' => 'text-blue-600', 'hint' => 'Role dibuka'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-green-600', 'hint' => 'Tampil untuk employee'],
                ['label' => 'Application', 'value' => $summary['applications'], 'color' => 'text-blue-600', 'hint' => 'Kandidat masuk'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600', 'hint' => 'Siap movement'],
                ['label' => 'Avg Match', 'value' => $summary['avg_match'], 'color' => 'text-amber-600', 'hint' => 'Skor match'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Buka Opportunity Internal</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Career Marketplace',
                        'description' => 'Form ini dipakai HR untuk membuka peluang promosi, rotasi, lateral move, atau project assignment bagi employee internal.',
                        'steps' => [
                            'Pilih posisi tujuan dan scope company/site.',
                            'Tentukan jenis movement dan readiness requirement.',
                            'Isi eligibility, manfaat development, slot, periode buka, owner, dan status published jika siap tampil.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Opportunity menjadi katalog role internal yang bisa dilamar atau dinominasikan.</p>
            </div>
            <form action="{{ route('performance.internal-mobility.opportunities') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Opportunity</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi Tujuan</label>
                            <select name="job_position_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih posisi</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->code }} - {{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Opportunity</label>
                            <input name="opportunity_number" required placeholder="Contoh: MOB-2026-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Judul Opportunity</label>
                            <input name="title" required placeholder="Contoh: Supervisor Operasional Site - Internal Mobility" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Company</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Career Track</label>
                            <input name="career_track" value="Operations Leadership" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Movement</label>
                            <select name="movement_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="promotion">Promotion</option>
                                <option value="lateral_move">Lateral move</option>
                                <option value="rotation">Rotation</option>
                                <option value="cross_function">Cross function</option>
                                <option value="project_assignment">Project assignment</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Kriteria</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Readiness Requirement</label>
                            <select name="readiness_requirement" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="ready_12_months">Ready 12 bulan</option>
                                <option value="ready_now">Ready now</option>
                                <option value="ready_6_months">Ready 6 bulan</option>
                                <option value="ready_24_months">Ready 24 bulan</option>
                                <option value="needs_development">Needs development</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Slot</label>
                            <input type="number" name="open_slots" value="1" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="screening">Screening</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Buka</label>
                            <input type="date" name="open_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Tutup</label>
                            <input type="date" name="close_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                            <input name="owner_name" placeholder="HRBP / Talent owner" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Eligibility</label>
                            <textarea name="eligibility_note" rows="2" placeholder="Kriteria kandidat, minimal masa kerja, rating performance, readiness, lokasi, atau sertifikasi." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Manfaat Development</label>
                            <textarea name="development_benefit" rows="2" placeholder="Manfaat career path, exposure, learning, atau scope pekerjaan baru." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-store"></i> Simpan Opportunity
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Screening Mobilitas</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Screening Mobilitas',
                        'description' => 'Form ini mencatat kandidat yang apply sendiri, dinominasikan manager, atau ditarik dari talent pool/succession.',
                        'steps' => [
                            'Pilih opportunity dan karyawan kandidat.',
                            'Link succession candidate atau IDP jika sudah ada data readiness.',
                            'Isi match score, readiness, manager support, constraint, dan status screening.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Screening ini membantu HR membaca match role sebelum promosi, rotasi, atau assignment.</p>
            </div>
            <form action="{{ route('performance.internal-mobility.applications') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kandidat & Match</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Opportunity</label>
                            <select name="internal_mobility_opportunity_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih opportunity</option>
                                @foreach($opportunities as $opportunity)
                                    <option value="{{ $opportunity->id }}">{{ $opportunity->opportunity_number }} - {{ $opportunity->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Aplikasi</label>
                            <input name="application_number" required placeholder="Contoh: MOB-APP-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Source</label>
                            <select name="source" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="employee_self_nomination">Employee self nomination</option>
                                <option value="manager_nomination">Manager nomination</option>
                                <option value="hr_nomination">HR nomination</option>
                                <option value="succession_pool">Succession pool</option>
                                <option value="idp_recommendation">IDP recommendation</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link Talent Pool</label>
                            <select name="succession_candidate_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, link succession candidate</option>
                                @foreach($successionCandidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->employeeProfile?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link IDP</label>
                            <select name="individual_development_plan_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, link IDP</option>
                                @foreach($idps as $idp)
                                    <option value="{{ $idp->id }}">{{ $idp->plan_number }} - {{ $idp->employeeProfile?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Decision Signal</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Match Score</label>
                            <input type="number" name="match_score" value="70" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Readiness</label>
                            <select name="readiness_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="ready_12_months">Ready 12 bulan</option>
                                <option value="ready_now">Ready now</option>
                                <option value="ready_6_months">Ready 6 bulan</option>
                                <option value="ready_24_months">Ready 24 bulan</option>
                                <option value="needs_development">Needs development</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Manager Support</label>
                            <select name="manager_support" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="pending">Pending</option>
                                <option value="supported">Supported</option>
                                <option value="conditional">Conditional</option>
                                <option value="not_supported">Not supported</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Submitted</option>
                                <option value="screening">Screening</option>
                                <option value="shortlisted">Shortlisted</option>
                                <option value="approved">Approved</option>
                                <option value="offer">Offer</option>
                                <option value="moved">Moved</option>
                                <option value="rejected">Rejected</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Movement</label>
                            <input type="date" name="target_movement_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                            <input name="owner_name" placeholder="HRBP / recruiter internal" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Constraint Mobilitas</label>
                            <input name="mobility_constraint" placeholder="Contoh: hanya site tertentu, notice period, keluarga, atau jadwal shift." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Motivasi Kandidat</label>
                            <textarea name="motivation_note" rows="2" placeholder="Alasan kandidat tertarik, aspirasi career path, dan readiness personal." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Screening</label>
                            <textarea name="screening_note" rows="2" placeholder="Catatan HR/manager, gap yang perlu ditutup, atau keputusan committee." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-user-check"></i> Simpan Screening
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Pipeline Mobilitas Internal</h2>
            <p class="text-sm text-gray-500">Pantau opportunity, aplikasi kandidat, match score, dan status movement.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Opportunity</th>
                        <th class="px-5 py-3">Kandidat</th>
                        <th class="px-5 py-3">Match</th>
                        <th class="px-5 py-3">Support</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $application)
                        @php
                            $scoreClass = $application->match_score >= 80 ? 'text-green-600' : ($application->match_score >= 60 ? 'text-amber-600' : 'text-red-600');
                            $statusClass = in_array($application->status, ['approved', 'offer', 'moved'], true) ? 'bg-green-50 text-green-700' : (in_array($application->status, ['rejected', 'withdrawn'], true) ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700');
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $application->opportunity?->title }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $application->opportunity?->opportunity_number }}</div>
                                <div class="text-xs text-gray-500">{{ $application->opportunity?->jobPosition?->name }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $application->employeeProfile?->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $application->application_number }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $application->source)) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span class="font-semibold {{ $scoreClass }}">{{ $application->match_score }}</span>
                                    <span>{{ ucfirst(str_replace('_', ' ', $application->readiness_level)) }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $application->match_score }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ ucfirst(str_replace('_', ' ', $application->manager_support)) }}</div>
                                <div class="text-xs">Target: {{ $application->target_movement_date?->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs">Owner: {{ $application->owner_name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $application->screening_note ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada pipeline mobilitas internal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
