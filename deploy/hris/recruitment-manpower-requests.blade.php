@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pengajuan Manpower</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan kebutuhan karyawan baru, replacement, ekspansi, dan approval rekrutmen.</p>
        </div>
        <a href="{{ route('settings.approval-workflows') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-route"></i>
            Workflow Approval
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Submitted', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Open Headcount', 'value' => $summary['open_headcount'], 'color' => 'text-blue-600'],
                ['label' => 'Urgent', 'value' => $summary['urgent'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.78fr_1.22fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Pengajuan</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Pengajuan Manpower',
                        'description' => 'Form ini dipakai untuk membuat kebutuhan tenaga kerja baru sebelum masuk approval dan pipeline rekrutmen.',
                        'steps' => [
                            'Isi nomor pengajuan, prioritas, perusahaan, cabang/site, dan posisi yang dibutuhkan.',
                            'Lengkapi headcount, target join, alasan pengajuan, budget, dan ringkasan pekerjaan.',
                            'Tambahkan requirement utama agar recruiter memahami kualifikasi kandidat.',
                            'Isi pemohon, tanggal request, status approval, approver, dan catatan approval.',
                        ],
                        'notes' => [
                            'Pengajuan approved dapat menjadi referensi kandidat di ATS Pipeline.',
                            'Budget minimum dan maksimum membantu HR menjaga proses rekrutmen sesuai range.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Data ini menjadi dasar approval dan pipeline rekrutmen.</p>
            </div>
            <form action="{{ route('recruitment.manpower-requests.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Informasi Pengajuan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Pengajuan</label>
                            <input name="request_number" required placeholder="Contoh: MPR-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Prioritas</label>
                            <select name="priority" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="normal">Prioritas normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope Organisasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kebutuhan Posisi</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi yang Dibutuhkan</label>
                        <input name="position_title" required placeholder="Contoh: HR Admin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Departemen</label>
                            <input name="department" placeholder="Contoh: Human Capital" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Karyawan</label>
                            <select name="employment_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="permanent">Permanent</option>
                                <option value="contract">Kontrak</option>
                                <option value="internship">Magang</option>
                                <option value="outsourcing">Outsourcing</option>
                                <option value="daily_worker">Harian</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount</label>
                            <input type="number" min="1" name="headcount" value="1" required placeholder="Jumlah orang" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Join</label>
                            <input type="date" name="target_join_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Pengajuan</label>
                            <select name="request_reason" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="replacement">Replacement</option>
                                <option value="new_position">Posisi baru</option>
                                <option value="expansion">Ekspansi</option>
                                <option value="project">Project</option>
                                <option value="succession">Succession</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Budget & Kualifikasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Budget Minimum</label>
                            <input type="number" min="0" name="budget_min" placeholder="Contoh: 5000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Budget Maksimum</label>
                            <input type="number" min="0" name="budget_max" placeholder="Contoh: 8000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Ringkasan Pekerjaan</label>
                        <textarea name="job_summary" rows="3" placeholder="Ringkasan tanggung jawab utama posisi." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Requirement Utama</label>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <input name="requirements[]" placeholder="Requirement 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input name="requirements[]" placeholder="Requirement 2" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input name="requirements[]" placeholder="Requirement 3" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Diminta Oleh</label>
                            <input name="requested_by" placeholder="Nama pemohon" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Request</label>
                            <input type="date" name="requested_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="submitted">Submitted</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver</label>
                            <input name="approved_by" placeholder="Nama approver" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Approval</label>
                        <textarea name="approval_notes" rows="2" placeholder="Catatan approval, alasan reject, atau instruksi tambahan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Pengajuan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Pengajuan</h2>
                <p class="text-sm text-gray-500">Monitoring kebutuhan manpower dan status approval.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Pengajuan</th>
                            <th class="px-5 py-3">Posisi</th>
                            <th class="px-5 py-3">Scope</th>
                            <th class="px-5 py-3">Approval</th>
                            <th class="px-5 py-3">Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($requests as $request)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $request->request_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $request->request_reason)) }} / {{ ucfirst($request->priority) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $request->position_title }}</div>
                                    <div class="text-xs text-gray-500">{{ $request->department ?: '-' }} / {{ ucfirst(str_replace('_', ' ', $request->employment_type)) }}</div>
                                    <div class="text-xs text-gray-500">{{ $request->headcount }} orang</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $request->company?->code }}</div>
                                    <div class="text-xs text-gray-500">{{ $request->branch?->code ?: 'Semua cabang/site' }}</div>
                                    <div class="text-xs text-gray-500">Budget: {{ $request->budget_min ? 'Rp '.number_format((float) $request->budget_min, 0, ',', '.') : '-' }} - {{ $request->budget_max ? 'Rp '.number_format((float) $request->budget_max, 0, ',', '.') : '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $request->status === 'approved' ? 'bg-green-50 text-green-600' : ($request->status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600') }} px-3 py-1 text-xs font-semibold">{{ ucfirst($request->status) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $request->approved_by ?: $request->requested_by ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Join: {{ $request->target_join_date?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">Request: {{ $request->requested_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada pengajuan manpower.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
