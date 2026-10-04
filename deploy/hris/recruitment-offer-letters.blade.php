@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Offer Letter</h1>
            <p class="mt-1 text-sm text-gray-500">Buat offer letter, kirim ke kandidat, dan pantau tanda tangan elektronik.</p>
        </div>
        <a href="{{ route('recruitment.interviews') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calendar-check"></i>
            Jadwal Interview
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
                ['label' => 'Draft', 'value' => $summary['draft'], 'color' => 'text-gray-900'],
                ['label' => 'Terkirim', 'value' => $summary['sent'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu TTD', 'value' => $summary['pending'], 'color' => 'text-amber-600'],
                ['label' => 'Sudah Signed', 'value' => $summary['signed'], 'color' => 'text-green-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Buat Offer Letter</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Offer Letter',
                        'description' => 'Form ini dipakai untuk membuat data offer letter dan memantau status tanda tangan elektronik kandidat.',
                        'steps' => [
                            'Pilih kandidat yang sudah direkomendasikan lanjut.',
                            'Isi nomor offer, posisi, tipe kontrak, tanggal join, dan gaji yang ditawarkan.',
                            'Isi data e-sign jika offer sudah dikirim ke provider tanda tangan elektronik.',
                            'Update status sampai Signed agar kandidat bisa masuk proses konversi employee.',
                        ],
                        'notes' => [
                            'Tanggal expired membantu HR follow-up sebelum offer kedaluwarsa.',
                            'Reference e-sign dipakai untuk rekonsiliasi dengan provider.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Data ini menjadi referensi dokumen offer dan tracking e-sign.</p>
            </div>
            <form action="{{ route('recruitment.offer-letters.store') }}" method="POST" class="mt-5 space-y-4">
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
                    <h3 class="text-sm font-semibold text-gray-900">Dokumen Offer</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Offer</label>
                            <input name="offer_number" required placeholder="Contoh: OFFER-063" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Template Dokumen</label>
                            <input name="template_name" required value="Standard Offer Letter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi Ditawarkan</label>
                        <input name="position_title" required placeholder="Contoh: HRIS Product Analyst" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Paket Kerja Ditawarkan</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Kontrak</label>
                            <select name="employment_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="probation">Probation</option>
                                <option value="internship">Internship</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Join</label>
                            <input type="date" name="join_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gaji Ditawarkan</label>
                            <input type="number" min="0" name="salary_offer" required placeholder="Contoh: 12000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Tracking Tanda Tangan Elektronik</h3>
                        <p class="text-xs text-gray-500">Diisi saat dokumen dikirim ke provider e-sign atau sudah ditandatangani.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Offer</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="sent">Terkirim</option>
                                <option value="viewed">Dibuka Kandidat</option>
                                <option value="signed">Sudah Signed</option>
                                <option value="declined">Ditolak</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Email Signer</label>
                            <input type="email" name="signer_email" placeholder="email kandidat" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Provider E-sign</label>
                            <input name="esign_provider" placeholder="Contoh: Privy / Mekari Sign" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Reference E-sign</label>
                            <input name="esign_reference" placeholder="ID dokumen dari provider" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Kirim</label>
                            <input type="date" name="sent_at" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Expired</label>
                            <input type="date" name="expires_at" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Signed</label>
                            <input type="date" name="signed_at" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Catatan Offer</h3>
                    <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Ringkasan Offer</label>
                    <textarea name="offer_summary" rows="3" placeholder="Benefit, lokasi kerja, reporting line, catatan negosiasi, atau syarat offer." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-file-signature"></i>
                    Simpan Offer Letter
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Tracking Offer & E-sign</h2>
                <p class="text-sm text-gray-500">Pantau status dokumen offer sampai kandidat tanda tangan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Offer</th>
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Nilai Offer</th>
                            <th class="px-5 py-3">E-sign</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($offers as $offer)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $offer->offer_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $offer->template_name }}</div>
                                    <div class="text-xs text-gray-500">Join: {{ $offer->join_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $offer->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $offer->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $offer->position_title }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">IDR {{ number_format((float) $offer->salary_offer, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($offer->employment_type) }}</div>
                                    <p class="mt-1 max-w-xs text-xs text-gray-500">{{ $offer->offer_summary ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $offer->esign_provider ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $offer->esign_reference ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $offer->signer_email ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $statusClass = [
                                            'draft' => 'bg-gray-100 text-gray-700',
                                            'sent' => 'bg-blue-50 text-blue-700',
                                            'viewed' => 'bg-indigo-50 text-indigo-700',
                                            'signed' => 'bg-green-50 text-green-700',
                                            'declined' => 'bg-red-50 text-red-700',
                                            'expired' => 'bg-amber-50 text-amber-700',
                                            'cancelled' => 'bg-gray-100 text-gray-600',
                                        ][$offer->status] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $offer->status)) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">Kirim: {{ $offer->sent_at?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Expired: {{ $offer->expires_at?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Signed: {{ $offer->signed_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada offer letter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
