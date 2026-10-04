@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Bank Soal Psikotest</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola kategori, dimensi, soal, opsi skoring, dan template battery internal.</p>
        </div>
        <a href="{{ route('recruitment.psychotest-invitations') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-paper-plane"></i>
            Undangan Psikotest
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
                ['label' => 'Kategori', 'value' => $summary['categories'], 'color' => 'text-gray-900', 'hint' => 'Domain test'],
                ['label' => 'Dimensi', 'value' => $summary['dimensions'], 'color' => 'text-blue-600', 'hint' => 'Kompetensi'],
                ['label' => 'Total Soal', 'value' => $summary['questions'], 'color' => 'text-blue-600', 'hint' => 'Question bank'],
                ['label' => 'Soal Aktif', 'value' => $summary['active_questions'], 'color' => 'text-green-600', 'hint' => 'Bisa dipakai'],
                ['label' => 'Battery', 'value' => $summary['templates'], 'color' => 'text-amber-600', 'hint' => 'Template test'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Kategori & Dimensi</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Kategori & Dimensi',
                        'description' => 'Kategori adalah domain test, misalnya personality atau cognitive. Dimensi adalah kompetensi yang akan diukur di dalam kategori.',
                        'steps' => [
                            'Buat kategori sesuai domain psikotest.',
                            'Tambahkan dimensi yang akan dinilai, misalnya integritas atau problem solving.',
                            'Gunakan scoring direction reverse untuk item yang skornya dibalik.',
                        ],
                        'notes' => ['Dimensi aktif akan tersedia saat membuat soal dan template battery.'],
                    ])
                </div>
                <p class="text-sm text-gray-500">Struktur dasar agar hasil test bisa dibaca per kompetensi.</p>
            </div>

            <form action="{{ route('recruitment.psychotest-question-bank.categories') }}" method="POST" class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
                @csrf
                <h3 class="text-sm font-semibold text-gray-900">Tambah Kategori</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Kategori</label>
                        <input name="category_code" required placeholder="Contoh: PSY-CAT-PER" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Kategori</label>
                        <input name="category_name" required placeholder="Contoh: Personality" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Domain</label>
                        <select name="assessment_domain" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="personality">Personality</option>
                            <option value="cognitive">Cognitive</option>
                            <option value="integrity">Integrity</option>
                            <option value="leadership">Leadership</option>
                            <option value="technical">Technical</option>
                            <option value="culture_fit">Culture Fit</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner</label>
                        <input name="owner_name" placeholder="PIC HR/psikolog" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <textarea name="description" rows="2" placeholder="Deskripsi kategori dan cakupan test." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <label class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Kategori aktif
                </label>
                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-folder-plus"></i>
                    Simpan Kategori
                </button>
            </form>

            <form action="{{ route('recruitment.psychotest-question-bank.dimensions') }}" method="POST" class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                @csrf
                <h3 class="text-sm font-semibold text-gray-900">Tambah Dimensi</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                        <select name="psychotest_question_category_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_code }} - {{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Dimensi</label>
                        <input name="dimension_code" required placeholder="Contoh: PSY-DIM-INT" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Dimensi</label>
                        <input name="dimension_name" required placeholder="Contoh: Integritas" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Arah Skoring</label>
                        <select name="scoring_direction" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="positive">Positive</option>
                            <option value="reverse">Reverse</option>
                            <option value="neutral">Neutral</option>
                            <option value="weighted">Weighted</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bobot</label>
                        <input type="number" name="weight_percent" value="100" min="1" max="100" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <textarea name="interpretation_note" rows="2" placeholder="Catatan interpretasi dimensi." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <label class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Dimensi aktif
                </label>
                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-chart-simple"></i>
                    Simpan Dimensi
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Soal & Template Battery</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Soal & Battery',
                        'description' => 'Soal berisi pertanyaan dan opsi skoring. Template battery menggabungkan beberapa section soal ke dalam satu paket test.',
                        'steps' => [
                            'Pilih dimensi lalu buat soal beserta opsi jawaban dan skor.',
                            'Aktifkan soal jika sudah siap dipakai.',
                            'Buat template battery yang terhubung ke paket psikotest aktif.',
                        ],
                        'notes' => ['Versioning dipakai agar histori hasil test lama tetap valid walaupun soal berubah.'],
                    ])
                </div>
                <p class="text-sm text-gray-500">Soal aktif akan dipakai oleh runner internal pada backlog berikutnya.</p>
            </div>

            <form action="{{ route('recruitment.psychotest-question-bank.questions') }}" method="POST" class="mt-5 rounded-xl border border-gray-200 bg-white p-4">
                @csrf
                <h3 class="text-sm font-semibold text-gray-900">Tambah Soal</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dimensi</label>
                        <select name="psychotest_dimension_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih dimensi</option>
                            @foreach($dimensions as $dimension)
                                <option value="{{ $dimension->id }}">{{ $dimension->dimension_code }} - {{ $dimension->dimension_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Soal</label>
                        <input name="question_code" required placeholder="Contoh: PSY-Q-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Soal</label>
                        <select name="question_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="likert">Likert</option>
                            <option value="single_choice">Pilihan Tunggal</option>
                            <option value="true_false">Benar/Salah</option>
                            <option value="priority_order">Urutan Prioritas</option>
                            <option value="case_study">Studi Kasus</option>
                        </select>
                    </div>
                </div>
                <textarea name="question_text" rows="3" required placeholder="Tulis pertanyaan atau statement psikotest." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <div class="mt-3 grid gap-3 sm:grid-cols-4">
                    <input name="option_codes[]" placeholder="A" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="option_texts[]" placeholder="Sangat tidak setuju" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="option_scores[]" type="number" step="0.01" placeholder="1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="option_codes[]" placeholder="B" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="option_texts[]" placeholder="Tidak setuju" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 sm:col-span-2">
                    <input name="option_scores[]" type="number" step="0.01" placeholder="2" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-4">
                    <input name="instruction" placeholder="Instruksi khusus" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 sm:col-span-2">
                    <input type="number" name="time_limit_seconds" placeholder="Detik" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="number" name="version" value="1" min="1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="difficulty_level" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="normal">Normal</option>
                        <option value="easy">Easy</option>
                        <option value="hard">Hard</option>
                        <option value="expert">Expert</option>
                    </select>
                    <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="retired">Retired</option>
                    </select>
                    <input name="scoring_key" placeholder="scoring key, pisahkan koma" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 sm:col-span-2">
                </div>
                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-list-check"></i>
                    Simpan Soal
                </button>
            </form>

            <form action="{{ route('recruitment.psychotest-question-bank.templates') }}" method="POST" class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                @csrf
                <h3 class="text-sm font-semibold text-gray-900">Tambah Template Battery</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Paket Psikotest</label>
                        <select name="psychotest_package_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih paket</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->package_code }} - {{ $package->package_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input name="template_code" required placeholder="Kode template, contoh: BAT-STF-001" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="template_name" required placeholder="Nama template battery" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="target_role_level" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="staff">Staff</option>
                        <option value="operator">Operator</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="manager">Manager</option>
                        <option value="executive">Executive</option>
                        <option value="all_level">Semua level</option>
                    </select>
                    <input type="number" name="total_duration_minutes" value="60" min="5" max="480" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="number" name="version" value="1" min="1" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <input name="section_config" placeholder="Section config, contoh: personality:20, cognitive:15" class="mt-3 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="instruction" rows="2" placeholder="Instruksi umum template battery." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-layer-group"></i>
                    Simpan Template
                </button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Soal</h2>
                <p class="text-sm text-gray-500">Soal aktif, versi, dimensi, dan jumlah opsi.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Soal</th>
                            <th class="px-5 py-3">Dimensi</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($questions as $question)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $question->question_code }}</div>
                                    <p class="mt-1 max-w-sm text-xs text-gray-500">{{ $question->question_text }}</p>
                                    <div class="mt-1 text-xs text-gray-400">{{ $question->options->count() }} opsi / v{{ $question->version }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $question->dimension?->dimension_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $question->dimension?->category?->category_name }}</div>
                                </td>
                                <td class="px-5 py-4">{{ ucwords(str_replace('_', ' ', $question->question_type)) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ strtoupper($question->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada soal psikotest.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Template Battery</h2>
                <p class="text-sm text-gray-500">Template yang mengikat paket test dan section config.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Template</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Durasi</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($templates as $template)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $template->template_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $template->template_code }} / v{{ $template->version }}</div>
                                    <div class="text-xs text-gray-500">{{ implode(', ', $template->section_config ?? []) ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $template->package?->package_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $template->package?->provider?->provider_name }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $template->total_duration_minutes }} menit</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ strtoupper($template->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada template battery.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
