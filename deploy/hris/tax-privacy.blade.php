@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Privasi Pajak</h1>
            <p class="mt-1 text-sm text-gray-500">Masking NIK/NPWP, izin ekspor data pajak, dan retensi legal.</p>
        </div>
        <a href="{{ route('payroll.tax-profiles') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-invoice-dollar"></i>
            Master PPh 21
        </a>
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
                ['label' => 'Kebijakan', 'value' => $summary['policies'], 'color' => 'text-blue-600'],
                ['label' => 'Ekspor Pending', 'value' => $summary['pending_exports'], 'color' => 'text-yellow-600'],
                ['label' => 'Ekspor Approved', 'value' => $summary['approved_exports'], 'color' => 'text-green-600'],
                ['label' => 'Profil Dimasking', 'value' => $summary['masked_profiles'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Kebijakan Privasi</h2>
                <p class="text-sm text-gray-500">Atur default masking dan masa simpan data pajak.</p>
            </div>
            <form action="{{ route('payroll.tax-privacy.policy') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-2">
                @csrf
                <input name="name" required value="{{ $policy?->name ?? 'Kebijakan Privasi Pajak Default' }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Global semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="retention_years" min="1" max="30" required value="{{ $policy?->retention_years ?? 10 }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="legal_basis" required value="{{ $policy?->legal_basis ?? 'Kepatuhan perpajakan dan ketenagakerjaan Indonesia' }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="notes" rows="3" placeholder="Catatan kebijakan" class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ $policy?->notes }}</textarea>
                <div class="xl:col-span-2 grid gap-3 md:grid-cols-3">
                    @foreach([
                        'mask_nik' => 'Masking NIK',
                        'mask_npwp' => 'Masking NPWP',
                        'export_requires_approval' => 'Ekspor wajib approval',
                    ] as $name => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked($policy ? $policy->{$name} : true) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div class="xl:col-span-2 flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-shield-halved"></i>
                        Simpan Kebijakan
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Preview Masking</h2>
                <p class="text-sm text-gray-500">Contoh data sensitif yang tampil di UI/export.</p>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($sampleProfiles as $profile)
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="font-semibold text-gray-900">{{ $profile->employee_name }}</div>
                        <div class="mt-1 font-mono text-xs text-gray-500">NIK: {{ \App\Http\Controllers\TaxPrivacyController::mask($profile->nik) }}</div>
                        <div class="font-mono text-xs text-gray-500">NPWP: {{ \App\Http\Controllers\TaxPrivacyController::mask($profile->npwp) }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada profil pajak untuk preview.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Permintaan Ekspor Pajak</h2>
            <p class="text-sm text-gray-500">Setiap ekspor sensitif wajib punya tujuan, scope, dan masa berlaku.</p>
        </div>
        <form action="{{ route('payroll.tax-privacy.exports') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-5">
            @csrf
            <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Semua company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                @endforeach
            </select>
            <select name="export_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="pph21_profile">Profil PPh 21</option>
                <option value="calculation_audit">Jejak Perhitungan</option>
                <option value="coretax_template">Template Coretax</option>
                <option value="bukti_potong">Bukti Potong</option>
            </select>
            <select name="scope" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="company">Company</option>
                <option value="branch">Cabang</option>
                <option value="period">Periode</option>
                <option value="employee">Karyawan</option>
            </select>
            <input type="month" name="period" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="purpose" required placeholder="Tujuan ekspor" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <textarea name="notes" rows="2" placeholder="Catatan approval" class="xl:col-span-4 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-file-export"></i>
                    Catat Ekspor
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Log Ekspor</h2>
            <p class="text-sm text-gray-500">50 permintaan ekspor pajak terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Tujuan</th>
                        <th class="px-5 py-3">Masking</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($exports as $export)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ str_replace('_', ' ', $export->export_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $export->company?->code ?: 'Global' }} / {{ $export->period ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($export->scope) }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $export->purpose }}</div>
                                <div class="text-xs text-gray-500">Req: {{ $export->requester?->name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ implode(', ', $export->masked_fields ?? []) ?: '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $export->status === 'approved' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($export->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada log ekspor pajak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
