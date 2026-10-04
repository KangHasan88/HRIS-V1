@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Jurnal Performa Tim</h1>
            <p class="mt-1 text-sm text-gray-500">Catat observasi performa, coaching, pencapaian, dan hasil one-on-one bawahan.</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-chart-line"></i> Dashboard Manager
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
                ['label' => 'Catatan', 'value' => $summary['notes'], 'color' => 'text-indigo-600'],
                ['label' => 'One-on-One', 'value' => $summary['one_on_ones'], 'color' => 'text-green-600'],
                ['label' => 'Follow-up', 'value' => $summary['follow_up'], 'color' => 'text-amber-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Catatan Performa</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai catatan performa',
                        'description' => 'Dipakai manager untuk mencatat observasi kerja yang faktual dan mudah ditindaklanjuti.',
                        'steps' => [
                            'Pilih bawahan dan tanggal kejadian.',
                            'Pilih kategori: achievement, coaching, behavior, risk, atau recognition.',
                            'Tulis bukti/observasi spesifik, bukan opini umum.',
                            'Isi follow-up jika butuh review berikutnya.',
                        ],
                        'notes' => [
                            'Visibility manager only berarti catatan hanya untuk manager.',
                            'HR visible dipakai jika catatan perlu jadi referensi HR/performance review.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Catatan ini membantu performance review agar tidak hanya mengandalkan ingatan akhir periode.</p>
            </div>

            <form action="{{ route('manager.performance-journal.notes') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Catatan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bawahan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih bawahan</option>
                                @foreach($team as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Catatan</label>
                            <input type="date" name="note_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                            <select name="category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="performance">Performance</option>
                                <option value="achievement">Achievement</option>
                                <option value="recognition">Recognition</option>
                                <option value="coaching">Coaching</option>
                                <option value="behavior">Behavior</option>
                                <option value="risk">Risk</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sentimen</label>
                            <select name="sentiment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="positive">Positive</option>
                                <option value="neutral">Neutral</option>
                                <option value="concern">Concern</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Visibility</label>
                            <select name="visibility" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="manager_only">Manager only</option>
                                <option value="hr_visible">HR visible</option>
                                <option value="employee_visible">Employee visible</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="open">Open</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Isi Catatan</h3>
                    <div class="mt-3 space-y-3">
                        <input name="title" required placeholder="Contoh: Konsisten menyelesaikan closing harian tepat waktu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <textarea name="note_body" rows="4" required placeholder="Tulis observasi faktual, konteks, dampak ke tim, dan hal yang perlu ditindaklanjuti." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <div class="grid gap-3 md:grid-cols-2">
                            <input name="tags" placeholder="Tag, contoh: disiplin,leadership,quality" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <input type="date" name="follow_up_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Log One-on-One</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai log one-on-one',
                        'description' => 'Dipakai untuk mencatat hasil percakapan rutin manager dan bawahan, termasuk feedback dan action item.',
                        'steps' => [
                            'Pilih bawahan dan jenis meeting.',
                            'Ringkas topik utama, feedback employee, dan komitmen manager.',
                            'Tulis action item dalam format dipisah koma.',
                            'Isi next meeting jika butuh follow-up terjadwal.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Log ini menjaga kontinuitas coaching, probation, dan career discussion.</p>
            </div>

            <form action="{{ route('manager.performance-journal.one-on-ones') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Meeting</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bawahan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih bawahan</option>
                                @foreach($team as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Meeting</label>
                            <input type="date" name="meeting_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Meeting</label>
                            <select name="meeting_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="regular">Regular</option>
                                <option value="probation">Probation</option>
                                <option value="performance">Performance</option>
                                <option value="career">Career</option>
                                <option value="issue_follow_up">Issue follow-up</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mood</label>
                            <select name="mood" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="positive">Positive</option>
                                <option value="neutral">Neutral</option>
                                <option value="concern">Concern</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Hasil Diskusi</h3>
                    <div class="mt-3 space-y-3">
                        <input name="topic_summary" required placeholder="Contoh: Review target mingguan dan hambatan operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <textarea name="discussion_notes" rows="3" placeholder="Ringkasan diskusi utama." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <textarea name="employee_feedback" rows="2" placeholder="Feedback atau concern dari employee." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <textarea name="manager_commitment" rows="2" placeholder="Komitmen manager atau dukungan yang dijanjikan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        <div class="grid gap-3 md:grid-cols-3">
                            <input name="action_items" placeholder="Action item dipisah koma" class="rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2">
                            <input type="date" name="next_meeting_date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="open">Open</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-comments"></i> Simpan One-on-One
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Catatan Performa</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($notes as $note)
                    <div class="px-5 py-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $note->title }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $note->employeeProfile?->full_name }} · {{ $note->note_date->format('d M Y') }}</div>
                            </div>
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $note->sentiment === 'concern' ? 'bg-red-50 text-red-700' : ($note->sentiment === 'positive' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($note->sentiment) }}</span>
                        </div>
                        <p class="mt-2 text-gray-600">{{ $note->note_body }}</p>
                        <div class="mt-2 text-xs text-gray-500">Follow-up: {{ $note->follow_up_date?->format('d M Y') ?: '-' }} · {{ ucfirst($note->status) }}</div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada catatan performa.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat One-on-One</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($oneOnOnes as $log)
                    <div class="px-5 py-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $log->topic_summary }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $log->employeeProfile?->full_name }} · {{ $log->meeting_date->format('d M Y') }}</div>
                            </div>
                            <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ str_replace('_', ' ', ucfirst($log->meeting_type)) }}</span>
                        </div>
                        <p class="mt-2 text-gray-600">{{ $log->discussion_notes ?: 'Tidak ada catatan diskusi.' }}</p>
                        <div class="mt-2 text-xs text-gray-500">Next meeting: {{ $log->next_meeting_date?->format('d M Y') ?: '-' }} · {{ ucfirst($log->status) }}</div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada log one-on-one.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
