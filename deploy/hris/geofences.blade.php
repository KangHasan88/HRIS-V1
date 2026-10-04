@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Geofence Attendance</h1>
            <p class="mt-1 text-sm text-gray-500">Atur lokasi clock untuk kantor, WFH, client site, dan perjalanan dinas.</p>
        </div>
        <a href="{{ route('attendance.clock-events') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i>
            Clock Mobile
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
                ['label' => 'Geofence', 'value' => $summary['geofences'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Policy', 'value' => $summary['policies'], 'color' => 'text-indigo-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-emerald-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Buat Geofence</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Geofence',
                            'description' => 'Form ini dipakai untuk mendaftarkan area clock-in/out berbasis koordinat dan radius.',
                            'steps' => [
                                'Isi kode, nama, tipe lokasi, scope perusahaan/site, koordinat, dan radius.',
                                'Pilih apakah lokasi boleh dipakai clock-in dan/atau clock-out.',
                                'Tentukan apakah selfie dan liveness wajib untuk lokasi tersebut.',
                                'Gunakan simulasi jarak untuk mengecek apakah titik GPS masuk radius geofence.',
                            ],
                            'notes' => [
                                'WFH dan perjalanan dinas tetap bisa memakai radius, tetapi biasanya radius lebih fleksibel.',
                                'Geofence ini akan dipakai oleh Clock Mobile untuk validasi lokasi.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">Master lokasi clock berbasis GPS dan radius.</p>
                </div>
                <form action="{{ route('attendance.geofences.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Identitas Lokasi</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Geofence</label>
                                <input name="code" required placeholder="Contoh: BMP-HO-GEO" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Lokasi</label>
                                <input name="name" required placeholder="Contoh: Head Office BMP" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Lokasi</label>
                                <select name="location_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($locationTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Radius</label>
                                <input type="number" min="10" max="100000" name="radius_meter" required value="100" placeholder="Meter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Scope Organisasi</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
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
                        <h3 class="text-sm font-semibold text-gray-900">Koordinat</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Latitude</label>
                                <input type="number" step="0.0000001" name="latitude" required placeholder="Contoh: -6.2000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Longitude</label>
                                <input type="number" step="0.0000001" name="longitude" required placeholder="Contoh: 106.8166667" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alamat</label>
                            <input name="address" placeholder="Alamat kantor/site/client/WFH" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Aturan Clock</h3>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach([
                                'allow_clock_in' => 'Boleh clock-in',
                                'allow_clock_out' => 'Boleh clock-out',
                                'requires_photo' => 'Wajib selfie',
                                'requires_liveness' => 'Wajib liveness',
                            ] as $name => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                    <input type="checkbox" name="{{ $name }}" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="3" placeholder="Catatan aturan lokasi, pengecualian, atau instruksi ESS." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-location-dot"></i>
                        Simpan Geofence
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Buat Policy Lokasi</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Policy Lokasi',
                            'description' => 'Form ini mengatur siapa yang boleh memakai geofence tertentu dan pada periode apa.',
                            'steps' => [
                                'Pilih geofence dan tipe policy: office, WFH, client site, atau perjalanan dinas.',
                                'Tentukan scope policy per karyawan, perusahaan, atau cabang/site.',
                                'Isi periode berlaku, approval status, approver, dan hari yang diperbolehkan.',
                                'Gunakan Approved agar policy bisa dipakai validasi clock mobile.',
                            ],
                            'notes' => [
                                'WFH dan perjalanan dinas sebaiknya punya tanggal mulai dan akhir.',
                                'Policy ini membuat validasi geofence fleksibel tanpa mengubah master lokasi.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">Atur izin lokasi clock berdasarkan employee/scope dan periode.</p>
                </div>
                <form action="{{ route('attendance.geofences.policies') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Data Policy</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Policy</label>
                                <input name="policy_number" required placeholder="Contoh: GEO-POL-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Geofence</label>
                                <select name="attendance_geofence_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih geofence</option>
                                    @foreach($geofences as $geofence)
                                        <option value="{{ $geofence->id }}">{{ $geofence->code }} - {{ $geofence->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Policy</label>
                                <select name="policy_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($policyTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Policy</label>
                                <select name="assignment_scope" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($scopeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Target Policy</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                                <select name="employee_profile_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Jika scope karyawan</option>
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
                                    <option value="">Jika scope site</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Periode & Approval</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Berlaku</label>
                                <input type="date" name="effective_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Berlaku</label>
                                <input type="date" name="effective_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                                <select name="approval_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approved By</label>
                                <input name="approved_by" placeholder="Nama approver" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Hari Berlaku</h3>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($dayOptions as $value => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                    <input type="checkbox" name="allowed_days[]" value="{{ $value }}" @checked(in_array($value, ['mon', 'tue', 'wed', 'thu', 'fri'])) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Policy</label>
                            <textarea name="notes" rows="3" placeholder="Catatan WFH, client visit, nomor surat tugas, atau approval." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-shield-location"></i>
                        Simpan Policy Lokasi
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Simulasi Validasi GPS</h2>
                    <p class="text-sm text-gray-500">Cek apakah titik clock masuk radius geofence.</p>
                </div>
                <form method="GET" action="{{ route('attendance.geofences') }}" class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Geofence</label>
                        <select name="attendance_geofence_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih geofence</option>
                            @foreach($geofences as $geofence)
                                <option value="{{ $geofence->id }}">{{ $geofence->code }} - {{ $geofence->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Latitude Clock</label>
                        <input type="number" step="0.0000001" name="latitude" required placeholder="-6.2000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Longitude Clock</label>
                        <input type="number" step="0.0000001" name="longitude" required placeholder="106.8166667" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="flex items-end">
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <i class="fas fa-ruler-combined"></i>
                            Cek Radius
                        </button>
                    </div>
                </form>
                @if($simulation)
                    <div class="mt-4 rounded-xl border {{ $simulation['inside'] ? 'border-green-100 bg-green-50 text-green-700' : 'border-red-100 bg-red-50 text-red-700' }} px-4 py-3 text-sm font-semibold">
                        {{ $simulation['inside'] ? 'Titik clock masuk radius' : 'Titik clock di luar radius' }}:
                        jarak {{ number_format($simulation['distance'], 2, ',', '.') }} meter dari {{ $simulation['geofence']->code }}.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Geofence</h2>
                    <p class="text-sm text-gray-500">Master lokasi validasi clock mobile.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Lokasi</th>
                                <th class="px-5 py-3">Koordinat</th>
                                <th class="px-5 py-3">Scope</th>
                                <th class="px-5 py-3">Aturan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($geofences as $geofence)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $geofence->code }}</div>
                                        <div class="text-xs text-gray-500">{{ $geofence->name }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $locationTypes[$geofence->location_type] ?? $geofence->location_type }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $geofence->latitude }}, {{ $geofence->longitude }}</div>
                                        <div class="text-xs text-gray-500">Radius {{ $geofence->radius_meter }} meter</div>
                                        <div class="text-xs text-gray-500">{{ $geofence->address ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900">{{ $geofence->company?->code ?: 'Umum' }}</div>
                                        <div class="text-xs text-gray-500">{{ $geofence->branch?->code ?: 'Semua cabang/site' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if($geofence->allow_clock_in)<span class="rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-600">In</span>@endif
                                            @if($geofence->allow_clock_out)<span class="rounded-full bg-green-50 px-2 py-1 text-[11px] font-semibold text-green-600">Out</span>@endif
                                            @if($geofence->requires_photo)<span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-600">Selfie</span>@endif
                                            @if($geofence->requires_liveness)<span class="rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-600">Liveness</span>@endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada geofence.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Policy Lokasi</h2>
                    <p class="text-sm text-gray-500">Izin penggunaan geofence berdasarkan employee atau scope organisasi.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($policies as $policy)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $policy->policy_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $policyTypes[$policy->policy_type] ?? $policy->policy_type }} / {{ $policy->geofence?->code }}</div>
                                    <div class="text-xs text-gray-500">{{ $policy->employeeProfile?->full_name ?: ($policy->branch?->code ?: ($policy->company?->code ?: ucfirst($policy->assignment_scope))) }}</div>
                                </div>
                                <span class="rounded-full {{ $policy->approval_status === 'approved' ? 'bg-green-50 text-green-600' : ($policy->approval_status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }} px-3 py-1 text-xs font-semibold">{{ ucfirst($policy->approval_status) }}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">{{ $policy->effective_start?->format('d M Y') }} - {{ $policy->effective_end?->format('d M Y') ?? 'tanpa akhir' }} | Hari: {{ implode(', ', $policy->allowed_days ?? []) ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada policy lokasi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
