@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Approval Update Profil</h1>
            <p class="mt-1 text-sm text-gray-500">Review perubahan data dari ESS sebelum masuk ke master karyawan.</p>
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
                ['label' => 'Total Request', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu Review', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
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

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Request</h2>
                @include('partials.form-help', [
                    'title' => 'Panduan Approval Update Profil',
                    'description' => 'Halaman ini dipakai HR untuk memvalidasi perubahan data karyawan yang dikirim dari ESS.',
                    'steps' => [
                        'Bandingkan data lama dan data baru pada setiap request.',
                        'Cek referensi bukti jika ada perubahan rekening, alamat, atau kontak penting.',
                        'Approve untuk menerapkan perubahan ke master karyawan, atau reject jika data belum valid.',
                    ],
                    'notes' => [
                        'Request yang sudah direview tidak bisa diproses ulang.',
                        'Semua review dicatat di audit trail.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Approval ini mempengaruhi data master karyawan.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($requests as $profileRequest)
                @php
                    $current = $profileRequest->current_snapshot ?? [];
                    $requested = $profileRequest->requested_snapshot ?? [];
                    $profileFields = [
                        'personal_email' => 'Email Personal',
                        'phone' => 'Nomor HP',
                        'emergency_contact_name' => 'Kontak Darurat',
                        'emergency_contact_phone' => 'Nomor Darurat',
                        'address' => 'Alamat',
                    ];
                    $bankFields = [
                        'bank_name' => 'Bank',
                        'account_number' => 'Nomor Rekening',
                        'account_holder_name' => 'Nama Pemilik',
                    ];
                @endphp
                <div class="p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-gray-900">{{ $profileRequest->request_number }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{
                                    $profileRequest->approval_status === 'approved' ? 'bg-green-50 text-green-700' :
                                    ($profileRequest->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')
                                }}">{{ ucfirst($profileRequest->approval_status) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $profileRequest->employeeProfile?->employee_number }} - {{ $profileRequest->employeeProfile?->full_name }}
                                · {{ $profileRequest->employeeProfile?->company?->code ?? '-' }}
                                · {{ $profileRequest->employeeProfile?->branch?->code ?? '-' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">Submit: {{ $profileRequest->submitted_at?->format('d M Y H:i') }} · Bukti: {{ $profileRequest->evidence_reference ?: '-' }}</p>
                        </div>
                        @if($profileRequest->approval_status === 'submitted')
                            <form action="{{ route('hris.employee-profile-updates.review', $profileRequest) }}" method="POST" class="grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                                @csrf
                                <input name="review_note" placeholder="Catatan review HR" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <button name="approval_status" value="approved" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    <i class="fas fa-check"></i>
                                    Approve
                                </button>
                                <button name="approval_status" value="rejected" class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                                    <i class="fas fa-xmark"></i>
                                    Reject
                                </button>
                            </form>
                        @else
                            <div class="text-sm text-gray-500">
                                Direview oleh {{ $profileRequest->reviewer?->name ?? '-' }} pada {{ $profileRequest->reviewed_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-900">Data Profil</h4>
                            <div class="mt-3 divide-y divide-gray-200 text-sm">
                                @foreach($profileFields as $key => $label)
                                    <div class="grid gap-2 py-2 sm:grid-cols-[140px_1fr_1fr]">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</div>
                                        <div class="text-gray-500">{{ $current['profile'][$key] ?? '-' }}</div>
                                        <div class="font-medium text-gray-900">{{ $requested['profile'][$key] ?? '-' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-900">Data Rekening</h4>
                            <div class="mt-3 divide-y divide-gray-200 text-sm">
                                @foreach($bankFields as $key => $label)
                                    <div class="grid gap-2 py-2 sm:grid-cols-[140px_1fr_1fr]">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</div>
                                        <div class="text-gray-500">{{ $current['bank'][$key] ?? '-' }}</div>
                                        <div class="font-medium text-gray-900">{{ $requested['bank'][$key] ?? '-' }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 rounded-lg bg-white p-3 text-sm text-gray-600">
                                <span class="font-semibold text-gray-900">Catatan karyawan:</span>
                                {{ $profileRequest->employee_note ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada request update profil.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
