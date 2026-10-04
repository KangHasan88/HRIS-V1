@extends('layouts.sidebar')

@section('title', 'Goal & OKR')

@php
    $statusTone = fn ($status) => match ($status) {
        'active', 'on_track', 'reviewed', 'completed' => 'bg-green-50 text-green-700',
        'at_risk', 'returned', 'draft' => 'bg-amber-50 text-amber-700',
        'blocked', 'cancelled' => 'bg-red-50 text-red-700',
        default => 'bg-blue-50 text-blue-700',
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Goal & OKR</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola siklus OKR, objective, key result, cascading target, check-in progress, dan scoring.</p>
        </div>
        <a href="{{ route('performance.review-360') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-people-arrows mr-2"></i> 360 Feedback
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Siklus Aktif', 'value' => $summary['active_cycles'].'/'.$summary['cycles'], 'color' => 'text-blue-600', 'sub' => 'Cycle'],
                ['label' => 'Total Goal', 'value' => $summary['goals'], 'color' => 'text-gray-900', 'sub' => 'Objective'],
                ['label' => 'On Track', 'value' => $summary['on_track'], 'color' => 'text-green-600', 'sub' => 'Aman'],
                ['label' => 'At Risk', 'value' => $summary['at_risk'], 'color' => $summary['at_risk'] > 0 ? 'text-red-600' : 'text-green-600', 'sub' => 'Butuh review'],
                ['label' => 'Avg Progress', 'value' => $summary['avg_progress'].'%', 'color' => 'text-blue-600', 'sub' => 'Progress'],
                ['label' => 'Avg Score', 'value' => $summary['avg_score'], 'color' => 'text-green-600', 'sub' => $summary['checkins'].' check-in'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="px-5 pt-5">
                <h2 class="text-lg font-semibold text-gray-900">Setup Goal/OKR</h2>
                <p class="text-sm text-gray-500">Buat cycle, turunkan target, lalu update progress berkala lewat check-in.</p>
            </div>
            <div class="group relative mr-5 mt-5">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Siklus OKR dipakai untuk menentukan periode target. Goal bisa level company, department, team, atau individual. Parent goal dipakai untuk cascading agar target individu/tim terhubung ke objective yang lebih besar. Check-in mencatat nilai terbaru, confidence, risiko, blocker, dan action berikutnya.
                </div>
            </div>
        </div>

        <div class="grid gap-6 p-5 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-5">
                <form method="POST" action="{{ route('performance.goal-okr.cycles') }}" class="rounded-xl border border-gray-200 bg-white p-5">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">1. Siklus OKR</h3>
                            <p class="text-sm text-gray-500">Periode kerja, scope organisasi, dan metode scoring.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Cycle</span>
                    </div>
                    <div class="grid gap-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Kode Siklus</span><input name="cycle_code" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: OKR-Q3-2026" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nama Siklus</span><input name="cycle_name" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="OKR Quarter 3 2026" required></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Company</span><select name="company_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua company</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>@endforeach</select></label>
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Cabang / Site</span><select name="branch_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua cabang/site</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>@endforeach</select></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Tipe Periode</span><select name="period_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="quarter">Quarter</option><option value="month">Bulanan</option><option value="semester">Semester</option><option value="year">Tahunan</option><option value="project">Project</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="draft">Draft</option><option value="active">Active</option><option value="closed">Closed</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Mulai</span><input type="date" name="period_start" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Selesai</span><input type="date" name="period_end" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Deadline Check-in</span><input type="date" name="checkin_deadline" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Metode Scoring</span><select name="scoring_method" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="weighted_progress">Weighted progress</option><option value="manual_score">Manual score</option><option value="hybrid">Hybrid</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Aturan Kalibrasi</span><input name="calibration_rules" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: progress factual, bukti delivery, manager review"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Deskripsi Siklus</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan scope, aturan review, atau pengecualian."></textarea></label>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Simpan Siklus</button></div>
                    </div>
                </form>

                <form method="POST" action="{{ route('performance.goal-okr.goals') }}" class="rounded-xl border border-gray-200 bg-white p-5">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">2. Goal / Key Result</h3>
                            <p class="text-sm text-gray-500">Objective yang bisa di-cascade ke company, tim, atau individu.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Objective</span>
                    </div>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Siklus OKR</span><select name="performance_goal_cycle_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih siklus OKR</option>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}">{{ $cycle->cycle_code }} - {{ $cycle->cycle_name }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Nomor Goal</span><input name="goal_number" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: OKR-GOAL-001" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Tipe Goal</span><select name="goal_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="company">Company</option><option value="department">Department</option><option value="team">Team</option><option value="individual">Individual</option><option value="project">Project</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="draft">Draft</option><option value="active">Active</option><option value="on_track">On track</option><option value="at_risk">At risk</option><option value="blocked">Blocked</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Parent Goal / Cascading</span><select name="parent_goal_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Tanpa parent goal</option>@foreach($goals as $goal)<option value="{{ $goal->id }}">{{ $goal->goal_number }} - {{ $goal->objective_title }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Owner Employee</span><select name="employee_profile_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Scope non-individual</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>@endforeach</select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Reviewer / Atasan</span><select name="manager_user_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Reviewer/atasan opsional</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Objective</span><input name="objective_title" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Tingkatkan akurasi payroll" required></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Key Result Metric</span><input name="key_result_metric" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Akurasi payroll minimal 99%" required></label>
                        <div class="grid gap-4 md:grid-cols-5">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Baseline</span><input type="number" step="0.01" name="baseline_value" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="0"></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Target</span><input type="number" step="0.01" name="target_value" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="100" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Current</span><input type="number" step="0.01" name="current_value" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="0"></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Bobot %</span><input type="number" step="0.01" name="weight" value="100" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Confidence</span><select name="confidence_level" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Relasi Cascading</span><input name="cascading_snapshot" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Company OKR, HR OKR, Payroll OKR"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Catatan Objective</span><textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan objective, asumsi target, atau batasan scope."></textarea></label>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Simpan Goal</button></div>
                    </div>
                </form>

                <form method="POST" action="{{ route('performance.goal-okr.checkins') }}" class="rounded-xl border border-gray-200 bg-white p-5">
                    @csrf
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">3. Check-in Progress</h3>
                            <p class="text-sm text-gray-500">Update nilai terbaru, risiko, blocker, dan next action.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Progress</span>
                    </div>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Goal</span><select name="performance_goal_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih goal</option>@foreach($goals as $goal)<option value="{{ $goal->id }}">{{ $goal->goal_number }} - {{ $goal->objective_title }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Nomor Check-in</span><input name="checkin_number" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: OKR-CI-001" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Tanggal</span><input type="date" name="checkin_date" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nilai Terbaru</span><input type="number" step="0.01" name="new_value" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="96" required></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Employee</span><select name="employee_profile_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Ikuti employee di goal</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>@endforeach</select></label>
                            <label class="block md:col-span-2"><span class="text-xs font-semibold uppercase text-gray-500">Reviewer</span><select name="reviewer_user_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Reviewer opsional</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Confidence</span><select name="confidence_level" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Risiko</span><select name="risk_status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="on_track">On track</option><option value="at_risk">At risk</option><option value="blocked">Blocked</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status Review</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="submitted">Submitted</option><option value="reviewed">Reviewed</option><option value="draft">Draft</option><option value="returned">Returned</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Achievement</span><textarea name="achievement_notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Progress/achievement yang sudah tercapai."></textarea></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Blocker</span><textarea name="blocker_notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Hambatan jika ada."></textarea></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Next Action</span><textarea name="next_action" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Tindak lanjut dan owner."></textarea></label>
                        </div>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Simpan Check-in</button></div>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <h3 class="text-sm font-semibold text-blue-900">Urutan Pakai</h3>
                    <div class="mt-3 space-y-3 text-sm text-blue-900/80">
                        <div class="flex gap-3"><span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700">1</span><span>Buat siklus OKR untuk periode kerja.</span></div>
                        <div class="flex gap-3"><span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700">2</span><span>Buat goal dan sambungkan ke parent goal bila perlu cascading.</span></div>
                        <div class="flex gap-3"><span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700">3</span><span>Update progress berkala lewat check-in.</span></div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Quick Insight</h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">Goal on track</span><span class="font-semibold text-green-600">{{ $summary['on_track'] }}</span></div>
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">Goal at risk</span><span class="font-semibold text-red-600">{{ $summary['at_risk'] }}</span></div>
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">Rata-rata progress</span><span class="font-semibold text-blue-600">{{ $summary['avg_progress'] }}%</span></div>
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">Check-in tercatat</span><span class="font-semibold text-gray-900">{{ $summary['checkins'] }}</span></div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Catatan UX</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-500">Field angka memakai baseline, target, dan current agar progress bisa dihitung otomatis. Jika target turun atau goal bersifat kualitatif, gunakan metode scoring manual di siklus.</p>
                </div>
            </aside>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Goal</h2>
                <p class="text-sm text-gray-500">Objective, owner, cascading, progress, dan skor.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr><th class="px-5 py-3 text-left">Goal</th><th class="px-5 py-3 text-left">Owner</th><th class="px-5 py-3 text-left">Target</th><th class="px-5 py-3 text-left">Progress</th><th class="px-5 py-3 text-left">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($goals as $goal)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $goal->goal_number }}</div>
                                    <div class="mt-1 max-w-md text-gray-600">{{ $goal->objective_title }}</div>
                                    <div class="mt-1 text-xs text-gray-400">Parent: {{ $goal->parentGoal?->goal_number ?? '-' }} · {{ $goal->child_goals_count }} child</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $goal->employeeProfile?->full_name ?? ucfirst($goal->goal_type) }}<div class="text-xs text-gray-400">{{ $goal->manager?->name ?? '-' }}</div></td>
                                <td class="px-5 py-4 text-gray-600">{{ $goal->key_result_metric }}<div class="text-xs text-gray-400">{{ $goal->current_value }} / {{ $goal->target_value }} · Bobot {{ $goal->weight }}%</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-blue-600">{{ $goal->progress_percent }}%</div><div class="mt-1 h-2 w-36 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-blue-600" style="width: {{ min(100, (float) $goal->progress_percent) }}%"></div></div><div class="mt-1 text-xs text-gray-400">Skor {{ $goal->score }}</div></td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusTone($goal->status) }}">{{ ucfirst(str_replace('_', ' ', $goal->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada goal/OKR.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Check-in Terbaru</h2>
                <p class="text-sm text-gray-500">Update progress dan risiko terakhir.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($checkIns as $checkIn)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $checkIn->checkin_number }}</div>
                                <div class="text-sm text-gray-500">{{ $checkIn->goal?->goal_number }} · {{ $checkIn->employeeProfile?->full_name ?? $checkIn->goal?->employeeProfile?->full_name ?? '-' }}</div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusTone($checkIn->risk_status) }}">{{ ucfirst(str_replace('_', ' ', $checkIn->risk_status)) }}</span>
                        </div>
                        <div class="mt-3 flex items-end justify-between">
                            <div class="text-xs text-gray-500">{{ $checkIn->checkin_date?->format('d M Y') }} · {{ $checkIn->confidence_level }} confidence</div>
                            <div class="text-xl font-semibold text-blue-600">{{ $checkIn->progress_percent }}%</div>
                        </div>
                        @if($checkIn->next_action)
                            <div class="mt-2 text-xs leading-5 text-gray-500">{{ $checkIn->next_action }}</div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada check-in.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
