@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Jadwal Shift</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola pola kerja, shift rotasi, split shift, long shift, dan roster karyawan.</p>
        </div>
        <a href="{{ route('attendance.holidays') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calendar-days"></i>
            Kalender Libur
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
                ['label' => 'Pola Shift', 'value' => $summary['patterns'], 'color' => 'text-blue-600'],
                ['label' => 'Pola Aktif', 'value' => $summary['active_patterns'], 'color' => 'text-green-600'],
                ['label' => 'Roster Terpasang', 'value' => $summary['assignments'], 'color' => 'text-indigo-600'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-emerald-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.92fr_1.08fr]">
        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Buat Pola Shift</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Pola Shift',
                            'description' => 'Form ini dipakai untuk membuat master jam kerja yang bisa dipasang ke karyawan, cabang/site, atau perusahaan.',
                            'steps' => [
                                'Isi kode, nama, tipe shift, jam mulai, jam selesai, dan durasi istirahat.',
                                'Aktifkan cross-day untuk shift yang selesai di hari berikutnya, misalnya shift malam.',
                                'Aktifkan split shift jika jam kerja dibagi dua sesi dalam satu hari.',
                                'Atur grace late dan early leave agar mesin absensi punya toleransi keterlambatan dan pulang cepat.',
                            ],
                            'notes' => [
                                'Pola shift adalah master. Roster dibuat di form Pasang Roster Shift.',
                                'Long shift sebaiknya diberi planned hours yang jelas untuk dasar overtime dan payroll.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">Master pola shift dipakai ulang untuk roster rotasi dan jadwal kerja.</p>
                </div>

                <form action="{{ route('attendance.shift-schedules.patterns') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Identitas Pola</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Shift</label>
                                <input name="code" required placeholder="Contoh: SHIFT-PAGI" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Shift</label>
                                <input name="name" required placeholder="Contoh: Shift Pagi Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                                <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Berlaku umum</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Semua cabang/site</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Jam Kerja</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Shift</label>
                                <select name="shift_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($shiftTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Mulai</label>
                                <input type="time" name="start_time" required value="08:00" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Selesai</label>
                                <input type="time" name="end_time" required value="17:00" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Istirahat</label>
                                <input type="number" min="0" max="480" name="break_minutes" required value="60" placeholder="Menit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Planned Hours</label>
                                <input type="number" min="0" max="24" step="0.25" name="planned_hours" required value="8" placeholder="Contoh: 8" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_cross_day" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Shift selesai hari berikutnya
                            </label>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Split Shift & Toleransi</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_split_shift" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Pola split shift
                            </label>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Split Mulai</label>
                                <input type="time" name="split_start_time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Split Selesai</label>
                                <input type="time" name="split_end_time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Grace Telat</label>
                                <input type="number" min="0" max="240" name="grace_late_minutes" required value="10" placeholder="Menit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Grace Pulang Cepat</label>
                                <input type="number" min="0" max="240" name="grace_early_leave_minutes" required value="10" placeholder="Menit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Pola</label>
                            <textarea name="notes" rows="3" placeholder="Catatan aturan shift, overtime, atau pengecualian operasional." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i>
                        Simpan Pola Shift
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Pasang Roster Shift</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Roster Shift',
                            'description' => 'Form ini dipakai untuk memasang pola shift ke karyawan, perusahaan, atau cabang/site pada periode tertentu.',
                            'steps' => [
                                'Pilih pola shift yang sudah dibuat.',
                                'Tentukan scope roster: per karyawan, perusahaan, atau cabang/site.',
                                'Pilih siklus rotasi dan periode berlaku.',
                                'Tentukan hari kerja dan hari off agar jadwal mudah dibaca di kalender absensi.',
                            ],
                            'notes' => [
                                'Status Draft untuk simulasi, Published untuk jadwal yang berlaku.',
                                'Roster ini nanti menjadi dasar clock-in/out, overtime, anomali absensi, dan payroll attendance.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">Roster menghubungkan master pola shift dengan employee/scope operasional.</p>
                </div>

                <form action="{{ route('attendance.shift-schedules.assignments') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Data Roster</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pola Shift</label>
                                <select name="attendance_shift_pattern_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih pola shift</option>
                                    @foreach($patterns as $pattern)
                                        <option value="{{ $pattern->id }}">{{ $pattern->code }} - {{ $pattern->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Roster</label>
                                <select name="assignment_scope" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($scopeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Target Roster</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                                <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Jika scope per karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                                <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Jika scope perusahaan/site</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Jika scope cabang/site</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Periode & Rotasi</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Siklus Rotasi</label>
                                <select name="rotation_cycle" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($rotationCycles as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Berlaku</label>
                                <input type="date" name="effective_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Berlaku</label>
                                <input type="date" name="effective_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Hari Kerja & Off</h3>
                        <div class="mt-3 grid gap-4 lg:grid-cols-2">
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Hari Kerja</div>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach($dayOptions as $value => $label)
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                            <input type="checkbox" name="work_days[]" value="{{ $value }}" @checked(in_array($value, ['mon', 'tue', 'wed', 'thu', 'fri'])) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Hari Off</div>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach($dayOptions as $value => $label)
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                            <input type="checkbox" name="off_days[]" value="{{ $value }}" @checked(in_array($value, ['sat', 'sun'])) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Status & Catatan</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Roster</label>
                                <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Roster</label>
                                <input name="notes" placeholder="Catatan rotasi, pengecualian, atau approval." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-calendar-plus"></i>
                        Simpan Roster Shift
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Pola Shift</h2>
                    <p class="text-sm text-gray-500">Master shift yang dapat dipakai di roster karyawan dan site.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Pola</th>
                                <th class="px-5 py-3">Jam</th>
                                <th class="px-5 py-3">Scope</th>
                                <th class="px-5 py-3">Toleransi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($patterns as $pattern)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $pattern->code }}</div>
                                        <div class="text-xs text-gray-500">{{ $pattern->name }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $shiftTypes[$pattern->shift_type] ?? $pattern->shift_type }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ substr($pattern->start_time, 0, 5) }} - {{ substr($pattern->end_time, 0, 5) }}</div>
                                        <div class="text-xs text-gray-500">{{ $pattern->planned_hours }} jam / istirahat {{ $pattern->break_minutes }} menit</div>
                                        @if($pattern->is_cross_day)
                                            <div class="text-xs font-semibold text-indigo-600">Cross-day</div>
                                        @endif
                                        @if($pattern->is_split_shift)
                                            <div class="text-xs font-semibold text-amber-600">Split {{ $pattern->split_start_time ? substr($pattern->split_start_time, 0, 5) : '-' }} - {{ $pattern->split_end_time ? substr($pattern->split_end_time, 0, 5) : '-' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $pattern->company?->code ?: 'Umum' }}</div>
                                        <div class="text-xs text-gray-500">{{ $pattern->branch?->code ?: 'Semua cabang/site' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-xs text-gray-500">Telat: {{ $pattern->grace_late_minutes }} menit</div>
                                        <div class="text-xs text-gray-500">Pulang cepat: {{ $pattern->grace_early_leave_minutes }} menit</div>
                                        <span class="mt-2 inline-flex rounded-full {{ $pattern->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500' }} px-3 py-1 text-xs font-semibold">{{ $pattern->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada pola shift.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Roster Aktif</h2>
                    <p class="text-sm text-gray-500">Assignment shift yang sudah dipasang untuk employee atau scope organisasi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Roster</th>
                                <th class="px-5 py-3">Target</th>
                                <th class="px-5 py-3">Periode</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($assignments as $assignment)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $assignment->shiftPattern?->code }}</div>
                                        <div class="text-xs text-gray-500">{{ $assignment->shiftPattern?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $rotationCycles[$assignment->rotation_cycle] ?? $assignment->rotation_cycle }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $scopeOptions[$assignment->assignment_scope] ?? $assignment->assignment_scope }}</div>
                                        <div class="text-xs text-gray-500">{{ $assignment->employeeProfile?->full_name ?: ($assignment->branch?->code ?: ($assignment->company?->code ?: '-')) }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $assignment->effective_start?->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">Sampai: {{ $assignment->effective_end?->format('d M Y') ?? 'Tanpa akhir' }}</div>
                                        <div class="text-xs text-gray-500">Kerja: {{ implode(', ', $assignment->work_days ?? []) ?: '-' }}</div>
                                        <div class="text-xs text-gray-500">Off: {{ implode(', ', $assignment->off_days ?? []) ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full {{ $assignment->status === 'published' ? 'bg-green-50 text-green-600' : ($assignment->status === 'archived' ? 'bg-gray-100 text-gray-500' : 'bg-amber-50 text-amber-600') }} px-3 py-1 text-xs font-semibold">{{ ucfirst($assignment->status) }}</span>
                                        <p class="mt-2 max-w-xs text-xs text-gray-500">{{ $assignment->notes ?: '-' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada roster shift.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
