@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Import Absensi</h1>
            <p class="mt-1 text-sm text-gray-500">Rekonsiliasi data mesin biometric, CSV, API, atau input manual sebelum masuk absensi final.</p>
        </div>
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
                ['label' => 'Batch Import', 'value' => $summary['batches'], 'color' => 'text-blue-600'],
                ['label' => 'Row Masuk', 'value' => $summary['imported'], 'color' => 'text-gray-900'],
                ['label' => 'Matched', 'value' => $summary['matched'], 'color' => 'text-green-600'],
                ['label' => 'Perlu Review', 'value' => $summary['anomaly'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.92fr_1.08fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Batch Import</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Batch Import Absensi',
                        'description' => 'Batch dipakai sebagai wadah data absensi dari satu proses import mesin, CSV, API, atau input manual.',
                        'steps' => [
                            'Isi nomor batch, sumber data, scope perusahaan/site, dan periode data.',
                            'Tambahkan kode mesin, nama mesin, atau referensi file supaya audit mudah ditelusuri.',
                            'Setelah batch dibuat, isi row import atau upload data dari connector pada tahap berikutnya.',
                        ],
                        'notes' => [
                            'Status Validated berarti data sudah masuk tetapi masih perlu dicek.',
                            'Status Reconciled berarti semua row sudah match dan aman dipakai proses absensi/payroll.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu batch mewakili satu sesi import dari mesin atau file.</p>
            </div>
            <form action="{{ route('attendance.imports.batches') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Batch</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Batch</label>
                            <input name="batch_number" required placeholder="Contoh: IMP-ABS-20260714-01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Data</label>
                            <select name="source_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="biometric_machine">Mesin biometric</option>
                                <option value="csv_import">Import CSV</option>
                                <option value="api_sync">Sinkron API</option>
                                <option value="admin_manual">Input manual HR</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Periode & Source</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Periode</label>
                            <input type="date" name="import_period_start" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Periode</label>
                            <input type="date" name="import_period_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Mesin</label>
                            <input name="machine_code" placeholder="Contoh: BIO-HO-01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Mesin</label>
                            <input name="machine_name" placeholder="Contoh: Fingerprint HO Lobby" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi File / API</label>
                            <input name="file_reference" placeholder="Nama file, URL, atau request id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Batch</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="validated">Validated</option>
                                <option value="reconciled">Reconciled</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Upload CSV</label>
                            <input type="file" name="csv_file" accept=".csv,.txt" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-blue-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Opsional. Pakai jika sumber data berupa file CSV dari mesin/vendor.</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Batch</label>
                        <textarea name="notes" rows="3" placeholder="Catatan sumber data, filter mesin, atau kendala import." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Batch
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Row Import</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Row Import Absensi',
                        'description' => 'Row adalah satu baris event absensi dari mesin atau file, misalnya clock-in atau clock-out karyawan.',
                        'steps' => [
                            'Pilih batch lalu isi nomor karyawan atau raw identifier dari mesin.',
                            'Isi tipe event dan waktu event sesuai data dari mesin.',
                            'Sistem akan coba cocokkan karyawan dan memberi status matched atau perlu review.',
                        ],
                        'notes' => [
                            'Jika karyawan tidak ditemukan, row masuk status Perlu Review.',
                            'Jika clock-out masuk tanpa clock-in pada hari yang sama, row ditandai sebagai anomali.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Dipakai untuk simulasi row atau input manual hasil import.</p>
            </div>
            <form action="{{ route('attendance.imports.rows') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Row</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Batch Import</label>
                            <select name="attendance_import_batch_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih batch import</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->batch_number }} - {{ $batch->source_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mode Pencocokan</label>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600">
                                Auto match + review anomali
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan Terhubung</label>
                            <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Auto match dari nomor karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Karyawan</label>
                            <input name="employee_number" placeholder="Contoh: KRM-0002" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama dari Mesin/File</label>
                            <input name="employee_name" placeholder="Nama yang terbaca di file import" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Raw Identifier</label>
                            <input name="raw_identifier" placeholder="PIN/NIK/ID mesin jika berbeda" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Event Absensi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Event</label>
                            <select name="event_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="clock_in">Clock-in</option>
                                <option value="clock_out">Clock-out</option>
                                <option value="break_start">Mulai istirahat</option>
                                <option value="break_end">Selesai istirahat</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu Event</label>
                            <input type="datetime-local" name="event_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Source Device</label>
                            <input name="source_device" placeholder="Contoh: BIO-HO-01 / API vendor / file CSV" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Row</label>
                        <textarea name="notes" rows="3" placeholder="Catatan koreksi, alasan anomali, atau tindak lanjut HR." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-file-import"></i>
                    Proses Row
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Batch & Row Import</h2>
            <p class="text-sm text-gray-500">Pantau hasil import, status match, dan anomali yang perlu direview HR.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Batch</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Anomali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $row->batch?->batch_number }}</div>
                                <div class="text-xs text-gray-500">{{ $row->source_device ?: $row->batch?->machine_name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $row->employee_number ?: $row->raw_identifier ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $row->employeeProfile?->full_name ?: $row->employee_name ?: 'Belum match' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ str_replace('_', ' ', $row->event_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $row->event_at?->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row->match_status === 'matched' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $row->match_status === 'matched' ? 'Matched' : 'Perlu Review' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500">
                                {{ $row->anomaly_flags ? implode(', ', $row->anomaly_flags) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada row import absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
