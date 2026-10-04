@extends('layouts.sidebar')

@section('title', 'Asisten Dokumen HR')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Asisten Dokumen HR</h1>
            <p class="mt-1 text-sm text-gray-500">Tanya kebijakan, cek sumber dokumen, dan buat draft surat yang tetap bisa direview HR.</p>
        </div>
        <a href="{{ route('hris.employee-documents') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-folder-open mr-2"></i> Dokumen Karyawan
        </a>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Sumber Terbaca', 'value' => $summary['knowledge_items'], 'color' => 'text-blue-600', 'sub' => 'Evidence aktif'],
                ['label' => 'Kebijakan Cuti', 'value' => $summary['leave_policies'], 'color' => 'text-green-600', 'sub' => 'Policy aktif'],
                ['label' => 'Dokumen Employee', 'value' => $summary['employee_documents'], 'color' => 'text-gray-900', 'sub' => 'Record dokumen'],
                ['label' => 'Kontrak 60 Hari', 'value' => $summary['contract_expiring'], 'color' => $summary['contract_expiring'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Butuh review'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Tanya Asisten</h2>
                <p class="text-sm text-gray-500">Jawaban ditampilkan dengan sumber dan tingkat keyakinan agar HR bisa audit.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Modul ini membantu HR mencari jawaban awal dari data kebijakan dan dokumen yang sudah ada. Hasilnya bukan keputusan final. HR tetap perlu review sebelum mengirim surat, menjawab kasus sensitif, atau mengambil keputusan ketenagakerjaan.
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('analytics.hr-document-assistant') }}" class="mt-5 space-y-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pertanyaan HR</label>
                <input name="question" value="{{ $filters['question'] }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Apa syarat cuti sakit dan lampirannya?">
            </div>
            <div class="grid gap-4 lg:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan Opsional</label>
                    <select name="employee_id" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tanpa konteks karyawan</option>
                        @foreach($employees as $item)
                            <option value="{{ $item->id }}" @selected((int) $filters['employee_id'] === $item->id)>{{ $item->employee_number }} - {{ $item->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Draft</label>
                    <select name="draft_type" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach([
                            'work_certificate' => 'Surat keterangan kerja',
                            'warning_letter' => 'Surat pembinaan/peringatan',
                            'leave_confirmation' => 'Konfirmasi cuti',
                            'document_request' => 'Permintaan dokumen',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['draft_type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gaya Bahasa</label>
                    <select name="tone" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="formal" @selected($filters['tone'] === 'formal')>Formal</option>
                        <option value="friendly" @selected($filters['tone'] === 'friendly')>Humanis</option>
                        <option value="concise" @selected($filters['tone'] === 'concise')>Ringkas</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-wand-magic-sparkles mr-2"></i> Generate
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($quickQuestions as $quick)
                <a href="{{ route('analytics.hr-document-assistant', array_merge($filters, ['question' => $quick])) }}" class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-white">
                    {{ $quick }}
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Jawaban & Sumber</h2>
                <p class="text-sm text-gray-500">Ringkasan ini dibuat dari data internal yang tersedia.</p>
            </div>
            <div class="p-5">
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $answer['headline'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ $answer['body'] }}</p>
                        </div>
                        <div class="shrink-0 rounded-lg bg-white px-3 py-2 text-center">
                            <div class="text-xl font-semibold text-blue-600">{{ $answer['confidence'] }}%</div>
                            <div class="text-xs text-gray-400">confidence</div>
                        </div>
                    </div>
                    <div class="mt-3 rounded-lg bg-white px-3 py-2 text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">Next step:</span> {{ $answer['next_step'] }}
                    </div>
                    <div class="mt-2 text-xs leading-5 text-amber-700">{{ $answer['disclaimer'] }}</div>
                </div>

                <div class="mt-5 divide-y divide-gray-100 rounded-xl border border-gray-200">
                    @foreach($knowledgeItems as $item)
                        <div class="grid gap-3 p-4 md:grid-cols-[1fr_auto] md:items-center">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['source'] }}</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $item['title'] }}</div>
                                <div class="mt-1 text-sm leading-5 text-gray-500">{{ $item['evidence'] }}</div>
                                <div class="mt-2 text-xs text-gray-500">{{ $item['action'] }}</div>
                            </div>
                            <div class="text-left md:text-right">
                                <div class="text-lg font-semibold {{ $item['confidence'] >= 80 ? 'text-green-600' : ($item['confidence'] >= 60 ? 'text-blue-600' : 'text-amber-600') }}">{{ $item['confidence'] }}%</div>
                                <div class="text-xs text-gray-400">relevansi</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Draft Surat</h2>
                <p class="text-sm text-gray-500">Draft awal untuk diedit sebelum dipakai sebagai dokumen resmi.</p>
            </div>
            <div class="p-5">
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $draft['tone'] }}</div>
                            <h3 class="mt-1 font-semibold text-gray-900">{{ $draft['title'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $draft['subject'] }}</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">Draft</span>
                    </div>
                    <div class="mt-4 rounded-lg bg-gray-50 p-4 text-sm leading-7 text-gray-700">
                        {{ $draft['body'] }}
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-gray-200 p-4">
                    <h3 class="font-semibold text-gray-900">Checklist Sebelum Dipakai</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($draft['checklist'] as $check)
                            <div class="flex gap-2 text-sm text-gray-600">
                                <i class="fas fa-check-circle mt-0.5 text-green-600"></i>
                                <span>{{ $check }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($employee)
                    <div class="mt-5 rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900">Konteks Karyawan</h3>
                        <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nama</div>
                                <div class="font-medium text-gray-900">{{ $employee->full_name }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Posisi</div>
                                <div class="font-medium text-gray-900">{{ $employee->position ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Company</div>
                                <div class="font-medium text-gray-900">{{ $employee->company?->code ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Site</div>
                                <div class="font-medium text-gray-900">{{ $employee->branch?->code ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
