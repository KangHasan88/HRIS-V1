@extends('layouts.sidebar')

@section('title', 'Engagement Survey')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Engagement Survey</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola pulse survey, eNPS, sentiment karyawan, dan action plan perbaikan engagement.</p>
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
                ['label' => 'Survey', 'value' => $summary['surveys'], 'color' => 'text-blue-600', 'hint' => 'Total survey'],
                ['label' => 'Response', 'value' => $summary['responses'], 'color' => 'text-blue-600', 'hint' => 'Jawaban masuk'],
                ['label' => 'Avg Engagement', 'value' => $summary['avg_engagement'], 'color' => 'text-green-600', 'hint' => 'Skor 0-100'],
                ['label' => 'Avg eNPS', 'value' => $summary['avg_enps'], 'color' => 'text-amber-600', 'hint' => 'Skor 0-10'],
                ['label' => 'Open Action', 'value' => $summary['open_actions'], 'color' => 'text-red-600', 'hint' => 'Belum selesai'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Survey</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Engagement Survey',
                        'description' => 'Form ini dipakai HR untuk membuat pulse survey, engagement survey, eNPS, atau culture check.',
                        'steps' => [
                            'Tentukan scope audience, periode, dan apakah survey anonim.',
                            'Isi question bank singkat dipisahkan koma sebagai template pertanyaan awal.',
                            'Set status Published/Running saat siap dikirim ke karyawan.',
                            'Setelah response masuk, buat action plan untuk area yang perlu diperbaiki.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Survey bisa dipakai untuk semua karyawan, company, cabang/site, departemen, atau team manager.</p>
            </div>

            <form action="{{ route('performance.engagement.surveys') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Survey</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Survey</label>
                            <input name="survey_number" required placeholder="Contoh: ENG-2026-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Judul Survey</label>
                            <input name="title" required placeholder="Pulse Check Q3 2026" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Survey</label>
                            <select name="survey_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="pulse">Pulse</option>
                                <option value="engagement">Engagement</option>
                                <option value="enps">eNPS</option>
                                <option value="culture">Culture</option>
                                <option value="exit_signal">Exit signal</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Audience</label>
                            <select name="audience_scope" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="all_employee">Semua karyawan</option>
                                <option value="company">Company</option>
                                <option value="branch">Cabang / site</option>
                                <option value="department">Departemen</option>
                                <option value="manager_team">Team manager</option>
                            </select>
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
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Pertanyaan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                            <input name="owner_name" placeholder="HRBP / People team" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="running">Running</option>
                                <option value="closed">Closed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Question Bank</label>
                            <textarea name="question_bank" rows="2" placeholder="Pisahkan koma, contoh: Saya punya tools kerja yang cukup, Atasan memberi arahan jelas, Saya merekomendasikan perusahaan ini" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <label class="md:col-span-2 flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_anonymous" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Survey anonim
                        </label>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Survey</label>
                            <textarea name="notes" rows="2" placeholder="Catatan komunikasi survey, target response rate, atau konteks bisnis." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-chart-simple"></i> Simpan Survey
                    </button>
                </div>
            </form>
        </section>

        <div class="space-y-6">
            <section class="panel rounded-2xl p-5">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Input Response</h2>
                    <p class="text-sm text-gray-500">Simulasi response atau import ringkas hasil survey.</p>
                </div>
                <form action="{{ route('performance.engagement.responses') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Survey</label>
                            <select name="engagement_survey_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih survey</option>
                                @foreach($surveys as $survey)
                                    <option value="{{ $survey->id }}">{{ $survey->survey_number }} - {{ $survey->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Anonim / tidak link employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Response</label>
                            <input name="response_number" required placeholder="RESP-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Engagement Score</label>
                            <input type="number" name="engagement_score" value="75" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">eNPS Score</label>
                            <input type="number" name="enps_score" value="8" min="0" max="10" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sentiment</label>
                            <select name="sentiment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="positive">Positive</option>
                                <option value="neutral">Neutral</option>
                                <option value="mixed">Mixed</option>
                                <option value="negative">Negative</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Submitted</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="excluded">Excluded</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dimension Scores</label>
                            <input name="dimension_scores" placeholder="Contoh: leadership:80, workload:65, culture:75" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Komentar</label>
                            <textarea name="comment" rows="2" placeholder="Komentar karyawan atau hasil sentiment analysis." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <i class="fas fa-comment-dots"></i> Simpan Response
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Action Planning</h2>
            <p class="text-sm text-gray-500">Tindak lanjut dari hasil survey agar engagement tidak berhenti di dashboard.</p>
        </div>
        <form action="{{ route('performance.engagement.actions') }}" method="POST" class="mt-5">
            @csrf
            <div class="grid gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Survey</label>
                    <select name="engagement_survey_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih survey</option>
                        @foreach($surveys as $survey)
                            <option value="{{ $survey->id }}">{{ $survey->survey_number }} - {{ $survey->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Action</label>
                    <input name="action_number" required placeholder="ACT-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Focus Area</label>
                    <input name="focus_area" required placeholder="Workload / leadership / career" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Root Cause</label>
                    <input name="root_cause" placeholder="Penyebab utama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Action Plan</label>
                    <input name="action_plan" placeholder="Rencana aksi konkret dan cadence follow-up" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                    <input name="owner_name" placeholder="PIC action" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Date</label>
                    <input type="date" name="target_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Progress</label>
                    <input type="number" name="progress_percent" value="0" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Impact Metric</label>
                    <input name="impact_metric" placeholder="Target metric" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Priority</label>
                    <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="open">Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="paused">Paused</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan blocker, komunikasi, atau keputusan people committee." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-list-check"></i> Simpan Action
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Monitoring Action Plan</h2>
            <p class="text-sm text-gray-500">Pantau fokus perbaikan, progress, owner, dan target dampak.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Focus</th>
                        <th class="px-5 py-3">Survey</th>
                        <th class="px-5 py-3">Owner</th>
                        <th class="px-5 py-3">Progress</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($actions as $action)
                        @php
                            $statusClass = $action->status === 'completed' ? 'bg-green-50 text-green-700' : (in_array($action->priority, ['high', 'critical'], true) ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700');
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $action->focus_area }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $action->action_number }}</div>
                                <div class="text-xs text-gray-500">{{ $action->root_cause ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $action->survey?->title }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $action->survey?->survey_number }}</div>
                                <div class="text-xs text-gray-500">{{ $action->impact_metric ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $action->owner_name ?: '-' }}</div>
                                <div class="text-xs">Target: {{ $action->target_date?->format('d M Y') ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $action->progress_percent }}%</span>
                                    <span>{{ ucfirst($action->priority) }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $action->progress_percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $action->status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $action->notes ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada action plan engagement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
