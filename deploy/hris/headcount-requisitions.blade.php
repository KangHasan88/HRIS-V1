@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Headcount & Requisition Tim</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan kebutuhan penambahan orang dan pengganti posisi kosong dari scope tim manager.</p>
        </div>
        <a href="{{ route('manager.performance-journal') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-clipboard-user"></i> Jurnal Performa
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
                ['label' => 'Anggota Tim', 'value' => $summary['team_total'], 'color' => 'text-blue-600'],
                ['label' => 'Plan Aktif', 'value' => $summary['open_plans'], 'color' => 'text-amber-600'],
                ['label' => 'Requisition Aktif', 'value' => $summary['open_requisitions'], 'color' => 'text-indigo-600'],
                ['label' => 'Headcount Diminta', 'value' => $summary['requested_headcount'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Ajukan Rencana Headcount</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai rencana headcount',
                        'description' => 'Dipakai manager untuk mengajukan kebutuhan orang tambahan sebelum dibuat proses rekrutmen formal.',
                        'steps' => [
                            'Isi posisi, periode kebutuhan, jumlah orang saat ini, dan jumlah yang diminta.',
                            'Pilih prioritas dan status budget agar HR/Finance bisa menilai kelayakan.',
                            'Tulis alasan bisnis dan risiko jika posisi tidak dipenuhi.',
                            'Gunakan status Submitted jika siap dikirim ke approval.',
                        ],
                        'notes' => [
                            'Headcount plan cocok untuk growth, seasonal, atau kebutuhan kapasitas baru.',
                            'Untuk mengganti karyawan tertentu, gunakan form Requisition Pengganti.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Plan ini menjadi dasar HR memvalidasi kebutuhan manpower dan budget.</p>
            </div>

            <form action="{{ route('manager.headcount-requisitions.plans') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Kebutuhan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Plan</label>
                            <input name="plan_number" required placeholder="Contoh: HC-OPS-055" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Kebutuhan</label>
                            <select name="request_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="new_headcount">Headcount baru</option>
                                <option value="expansion">Ekspansi tim</option>
                                <option value="seasonal">Musiman / peak season</option>
                                <option value="replacement_pool">Pool pengganti</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi</label>
                            <input name="position_title" required placeholder="Contoh: Staff Operasional Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Departemen</label>
                            <input name="department" placeholder="Contoh: Operasional Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope & Target</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Ikuti scope manager</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Ikuti scope manager</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Plan</label>
                            <input type="date" name="planning_period" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Join</label>
                            <input type="date" name="target_join_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Saat Ini</label>
                            <input type="number" name="current_headcount" value="0" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Diminta</label>
                            <input type="number" name="requested_headcount" value="1" min="1" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Prioritas & Justifikasi</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Prioritas</label>
                            <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Budget</label>
                            <select name="budget_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="not_checked">Belum dicek</option>
                                <option value="within_budget">Masuk budget</option>
                                <option value="over_budget">Melebihi budget</option>
                                <option value="needs_finance_review">Perlu review Finance</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Estimasi Budget</label>
                            <input type="number" name="budget_estimate" min="0" step="1" placeholder="Contoh: 6500000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="review">Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: HRBP / Finance / Direktur Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Bisnis</label>
                            <textarea name="business_reason" rows="3" required placeholder="Jelaskan beban kerja, kebutuhan operasional, SLA, atau alasan kapasitas." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Risiko Jika Tidak Dipenuhi</label>
                            <textarea name="risk_if_not_filled" rows="2" placeholder="Contoh: backlog pekerjaan naik, SLA turun, overtime meningkat." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Headcount
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Ajukan Requisition Pengganti</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai requisition pengganti',
                        'description' => 'Dipakai saat manager perlu mengganti posisi yang kosong karena resign, mutasi, promosi, terminasi, atau backfill sementara.',
                        'steps' => [
                            'Pilih karyawan yang diganti jika sudah diketahui.',
                            'Isi posisi, alasan penggantian, target join, dan skill utama.',
                            'Tambahkan catatan handover agar HR tahu konteks transisi.',
                            'Gunakan status Submitted jika siap diproses HR.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Requisition ini bisa menjadi input awal pipeline recruitment.</p>
            </div>

            <form action="{{ route('manager.headcount-requisitions.replacements') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Requisition</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Requisition</label>
                            <input name="requisition_number" required placeholder="Contoh: REQ-OPS-055" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan Diganti</label>
                            <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Belum spesifik / posisi kosong</option>
                                @foreach($team as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi</label>
                            <input name="position_title" required placeholder="Contoh: Staff Operasional Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Pengganti</label>
                            <select name="replacement_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="resignation">Resign</option>
                                <option value="transfer">Mutasi</option>
                                <option value="promotion">Promosi</option>
                                <option value="termination">Terminasi</option>
                                <option value="temporary_backfill">Backfill sementara</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Target & Requirement</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Kosong</label>
                            <input type="date" name="vacancy_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Join</label>
                            <input type="date" name="target_join_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Kerja</label>
                            <select name="employment_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="daily_worker">Daily worker</option>
                                <option value="intern">Intern</option>
                                <option value="outsourcing">Outsourcing</option>
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
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="review">Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: HRBP / Direktur Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skill Utama</label>
                            <input name="skill_requirements" placeholder="Pisahkan koma, contoh: Excel, leadership shift, HSE dasar" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Penggantian</label>
                            <textarea name="replacement_reason" rows="3" required placeholder="Jelaskan alasan, urgensi, dan dampak ke operasional." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Handover</label>
                            <textarea name="handover_notes" rows="2" placeholder="Contoh: perlu overlap 2 minggu untuk transfer knowledge." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-paper-plane"></i> Simpan Requisition
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Pengajuan</h2>
            <p class="text-sm text-gray-500">Pantau rencana headcount dan requisition pengganti yang dibuat manager.</p>
        </div>
        <div class="grid gap-0 lg:grid-cols-2">
            <div class="border-b border-gray-100 lg:border-b-0 lg:border-r">
                <div class="bg-gray-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Plan</div>
                <div class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $plan->plan_number }} - {{ $plan->position_title }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $plan->department ?: 'Tanpa departemen' }} • {{ $plan->requested_headcount }} orang • {{ $plan->target_join_date?->format('d M Y') ?: 'Target belum diisi' }}</div>
                                </div>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($plan->approval_status) }}</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $plan->business_reason }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada rencana headcount.</div>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="bg-gray-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Replacement Requisition</div>
                <div class="divide-y divide-gray-100">
                    @forelse($requisitions as $requisition)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $requisition->requisition_number }} - {{ $requisition->position_title }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $requisition->employeeProfile?->full_name ?: 'Posisi kosong' }} • {{ $requisition->target_join_date?->format('d M Y') ?: 'Target belum diisi' }}</div>
                                </div>
                                <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($requisition->approval_status) }}</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $requisition->replacement_reason }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada requisition pengganti.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
