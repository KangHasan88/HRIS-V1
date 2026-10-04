@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Konversi Employee</h1>
            <p class="mt-1 text-sm text-gray-500">Ubah kandidat accepted menjadi employee master dengan jejak mapping yang jelas.</p>
        </div>
        <a href="{{ route('hris.employees') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users"></i>
            Employee Master
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
                ['label' => 'Total Konversi', 'value' => $summary['total'], 'color' => 'text-gray-900'],
                ['label' => 'Draft Review', 'value' => $summary['draft'], 'color' => 'text-amber-600'],
                ['label' => 'Converted', 'value' => $summary['converted'], 'color' => 'text-green-600'],
                ['label' => 'Employee Dibuat', 'value' => $summary['employees'], 'color' => 'text-blue-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.82fr_1.18fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Konversi</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Konversi Employee',
                        'description' => 'Form ini dipakai untuk mengubah kandidat accepted atau hired menjadi data Employee Master.',
                        'steps' => [
                            'Pilih kandidat yang sudah accepted/hired.',
                            'Isi nomor konversi, nomor karyawan baru, organisasi, status karyawan, posisi, dan tanggal join.',
                            'Pilih Draft jika data masih perlu direview.',
                            'Pilih Converted jika data sudah final dan employee master boleh langsung dibuat.',
                        ],
                        'notes' => [
                            'Status Converted akan membuat EmployeeProfile baru.',
                            'Mapping kandidat ke employee tetap disimpan sebagai audit trail.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Pilih status draft jika data masih perlu dicek; pilih converted untuk langsung membuat employee master.</p>
            </div>
            <form action="{{ route('recruitment.employee-conversions.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Kandidat</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat Accepted</label>
                        <select name="recruitment_candidate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kandidat offer / hired</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->full_name }}{{ $candidate->manpowerRequest?->position_title ? ' / '.$candidate->manpowerRequest->position_title : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Employee Baru</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Konversi</label>
                            <input name="conversion_number" required placeholder="Contoh: CONV-065" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Karyawan Baru</label>
                            <input name="employee_number" required placeholder="Contoh: KRM-0065" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Organisasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                        <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Ikuti pengajuan manpower</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                        <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Ikuti pengajuan manpower</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Status Kerja</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Karyawan</label>
                        <select name="employment_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="probation">Probation</option>
                            <option value="contract">Contract</option>
                            <option value="permanent">Permanent</option>
                            <option value="intern">Internship</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Join</label>
                        <input type="date" name="join_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Posisi & Payroll Starter</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi</label>
                        <input name="position" placeholder="Jika kosong ikut posisi manpower" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Departemen</label>
                        <input name="department" placeholder="Contoh: Human Capital" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Grade</label>
                        <input name="grade" placeholder="Contoh: G5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cost Center</label>
                        <input name="cost_center" placeholder="Contoh: HC-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Email Kerja</label>
                        <input type="email" name="work_email" placeholder="nama@kurmigo.id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Finalisasi Konversi</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Konversi</label>
                        <select name="conversion_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="draft">Draft review dulu</option>
                            <option value="converted">Converted - buat employee master</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilihan converted akan langsung membuat data di Employee Master.</p>
                    </div>

                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Konversi</label>
                        <textarea name="notes" rows="3" placeholder="Catatan payroll starter, dokumen yang belum lengkap, atau approval HR." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-tie"></i>
                    Simpan Konversi
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Konversi</h2>
                <p class="text-sm text-gray-500">Pantau kandidat yang sudah atau akan masuk employee master.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Konversi</th>
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Employee</th>
                            <th class="px-5 py-3">Organisasi</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($conversions as $conversion)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $conversion->conversion_number }}</div>
                                    <div class="text-xs text-gray-500">Join: {{ $conversion->join_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $conversion->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $conversion->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $conversion->candidate?->manpowerRequest?->request_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $conversion->employee_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $conversion->employeeProfile?->full_name ?: 'Belum dibuat' }}</div>
                                    <div class="text-xs text-gray-500">{{ $conversion->work_email ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $conversion->company?->code ?: '-' }} / {{ $conversion->branch?->code ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $conversion->position ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $conversion->department ?: '-' }} / {{ $conversion->grade ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $statusClass = $conversion->conversion_status === 'converted'
                                            ? 'bg-green-50 text-green-700'
                                            : 'bg-amber-50 text-amber-700';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ strtoupper($conversion->conversion_status) }}</span>
                                    <p class="mt-2 max-w-xs text-xs text-gray-500">{{ $conversion->notes ?: 'Tidak ada catatan.' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada konversi kandidat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
