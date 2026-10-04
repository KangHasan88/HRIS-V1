@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">ATS Pipeline</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola kandidat dari sourcing, screening, interview, offer, sampai hired.</p>
        </div>
        <a href="{{ route('recruitment.manpower-requests') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-user-plus"></i>
            Pengajuan Manpower
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
                ['label' => 'Total Kandidat', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Interview', 'value' => $summary['interview'], 'color' => 'text-indigo-600'],
                ['label' => 'Hired', 'value' => $summary['hired'], 'color' => 'text-emerald-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-5">
        @foreach($stages as $stage)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ str_replace('_', ' ', $stage) }}</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900">{{ $stageCounts[$stage] ?? 0 }}</div>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, (($stageCounts[$stage] ?? 0) * 20)) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[.78fr_1.22fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Kandidat</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Tambah Kandidat',
                        'description' => 'Form ini dipakai HR untuk memasukkan kandidat ke pipeline ATS dan memberi skor screening awal sebelum interview.',
                        'steps' => [
                            'Link ke manpower jika kandidat berasal dari pengajuan posisi tertentu.',
                            'Isi identitas, kontak, sumber kandidat, dan skor screening awal.',
                            'Tentukan stage pipeline dan status kandidat agar bisa diproses ke interview.',
                            'Tambahkan PIC recruiter, label, dan catatan untuk memudahkan follow-up.',
                        ],
                        'notes' => [
                            'Skor screening adalah nilai awal sebelum interview, bukan hasil interview.',
                            'Label kandidat dipakai untuk filter cepat, misalnya Senior, Referral, atau Payroll.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Masukkan kandidat ke pipeline rekrutmen aktif.</p>
            </div>
            <form action="{{ route('recruitment.candidates.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Link Kebutuhan Posisi</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Manpower Request</label>
                        <select name="recruitment_manpower_request_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Tanpa link manpower</option>
                            @foreach($manpowerRequests as $request)
                                <option value="{{ $request->id }}">{{ $request->request_number }} - {{ $request->position_title }} / {{ $request->company?->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Kandidat</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Kandidat</label>
                            <input name="candidate_number" required placeholder="Contoh: CAND-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Kandidat</label>
                            <input name="full_name" required placeholder="Nama lengkap kandidat" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Email</label>
                            <input type="email" name="email" placeholder="email@domain.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor HP</label>
                            <input name="phone" placeholder="08xxxxxxxxxx" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Screening Awal</h3>
                    <div class="mt-3 grid items-start gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Kandidat</label>
                            <select name="source_channel" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="direct">Direct</option>
                                <option value="job_portal">Job Portal</option>
                                <option value="referral">Referral</option>
                                <option value="headhunter">Headhunter</option>
                                <option value="social_media">Social Media</option>
                                <option value="campus">Campus</option>
                                <option value="internal">Internal</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Channel asal kandidat masuk pipeline.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skor Screening (0-100)</label>
                            <input type="number" min="0" max="100" name="fit_score" value="70" required placeholder="Contoh: 70" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Nilai awal sebelum interview, berdasarkan CV/profil dan kebutuhan posisi.</p>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi Saat Ini</label>
                            <input name="current_position" placeholder="Contoh: Payroll Staff" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan Saat Ini</label>
                            <input name="current_company" placeholder="Nama perusahaan saat ini" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pipeline & Follow Up</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Stage Pipeline</label>
                            <select name="pipeline_stage" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @foreach($stages as $stage)
                                    <option value="{{ $stage }}">{{ ucfirst($stage) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Kandidat</label>
                            <select name="candidate_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="active">Active</option>
                                <option value="hold">Hold</option>
                                <option value="rejected">Rejected</option>
                                <option value="withdrawn">Withdrawn</option>
                                <option value="hired">Hired</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 grid items-start gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Ekspektasi Gaji</label>
                            <input type="number" min="0" name="expected_salary" placeholder="Contoh: 10000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Apply</label>
                            <input type="date" name="applied_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Terakhir Follow Up</label>
                            <input type="date" name="last_touch_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">PIC Recruiter</label>
                        <input name="owner_recruiter" placeholder="Nama recruiter penanggung jawab" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Label & Catatan</h3>
                    <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Label Kandidat</label>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input name="tags[]" placeholder="Contoh: Payroll" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="tags[]" placeholder="Contoh: Senior" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="tags[]" placeholder="Contoh: Referral" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Penanda cepat untuk filter dan prioritas kandidat.</p>
                </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Kandidat</label>
                        <textarea name="notes" rows="3" placeholder="Catatan kandidat, risiko, atau next step." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Kandidat
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Kandidat</h2>
                <p class="text-sm text-gray-500">Kandidat aktif dan progres stage rekrutmen.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Pipeline</th>
                            <th class="px-5 py-3">Manpower</th>
                            <th class="px-5 py-3">Skor Screening</th>
                            <th class="px-5 py-3">Touch</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($candidates as $candidate)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $candidate->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $candidate->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $candidate->email ?: '-' }} / {{ $candidate->phone ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($candidate->pipeline_stage) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ ucfirst($candidate->candidate_status) }} / {{ str_replace('_', ' ', $candidate->source_channel) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $candidate->manpowerRequest?->request_number ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $candidate->manpowerRequest?->position_title ?: $candidate->current_position ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $candidate->fit_score }}%</div>
                                    <div class="mt-2 h-2 w-24 rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-blue-600" style="width: {{ $candidate->fit_score }}%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Apply: {{ $candidate->applied_at?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">Last: {{ $candidate->last_touch_at?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $candidate->owner_recruiter ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kandidat ATS.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
