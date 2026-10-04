@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Jadwal Interview</h1>
            <p class="mt-1 text-sm text-gray-500">Atur jadwal interview, interviewer, scorecard feedback, dan rekomendasi kandidat.</p>
        </div>
        <a href="{{ route('recruitment.candidates') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-filter"></i>
            ATS Pipeline
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
                ['label' => 'Terjadwal', 'value' => $summary['scheduled'], 'color' => 'text-blue-600'],
                ['label' => 'Selesai Interview', 'value' => $summary['completed'], 'color' => 'text-green-600'],
                ['label' => 'Rata-rata Scorecard', 'value' => $summary['average_score'] ?: '0', 'color' => 'text-indigo-600'],
                ['label' => 'Direkomendasikan', 'value' => $summary['recommended'], 'color' => 'text-emerald-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.86fr_1.14fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Jadwalkan Interview</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Jadwal Interview',
                        'description' => 'Form ini dipakai HR untuk membuat agenda interview dan mencatat scorecard setelah interview selesai.',
                        'steps' => [
                            'Pilih kandidat aktif dari ATS Pipeline.',
                            'Isi tahap, tanggal, durasi, mode, interviewer, dan lokasi atau link meeting.',
                            'Jika interview belum selesai, biarkan scorecard bernilai 0 dan status tetap Terjadwal.',
                            'Setelah interview selesai, ubah status ke Selesai, isi scorecard, rekomendasi, dan feedback.',
                        ],
                        'notes' => [
                            'Scorecard interview berbeda dari skor screening awal.',
                            'Status Selesai akan membantu HR membaca kualitas kandidat dari daftar scorecard.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Scorecard diisi setelah interview selesai; saat membuat jadwal boleh pakai nilai 0 dulu.</p>
            </div>
            <form action="{{ route('recruitment.interviews.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Kandidat</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat</label>
                        <select name="recruitment_candidate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kandidat aktif</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->full_name }}{{ $candidate->manpowerRequest?->position_title ? ' / '.$candidate->manpowerRequest->position_title : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Detail Interview</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Interview</label>
                        <input name="interview_number" required placeholder="Contoh: INTV-062" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tahap Interview</label>
                        <select name="interview_stage" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="hr">HR Interview</option>
                            <option value="user">User Interview</option>
                            <option value="technical">Technical Interview</option>
                            <option value="final">Final Interview</option>
                            <option value="offering">Offering Discussion</option>
                        </select>
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Jadwal & Lokasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal & Jam</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Durasi</label>
                        <input type="number" min="15" max="480" name="duration_minutes" value="60" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mode</label>
                        <select name="interview_mode" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="online">Online</option>
                            <option value="onsite">Onsite</option>
                            <option value="phone">Telepon</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Lokasi / Link Meeting</label>
                    <input name="location_or_link" placeholder="Ruang meeting, alamat, atau link video call" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Interviewer</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Interviewer</label>
                        <input name="interviewer_name" required placeholder="Nama interviewer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Role Interviewer</label>
                        <input name="interviewer_role" placeholder="Contoh: HRBP / User Manager" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Scorecard Interview (0-100)</h3>
                        <p class="text-xs text-gray-500">Nilai ini adalah hasil interview, berbeda dari skor screening awal di ATS.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-4">
                        @foreach([
                            'communication_score' => 'Komunikasi',
                            'technical_score' => 'Teknis',
                            'culture_score' => 'Culture Fit',
                            'experience_score' => 'Pengalaman',
                        ] as $field => $label)
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</label>
                                <input type="number" min="0" max="100" name="{{ $field }}" value="0" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Status & Keputusan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Jadwal</label>
                        <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="scheduled">Terjadwal</option>
                            <option value="completed">Selesai</option>
                            <option value="rescheduled">Dijadwalkan ulang</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option value="no_show">Tidak hadir</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rekomendasi</label>
                        <select name="recommendation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="hold">Hold / Perlu review</option>
                            <option value="strong_hire">Strong Hire</option>
                            <option value="hire">Hire</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Feedback Interview</label>
                    <textarea name="feedback_notes" rows="3" placeholder="Ringkasan hasil interview, strength, risk, dan next step." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calendar-plus"></i>
                    Simpan Jadwal Interview
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Kalender Interview & Scorecard</h2>
                <p class="text-sm text-gray-500">Pantau agenda interview dan hasil feedback interviewer.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Jadwal</th>
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Interview</th>
                            <th class="px-5 py-3">Scorecard</th>
                            <th class="px-5 py-3">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($interviews as $interview)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $interview->scheduled_at?->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $interview->scheduled_at?->format('H:i') }} / {{ $interview->duration_minutes }} menit</div>
                                    <div class="mt-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ ucfirst(str_replace('_', ' ', $interview->status)) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $interview->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $interview->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $interview->candidate?->manpowerRequest?->position_title ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ strtoupper($interview->interview_number) }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($interview->interview_stage) }} / {{ ucfirst($interview->interview_mode) }}</div>
                                    <div class="text-xs text-gray-500">{{ $interview->interviewer_name }}{{ $interview->interviewer_role ? ' - '.$interview->interviewer_role : '' }}</div>
                                    <div class="text-xs text-gray-500">{{ $interview->location_or_link ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-lg font-semibold text-blue-600">{{ number_format((float) $interview->overall_score, 1) }}</div>
                                    <div class="mt-2 h-2 w-28 rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, (int) $interview->overall_score) }}%"></div>
                                    </div>
                                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-500">
                                        <span>Kom: {{ $interview->communication_score }}</span>
                                        <span>Tek: {{ $interview->technical_score }}</span>
                                        <span>Fit: {{ $interview->culture_score }}</span>
                                        <span>Exp: {{ $interview->experience_score }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $recommendationClass = [
                                            'strong_hire' => 'bg-green-50 text-green-700',
                                            'hire' => 'bg-blue-50 text-blue-700',
                                            'hold' => 'bg-amber-50 text-amber-700',
                                            'reject' => 'bg-red-50 text-red-700',
                                        ][$interview->recommendation] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $recommendationClass }}">{{ strtoupper(str_replace('_', ' ', $interview->recommendation)) }}</span>
                                    <p class="mt-2 max-w-xs text-xs text-gray-500">{{ $interview->feedback_notes ?: 'Belum ada feedback.' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada jadwal interview.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
