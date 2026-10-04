@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Update Profil</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan perubahan data kontak, alamat, kontak darurat, dan rekening dari ESS.</p>
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
                ['label' => 'Total Pengajuan', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu HR', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Disetujui', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Ditolak', 'value' => $summary['rejected'], 'color' => 'text-red-600'],
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
            <p class="mt-1">Halaman sudah siap, tapi pengajuan update profil baru aktif setelah HR/admin menghubungkan akun ini ke profil karyawan.</p>
        </div>
    @else
    <div class="grid gap-5 xl:grid-cols-[.95fr_1.05fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Ajukan Update Profil</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Update Profil ESS',
                        'description' => 'Form ini dipakai karyawan untuk meminta perubahan data personal yang butuh review HR sebelum masuk ke master karyawan.',
                        'steps' => [
                            'Isi hanya field yang perlu diubah.',
                            'Tambahkan referensi bukti jika perubahan sensitif, misalnya rekening atau alamat.',
                            'Kirim pengajuan dan tunggu HR menyetujui atau menolak.',
                        ],
                        'notes' => [
                            'Data master tidak langsung berubah sebelum HR menyetujui.',
                            'Riwayat request tersimpan untuk audit.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">{{ $employee->employee_number }} - {{ $employee->full_name }}</p>
            </div>

            <form action="{{ route('ess.profile-updates.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Request</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Request</label>
                            <input name="request_number" required placeholder="Contoh: ESS-PROFILE-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber</label>
                            <select name="request_source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="ess_webview">ESS WebView</option>
                                <option value="mobile_webview">Mobile WebView</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kontak & Alamat</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Email Personal</label>
                            <input type="email" name="personal_email" value="{{ $employee->personal_email }}" placeholder="Email pribadi aktif" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor HP</label>
                            <input name="phone" value="{{ $employee->phone }}" placeholder="Nomor HP aktif" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alamat Domisili</label>
                            <textarea name="address" rows="3" placeholder="Alamat domisili terbaru" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ $employee->address_snapshot['address'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kontak Darurat</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Kontak</label>
                            <input name="emergency_contact_name" value="{{ $employee->emergency_contact_name }}" placeholder="Contoh: Siti Aminah" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Kontak</label>
                            <input name="emergency_contact_phone" value="{{ $employee->emergency_contact_phone }}" placeholder="Nomor HP kontak darurat" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Rekening Payroll</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bank</label>
                            <input name="bank_name" value="{{ $primaryBank?->bank_name }}" placeholder="Contoh: BCA" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Rekening</label>
                            <input name="account_number" value="{{ $primaryBank?->account_number }}" placeholder="Nomor rekening payroll" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Pemilik</label>
                            <input name="account_holder_name" value="{{ $primaryBank?->account_holder_name }}" placeholder="Nama sesuai rekening" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Bukti & Catatan</h3>
                    <div class="mt-3 grid gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Bukti</label>
                            <input name="evidence_reference" placeholder="Nomor dokumen, URL, atau file reference dari ESS" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Karyawan</label>
                            <textarea name="employee_note" rows="3" placeholder="Jelaskan alasan perubahan data." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                    Kirim ke HR
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Pengajuan</h2>
                <p class="text-sm text-gray-500">Hanya menampilkan pengajuan milik akun login.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($requests as $profileRequest)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $profileRequest->request_number }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $profileRequest->submitted_at?->format('d M Y H:i') }} · {{ $profileRequest->request_source }}</div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{
                                $profileRequest->approval_status === 'approved' ? 'bg-green-50 text-green-700' :
                                ($profileRequest->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')
                            }}">{{ ucfirst($profileRequest->approval_status) }}</span>
                        </div>
                        <div class="mt-3 grid gap-2 text-xs text-gray-600 sm:grid-cols-2">
                            <div class="rounded-lg bg-gray-50 p-3">
                                <div class="font-semibold uppercase text-gray-400">Kontak</div>
                                <div>{{ $profileRequest->requested_snapshot['profile']['phone'] ?? '-' }}</div>
                                <div>{{ $profileRequest->requested_snapshot['profile']['personal_email'] ?? '-' }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <div class="font-semibold uppercase text-gray-400">Rekening</div>
                                <div>{{ $profileRequest->requested_snapshot['bank']['bank_name'] ?? '-' }}</div>
                                <div>{{ $profileRequest->requested_snapshot['bank']['account_number'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada pengajuan update profil.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
