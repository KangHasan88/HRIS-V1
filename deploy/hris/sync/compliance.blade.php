@extends('layouts.sidebar')

@section('title', 'Compliance Outsourcing')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Compliance Outsourcing</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau kewajiban pajak, BPJS, HSE, akses area, dan dokumen kontrak vendor penyedia tenaga kerja.</p>
        </div>
        <a href="{{ route('external-workforce.vendors') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-people-carry-box"></i> Vendor Outsourcing
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Total Item', 'value' => $summary['total'], 'color' => 'text-blue-600', 'hint' => 'Register compliance'],
                ['label' => 'Verified', 'value' => $summary['verified'], 'color' => 'text-green-600', 'hint' => 'Sudah dicek'],
                ['label' => 'Perlu Revisi', 'value' => $summary['need_revision'], 'color' => 'text-red-600', 'hint' => 'Belum aman'],
                ['label' => 'High Risk', 'value' => $summary['high_risk'], 'color' => 'text-red-600', 'hint' => 'Prioritas follow-up'],
                ['label' => 'Akan Expired', 'value' => $summary['expiring'], 'color' => 'text-amber-600', 'hint' => '45 hari ke depan'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Item Compliance</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai Compliance Outsourcing',
                        'description' => 'Form ini dipakai HR/GA/HSE untuk mencatat bukti kewajiban vendor dan menentukan siapa pemilik follow-up-nya.',
                        'steps' => [
                            'Pilih vendor dan kontrak jika item compliance terkait kontrak tertentu.',
                            'Pilih area compliance: pajak, BPJS, HSE, akses area, legal kontrak, atau privasi data.',
                            'Isi bukti dokumen, masa berlaku, status verifikasi, risiko, dan owner follow-up.',
                            'Item yang high risk, expired, atau perlu revisi menjadi prioritas review sebelum vendor dipakai.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu baris mewakili satu kewajiban atau bukti compliance yang harus dimonitor.</p>
            </div>

            <form action="{{ route('external-workforce.compliance.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Vendor & Item</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                            <select name="external_workforce_vendor_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontrak</label>
                            <select name="external_workforce_contract_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional, jika terkait kontrak</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ $contract->vendor?->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Item</label>
                            <input name="item_number" required placeholder="Contoh: EXT-COMP-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Area Compliance</label>
                            <select name="compliance_area" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="tax">Pajak vendor / PPh</option>
                                <option value="bpjs">BPJS tenaga outsource</option>
                                <option value="hse">HSE & induksi safety</option>
                                <option value="access_area">Akses area kerja</option>
                                <option value="legal_contract">Legal kontrak</option>
                                <option value="data_privacy">Privasi data</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Kewajiban / Dokumen</label>
                            <input name="requirement_name" required placeholder="Contoh: Bukti pembayaran BPJS tenaga outsource periode berjalan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Verifikasi & Risiko</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggung Jawab</label>
                            <select name="responsible_party" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="vendor">Vendor</option>
                                <option value="company">Perusahaan</option>
                                <option value="shared">Shared</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Verifikasi</label>
                            <select name="verification_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="verified">Verified</option>
                                <option value="need_revision">Perlu revisi</option>
                                <option value="expired">Expired</option>
                                <option value="waived">Waived</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Level Risiko</label>
                            <select name="risk_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Dokumen</label>
                            <input type="date" name="issued_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Berlaku Sampai</label>
                            <input type="date" name="expiry_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Follow-up Berikutnya</label>
                            <input type="date" name="next_follow_up_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner Follow-up</label>
                            <input name="owner_name" placeholder="Contoh: HR Ops / HSE / GA" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Dokumen</label>
                            <input name="document_reference" placeholder="Nomor file, URL, atau lokasi dokumen vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan gap dokumen, risiko vendor, atau instruksi follow-up." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-shield-halved"></i> Simpan Compliance
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="panel rounded-2xl p-5">
                <h2 class="text-lg font-semibold text-gray-900">Prioritas Review</h2>
                <p class="mt-1 text-sm text-gray-500">Utamakan item yang high risk, expired, atau belum verified sebelum worker masuk area kerja.</p>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="rounded-xl border border-red-100 bg-red-50 p-3 text-red-700">
                        <div class="font-semibold">Critical / High</div>
                        <p class="mt-1 text-xs">Vendor tidak boleh dipakai tanpa keputusan HR/Legal/HSE.</p>
                    </div>
                    <div class="rounded-xl border border-amber-100 bg-amber-50 p-3 text-amber-700">
                        <div class="font-semibold">Akan expired</div>
                        <p class="mt-1 text-xs">Minta dokumen renewal sebelum masa berlaku habis.</p>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3 text-blue-700">
                        <div class="font-semibold">Shared responsibility</div>
                        <p class="mt-1 text-xs">Pastikan PIC vendor dan PIC internal sama-sama jelas.</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Register Compliance Vendor</h2>
            <p class="text-sm text-gray-500">Daftar bukti compliance vendor, status verifikasi, risiko, dan follow-up.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Item</th>
                        <th class="px-5 py-3">Vendor / Kontrak</th>
                        <th class="px-5 py-3">Area</th>
                        <th class="px-5 py-3">Berlaku</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        @php
                            $statusClass = [
                                'verified' => 'bg-green-50 text-green-700',
                                'submitted' => 'bg-blue-50 text-blue-700',
                                'need_revision' => 'bg-amber-50 text-amber-700',
                                'expired' => 'bg-red-50 text-red-700',
                                'waived' => 'bg-gray-100 text-gray-700',
                            ][$item->verification_status] ?? 'bg-gray-100 text-gray-700';
                            $riskClass = in_array($item->risk_level, ['high', 'critical'], true) ? 'text-red-600' : ($item->risk_level === 'medium' ? 'text-amber-600' : 'text-green-600');
                        @endphp
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->requirement_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $item->item_number }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $item->document_reference ?: 'Belum ada referensi dokumen' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->vendor?->vendor_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $item->vendor?->vendor_code }}</div>
                                <div class="text-xs text-gray-500">{{ $item->contract?->contract_number ?: 'Tidak spesifik kontrak' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ ucfirst(str_replace('_', ' ', $item->compliance_area)) }}</div>
                                <div class="text-xs">Tanggung jawab: <span class="font-semibold text-gray-900">{{ ucfirst($item->responsible_party) }}</span></div>
                                <div class="text-xs">Risiko: <span class="font-semibold {{ $riskClass }}">{{ ucfirst($item->risk_level) }}</span></div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $item->issued_date?->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-500">s/d {{ $item->expiry_date?->format('d M Y') ?: 'Tidak ada expiry' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $item->verification_status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">Follow-up: {{ $item->next_follow_up_date?->format('d M Y') ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div class="font-semibold text-gray-900">{{ $item->owner_name ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->notes ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada item compliance outsourcing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
