@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Module Switcher</h1>
            <p class="mt-1 text-sm text-gray-500">Aktifkan atau nonaktifkan modul HRIS per perusahaan.</p>
        </div>
        <a href="{{ route('hris.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-arrow-left"></i>
            Kembali Dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Perusahaan Aktif</h2>
                <p class="mt-1 text-sm text-gray-500">Konfigurasi modul disimpan per perusahaan, global module dipakai sebagai default.</p>
            </div>
            <form method="GET" action="{{ route('settings.modules') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <select name="company_id" class="min-w-72 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ $selectedCompany?->id === $company->id ? 'selected' : '' }}>
                            {{ $company->code }} - {{ $company->name }}
                        </option>
                    @endforeach
                </select>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i>
                    Terapkan
                </button>
            </form>
        </div>
        @if($selectedCompany)
            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                <i class="fas fa-building mr-2"></i>
                Sedang mengatur modul untuk <strong>{{ $selectedCompany->code }}</strong> - {{ $selectedCompany->name }}.
            </div>
        @else
            <div class="mt-4 rounded-xl border border-yellow-100 bg-yellow-50 px-4 py-3 text-sm text-yellow-700">
                Belum ada perusahaan aktif. Tambahkan perusahaan dulu di menu Organisasi.
            </div>
        @endif
    </div>

    @foreach($modules as $group => $groupModules)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $group }}</h2>
                    <p class="text-sm text-gray-500">{{ $groupModules->count() }} modul tersedia di group ini.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">
                    {{ $groupModules->where('effective_is_enabled', true)->count() }} aktif
                </span>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($groupModules as $module)
                    <div class="rounded-xl border border-gray-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $module->name }}</div>
                                <div class="mt-1 text-xs text-gray-400">{{ $module->code }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $module->effective_is_enabled ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $module->is_core && $module->effective_is_enabled ? 'Core' : ($module->effective_is_enabled ? 'Aktif' : 'Nonaktif') }}
                                </span>
                                <form method="POST" action="{{ route('settings.modules.toggle', $module) }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ $selectedCompany?->id }}">
                                    <input type="hidden" name="is_enabled" value="{{ $module->effective_is_enabled ? 0 : 1 }}">
                                    <button type="submit"
                                            class="group relative inline-flex h-6 w-11 items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $module->effective_is_enabled ? 'bg-blue-600' : 'bg-gray-200' }} {{ ($module->is_core && $module->effective_is_enabled) || ! $selectedCompany ? 'cursor-not-allowed opacity-60' : 'hover:shadow-sm' }}"
                                            title="{{ $module->is_core && $module->effective_is_enabled ? 'Modul core wajib aktif' : ($module->effective_is_enabled ? 'Nonaktifkan modul' : 'Aktifkan modul') }}"
                                            aria-label="{{ $module->effective_is_enabled ? 'Nonaktifkan ' . $module->name : 'Aktifkan ' . $module->name }}"
                                            {{ ($module->is_core && $module->effective_is_enabled) || ! $selectedCompany ? 'disabled' : '' }}>
                                        <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition {{ $module->effective_is_enabled ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="mt-3 min-h-[42px] text-sm leading-5 text-gray-500">{{ $module->description }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full {{ $module->has_company_override ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500' }} px-2.5 py-1 font-semibold">
                                {{ $module->has_company_override ? 'Override company' : 'Default global' }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 font-semibold text-gray-500">
                                Global: {{ $module->is_enabled ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="mb-2 flex items-center justify-between text-xs">
                                <span class="font-semibold uppercase tracking-wide text-gray-400">{{ $module->platform }}</span>
                                <span class="font-semibold text-blue-600">{{ $module->progress }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ $module->progress }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
