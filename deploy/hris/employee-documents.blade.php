@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dokumen Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Arsip dokumen, versi, metadata, OCR text, dan reminder masa berlaku.</p>
        </div>
        <a href="{{ route('hris.employees') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users"></i>
            Data Karyawan
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
                ['label' => 'Total', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Reminder 30 Hari', 'value' => $summary['expiring'], 'color' => 'text-red-600'],
                ['label' => 'OCR/Metadata', 'value' => $summary['ocr_ready'], 'color' => 'text-purple-600'],
                ['label' => 'Versi', 'value' => $summary['versions'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.8fr_1.2fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Dokumen</h2>
                <p class="text-sm text-gray-500">Gunakan dokumen induk untuk membuat versi baru.</p>
            </div>
            <form action="{{ route('hris.employee-documents.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->company?->code ?? '-' }}</option>
                    @endforeach
                </select>
                <select name="parent_document_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Dokumen induk opsional</option>
                    @foreach($documentOptions as $document)
                        <option value="{{ $document->id }}">{{ $document->document_number }} - {{ $document->title }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="document_number" required placeholder="Nomor dokumen" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="document_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="ktp">KTP</option>
                        <option value="npwp">NPWP</option>
                        <option value="contract">Kontrak</option>
                        <option value="certificate">Sertifikat</option>
                        <option value="bpjs">BPJS</option>
                        <option value="medical">Medical</option>
                        <option value="education">Pendidikan</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <input name="title" required placeholder="Judul dokumen" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="draft">Draft</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
                        <option value="archived">Archived</option>
                    </select>
                    <select name="ocr_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="manual">Manual</option>
                        <option value="queued">Queued</option>
                        <option value="processed">Processed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input type="date" name="issued_at" title="Tanggal terbit" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="expired_at" title="Tanggal expired" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="reminder_date" title="Tanggal reminder" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="file_reference" placeholder="Referensi file / nomor arsip" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="storage_path" placeholder="Path storage / link file" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="metadata_tags" placeholder="Tag metadata, pisahkan koma" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="ocr_text" rows="4" placeholder="OCR/manual text" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <textarea name="notes" rows="2" placeholder="Catatan" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Dokumen
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Dokumen</h2>
                <p class="text-sm text-gray-500">Tracking arsip, versi, metadata, dan reminder.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Dokumen</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Masa Berlaku</th>
                            <th class="px-5 py-3">OCR</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($documents as $document)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $document->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $document->document_number }} / V{{ $document->version }} / {{ strtoupper($document->document_type) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $document->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $document->employeeProfile?->employee_number }} / {{ $document->employeeProfile?->company?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $document->issued_at?->format('d M Y') ?? '-' }} - {{ $document->expired_at?->format('d M Y') ?? 'Tidak terbatas' }}
                                    <div class="text-xs text-gray-500">Reminder: {{ $document->reminder_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ ucfirst($document->ocr_status) }}
                                    <div class="max-w-xs truncate text-xs text-gray-500">{{ $document->metadata['ocr_excerpt'] ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($document->status) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $document->file_reference ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada dokumen karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
