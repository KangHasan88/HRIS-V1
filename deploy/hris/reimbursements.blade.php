@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Klaim Reimbursement</h1>
            <p class="mt-1 text-sm text-gray-500">Review klaim ESS, validasi struk/OCR, dan siapkan posting ke payroll.</p>
        </div>
        <a href="{{ route('ess.reimbursements') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i> Preview ESS
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
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Payroll</label>
                <input type="month" name="period" value="{{ $filters['period'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Klaim</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    @foreach(['draft' => 'Draft', 'submitted' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Total Klaim', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Disetujui', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Ready Payroll', 'value' => $summary['posting'], 'color' => 'text-blue-600'],
                ['label' => 'Risiko Review', 'value' => $summary['risk'], 'color' => 'text-red-600'],
                ['label' => 'Nilai Aktif', 'value' => 'Rp ' . number_format($summary['amount'], 0, ',', '.'), 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[.75fr_1.25fr]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Catat Klaim Manual</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara kerja klaim reimbursement',
                        'description' => 'Klaim normalnya diajukan karyawan dari ESS. Form manual dipakai payroll/HR jika klaim diterima via email, WhatsApp, walk-in, atau import data.',
                        'steps' => [
                            'Pilih karyawan, isi nomor klaim, tanggal, tipe klaim, nominal, dan referensi struk.',
                            'Isi hasil OCR bila struk sudah terbaca, atau tandai needs review jika belum yakin.',
                            'Set status approval dan posting payroll sesuai hasil review.',
                        ],
                        'notes' => [
                            'Nilai OCR dibandingkan dengan nominal klaim untuk mendeteksi selisih.',
                            'Risiko duplikat 60 ke atas sebaiknya direview manual sebelum masuk payroll.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Utamakan ESS; manual hanya untuk pengecualian operasional.</p>
            </div>

            <form action="{{ route('payroll.reimbursements.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Klaim</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Klaim</label>
                            <input name="claim_number" required placeholder="Contoh: REIMB-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Klaim</label>
                            <input type="date" name="claim_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Klaim</label>
                            <select name="claim_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="transport">Transport</option>
                                <option value="meal">Makan</option>
                                <option value="medical">Medical</option>
                                <option value="training">Training</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal Klaim</label>
                            <input type="number" name="amount" min="1" step="1" required placeholder="Contoh: 175000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Merchant / Vendor</label>
                            <input name="merchant_name" placeholder="Contoh: Grab, SPBU, Apotek" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Struk</label>
                            <input name="attachment_reference" placeholder="URL/file id/foto struk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">OCR & Payroll</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status OCR</label>
                            <select name="ocr_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="not_scanned">Belum scan</option>
                                <option value="scanned">Sudah scan</option>
                                <option value="needs_review">Perlu review</option>
                                <option value="verified">Terverifikasi</option>
                                <option value="failed">Gagal baca</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Confidence OCR (%)</label>
                            <input type="number" name="ocr_confidence" value="0" min="0" max="100" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor Terbaca</label>
                            <input name="ocr_vendor_name" placeholder="Nama merchant dari OCR" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Total Terbaca OCR</label>
                            <input type="number" name="ocr_total_amount" min="0" step="1" placeholder="Nominal dari struk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Risiko Duplikat (%)</label>
                            <input type="number" name="duplicate_risk_score" value="0" min="0" max="100" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Payroll</label>
                            <input type="month" name="payroll_period" value="{{ $filters['period'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Treatment Payroll</label>
                            <select name="payroll_treatment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="non_taxable_reimbursement">Reimbursement non-pajak</option>
                                <option value="taxable_benefit">Benefit kena pajak</option>
                                <option value="deduct_from_cash_advance">Potong kasbon</option>
                                <option value="exclude_from_payroll">Tidak masuk payroll</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Posting</label>
                            <select name="payroll_posting_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="not_posted">Belum posting</option>
                                <option value="ready_for_payroll">Siap payroll</option>
                                <option value="posted_to_payroll">Sudah masuk payroll</option>
                                <option value="excluded">Dikecualikan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Keputusan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="draft">Draft</option>
                                <option value="rejected">Ditolak</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Payroll Reviewer / Atasan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Review</label>
                            <textarea name="decision_notes" rows="3" placeholder="Alasan approval/reject, koreksi nominal, atau catatan bukti struk." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Klaim
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Review Klaim Masuk</h2>
                <p class="text-sm text-gray-500">Klaim dari ESS dan manual dipantau di sini sebelum masuk payroll.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Klaim</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">OCR</th>
                            <th class="px-5 py-3">Payroll</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($claims as $claim)
                            @php
                                $difference = $claim->ocr_total_amount === null ? null : (float) $claim->amount - (float) $claim->ocr_total_amount;
                                $riskHigh = (int) $claim->duplicate_risk_score >= 60 || ($difference !== null && abs($difference) > 1000);
                            @endphp
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $claim->claim_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->claim_date?->format('d M Y') }} · {{ ucfirst($claim->claim_type) }}</div>
                                    <div class="mt-1 font-semibold text-gray-900">Rp {{ number_format((float) $claim->amount, 0, ',', '.') }}</div>
                                    <div class="max-w-xs truncate text-xs text-gray-500">{{ $claim->attachment_reference ?: 'Belum ada referensi struk' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $claim->employeeProfile?->full_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->employeeProfile?->employee_number ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->company?->code ?: '-' }} {{ $claim->branch?->code ? '· '.$claim->branch?->code : '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst($claim->ocr_status ?? 'not_scanned')) }}</div>
                                    <div class="text-xs text-gray-500">Confidence {{ (int) $claim->ocr_confidence }}%</div>
                                    <div class="text-xs {{ $riskHigh ? 'text-red-600' : 'text-gray-500' }}">Risk {{ (int) $claim->duplicate_risk_score }}%</div>
                                    <div class="text-xs text-gray-500">OCR Rp {{ $claim->ocr_total_amount === null ? '-' : number_format((float) $claim->ocr_total_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ str_replace('_', ' ', $claim->payroll_treatment ?? '-') }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->payroll_period ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $claim->payroll_posting_status ?? 'not_posted') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{
                                        $claim->approval_status === 'approved' ? 'bg-green-50 text-green-700' :
                                        ($claim->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')
                                    }}">{{ ucfirst($claim->approval_status) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">{{ $claim->reviewer?->name ? 'Review: '.$claim->reviewer?->name : 'Belum direview' }}</div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('payroll.reimbursements.review', $claim) }}" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="ocr_status" value="{{ $riskHigh ? 'needs_review' : 'verified' }}">
                                        <input type="hidden" name="ocr_confidence" value="{{ max((int) $claim->ocr_confidence, $riskHigh ? 65 : 90) }}">
                                        <input type="hidden" name="duplicate_risk_score" value="{{ (int) $claim->duplicate_risk_score }}">
                                        <input type="hidden" name="payroll_treatment" value="{{ $claim->payroll_treatment ?: 'non_taxable_reimbursement' }}">
                                        <input type="hidden" name="payroll_period" value="{{ $claim->payroll_period ?: $filters['period'] }}">
                                        <input type="hidden" name="payroll_posting_status" value="ready_for_payroll">
                                        <input type="hidden" name="approval_status" value="approved">
                                        <input type="hidden" name="decision_notes" value="Disetujui dari review payroll reimbursement.">
                                        <button class="inline-flex w-32 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('payroll.reimbursements.review', $claim) }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="ocr_status" value="needs_review">
                                        <input type="hidden" name="ocr_confidence" value="{{ (int) $claim->ocr_confidence }}">
                                        <input type="hidden" name="duplicate_risk_score" value="{{ max((int) $claim->duplicate_risk_score, 60) }}">
                                        <input type="hidden" name="payroll_treatment" value="exclude_from_payroll">
                                        <input type="hidden" name="payroll_period" value="{{ $claim->payroll_period ?: $filters['period'] }}">
                                        <input type="hidden" name="payroll_posting_status" value="excluded">
                                        <input type="hidden" name="approval_status" value="rejected">
                                        <input type="hidden" name="decision_notes" value="Ditolak/perlu revisi berdasarkan review bukti reimbursement.">
                                        <button class="inline-flex w-32 items-center justify-center gap-2 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                            <i class="fas fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada klaim reimbursement untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
