@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">360 Feedback</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola siklus review, form 360, peer review, self review, dan review manager.</p>
        </div>
        <a href="{{ route('performance.training-learning') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-graduation-cap"></i> Learning
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
                ['label' => 'Siklus', 'value' => $summary['cycles'], 'color' => 'text-blue-600'],
                ['label' => 'Siklus Aktif', 'value' => $summary['active_cycles'], 'color' => 'text-green-600'],
                ['label' => 'Form Review', 'value' => $summary['forms'], 'color' => 'text-indigo-600'],
                ['label' => 'Response Masuk', 'value' => $summary['submitted_responses'], 'color' => 'text-amber-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Siklus Review</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai siklus review',
                        'description' => 'Siklus review adalah periode resmi untuk menjalankan self review, peer review, manager review, dan 360 feedback.',
                        'steps' => [
                            'Isi kode, nama, tipe review, dan periode.',
                            'Masukkan competency group dipisahkan koma.',
                            'Set status Active jika form sudah boleh dibuat.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu siklus bisa punya banyak form review per karyawan.</p>
            </div>
            <form action="{{ route('performance.review-360.cycles') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Siklus</h3>
                    <div class="mt-3 space-y-3">
                        <input name="cycle_code" required placeholder="Contoh: PR-360-2026-H2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="cycle_name" required placeholder="Contoh: Review 360 Semester 2 2026" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <select name="review_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="360">360 feedback</option>
                                <option value="peer">Peer review</option>
                                <option value="manager">Manager review</option>
                                <option value="self">Self review</option>
                                <option value="probation">Probation</option>
                            </select>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Scope</h3>
                    <div class="mt-3 space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input type="date" name="period_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <input type="date" name="period_end" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <input type="date" name="submission_deadline" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                        <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua cabang/site</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <input name="competency_groups" placeholder="Contoh: leadership,collaboration,delivery" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <textarea name="description" rows="2" placeholder="Deskripsi siklus review." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white">
                        <i class="fas fa-save"></i> Simpan Siklus
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Form Review</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai form review',
                        'description' => 'Form review adalah assignment untuk satu karyawan dalam satu siklus review.',
                        'steps' => [
                            'Pilih siklus dan employee yang direview.',
                            'Isi manager reviewer jika perlu.',
                            'Set target score dan status Assigned untuk mulai proses.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Form menjadi container untuk self, peer, manager, atau HR response.</p>
            </div>
            <form action="{{ route('performance.review-360.forms') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Assignment Form</h3>
                    <div class="mt-3 space-y-3">
                        <input name="form_number" required placeholder="Contoh: FORM-360-069" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <select name="performance_review_cycle_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih siklus review</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->cycle_code }} - {{ $cycle->cycle_name }}</option>
                            @endforeach
                        </select>
                        <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <select name="manager_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Manager reviewer opsional</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <select name="review_scope" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="annual">Annual</option>
                                <option value="semester">Semester</option>
                                <option value="quarter">Quarter</option>
                                <option value="probation">Probation</option>
                                <option value="project">Project</option>
                            </select>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="assigned">Assigned</option>
                                <option value="in_review">In review</option>
                                <option value="calibrated">Calibrated</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <input type="number" min="0" max="100" step="0.01" name="target_score" placeholder="Target score, contoh: 85" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <textarea name="summary_note" rows="3" placeholder="Catatan ringkas tujuan review." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white">
                        <i class="fas fa-clipboard-list"></i> Simpan Form
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Submit Response</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai response review',
                        'description' => 'Response adalah feedback dari self, manager, peer, subordinate, atau HR untuk form review tertentu.',
                        'steps' => [
                            'Pilih form review dan tipe reviewer.',
                            'Isi score 0-100 dan feedback utama.',
                            'Competency score bisa diisi format: leadership:85,delivery:90.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Response submitted akan otomatis menghitung ulang final score form.</p>
            </div>
            <form action="{{ route('performance.review-360.responses') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Feedback Reviewer</h3>
                    <div class="mt-3 space-y-3">
                        <select name="performance_review_form_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih form review</option>
                            @foreach($forms as $form)
                                <option value="{{ $form->id }}">{{ $form->form_number }} - {{ $form->employeeProfile?->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <select name="reviewer_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="self">Self</option>
                                <option value="manager">Manager</option>
                                <option value="peer">Peer</option>
                                <option value="subordinate">Subordinate</option>
                                <option value="hr">HR</option>
                            </select>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="completed">Completed</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>
                        <select name="reviewer_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Reviewer user opsional</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                        <select name="reviewer_employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Reviewer employee opsional</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <input type="number" min="0" max="100" step="0.01" name="score" required placeholder="Score 0-100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="competency_scores" placeholder="Contoh: leadership:85,delivery:90,teamwork:88" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <textarea name="strength_feedback" rows="3" required placeholder="Kekuatan utama yang terlihat." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <textarea name="improvement_feedback" rows="2" placeholder="Area yang perlu dikembangkan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <textarea name="development_suggestion" rows="2" placeholder="Saran development atau learning plan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white">
                        <i class="fas fa-paper-plane"></i> Simpan Response
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Form Review</h2>
            <p class="text-sm text-gray-500">Ringkasan form 360 dan response yang sudah masuk.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Form</th>
                        <th class="px-5 py-3">Employee</th>
                        <th class="px-5 py-3">Siklus</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Score</th>
                        <th class="px-5 py-3">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($forms as $form)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $form->form_number }}</td>
                            <td class="px-5 py-4">{{ $form->employeeProfile?->full_name }}</td>
                            <td class="px-5 py-4">{{ $form->cycle?->cycle_code }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ ucfirst(str_replace('_', ' ', $form->status)) }}</span></td>
                            <td class="px-5 py-4 font-semibold text-green-700">{{ number_format((float) $form->final_score, 2) }}</td>
                            <td class="px-5 py-4">{{ $form->responses_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Belum ada form review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
