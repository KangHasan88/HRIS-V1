<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Psikotest Kandidat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-4 py-8">
        <div class="w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 border-b border-gray-100 pb-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        <i class="fas fa-brain"></i>
                        Internal Psikotest
                    </div>
                    <h1 class="mt-3 text-2xl font-semibold text-gray-900">{{ $invitation->package?->package_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Halo {{ $invitation->candidate?->full_name }}, silakan baca instruksi dan berikan consent sebelum mulai.</p>
                </div>
                <div class="text-sm text-gray-500">
                    <div class="font-mono text-gray-700">{{ $invitation->invitation_number }}</div>
                    <div>Expired: {{ $invitation->expires_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
            </div>

            @if (session('success'))
                <div class="mt-5 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
            @endif

            @if($isExpired)
                <div class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">Link psikotest sudah expired. Silakan hubungi HR untuk undangan baru.</div>
            @else
                <div class="mt-5 grid gap-5 lg:grid-cols-[.9fr_1.1fr]">
                    <section class="rounded-xl border border-gray-200 bg-white p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Instruksi</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ $invitation->instruction ?: 'Kerjakan test di tempat tenang, gunakan koneksi stabil, dan pastikan jawaban diberikan secara mandiri.' }}</p>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="rounded-lg bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Durasi</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $invitation->package?->estimated_minutes }} menit</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Status</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ strtoupper(str_replace('_', ' ', $invitation->status)) }}</div>
                            </div>
                        </div>

                        @unless($invitation->consented_at)
                            <form action="{{ route('psychotest.consent', $invitation->token) }}" method="POST" class="mt-5">
                                @csrf
                                <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                                    <input type="checkbox" name="candidate_consent" value="1" required class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Saya menyetujui pemrosesan data psikotest untuk kebutuhan rekrutmen dan memahami hasil akan direview oleh HR.
                                </label>
                                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    <i class="fas fa-check"></i>
                                    Setujui & Mulai
                                </button>
                            </form>
                        @else
                            <div class="mt-5 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">Consent sudah diterima pada {{ $invitation->consented_at->format('d M Y H:i') }}.</div>
                        @endunless
                    </section>

                    <section class="rounded-xl border border-gray-200 bg-white p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Submit Hasil Internal</h2>
                        <p class="mt-1 text-sm text-gray-500">Preview ASSESS-002: hasil disubmit dari internal engine dan tersinkron ke ATS.</p>
                        <form action="{{ route('psychotest.submit-result', $invitation->token) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Overall Score</label>
                                    <input type="number" name="overall_score" value="{{ $invitation->result?->overall_score ?? 82 }}" min="0" max="100" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @disabled(! $invitation->consented_at)>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rekomendasi</label>
                                    <select name="fit_recommendation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @disabled(! $invitation->consented_at)>
                                        <option value="strong_fit">Strong Fit</option>
                                        <option value="fit">Fit</option>
                                        <option value="conditional_fit">Conditional Fit</option>
                                        <option value="need_review">Need Review</option>
                                        <option value="not_fit">Not Fit</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Risk Level</label>
                                    <select name="risk_level" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @disabled(! $invitation->consented_at)>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skor Dimensi</label>
                                <input name="dimension_scores" placeholder="integritas:85, teamwork:80, problem solving:82" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @disabled(! $invitation->consented_at)>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Ringkasan</label>
                                <textarea name="summary" rows="4" placeholder="Ringkasan hasil, kekuatan, risiko, dan rekomendasi follow-up interview." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" @disabled(! $invitation->consented_at)>{{ $invitation->result?->summary }}</textarea>
                            </div>
                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:bg-gray-300" @disabled(! $invitation->consented_at)>
                                <i class="fas fa-cloud-arrow-up"></i>
                                Submit & Sinkron ATS
                            </button>
                        </form>
                    </section>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
