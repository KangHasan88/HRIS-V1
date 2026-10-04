@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Budget Tim</h1>
            <p class="mt-1 text-sm text-gray-500">Visibilitas budget manager untuk lembur, klaim, dan training tim.</p>
        </div>
        <a href="{{ route('manager.team-calendar') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calendar-days"></i> Kalender Tim
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Periode</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Periode</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Budget', 'value' => 'Rp ' . number_format($summary['allocated'], 0, ',', '.'), 'color' => 'text-gray-900'],
                ['label' => 'Realisasi', 'value' => 'Rp ' . number_format($summary['actual'], 0, ',', '.'), 'color' => 'text-blue-600'],
                ['label' => 'Sisa', 'value' => 'Rp ' . number_format($summary['remaining'], 0, ',', '.'), 'color' => $summary['remaining'] < 0 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Warning', 'value' => $summary['warning'], 'color' => 'text-amber-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Ringkasan Budget</h2>
                @include('partials.form-help', [
                    'title' => 'Cara membaca Budget Tim',
                    'description' => 'Halaman ini membantu manager melihat budget yang dialokasikan dan realisasi dari lembur, klaim, dan training dalam periode tertentu.',
                    'steps' => [
                        'Set periode budget yang mau dipantau.',
                        'Tambahkan allocation untuk kategori lembur, klaim, atau training.',
                        'Realisasi lembur dibaca dari pengajuan lembur tim.',
                        'Realisasi klaim/training dibaca dari klaim yang dicatat di halaman ini.',
                    ],
                    'notes' => [
                        'Training cost awal dicatat sebagai klaim tipe training sampai modul expense/training budget penuh dibuat.',
                        'Warning muncul jika realisasi melewati threshold allocation.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Budget dikontrol per kategori agar manager tahu risiko overspend sebelum approval.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Budget</th>
                        <th class="px-5 py-3">Realisasi</th>
                        <th class="px-5 py-3">Sisa</th>
                        <th class="px-5 py-3">Usage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($budgetRows as $row)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $row['label'] }}</div>
                                <div class="text-xs text-gray-500">Threshold {{ $row['threshold'] }}%</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">Rp {{ number_format($row['allocated'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-blue-700">Rp {{ number_format($row['actual'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 {{ $row['remaining'] < 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($row['remaining'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-32 rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full {{ $row['status'] === 'warning' ? 'bg-amber-500' : 'bg-blue-600' }}" style="width: {{ min(100, $row['usage']) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $row['status'] === 'warning' ? 'text-amber-700' : 'text-gray-600' }}">{{ $row['usage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Allocation</h2>
                <p class="text-sm text-gray-500">Allocation menjadi batas kontrol budget manager per kategori.</p>
            </div>
            <form action="{{ route('manager.budget.allocations') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Budget</label>
                        <input name="allocation_number" required placeholder="Contoh: BGT-MGR-058" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                        <select name="category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="overtime">Lembur</option>
                            <option value="claim">Klaim</option>
                            <option value="training">Training</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai</label>
                        <input type="date" name="period_start" value="{{ $filters['start_date'] }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir</label>
                        <input type="date" name="period_end" value="{{ $filters['end_date'] }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal Budget</label>
                        <input type="number" name="allocated_amount" min="0" step="1" required placeholder="Contoh: 5000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Warning Threshold (%)</label>
                        <input type="number" name="alert_threshold_percent" value="80" min="1" max="100" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
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
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <input name="notes" placeholder="Catatan batas pemakaian budget" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Budget
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Catat Klaim</h2>
                <p class="text-sm text-gray-500">Dipakai untuk klaim operasional dan training cost sementara.</p>
            </div>
            <form action="{{ route('manager.budget.claims') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Klaim</label>
                        <input name="claim_number" required placeholder="Contoh: CLM-MGR-058" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                        <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih karyawan</option>
                            @foreach($team as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Klaim</label>
                        <input type="date" name="claim_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Klaim</label>
                        <select name="claim_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="transport">Transport</option>
                            <option value="meal">Meal</option>
                            <option value="medical">Medical</option>
                            <option value="training">Training</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal</label>
                        <input type="number" name="amount" min="0" step="1" required placeholder="Contoh: 250000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="draft">Draft</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Keterangan</label>
                        <textarea name="description" rows="3" placeholder="Ringkasan klaim, vendor, atau kebutuhan training." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-receipt"></i> Simpan Klaim
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Realisasi Terbaru</h2>
            <p class="text-sm text-gray-500">Gabungan lembur, klaim, dan enrollment training dalam scope tim.</p>
        </div>
        <div class="grid divide-y divide-gray-100 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
            <div class="p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Lembur</h3>
                <div class="space-y-3">
                    @forelse($overtimes->take(5) as $item)
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="font-semibold text-gray-900">{{ $item->employeeProfile?->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->request_number }} - Rp {{ number_format((float) $item->estimated_amount, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Belum ada lembur.</div>
                    @endforelse
                </div>
            </div>
            <div class="p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Klaim</h3>
                <div class="space-y-3">
                    @forelse($claims->take(5) as $item)
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="font-semibold text-gray-900">{{ $item->employeeProfile?->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->claim_number }} - {{ ucfirst($item->claim_type) }} - Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Belum ada klaim.</div>
                    @endforelse
                </div>
            </div>
            <div class="p-5">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Training</h3>
                <div class="space-y-3">
                    @forelse($trainings->take(5) as $item)
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="font-semibold text-gray-900">{{ $item->employeeProfile?->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->course?->title }} - {{ ucfirst($item->enrollment_status) }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Belum ada training.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
