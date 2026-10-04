@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Klaim Reimbursement</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan klaim penggantian biaya dengan bukti struk dari kamera ESS.</p>
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
                ['label' => 'Total Klaim', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Disetujui', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Nilai Aktif', 'value' => 'Rp ' . number_format($summary['amount'], 0, ',', '.'), 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(! $employee)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800 shadow-sm">
            <div class="font-semibold">Akun login belum terhubung ke profil karyawan.</div>
            <p class="mt-1">Halaman sudah siap, tapi pengajuan reimbursement baru aktif setelah HR/admin menghubungkan akun ini ke profil karyawan.</p>
        </div>
    @else
        <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Ajukan Klaim</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Klaim Reimbursement',
                            'description' => 'Form ini dipakai karyawan untuk mengirim klaim biaya dari ESS. Bukti struk bisa berasal dari kamera WebView, galeri, upload file, atau referensi manual.',
                            'steps' => [
                                'Isi nomor klaim, tanggal transaksi, kategori, merchant, dan nominal.',
                                'Isi referensi struk dari hasil capture kamera atau file reference.',
                                'Kirim klaim; status awal otomatis menunggu approval dan masuk monitoring klaim manager/HR.',
                            ],
                            'notes' => [
                                'Capture dari kamera menjadi bukti utama untuk mengurangi risiko struk ganda.',
                                'Jika bukti bukan dari kamera, sistem menandai perlu cek dokumen asli.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">{{ $employee->employee_number }} - {{ $employee->full_name }}</p>
                </div>

                <form action="{{ route('ess.reimbursements.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Data Klaim</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Klaim</label>
                                <input name="claim_number" required placeholder="Contoh: ESS-REIMB-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Transaksi</label>
                                <input type="date" name="claim_date" required value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori Klaim</label>
                                <select name="claim_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="transport">Transport</option>
                                    <option value="meal">Makan</option>
                                    <option value="medical">Medical</option>
                                    <option value="training">Training</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal</label>
                                <input type="number" name="amount" min="1" step="1" required placeholder="Contoh: 125000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Merchant / Vendor</label>
                                <input name="merchant_name" placeholder="Contoh: Grab, SPBU, Apotek, restoran" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Bukti Struk</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Bukti</label>
                                <select name="receipt_capture_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="camera">Kamera ESS</option>
                                    <option value="gallery">Galeri</option>
                                    <option value="file_upload">Upload File</option>
                                    <option value="manual_reference">Referensi Manual</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu Capture</label>
                                <input type="datetime-local" name="receipt_captured_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Foto Struk</label>
                                <input name="attachment_reference" required placeholder="Contoh: camera://receipt/ESS-REIMB-001.jpg atau file id dari WebView" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Keterangan</h3>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi Klaim</label>
                            <textarea name="description" rows="3" placeholder="Jelaskan kebutuhan biaya dan konteks pekerjaan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-receipt"></i>
                        Kirim Reimbursement
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Klaim</h2>
                    <p class="text-sm text-gray-500">Hanya menampilkan klaim milik akun login.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Klaim</th>
                                <th class="px-5 py-3">Nominal</th>
                                <th class="px-5 py-3">Bukti</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($claims as $claim)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $claim->claim_number }}</div>
                                        <div class="text-xs text-gray-500">{{ $claim->claim_date?->format('d M Y') }} · {{ ucfirst($claim->claim_type) }}</div>
                                        <div class="text-xs text-gray-500">{{ $claim->merchant_name ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-gray-900">Rp {{ number_format((float) $claim->amount, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">{{ $claim->receipt_capture_method ?: '-' }}</div>
                                        <div class="max-w-xs truncate text-xs text-gray-500">{{ $claim->attachment_reference ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{
                                            $claim->approval_status === 'approved' ? 'bg-green-50 text-green-700' :
                                            ($claim->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')
                                        }}">{{ ucfirst($claim->approval_status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada klaim reimbursement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
