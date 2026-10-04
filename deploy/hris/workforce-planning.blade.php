@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Workforce Planning</h1>
            <p class="mt-1 text-sm text-gray-500">Simulator hiring, lembur, dan skenario biaya untuk keputusan kapasitas tim.</p>
        </div>
        <a href="{{ route('manager.budget') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-chart-pie"></i> Budget Tim
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Karyawan Aktif', 'value' => $summary['active_employee'], 'color' => 'text-gray-900'],
                ['label' => 'Open Manpower', 'value' => $summary['open_manpower'], 'color' => 'text-blue-600'],
                ['label' => 'Simulasi', 'value' => $summary['simulations'], 'color' => 'text-indigo-600'],
                ['label' => 'Rekomendasi Hybrid', 'value' => $summary['hybrid'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Buat Simulasi</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai simulator workforce',
                    'description' => 'Simulator ini membandingkan biaya jika kebutuhan kapasitas dipenuhi dengan hiring penuh, lembur penuh, atau kombinasi keduanya.',
                    'steps' => [
                        'Isi headcount saat ini dan kebutuhan headcount ideal.',
                        'Isi berapa orang yang realistis direkrut dalam skenario hybrid.',
                        'Masukkan asumsi gaji bulanan, rate lembur, jam lembur per minggu, dan horizon bulan.',
                        'Sistem menghitung estimasi biaya dan merekomendasikan skenario biaya paling rendah.',
                    ],
                    'notes' => [
                        'Ini alat simulasi keputusan, bukan final payroll calculation.',
                        'Hasilnya bisa dipakai sebagai bahan diskusi sebelum manpower request atau approval lembur besar.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Gunakan untuk membandingkan biaya kapasitas tim sebelum memutuskan hiring atau overtime.</p>
        </div>

        <form action="{{ route('analytics.workforce-planning.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Identitas Skenario</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Simulasi</label>
                        <input name="simulation_number" required placeholder="Contoh: WFP-080-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Skenario</label>
                        <input name="scenario_name" required placeholder="Contoh: Peak season site A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Departemen</label>
                        <input name="department" placeholder="Contoh: Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Bulan</label>
                        <input type="number" name="planning_months" value="3" min="1" max="60" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                        <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                        <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua cabang/site</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <input name="notes" placeholder="Catatan asumsi, risiko, atau konteks operasional." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Asumsi Kapasitas & Biaya</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Saat Ini</label>
                        <input type="number" name="current_headcount" value="10" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Dibutuhkan</label>
                        <input type="number" name="required_headcount" value="14" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Hire di Skenario Hybrid</label>
                        <input type="number" name="hire_headcount" value="2" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gaji Bulanan / Orang</label>
                        <input type="number" name="monthly_salary_budget" value="7000000" min="0" step="1" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rate Lembur / Jam</label>
                        <input type="number" name="overtime_hourly_rate" value="50000" min="0" step="1" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Lembur / Minggu</label>
                        <input type="number" name="overtime_hours_per_week" value="10" min="0" max="168" step="0.5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Hiring Cost / Orang</label>
                        <input type="number" name="hiring_cost" value="1000000" min="0" step="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Training Cost / Orang</label>
                        <input type="number" name="training_cost" value="1500000" min="0" step="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calculator"></i> Hitung Simulasi
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Hasil Simulasi</h2>
            <p class="text-sm text-gray-500">Perbandingan biaya hiring penuh, lembur penuh, dan hybrid.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Skenario</th>
                        <th class="px-5 py-3">Headcount</th>
                        <th class="px-5 py-3">Hire</th>
                        <th class="px-5 py-3">Biaya Hire</th>
                        <th class="px-5 py-3">Biaya Lembur</th>
                        <th class="px-5 py-3">Biaya Hybrid</th>
                        <th class="px-5 py-3">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($simulations as $simulation)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $simulation->scenario_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $simulation->simulation_number }}</div>
                                <div class="text-xs text-gray-500">{{ $simulation->department ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Saat ini: {{ $simulation->current_headcount }}</div>
                                <div class="text-xs text-gray-500">Butuh: {{ $simulation->required_headcount }}</div>
                                <div class="text-xs text-gray-500">Gap: {{ $simulation->assumption_snapshot['gap_headcount'] ?? 0 }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $simulation->hire_headcount }}</td>
                            <td class="px-5 py-4">Rp {{ number_format((float) $simulation->hire_scenario_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">Rp {{ number_format((float) $simulation->overtime_scenario_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">Rp {{ number_format((float) $simulation->hybrid_scenario_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-700">{{ ucfirst($simulation->recommended_scenario) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $simulation->planning_months }} bulan</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">Belum ada simulasi workforce.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
