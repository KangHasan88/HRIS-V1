@extends('layouts.sidebar')

@section('content')
<div class="ess-webview-page space-y-5 md:space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900 md:text-2xl">Pusat Pengajuan</h1>
        <p class="mt-1 text-sm text-gray-500">Ajukan cuti, lembur, perjalanan dinas, dan koreksi absensi dari ESS.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif
    @unless ($employee)
        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <div class="font-semibold">Akun login belum terhubung ke profil karyawan.</div>
            <div class="mt-1">Halaman tetap dibuka untuk preview, tapi pengajuan baru aktif setelah HR/admin menghubungkan akun ini ke profil karyawan.</div>
        </div>
    @endunless

    <div class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-5">
            @foreach([
                ['label' => 'Cuti', 'value' => $summary['leave'], 'color' => 'text-blue-600'],
                ['label' => 'Lembur', 'value' => $summary['overtime'], 'color' => 'text-indigo-600'],
                ['label' => 'Dinas', 'value' => $summary['trip'], 'color' => 'text-amber-600'],
                ['label' => 'Koreksi', 'value' => $summary['correction'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }} md:text-2xl">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
        <div class="flex items-center gap-2 border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Form Pengajuan</h2>
            @include('partials.form-help', [
                'title' => 'Panduan Pusat Pengajuan',
                'description' => 'Halaman ini dipakai karyawan untuk mengirim request operasional HR dari mobile WebView.',
                'steps' => [
                    'Pilih jenis request yang sesuai: cuti, lembur, dinas, atau koreksi absensi.',
                    'Isi nomor request dan data utama sesuai kebutuhan approval.',
                    'Semua request dari ESS otomatis masuk status Submitted dan approver Atasan langsung.',
                    'HR/admin akan memproses request dari halaman operasional terkait.',
                ],
                'notes' => [
                    'Cuti tetap memakai engine saldo dan policy cuti yang sudah tersedia.',
                    'Koreksi absensi dari ESS tidak otomatis membuat clock event sampai disetujui admin/manager.',
                ],
            ])
        </div>
        <p class="mt-3 text-sm text-gray-500">
            @if ($employee)
                {{ $employee->employee_number }} - {{ $employee->full_name }}
            @else
                Belum ada profil karyawan yang terhubung dengan akun login.
            @endif
        </p>
    </div>

    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 md:hidden">
        @foreach([
            ['href' => '#form-cuti', 'label' => 'Cuti', 'icon' => 'fas fa-calendar-check'],
            ['href' => '#form-lembur', 'label' => 'Lembur', 'icon' => 'fas fa-stopwatch'],
            ['href' => '#form-dinas', 'label' => 'Dinas', 'icon' => 'fas fa-route'],
            ['href' => '#form-koreksi', 'label' => 'Koreksi', 'icon' => 'fas fa-pen-to-square'],
        ] as $shortcut)
            <a href="{{ $shortcut['href'] }}" class="inline-flex min-w-fit items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm">
                <i class="{{ $shortcut['icon'] }} text-blue-600"></i> {{ $shortcut['label'] }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2 xl:gap-5">
        <form id="form-cuti" action="{{ route('ess.leave-requests.store') }}" method="POST" class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
            @csrf
            <h3 class="text-base font-semibold text-gray-900">Ajukan Cuti</h3>
            <fieldset @disabled(! $employee) class="mt-4 grid gap-3 md:grid-cols-2 disabled:opacity-60">
                <input name="request_number" required placeholder="Nomor request, contoh: ESS-LEAVE-044" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="request_source" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mobile_webview">Mobile WebView</option>
                    <option value="ess_webview">ESS WebView</option>
                </select>
                <select name="leave_policy_type_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2">
                    <option value="">Pilih jenis cuti</option>
                    @foreach($policyTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->code }} - {{ $type->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="date" name="end_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="attachment_reference" placeholder="Lampiran opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2">
                <textarea name="reason" rows="3" placeholder="Alasan cuti" class="resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2"></textarea>
            </fieldset>
            <button @disabled(! $employee) class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-300"><i class="fas fa-paper-plane"></i> Kirim Cuti</button>
        </form>

        <form id="form-lembur" action="{{ route('ess.request-center.overtime') }}" method="POST" class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
            @csrf
            <h3 class="text-base font-semibold text-gray-900">Ajukan Lembur</h3>
            <fieldset @disabled(! $employee) class="mt-4 grid gap-3 md:grid-cols-2 disabled:opacity-60">
                <input name="request_number" required placeholder="Nomor request, contoh: ESS-OT-044" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="request_source" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mobile_webview">Mobile WebView</option>
                    <option value="ess_webview">ESS WebView</option>
                </select>
                <input type="date" name="overtime_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="overtime_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="regular_day">Hari kerja</option>
                    <option value="night_shift">Night shift</option>
                    <option value="rest_day">Hari istirahat</option>
                    <option value="holiday">Hari libur</option>
                </select>
                <input type="datetime-local" name="start_at" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="datetime-local" name="end_at" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="reason" rows="3" placeholder="Alasan lembur" class="resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2"></textarea>
            </fieldset>
            <button @disabled(! $employee) class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-300"><i class="fas fa-stopwatch"></i> Kirim Lembur</button>
        </form>

        <form id="form-dinas" action="{{ route('ess.request-center.trips') }}" method="POST" class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
            @csrf
            <h3 class="text-base font-semibold text-gray-900">Ajukan Dinas</h3>
            <fieldset @disabled(! $employee) class="mt-4 grid gap-3 md:grid-cols-2 disabled:opacity-60">
                <input name="request_number" required placeholder="Nomor request, contoh: ESS-DINAS-044" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="request_source" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mobile_webview">Mobile WebView</option>
                    <option value="ess_webview">ESS WebView</option>
                </select>
                <input type="date" name="start_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="date" name="end_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="destination_city" required placeholder="Kota tujuan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="destination_site" placeholder="Site / lokasi tujuan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="trip_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="domestic">Dinas domestik</option>
                    <option value="site_visit">Site visit</option>
                    <option value="training">Training</option>
                    <option value="client_visit">Client visit</option>
                </select>
                <input name="transport_mode" placeholder="Transport, contoh: Pesawat" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="number" min="0" step="1" name="estimated_budget" placeholder="Estimasi budget" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="attachment_reference" placeholder="Lampiran opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="purpose" rows="3" placeholder="Tujuan perjalanan dinas" class="resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2"></textarea>
            </fieldset>
            <button @disabled(! $employee) class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-300"><i class="fas fa-route"></i> Kirim Dinas</button>
        </form>

        <form id="form-koreksi" action="{{ route('ess.request-center.corrections') }}" method="POST" class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
            @csrf
            <h3 class="text-base font-semibold text-gray-900">Ajukan Koreksi Absensi</h3>
            <fieldset @disabled(! $employee) class="mt-4 grid gap-3 md:grid-cols-2 disabled:opacity-60">
                <input name="correction_number" required placeholder="Nomor koreksi, contoh: ESS-COR-044" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="request_source" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="mobile_webview">Mobile WebView</option>
                    <option value="ess_webview">ESS WebView</option>
                </select>
                <input type="date" name="attendance_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="correction_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="missing_clock_in">Missing clock-in</option>
                    <option value="missing_clock_out">Missing clock-out</option>
                    <option value="wrong_time">Salah jam</option>
                    <option value="wrong_type">Salah tipe event</option>
                </select>
                <input type="datetime-local" name="original_event_at" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="datetime-local" name="requested_event_at" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="requested_event_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="clock_in">Clock-in</option>
                    <option value="clock_out">Clock-out</option>
                    <option value="break_start">Mulai istirahat</option>
                    <option value="break_end">Selesai istirahat</option>
                </select>
                <input name="attachment_reference" placeholder="Lampiran opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="reason" rows="3" placeholder="Alasan koreksi absensi" class="resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm md:col-span-2"></textarea>
            </fieldset>
            <button @disabled(! $employee) class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-300"><i class="fas fa-pen-to-square"></i> Kirim Koreksi</button>
        </form>
    </div>

    <div class="mobile-card rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Request Saya</h2>
            <p class="text-sm text-gray-500">Ringkasan request terbaru dari ESS.</p>
        </div>
        <div class="grid grid-cols-1 gap-0 divide-y divide-gray-100 md:grid-cols-4 md:divide-x md:divide-y-0">
            @foreach([
                'Cuti' => $leaveRequests->map(fn($r) => [$r->request_number, $r->approval_status]),
                'Lembur' => $overtimeRequests->map(fn($r) => [$r->request_number, $r->approval_status]),
                'Dinas' => $tripRequests->map(fn($r) => [$r->request_number, $r->approval_status]),
                'Koreksi' => $corrections->map(fn($r) => [$r->correction_number, $r->approval_status]),
            ] as $title => $rows)
                <div class="p-5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
                    <div class="mt-3 space-y-2">
                        @forelse($rows->take(5) as $row)
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="truncate font-medium text-gray-700">{{ $row[0] }}</span>
                                <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700">{{ ucfirst($row[1]) }}</span>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Belum ada data.</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
