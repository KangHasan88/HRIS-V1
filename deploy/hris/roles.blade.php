@extends('layouts.sidebar')

@section('content')
@php
    $moduleLabels = [
        'Dashboard' => 'Dashboard',
        'Employees' => 'Karyawan',
        'Attendance' => 'Absensi',
        'Leave' => 'Cuti & Izin',
        'Payroll' => 'Payroll & Pajak',
        'Documents' => 'Dokumen',
        'Settings' => 'Pengaturan',
    ];

    $permissionLabels = [
        'view dashboard' => 'Lihat Dashboard',
        'view employees' => 'Lihat Karyawan',
        'create employees' => 'Tambah Karyawan',
        'edit employees' => 'Ubah Karyawan',
        'delete employees' => 'Hapus Karyawan',
        'view attendance' => 'Lihat Absensi',
        'approve attendance' => 'Approve Absensi',
        'view leave' => 'Lihat Cuti',
        'approve leave' => 'Approve Cuti',
        'view payroll' => 'Lihat Payroll',
        'approve payroll' => 'Approve Payroll',
        'manage payroll settings' => 'Kelola Setting Payroll',
        'view documents' => 'Lihat Dokumen',
        'manage documents' => 'Kelola Dokumen',
        'manage settings' => 'Kelola Pengaturan',
        'manage modules' => 'Kelola Modul',
        'view delivery progress' => 'Lihat Progress Delivery',
    ];

    $sensitiveModules = ['Payroll', 'Settings'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Role & Permission</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola template peran, scope akses, dan izin modul HRIS.</p>
        </div>
        <a href="{{ route('settings.users') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users-gear"></i>
            Atur User
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Peran</h2>
                    <p class="text-sm text-gray-500">Buat role khusus untuk struktur organisasi perusahaan.</p>
                </div>

                <form action="{{ route('settings.roles.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Peran</label>
                        <input type="text" name="name" required placeholder="Contoh: HR Supervisor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi</label>
                        <textarea name="description" rows="4" placeholder="Tanggung jawab dan batas akses role ini." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        @error('description')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i>
                        Simpan Peran
                    </button>
                </form>
            </div>

            <div class="sticky top-20 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Role</h2>
                    <p class="text-sm text-gray-500">Navigasi cepat ke role yang ingin dicek.</p>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach ($roles as $role)
                        <a href="#role-{{ $role->id }}" class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 text-sm transition hover:border-blue-200 hover:bg-blue-50">
                            <span>
                                <span class="block font-semibold text-gray-900">{{ $role->name }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500">{{ $role->users_count }} user</span>
                            </span>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">{{ $role->permissions->count() }} izin</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-5">
            @foreach ($roles as $role)
                <div id="role-{{ $role->id }}" class="scroll-mt-24 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-gray-900">{{ $role->name }}</h2>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $role->permissions->count() }} izin</span>
                                @if ($role->permissions->whereIn('module', $sensitiveModules)->count() > 0)
                                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">Sensitive Access</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $role->description ?: 'Belum ada deskripsi role.' }}</p>
                        </div>
                    </div>

                    @if ($role->name === 'Super Admin')
                        <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            <i class="fas fa-shield-halved mt-0.5"></i>
                            <span>Super Admin otomatis memiliki akses penuh ke seluruh modul dan tidak perlu diedit manual.</span>
                        </div>
                    @else
                        <form action="{{ route('settings.roles.permissions', $role->id) }}" method="POST" class="mt-5">
                            @csrf
                            <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                                @foreach ($permissions as $module => $modulePermissions)
                                    @php
                                        $activeCount = $modulePermissions->filter(fn ($permission) => $role->permissions->contains('id', $permission->id))->count();
                                    @endphp
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                        <div class="mb-3 flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $moduleLabels[$module] ?? $module }}</h3>
                                                    @if (in_array($module, $sensitiveModules, true))
                                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600">sensitif</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-xs text-gray-400">{{ $activeCount }} dari {{ $modulePermissions->count() }} izin aktif</p>
                                            </div>
                                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-blue-600">{{ $modulePermissions->count() }}</span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach ($modulePermissions as $permission)
                                                <label class="flex cursor-pointer items-start gap-3 rounded-lg bg-white p-3 text-sm transition hover:bg-blue-50">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                        {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span>
                                                        <span class="block font-semibold text-gray-900">{{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                                                        <span class="mt-0.5 block text-xs leading-5 text-gray-500">{{ $permission->description }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    <i class="fas fa-shield-halved"></i>
                                    Simpan Izin {{ $role->name }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
