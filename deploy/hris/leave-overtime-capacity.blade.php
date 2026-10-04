@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Prediksi Kapasitas Cuti & Lembur</h1>
            <p class="mt-1 text-sm text-gray-500">Forecast risiko kekurangan kapasitas kerja sebelum approval cuti atau lembur besar.</p>
        </div>
        <a href="{{ route('analytics.workforce-planning') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-people-arrows"></i> Workforce Planning
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
                ['label' => 'Karyawan Aktif', 'value' => $summary['active_employee'], 'color' => 'text-gray-900', 'suffix' => ''],
                ['label' => 'Cuti Pending/Approved', 'value' => number_format((float) $summary['pending_leave_days'], 1, ',', '.'), 'color' => 'text-blue-600', 'suffix' => ' hari'],
                ['label' => 'Lembur Pending/Approved', 'value' => number_format((float) $summary['pending_overtime_hours'], 1, ',', '.'), 'color' => 'text-amber-600', 'suffix' => ' jam'],
                ['label' => 'Risiko Tinggi', 'value' => $summary['high_risk'], 'color' => 'text-red-600', 'suffix' => ' prediksi'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}<span class="text-sm font-medium text-gray-400">{{ $item['suffix'] }}</span></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Buat Prediksi</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai prediksi kapasitas',
                    'description' => 'Form ini membantu HR dan atasan melihat apakah rencana cuti dan lembur masih aman terhadap minimum kapasitas tim.',
                    'steps' => [
                        'Tentukan periode prediksi dan scope perusahaan/site/departemen.',
                        'Isi headcount yang tersedia dan minimum orang yang harus tetap masuk.',
                        'Tambahkan estimasi cuti tambahan dan lembur tambahan yang belum tercatat di sistem.',
                        'Sistem menghitung gap kapasitas, risiko, dan rekomendasi tindakan.',
                    ],
                    'notes' => [
                        'Cuti/lembur yang sudah submitted atau approved otomatis ikut dihitung.',
                        'Hasil prediksi dipakai untuk review kapasitas, bukan pengganti approval workflow.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Input ringkas untuk membaca risiko coverage tim dalam periode tertentu.</p>
        </div>

        <form action="{{ route('analytics.leave-overtime-capacity.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Identitas Prediksi</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Prediksi</label>
                        <input name="prediction_number" required placeholder="Contoh: CAP-083-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Prediksi</label>
                        <input name="prediction_name" required placeholder="Contoh: Libur panjang site A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Periode</label>
                        <input type="date" name="period_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Periode</label>
                        <input type="date" name="period_end" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Scope & Kapasitas</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
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
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Departemen</label>
                        <input name="department" placeholder="Opsional, contoh: Produksi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Headcount Tersedia</label>
                        <input type="number" name="planned_headcount" value="12" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Minimum Harus Masuk</label>
                        <input type="number" name="minimum_required_headcount" value="10" min="0" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Forecast Cuti Tambahan</label>
                        <input type="number" name="forecast_leave_days" value="8" min="0" step="0.5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Forecast Lembur Tambahan</label>
                        <input type="number" name="forecast_overtime_hours" value="24" min="0" step="0.5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <input name="notes" placeholder="Contoh: antisipasi long weekend." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-chart-line"></i> Hitung Prediksi
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Hasil Prediksi</h2>
            <p class="text-sm text-gray-500">Ringkasan kapasitas, gap, estimasi lembur, dan rekomendasi tindakan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Prediksi</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Kapasitas</th>
                        <th class="px-5 py-3">Cuti & Lembur</th>
                        <th class="px-5 py-3">Risiko</th>
                        <th class="px-5 py-3">Arahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($predictions as $prediction)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $prediction->prediction_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $prediction->prediction_number }}</div>
                                <div class="text-xs text-gray-500">{{ $prediction->department ?: 'Semua departemen' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                <div>{{ $prediction->period_start->format('d M Y') }}</div>
                                <div>s/d {{ $prediction->period_end->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                <div>Estimasi: {{ number_format((float) $prediction->estimated_capacity_days, 1, ',', '.') }} hari</div>
                                <div>Tersedia: {{ number_format((float) $prediction->projected_available_days, 1, ',', '.') }} hari</div>
                                <div>Gap: {{ number_format((float) $prediction->capacity_gap_days, 1, ',', '.') }} hari</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                <div>Cuti existing: {{ number_format((float) $prediction->existing_leave_days, 1, ',', '.') }} hari</div>
                                <div>Cuti forecast: {{ number_format((float) $prediction->forecast_leave_days, 1, ',', '.') }} hari</div>
                                <div>Lembur perlu: {{ number_format((float) $prediction->overtime_needed_hours, 1, ',', '.') }} jam</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $riskClass = [
                                        'critical' => 'bg-red-50 text-red-700',
                                        'high' => 'bg-orange-50 text-orange-700',
                                        'medium' => 'bg-amber-50 text-amber-700',
                                        'low' => 'bg-green-50 text-green-700',
                                    ][$prediction->risk_level] ?? 'bg-gray-50 text-gray-700';
                                @endphp
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $riskClass }}">{{ ucfirst($prediction->risk_level) }}</span>
                                <div class="mt-2 text-xs text-gray-500">Score {{ number_format((float) $prediction->risk_score, 1, ',', '.') }}%</div>
                            </td>
                            <td class="px-5 py-4">
                                <ul class="space-y-1 text-xs text-gray-600">
                                    @foreach(($prediction->recommendation_snapshot['actions'] ?? []) as $action)
                                        <li>{{ $action }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada prediksi kapasitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
