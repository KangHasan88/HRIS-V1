@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kalender Libur</h1>
            <p class="mt-1 text-sm text-gray-500">Atur libur nasional, cuti bersama, hari besar agama, wilayah, perusahaan, dan site.</p>
        </div>
        <form method="GET" action="{{ route('attendance.holidays') }}" class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tahun</span>
                <input name="year" value="{{ $year }}" class="mt-1 w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Company</span>
                <select name="company_id" class="mt-1 w-52 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-filter"></i>
                Terapkan
            </button>
        </form>
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
                ['label' => 'Total Libur', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Hitung Absensi', 'value' => $summary['attendance'], 'color' => 'text-green-600'],
                ['label' => 'Hitung Cuti', 'value' => $summary['leave'], 'color' => 'text-yellow-600'],
                ['label' => 'Dampak Payroll', 'value' => $summary['payroll'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Kalender Libur</h2>
                @include('partials.form-help', [
                    'title' => 'Panduan Kalender Libur',
                    'description' => 'Form ini dipakai untuk mendaftarkan hari libur yang berdampak ke absensi, cuti, payroll, dan ESS.',
                    'steps' => [
                        'Isi nama libur, tanggal, scope berlaku, dan kategori libur.',
                        'Pilih perusahaan atau cabang/site jika scope bukan nasional.',
                        'Isi agama atau wilayah jika libur hanya berlaku untuk konteks tertentu.',
                        'Pilih dampak libur: absensi, saldo cuti, dan/atau payroll.',
                    ],
                    'notes' => [
                        'Scope Nasional otomatis berlaku untuk semua perusahaan dan site.',
                        'Flag dampak payroll dipakai jika libur mempengaruhi perhitungan upah/lembur.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Scope menentukan apakah libur berlaku nasional, perusahaan tertentu, atau cabang/site tertentu.</p>
        </div>
        <form action="{{ route('attendance.holidays.store') }}" method="POST" class="mt-5 space-y-5">
            @csrf
            <div>
                <div class="mb-3 flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-gray-900">Data Libur</h3>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>
                <div class="grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Libur</label>
                        <input name="name" required placeholder="Contoh: Hari Kemerdekaan Republik Indonesia" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Libur</label>
                        <input type="date" name="holiday_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Berlaku</label>
                        <select name="scope" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach($scopeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori Libur</label>
                        <select name="category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach($categoryOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-3 flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-gray-900">Scope & Konteks</h3>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>
                <div class="grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                        <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Kosongkan jika scope nasional</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                        <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Opsional untuk scope cabang/site</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Agama</label>
                        <select name="religion" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Tidak spesifik agama</option>
                            @foreach($religionOptions as $religion)
                                <option value="{{ $religion }}">{{ $religion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Wilayah</label>
                        <input name="region" placeholder="Contoh: Morowali / Sulawesi Tengah" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Operasional</label>
                    <textarea name="notes" rows="2" placeholder="Catatan dampak operasional, pengecualian site, atau instruksi payroll." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="grid flex-1 gap-3 md:grid-cols-3">
                    <label class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="affects_attendance" value="1" checked class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block font-medium text-gray-800">Hitung sebagai hari libur absensi</span>
                            <span class="mt-0.5 block text-xs text-gray-500">Tidak dianggap alpa/telat jika tidak clock-in.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="affects_leave" value="1" checked class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block font-medium text-gray-800">Tidak potong saldo cuti</span>
                            <span class="mt-0.5 block text-xs text-gray-500">Cuti yang melewati tanggal ini tidak mengurangi saldo.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="affects_payroll" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            <span class="block font-medium text-gray-800">Aktifkan aturan payroll libur</span>
                            <span class="mt-0.5 block text-xs text-gray-500">Dipakai untuk lembur/upah khusus hari libur.</span>
                        </span>
                    </label>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Kalender
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kalender {{ $year }}</h2>
            <p class="text-sm text-gray-500">Data ini menjadi referensi attendance, cuti, payroll, dan ESS mobile.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Nama Libur</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Dampak</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($holidays as $holiday)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $holiday->holiday_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->holiday_date->translatedFormat('l') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $holiday->name }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->notes ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $scopeOptions[$holiday->scope] ?? $holiday->scope }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->branch?->code ?: ($holiday->company?->code ?: 'Semua company') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $categoryOptions[$holiday->category] ?? $holiday->category }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->religion ?: $holiday->region ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($holiday->affects_attendance)<span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-600">Absensi</span>@endif
                                    @if($holiday->affects_leave)<span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-600">Cuti</span>@endif
                                    @if($holiday->affects_payroll)<span class="rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-600">Payroll</span>@endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $holiday->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500' }} px-2 py-1 text-xs font-semibold">
                                    {{ $holiday->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kalender libur untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
