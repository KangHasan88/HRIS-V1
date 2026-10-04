@extends('layouts.sidebar')

@section('title', 'Wizard Payroll')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Wizard Proses Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Buat payroll run, jalankan validasi awal, dan cek anomali sebelum slip gaji dipublish.</p>
        </div>
        <a href="{{ route('payroll.salary-structures') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-lock mr-2"></i> Struktur Gaji
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Payroll Run', 'value' => $summary['runs'], 'color' => 'text-blue-600'],
                ['label' => 'Ready', 'value' => $summary['ready'], 'color' => 'text-green-600'],
                ['label' => 'Blocked', 'value' => $summary['blocked'], 'color' => 'text-red-600'],
                ['label' => 'Anomali Open', 'value' => $summary['anomalies'], 'color' => 'text-amber-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Buat Payroll Run</h2>
                <p class="text-sm text-gray-500">Satu run mewakili satu periode proses payroll sebelum generate slip dan transfer bank.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-80 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Step awal payroll: buat run, pilih periode dan cutoff, lalu klik validasi. Sistem mengecek salary assignment, rekening bank, BPJS, profil pajak, dan perubahan gross pay yang tidak wajar.
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('payroll.run-wizard.store') }}" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Data Run</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-5">
                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</span>
                        <select name="company_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</span>
                        <input name="period" type="month" required value="{{ now()->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Run</span>
                        <select name="run_type" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="regular">Regular</option>
                            <option value="thr">THR</option>
                            <option value="bonus">Bonus</option>
                            <option value="correction">Koreksi</option>
                            <option value="final_pay">Final Pay</option>
                        </select>
                    </label>
                    <div class="hidden md:block"></div>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cutoff Mulai</span>
                        <input name="cutoff_start" type="date" required value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cutoff Akhir</span>
                        <input name="cutoff_end" type="date" required value="{{ now()->endOfMonth()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <label class="block md:col-span-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</span>
                        <input name="notes" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Payroll Juli, cutoff attendance sudah final.">
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fas fa-play"></i> Buat Run
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Payroll Run</h2>
            <p class="text-sm text-gray-500">Validasi awal wajib clear sebelum run masuk tahap generate slip dan transfer.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Run</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Validasi</th>
                        <th class="px-5 py-3">Anomali</th>
                        <th class="px-5 py-3">Estimasi Net</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($runs as $run)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $run->run_number }}</div>
                                <div class="text-xs text-gray-500">{{ $run->company?->code ?? 'ALL' }} · {{ ucfirst(str_replace('_', ' ', $run->run_type)) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $run->period }}</div>
                                <div class="text-xs text-gray-500">{{ $run->cutoff_start?->format('d M') }} - {{ $run->cutoff_end?->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $run->status === 'ready' ? 'bg-green-50 text-green-700' : ($run->status === 'blocked' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($run->status) }}</span>
                                <div class="mt-1 text-xs text-gray-500">{{ $run->employee_count }} karyawan</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $run->validation_passed }} passed / {{ $run->validation_failed }} failed</div>
                                <div class="mt-1 space-y-1">
                                    @foreach($run->validations->take(3) as $validation)
                                        <div class="text-xs {{ $validation->status === 'failed' ? 'text-red-600' : 'text-green-600' }}">{{ $validation->check_name }}: {{ $validation->affected_count }}</div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold {{ $run->anomaly_count > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $run->anomaly_count }}</div>
                                @foreach($run->anomalies->take(2) as $anomaly)
                                    <div class="text-xs text-gray-500">{{ $anomaly->employee?->full_name }} · {{ number_format((float) $anomaly->variance_percent, 1) }}%</div>
                                @endforeach
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">Rp {{ number_format((float) $run->net_estimate, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('payroll.run-wizard.validate', $run) }}">
                                    @csrf
                                    <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-shield-halved"></i> Validasi
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada payroll run.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
