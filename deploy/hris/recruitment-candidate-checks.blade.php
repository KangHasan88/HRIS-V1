@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pengecekan Kandidat</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau medical check-up, background check, dan reference check sebelum kandidat join.</p>
        </div>
        <a href="{{ route('recruitment.offer-letters') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-signature"></i>
            Offer Letter
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
                ['label' => 'Total Check', 'value' => $summary['total'], 'color' => 'text-gray-900'],
                ['label' => 'Masih Berjalan', 'value' => $summary['open'], 'color' => 'text-blue-600'],
                ['label' => 'Lulus', 'value' => $summary['passed'], 'color' => 'text-green-600'],
                ['label' => 'Blocking Issue', 'value' => $summary['blocking'], 'color' => 'text-red-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Pengecekan</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Pengecekan Kandidat',
                        'description' => 'Form ini dipakai untuk melacak MCU, background check, dan reference check sebelum kandidat join.',
                        'steps' => [
                            'Pilih kandidat yang akan dicek.',
                            'Pilih jenis pengecekan: medical check-up, background check, atau reference check.',
                            'Isi vendor atau pihak pemeriksa, target selesai, status proses, dan hasil pengecekan.',
                            'Aktifkan blocking issue jika hasil pengecekan perlu menahan proses hiring.',
                        ],
                        'notes' => [
                            'Evidence link bisa berisi file hasil MCU, dokumen vendor, atau referensi internal.',
                            'Hasil Need Review atau Failed sebaiknya diberi catatan findings yang jelas.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu kandidat bisa punya beberapa check: MCU, background, dan reference.</p>
            </div>
            <form action="{{ route('recruitment.candidate-checks.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Kandidat</h3>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat</label>
                        <select name="recruitment_candidate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kandidat</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->full_name }}{{ $candidate->manpowerRequest?->position_title ? ' / '.$candidate->manpowerRequest->position_title : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Jenis Pengecekan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Check</label>
                            <input name="check_number" required placeholder="Contoh: CHECK-064" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Pengecekan</label>
                            <select name="check_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="medical_checkup">Medical Check-up</option>
                                <option value="background_check">Background Check</option>
                                <option value="reference_check">Reference Check</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pihak Pemeriksa</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor / Pihak Pemeriksa</label>
                            <input name="vendor_name" placeholder="Klinik, vendor, atau internal HR" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Referensi</label>
                            <input name="reference_name" placeholder="Diisi untuk reference check" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontak Referensi</label>
                        <input name="reference_contact" placeholder="Email / nomor HP / jabatan referensi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Timeline Pengecekan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Request</label>
                        <input type="date" name="requested_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Target Selesai</label>
                        <input type="date" name="due_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Selesai</label>
                        <input type="date" name="completed_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Status & Hasil</h3>
                    <div class="mt-3 grid items-start gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Proses</label>
                        <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="pending">Belum mulai</option>
                            <option value="in_progress">Sedang diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Hasil Pengecekan</label>
                        <select name="result" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="waiting">Menunggu hasil</option>
                            <option value="passed">Lulus</option>
                            <option value="passed_with_note">Lulus dengan catatan</option>
                            <option value="need_review">Perlu review</option>
                            <option value="failed">Tidak lulus</option>
                        </select>
                    </div>
                </div>

                <label class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_blocking" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Tandai sebagai blocking issue untuk proses hiring
                </label>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Dokumen & Catatan</h3>
                    <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Evidence / Link Dokumen</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input name="evidence_links[]" placeholder="Link hasil MCU / dokumen 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="evidence_links[]" placeholder="Link dokumen 2 opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Findings</label>
                    <textarea name="findings" rows="3" placeholder="Ringkasan hasil, catatan risiko, atau alasan perlu review." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-check"></i>
                    Simpan Pengecekan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Tracker Pengecekan</h2>
                <p class="text-sm text-gray-500">Urutan prioritas menampilkan check yang masih berjalan dan mendekati due date.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Check</th>
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Pemeriksa</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Hasil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($checks as $check)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $check->check_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $check->check_type)) }}</div>
                                    @if($check->is_blocking)
                                        <div class="mt-2 inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Blocking</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $check->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $check->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $check->candidate?->manpowerRequest?->position_title ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $check->vendor_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Ref: {{ $check->reference_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $check->reference_contact ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Request: {{ $check->requested_at?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">Due: {{ $check->due_at?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Selesai: {{ $check->completed_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $resultClass = [
                                            'waiting' => 'bg-gray-100 text-gray-700',
                                            'passed' => 'bg-green-50 text-green-700',
                                            'passed_with_note' => 'bg-blue-50 text-blue-700',
                                            'need_review' => 'bg-amber-50 text-amber-700',
                                            'failed' => 'bg-red-50 text-red-700',
                                        ][$check->result] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $resultClass }}">{{ strtoupper(str_replace('_', ' ', $check->result)) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">Status: {{ ucwords(str_replace('_', ' ', $check->status)) }}</div>
                                    <p class="mt-2 max-w-xs text-xs text-gray-500">{{ $check->findings ?: 'Belum ada catatan.' }}</p>
                                    @if(!empty($check->evidence_links))
                                        <div class="mt-2 text-xs text-blue-600">{{ count($check->evidence_links) }} evidence tersimpan</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada pengecekan kandidat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
