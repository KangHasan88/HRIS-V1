@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Portal Kandidat</h1>
            <p class="mt-1 text-sm text-gray-500">HR mengelola link portal, memantau dokumen masuk, dan melakukan verifikasi.</p>
        </div>
        <a href="{{ route('recruitment.candidates') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-filter-circle-dollar"></i>
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
                ['label' => 'Portal Ready', 'value' => $summary['portal_ready'], 'color' => 'text-blue-600'],
                ['label' => 'Submitted', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Verified', 'value' => $summary['verified'], 'color' => 'text-green-600'],
                ['label' => 'Need Revision', 'value' => $summary['need_revision'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Kandidat & Link Portal</h2>
                <p class="text-sm text-gray-500">Bagikan link ini agar kandidat submit dokumen sendiri.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($candidates as $candidate)
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $candidate->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $candidate->candidate_number }} / {{ ucfirst($candidate->pipeline_stage) }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $candidate->manpowerRequest?->position_title ?: 'Tanpa link manpower' }}</div>
                            </div>
                            <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $candidate->documents->count() }} dokumen</span>
                        </div>
                        @if($candidate->portal_token)
                            @php($portalUrl = route('candidate.portal.tracking', $candidate->portal_token))
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                <a target="_blank" href="{{ $portalUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                    Buka Portal
                                </a>
                                <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $portalUrl }}')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white">
                                    <i class="fas fa-copy"></i>
                                    Copy Link
                                </button>
                            </div>
                            <div class="mt-2 truncate rounded-lg bg-gray-50 px-3 py-2 font-mono text-xs text-gray-500">{{ $portalUrl }}</div>
                        @else
                            <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">Token portal belum tersedia.</div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kandidat ATS.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Review Dokumen & Link Portal</h2>
                <p class="text-sm text-gray-500">Dokumen utama disubmit kandidat melalui portal; HR memverifikasi dari sini.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Dokumen</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Portal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($documents as $document)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $document->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $document->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($document->candidate?->pipeline_stage ?? '-') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $document->document_title }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper($document->document_type) }} / {{ $document->file_reference ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $document->verification_status === 'verified' ? 'bg-green-50 text-green-600' : ($document->verification_status === 'need_revision' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600') }} px-3 py-1 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($document->verification_status)) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $document->submitted_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($document->candidate?->portal_token)
                                        <a target="_blank" href="{{ route('candidate.portal.tracking', $document->candidate->portal_token) }}" class="text-sm font-semibold text-blue-600">Buka Tracking</a>
                                    @else
                                        <span class="text-xs text-gray-500">Token belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada dokumen kandidat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <details class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <summary class="cursor-pointer list-none px-5 py-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Catat Dokumen Manual</h2>
                    <p class="text-sm text-gray-500">Fallback jika dokumen diterima via email, WhatsApp, walk-in, atau import data.</p>
                </div>
                <span class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700">Buka Form</span>
            </div>
        </summary>
        <form action="{{ route('recruitment.candidate-portal.documents') }}" method="POST" class="border-t border-gray-100 p-5">
            @csrf
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Dokumen</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat</label>
                            <select name="recruitment_candidate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih kandidat</option>
                                @foreach($candidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->full_name }} / {{ ucfirst($candidate->pipeline_stage) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Dokumen</label>
                            <input name="document_title" required placeholder="Contoh: CV terbaru" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Dokumen</label>
                            <select name="document_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="cv">CV</option>
                                <option value="ktp">KTP</option>
                                <option value="ijazah">Ijazah</option>
                                <option value="transkrip">Transkrip</option>
                                <option value="sertifikat">Sertifikat</option>
                                <option value="portfolio">Portfolio</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Verifikasi</label>
                            <select name="verification_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="submitted">Submitted</option>
                                <option value="verified">Verified</option>
                                <option value="need_revision">Need Revision</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi File / URL</label>
                            <input name="file_reference" placeholder="Link file, path upload, atau nomor referensi dari HR" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Verifikasi HR</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Submit</label>
                    <input type="date" name="submitted_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Diverifikasi Oleh</label>
                    <input name="verified_by" placeholder="Nama reviewer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Dokumen</label>
                            <textarea name="notes" rows="3" placeholder="Catatan kelengkapan dokumen, revisi, atau hasil verifikasi." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <button class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-save"></i>
                Simpan Manual
            </button>
        </form>
    </details>
</div>
@endsection
