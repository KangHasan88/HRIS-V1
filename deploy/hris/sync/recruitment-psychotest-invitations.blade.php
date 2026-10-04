@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Undangan Psikotest</h1>
            <p class="mt-1 text-sm text-gray-500">Kirim link psikotest internal ke kandidat, pantau progress, dan sinkron hasil ke ATS.</p>
        </div>
        <a href="{{ route('recruitment.psychotest-integrations') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-brain"></i>
            Integrasi Psikotest
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Total Invite', 'value' => $summary['total'], 'color' => 'text-gray-900', 'hint' => 'Semua undangan'],
                ['label' => 'Berjalan', 'value' => $summary['sent'], 'color' => 'text-blue-600', 'hint' => 'Sent/open/progress'],
                ['label' => 'Selesai', 'value' => $summary['completed'], 'color' => 'text-green-600', 'hint' => 'Completed'],
                ['label' => 'Sinkron ATS', 'value' => $summary['synced'], 'color' => 'text-green-600', 'hint' => 'Result synced'],
                ['label' => 'Avg Score', 'value' => $summary['avg_score'], 'color' => 'text-amber-600', 'hint' => 'Hasil internal'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.82fr_1.18fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Undangan</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Undangan Psikotest',
                        'description' => 'Form ini membuat link unik untuk kandidat. Untuk arah internal, pilih paket dari provider Internal Engine.',
                        'steps' => [
                            'Pilih kandidat dan paket psikotest.',
                            'Isi nomor undangan, channel, tanggal kirim, dan masa berlaku link.',
                            'Status Sent membuat undangan dianggap sudah dikirim ke kandidat.',
                            'Link kandidat bisa dibuka dari tracker untuk preview test runner.',
                        ],
                        'notes' => [
                            'Bank soal dan scoring detail akan diperdalam di backlog berikutnya.',
                            'Hasil submit internal akan langsung tersinkron ke ATS dan pengecekan kandidat.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Default flow internal, external provider tetap bisa menjadi fallback.</p>
            </div>

            <form action="{{ route('recruitment.psychotest-invitations.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kandidat & Paket</h3>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kandidat</label>
                            <select name="recruitment_candidate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih kandidat</option>
                                @foreach($candidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->candidate_number }} - {{ $candidate->full_name }}{{ $candidate->manpowerRequest?->position_title ? ' / '.$candidate->manpowerRequest->position_title : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Paket Psikotest</label>
                            <select name="psychotest_package_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih paket</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->package_code }} - {{ $package->package_name }} / {{ $package->provider?->provider_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pengiriman</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Undangan</label>
                            <input name="invitation_number" required placeholder="Contoh: PSY-INV-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Channel</label>
                            <select name="delivery_channel" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="candidate_portal">Portal Kandidat</option>
                                <option value="manual_link">Manual Link</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Kirim</label>
                            <input type="datetime-local" name="sent_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Expired Link</label>
                            <input type="datetime-local" name="expires_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Awal</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Instruksi Kandidat</h3>
                    <textarea name="instruction" rows="3" placeholder="Contoh: Kerjakan test di tempat tenang, gunakan koneksi stabil, dan pastikan data yang diberikan benar." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    <textarea name="notes" rows="2" placeholder="Catatan internal HR, reminder, atau konteks posisi." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                    Simpan Undangan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Tracker Undangan</h2>
                <p class="text-sm text-gray-500">Pantau status link kandidat, consent, completion, dan sinkronisasi hasil.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Undangan</th>
                            <th class="px-5 py-3">Kandidat</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Progress</th>
                            <th class="px-5 py-3">Hasil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invitations as $invitation)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $invitation->invitation_number }}</div>
                                    <a href="{{ route('psychotest.runner', $invitation->token) }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-blue-600">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                        Buka link kandidat
                                    </a>
                                    <div class="mt-1 text-xs text-gray-500">Channel: {{ ucwords(str_replace('_', ' ', $invitation->delivery_channel)) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $invitation->candidate?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $invitation->candidate?->candidate_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $invitation->candidate?->manpowerRequest?->position_title ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $invitation->package?->package_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $invitation->package?->package_code }}</div>
                                    <div class="text-xs text-gray-500">{{ $invitation->package?->provider?->provider_name }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ strtoupper(str_replace('_', ' ', $invitation->status)) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">Sent: {{ $invitation->sent_at?->format('d M Y H:i') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Consent: {{ $invitation->consented_at?->format('d M Y H:i') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Due: {{ $invitation->expires_at?->format('d M Y H:i') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Sync</div>
                                    <div class="font-semibold text-gray-900">{{ strtoupper(str_replace('_', ' ', $invitation->result_sync_status)) }}</div>
                                    @if($invitation->result)
                                        <div class="mt-2 text-2xl font-semibold text-blue-600">{{ $invitation->result->overall_score }}</div>
                                        <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $invitation->result->fit_recommendation)) }} / {{ ucwords($invitation->result->risk_level) }}</div>
                                    @else
                                        <div class="mt-2 text-xs text-gray-500">Belum ada hasil.</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada undangan psikotest.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
