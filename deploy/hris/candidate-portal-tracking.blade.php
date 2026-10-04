<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracking Kandidat - Kurmigo HRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
    <main class="mx-auto max-w-5xl px-5 py-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-blue-600">Kurmigo HRIS Candidate Portal</div>
                    <h1 class="mt-1 text-2xl font-semibold">{{ $candidate->full_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $candidate->candidate_number }} / {{ $candidate->manpowerRequest?->position_title ?: $candidate->current_position }}</p>
                </div>
                <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600">{{ ucfirst($candidate->pipeline_stage) }}</span>
            </div>

            @if (session('success'))
                <div class="mt-6 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                @foreach([
                    ['label' => 'Status', 'value' => ucfirst($candidate->candidate_status)],
                    ['label' => 'Source', 'value' => str_replace('_', ' ', ucfirst($candidate->source_channel))],
                    ['label' => 'Recruiter', 'value' => $candidate->owner_recruiter ?: '-'],
                    ['label' => 'Last Update', 'value' => $candidate->last_touch_at?->format('d M Y') ?? '-'],
                ] as $item)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                        <div class="mt-1 text-lg font-semibold">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold">Progress Rekrutmen</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-5">
                    @foreach($stageOrder as $index => $stage)
                        <div class="rounded-xl border {{ $index <= $currentIndex ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-400' }} p-4">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $index <= $currentIndex ? 'fa-circle-check' : 'fa-circle' }}"></i>
                                <span class="text-sm font-semibold">{{ ucfirst($stage) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-8">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-lg font-semibold">Submit Dokumen</h2>
                        <p class="text-sm text-gray-500">Upload/link dokumen dari kandidat. HR akan melakukan review dan verifikasi.</p>
                    </div>
                    <form action="{{ route('candidate.portal.documents', $candidate->portal_token) }}" method="POST" class="mt-5 grid gap-3 md:grid-cols-2">
                        @csrf
                        <select name="document_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="cv">CV</option>
                            <option value="ktp">KTP</option>
                            <option value="ijazah">Ijazah</option>
                            <option value="transkrip">Transkrip</option>
                            <option value="sertifikat">Sertifikat</option>
                            <option value="portfolio">Portfolio</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <input name="document_title" required placeholder="Nama dokumen" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="file_reference" required placeholder="Link file / nama file yang dikirim" class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <textarea name="notes" rows="2" placeholder="Catatan kandidat" class="md:col-span-2 resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        <button class="md:col-span-2 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            <i class="fas fa-upload"></i>
                            Submit Dokumen
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold">Dokumen Kandidat</h2>
                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Dokumen</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($candidate->documents as $document)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold">{{ $document->document_title }}</div>
                                        <div class="text-xs text-gray-500">{{ strtoupper($document->document_type) }} / {{ $document->submitted_at?->format('d M Y') ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $document->verification_status === 'verified' ? 'bg-green-50 text-green-600' : ($document->verification_status === 'need_revision' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600') }} px-3 py-1 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($document->verification_status)) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $document->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada dokumen yang dicatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
