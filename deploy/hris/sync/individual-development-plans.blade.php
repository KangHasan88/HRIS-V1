@extends('layouts.sidebar')

@section('title', 'Individual Development Plan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Individual Development Plan</h1>
            <p class="mt-1 text-sm text-gray-500">Rancang gap kompetensi, target karier, rekomendasi learning, mentor, dan progress pengembangan karyawan.</p>
        </div>
        <a href="{{ route('performance.training-learning') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-graduation-cap"></i> Learning & Training
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
                ['label' => 'Total IDP', 'value' => $summary['plans'], 'color' => 'text-blue-600', 'hint' => 'Plan dibuat'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-blue-600', 'hint' => 'Approved / progress'],
                ['label' => 'Selesai', 'value' => $summary['completed'], 'color' => 'text-green-600', 'hint' => 'Completed'],
                ['label' => 'Prioritas Tinggi', 'value' => $summary['high_priority'], 'color' => 'text-red-600', 'hint' => 'High / critical'],
                ['label' => 'Avg Progress', 'value' => $summary['avg_progress'].'%', 'color' => 'text-amber-600', 'hint' => 'Rata-rata'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat IDP Karyawan</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Individual Development Plan',
                        'description' => 'Form ini dipakai HR/atasan untuk membuat rencana pengembangan individual berdasarkan gap kompetensi, target role, dan rekomendasi learning.',
                        'steps' => [
                            'Pilih karyawan, lalu pilih competency requirement jika IDP berasal dari matriks kompetensi.',
                            'Isi area pengembangan, target karier, level saat ini, dan target level.',
                            'Pilih rekomendasi training atau isi rekomendasi learning manual.',
                            'Tetapkan mentor, reviewer, target date, status, dan progress agar bisa dipantau berkala.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">IDP bisa dibuat dari hasil review, gap kompetensi, aspirasi karier, atau kebutuhan operasional.</p>
            </div>

            <form action="{{ route('performance.idp.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Karyawan & Gap</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
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
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link Matriks Kompetensi</label>
                            <select name="competency_role_requirement_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, pilih gap kompetensi</option>
                                @foreach($requirements as $requirement)
                                    <option value="{{ $requirement->id }}">{{ $requirement->requirement_number }} - {{ $requirement->competency?->competency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor IDP</label>
                            <input name="plan_number" required placeholder="Contoh: IDP-2026-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Area Pengembangan</label>
                            <input name="development_area" required placeholder="Contoh: Leadership shift supervisor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Karier / Role</label>
                            <input name="career_goal" placeholder="Contoh: Siap promosi ke Supervisor Operasional dalam 6 bulan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Learning & Action Plan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Level Saat Ini</label>
                            <select name="current_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @for($level = 1; $level <= 5; $level++)
                                    <option value="{{ $level }}">Level {{ $level }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Level</label>
                            <select name="target_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @for($level = 1; $level <= 5; $level++)
                                    <option value="{{ $level }}" @selected($level === 3)>Level {{ $level }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Prioritas</label>
                            <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rekomendasi Training</label>
                            <select name="training_course_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, pilih katalog training</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_code }} - {{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rekomendasi Manual</label>
                            <input name="learning_recommendation" placeholder="Coaching, shadowing, sertifikasi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Action Plan</label>
                            <textarea name="action_plan" rows="2" placeholder="Contoh: Ikut training, praktik OJT 30 hari, weekly coaching dengan mentor, lalu review hasil." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Review</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Selesai</label>
                            <input type="date" name="target_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="in_progress">In progress</option>
                                <option value="completed">Completed</option>
                                <option value="paused">Paused</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mentor</label>
                            <input name="mentor_name" placeholder="Nama mentor / coach" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Reviewer</label>
                            <input name="reviewer_name" placeholder="Atasan / HR reviewer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Progress</label>
                            <input type="number" name="progress_percent" value="0" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Review</label>
                            <textarea name="notes" rows="2" placeholder="Catatan readiness, blocker, hasil coaching, atau keputusan review berikutnya." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-route"></i> Simpan IDP
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="panel rounded-2xl p-5">
                <h2 class="text-lg font-semibold text-gray-900">Cara Baca Level</h2>
                <p class="mt-1 text-sm text-gray-500">Level dipakai untuk membaca gap antara kemampuan saat ini dan target role berikutnya.</p>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    @foreach([
                        '1' => 'Awareness, masih butuh arahan penuh.',
                        '2' => 'Basic, bisa mengerjakan kasus rutin.',
                        '3' => 'Working, mandiri untuk mayoritas pekerjaan.',
                        '4' => 'Advanced, bisa membimbing orang lain.',
                        '5' => 'Expert, menjadi rujukan lintas tim.',
                    ] as $level => $desc)
                        <div class="rounded-xl border border-gray-100 p-3">
                            <span class="font-semibold text-gray-900">Level {{ $level }}</span>
                            <span class="ml-2 text-xs">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar IDP</h2>
            <p class="text-sm text-gray-500">Pantau IDP aktif, rekomendasi learning, progress, dan target review.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Area</th>
                        <th class="px-5 py-3">Learning</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Progress</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        @php
                            $statusClass = [
                                'completed' => 'bg-green-50 text-green-700',
                                'in_progress' => 'bg-blue-50 text-blue-700',
                                'approved' => 'bg-blue-50 text-blue-700',
                                'paused' => 'bg-amber-50 text-amber-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                            ][$plan->status] ?? 'bg-gray-100 text-gray-700';
                            $priorityClass = in_array($plan->priority, ['high', 'critical'], true) ? 'text-red-600' : ($plan->priority === 'medium' ? 'text-amber-600' : 'text-green-600');
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $plan->employeeProfile?->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $plan->employeeProfile?->employee_number }}</div>
                                <div class="text-xs text-gray-500">{{ $plan->employeeProfile?->position ?: '-' }} / {{ $plan->employeeProfile?->grade ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $plan->development_area }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $plan->plan_number }}</div>
                                <div class="text-xs text-gray-500">Goal: {{ $plan->career_goal ?: '-' }}</div>
                                <div class="mt-1 text-xs">Prioritas: <span class="font-semibold {{ $priorityClass }}">{{ ucfirst($plan->priority) }}</span></div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $plan->trainingCourse?->title ?: ($plan->learning_recommendation ?: '-') }}</div>
                                <div class="text-xs text-gray-500">{{ $plan->competencyRequirement?->competency?->competency_name ?: 'Tanpa link competency' }}</div>
                                <div class="text-xs text-gray-500">Level {{ $plan->current_level }} ke {{ $plan->target_level }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $plan->start_date?->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-500">Target: {{ $plan->target_date?->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-500">Reviewer: {{ $plan->reviewer_name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $plan->progress_percent }}%</span>
                                    <span>{{ $plan->mentor_name ?: '-' }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $plan->progress_percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $plan->notes ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada Individual Development Plan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
