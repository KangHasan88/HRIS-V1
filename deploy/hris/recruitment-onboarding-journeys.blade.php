@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Welcome Journey</h1>
            <p class="mt-1 text-sm text-gray-500">Atur buddy, checklist onboarding, probation plan, dan agenda welcome karyawan baru.</p>
        </div>
        <a href="{{ route('hris.employee-onboarding') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-list-check"></i>
            Onboarding Karyawan
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
                ['label' => 'Total Journey', 'value' => $summary['total'], 'color' => 'text-gray-900'],
                ['label' => 'Planned', 'value' => $summary['planned'], 'color' => 'text-blue-600'],
                ['label' => 'Active', 'value' => $summary['active'], 'color' => 'text-indigo-600'],
                ['label' => 'Completed', 'value' => $summary['completed'], 'color' => 'text-green-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Buat Welcome Journey</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Welcome Journey',
                        'description' => 'Form ini dipakai setelah kandidat diterima atau dikonversi menjadi employee untuk menyiapkan pengalaman onboarding awal.',
                        'steps' => [
                            'Pilih employee baru atau hasil konversi kandidat.',
                            'Isi pendamping onboarding yang mendampingi karyawan baru.',
                            'Isi checklist pre-join, day-1, dan week-1.',
                            'Isi target probation dan agenda welcome agar proses onboarding lebih terstruktur.',
                        ],
                        'notes' => [
                            'Checklist akan ikut digenerate ke Onboarding Karyawan sebagai task.',
                            'Pendamping onboarding menjadi owner task welcome journey.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Generate checklist awal dan rencana probation dari hasil hiring.</p>
            </div>
            <form action="{{ route('recruitment.onboarding-journeys.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Employee</h3>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Employee Baru</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih employee baru</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->department ?: '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link Konversi Kandidat</label>
                            <select name="recruitment_employee_conversion_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional, jika employee berasal dari hasil hiring</option>
                                @foreach($conversions as $conversion)
                                    <option value="{{ $conversion->id }}">{{ $conversion->conversion_number }} - {{ $conversion->candidate?->full_name }} / {{ $conversion->employee_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Journey</label>
                                <input name="journey_number" required placeholder="Contoh: WELCOME-066" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Journey</label>
                                <select name="journey_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="planned">Planned</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="blocked">Blocked</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pendamping Onboarding</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Pendamping</label>
                            <input name="buddy_name" required placeholder="Contoh: Ayu Prameswari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Role Pendamping</label>
                            <input name="buddy_role" placeholder="Contoh: HR Buddy / Mentor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontak Pendamping</label>
                            <input name="buddy_contact" placeholder="Email atau nomor HP" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Probation</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Onboarding</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Probation</label>
                            <input type="date" name="probation_end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Reviewer Probation</label>
                            <input name="probation_reviewer" placeholder="Nama atasan/reviewer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Checklist Onboarding Awal</h3>
                    <p class="mt-1 text-xs text-gray-500">Checklist ini otomatis dibuat sebagai task onboarding untuk employee baru.</p>
                    <div class="mt-3 space-y-3">
                        <input name="checklist_items[]" required placeholder="Pre-join: verifikasi dokumen dan akun kerja" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="checklist_items[]" required placeholder="Day-1: welcome session dan tour area kerja" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="checklist_items[]" required placeholder="Week-1: review role, target, dan ekspektasi kerja" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Target Probation</h3>
                        <div class="mt-3 space-y-3">
                            <input name="probation_goals[]" placeholder="Target probation 1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input name="probation_goals[]" placeholder="Target probation 2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Agenda Welcome</h3>
                        <div class="mt-3 space-y-3">
                            <input name="welcome_agenda[]" placeholder="Agenda welcome 1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input name="welcome_agenda[]" placeholder="Agenda welcome 2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Journey</label>
                    <textarea name="notes" rows="3" placeholder="Catatan risiko onboarding, kebutuhan khusus employee baru, atau follow-up HR." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-person-walking-arrow-right"></i>
                    Simpan Welcome Journey
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Welcome Journey</h2>
                <p class="text-sm text-gray-500">Monitoring buddy, checklist, dan probation plan karyawan baru.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Journey</th>
                            <th class="px-5 py-3">Employee</th>
                            <th class="px-5 py-3">Buddy</th>
                            <th class="px-5 py-3">Probation</th>
                            <th class="px-5 py-3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($journeys as $journey)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $journey->journey_number }}</div>
                                    <div class="text-xs text-gray-500">Start: {{ $journey->start_date?->format('d M Y') ?? '-' }}</div>
                                    <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ strtoupper($journey->journey_status) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $journey->employeeProfile?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $journey->employeeProfile?->employee_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $journey->employeeProfile?->company?->code ?: '-' }} / {{ $journey->employeeProfile?->branch?->code ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $journey->buddy_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $journey->buddy_role ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $journey->buddy_contact ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-gray-600">End: {{ $journey->probation_end_date?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $journey->probation_reviewer ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ count($journey->probation_plan ?? []) }} goal</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ count($journey->checklist_snapshot ?? []) }} checklist</div>
                                    <div class="text-xs text-gray-500">{{ count($journey->welcome_schedule ?? []) }} agenda welcome</div>
                                    <p class="mt-2 max-w-xs text-xs text-gray-500">{{ $journey->notes ?: 'Tidak ada catatan.' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada welcome journey.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
