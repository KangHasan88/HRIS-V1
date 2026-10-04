@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dokumen Tim</h1>
            <p class="mt-1 text-sm text-gray-500">Ajukan kebutuhan dokumen bawahan dan approve surat keterangan kerja dari scope tim manager.</p>
        </div>
        <a href="{{ route('manager.headcount-requisitions') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-user-plus"></i> Headcount Tim
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
                ['label' => 'Anggota Tim', 'value' => $summary['team_total'], 'color' => 'text-blue-600'],
                ['label' => 'Dokumen Open', 'value' => $summary['document_open'], 'color' => 'text-amber-600'],
                ['label' => 'Surat Pending', 'value' => $summary['certificate_pending'], 'color' => 'text-indigo-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Ajukan Dokumen Tim</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai pengajuan dokumen tim',
                        'description' => 'Dipakai manager untuk meminta dokumen bawahan ke HR, misalnya copy kontrak, ID badge, surat tugas, atau dokumen employment lainnya.',
                        'steps' => [
                            'Pilih bawahan yang membutuhkan dokumen.',
                            'Pilih jenis dokumen dan tujuan pemakaian.',
                            'Isi tanggal dibutuhkan dan channel pengiriman.',
                            'Gunakan status Submitted jika sudah siap diproses HR.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Pengajuan ini membantu HR memprioritaskan dokumen berdasarkan kebutuhan operasional manager.</p>
            </div>

            <form action="{{ route('manager.team-documents.requests') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Dokumen</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Request</label>
                            <input name="request_number" required placeholder="Contoh: DOC-TEAM-056" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bawahan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih bawahan</option>
                                @foreach($team as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Dokumen</label>
                            <select name="document_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="contract_copy">Copy kontrak</option>
                                <option value="id_badge">ID badge</option>
                                <option value="assignment_letter">Surat tugas</option>
                                <option value="training_certificate">Sertifikat training</option>
                                <option value="employment_document">Dokumen employment</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tujuan</label>
                            <input name="purpose" required placeholder="Contoh: kebutuhan audit site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Dibutuhkan</label>
                            <input type="date" name="needed_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Channel Pengiriman</label>
                            <select name="delivery_channel" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="portal">Portal</option>
                                <option value="email">Email</option>
                                <option value="printed">Printed</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="review">Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: HR Admin / HRBP" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Request</label>
                            <textarea name="request_note" rows="3" placeholder="Jelaskan konteks kebutuhan dokumen." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan HR</label>
                            <textarea name="hr_note" rows="2" placeholder="Opsional, dipakai jika HR sudah memberi update." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Approval Surat Keterangan Kerja</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai approval surat kerja',
                        'description' => 'Dipakai manager untuk menyetujui atau memberi catatan terhadap surat keterangan kerja bawahan sebelum HR menerbitkan dokumen final.',
                        'steps' => [
                            'Pilih bawahan dan jenis surat.',
                            'Isi tujuan surat, penerima, dan masa berlaku jika ada.',
                            'Pilih keputusan manager: pending, approved, rejected, atau perlu revisi.',
                            'Isi catatan keputusan jika ada batasan atau koreksi.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Approval manager memastikan surat yang diterbitkan HR sesuai konteks kerja aktual.</p>
            </div>

            <form action="{{ route('manager.team-documents.certificates') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Surat</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Surat</label>
                            <input name="certificate_number" required placeholder="Contoh: SKK-TEAM-056" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bawahan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih bawahan</option>
                                @foreach($team as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Surat</label>
                            <select name="certificate_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="employment_statement">Surat keterangan kerja</option>
                                <option value="salary_statement">Surat keterangan penghasilan</option>
                                <option value="visa_support">Dukungan visa</option>
                                <option value="bank_support">Dukungan bank</option>
                                <option value="assignment_statement">Keterangan penugasan</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Keputusan Manager</label>
                            <select name="manager_decision" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="needs_revision">Perlu revisi</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Penerima</label>
                            <input name="recipient_name" placeholder="Contoh: Bank Mandiri / Kedutaan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Institusi Penerima</label>
                            <input name="recipient_institution" placeholder="Contoh: Bank / Instansi / Vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Berlaku</label>
                            <input type="date" name="effective_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Berlaku Sampai</label>
                            <input type="date" name="valid_until" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="review">Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tujuan Surat</label>
                            <textarea name="certificate_purpose" rows="3" required placeholder="Jelaskan tujuan penggunaan surat dan konteks yang perlu diketahui HR." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Keputusan</label>
                            <textarea name="decision_note" rows="2" placeholder="Catatan approval, revisi, atau alasan reject." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-check-circle"></i> Simpan Approval
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Dokumen Tim</h2>
            <p class="text-sm text-gray-500">Tracking request dokumen dan surat keterangan kerja dari manager.</p>
        </div>
        <div class="grid gap-0 lg:grid-cols-2">
            <div class="border-b border-gray-100 lg:border-b-0 lg:border-r">
                <div class="bg-gray-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Request Dokumen</div>
                <div class="divide-y divide-gray-100">
                    @forelse($documentRequests as $request)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $request->request_number }} - {{ $request->employeeProfile?->full_name }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($request->document_type)) }} • {{ $request->needed_date?->format('d M Y') ?: 'Tanggal belum diisi' }}</div>
                                </div>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($request->approval_status) }}</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $request->purpose }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada request dokumen.</div>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="bg-gray-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Approval Surat Kerja</div>
                <div class="divide-y divide-gray-100">
                    @forelse($certificates as $certificate)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $certificate->certificate_number }} - {{ $certificate->employeeProfile?->full_name }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($certificate->certificate_type)) }} • {{ $certificate->effective_date?->format('d M Y') ?: 'Tanggal belum diisi' }}</div>
                                </div>
                                <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst(str_replace('_', ' ', $certificate->manager_decision)) }}</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $certificate->certificate_purpose }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada approval surat kerja.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
