@extends('layouts.sidebar')

@section('content')
<div class="space-y-6 ess-webview-page">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Survey Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Isi pulse check, eNPS, engagement survey, dan feedback anonim dari ESS.</p>
        </div>
        <a href="{{ route('ess.flow-center') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-route"></i> Alur ESS
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif
    @if(! $employee)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-800">
            Akun login belum terhubung ke profil karyawan. Survey tetap siap, tapi submit baru aktif setelah HR/admin menghubungkan user ke Employee Profile.
        </div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Survey Aktif', 'value' => $summary['available'], 'hint' => 'Bisa diisi', 'color' => 'text-blue-600'],
                ['label' => 'Sudah Submit', 'value' => $summary['submitted'], 'hint' => 'Riwayat personal', 'color' => 'text-green-600'],
                ['label' => 'Anonim', 'value' => $summary['anonymous'], 'hint' => 'Tanpa identitas karyawan', 'color' => 'text-amber-600'],
                ['label' => 'Avg eNPS Saya', 'value' => $summary['avg_enps'], 'hint' => 'Skor 0-10', 'color' => 'text-blue-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,.9fr)]">
        <section class="space-y-4">
            @forelse($surveys as $survey)
                <article class="panel rounded-2xl p-5">
                    <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-gray-900">{{ $survey->title }}</h2>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-600">{{ strtoupper($survey->survey_type) }}</span>
                                @if($survey->is_anonymous)
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Anonim</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $survey->notes ?: 'Isi survey sesuai kondisi yang kamu rasakan saat ini.' }}</p>
                        </div>
                        <div class="text-left text-xs text-gray-500 sm:text-right">
                            <div>{{ $survey->survey_number }}</div>
                            <div>{{ $survey->start_date?->format('d M Y') ?: 'Mulai bebas' }} - {{ $survey->end_date?->format('d M Y') ?: 'Tanpa batas' }}</div>
                        </div>
                    </div>

                    @if($survey->question_bank)
                        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-sm font-semibold text-gray-900">Pertanyaan Panduan</div>
                            <div class="mt-3 grid gap-2 md:grid-cols-2">
                                @foreach($survey->question_bank as $question)
                                    <div class="rounded-lg bg-white px-3 py-2 text-sm text-gray-600">{{ $question }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('ess.surveys.responses', $survey) }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Engagement Score</label>
                                <input type="number" name="engagement_score" min="0" max="100" value="75" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <p class="mt-1 text-xs text-gray-500">0 sangat rendah, 100 sangat baik.</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">eNPS</label>
                                <input type="number" name="enps_score" min="0" max="10" value="8" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <p class="mt-1 text-xs text-gray-500">0 tidak merekomendasikan, 10 sangat merekomendasikan.</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sentiment</label>
                                <select name="sentiment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="positive">Positive</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="mixed">Mixed</option>
                                    <option value="negative">Negative</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Pilih nuansa jawaban utama.</p>
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skor Dimensi Opsional</label>
                                <input name="dimension_scores" placeholder="Contoh: workload:70, atasan:80, fasilitas:75" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Feedback</label>
                                <textarea name="comment" rows="3" placeholder="Tulis feedback yang ingin disampaikan ke HR. Untuk survey anonim, identitas karyawan tidak disimpan di response." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button @disabled(! $employee) class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                                <i class="fas fa-paper-plane"></i> Kirim Survey
                            </button>
                        </div>
                    </form>
                </article>
            @empty
                <div class="panel rounded-2xl p-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <h2 class="mt-4 text-lg font-semibold text-gray-900">Belum Ada Survey Aktif</h2>
                    <p class="mt-1 text-sm text-gray-500">Nanti survey aktif dari HR akan muncul di sini.</p>
                </div>
            @endforelse
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Response</h2>
                <p class="text-sm text-gray-500">Response non-anonim yang terhubung ke profil kamu.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($responses as $response)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $response->survey?->title }}</div>
                                <div class="mt-1 font-mono text-xs text-gray-500">{{ $response->response_number }}</div>
                            </div>
                            <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($response->status) }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Score</div>
                                <div class="text-lg font-semibold text-blue-600">{{ $response->engagement_score }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">eNPS</div>
                                <div class="text-lg font-semibold text-green-600">{{ $response->enps_score }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Sentiment</div>
                                <div class="text-sm font-semibold text-gray-700">{{ ucfirst($response->sentiment) }}</div>
                            </div>
                        </div>
                        @if($response->comment)
                            <p class="mt-3 text-sm leading-5 text-gray-500">{{ $response->comment }}</p>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada riwayat response personal.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
