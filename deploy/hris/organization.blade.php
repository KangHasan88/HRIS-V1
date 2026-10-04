@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Organisasi</h1>
            <p class="mt-1 text-sm text-gray-500">Scope multi-perusahaan, badan hukum, cabang, dan assignment user.</p>
        </div>
        <a href="{{ route('settings.users') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users-gear"></i>
            Review User
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-7">
            @foreach([
                ['label' => 'Perusahaan', 'value' => $summary['companies'], 'color' => 'text-blue-600'],
                ['label' => 'Badan Hukum', 'value' => $summary['legal_entities'], 'color' => 'text-green-600'],
                ['label' => 'Cabang / Site', 'value' => $summary['branches'], 'color' => 'text-yellow-600'],
                ['label' => 'Unit', 'value' => $summary['organization_units'], 'color' => 'text-indigo-600'],
                ['label' => 'Posisi', 'value' => $summary['job_positions'], 'color' => 'text-blue-600'],
                ['label' => 'Grade', 'value' => $summary['job_grades'], 'color' => 'text-purple-600'],
                ['label' => 'Cost Center', 'value' => $summary['cost_centers'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Perusahaan</h2>
                <p class="text-sm text-gray-500">Tenant atau company induk HRIS.</p>
            </div>
            <form action="{{ route('settings.organization.companies.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode, contoh BMP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="brand_name" placeholder="Brand" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama perusahaan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="tax_id" placeholder="NPWP / Tax ID" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="address" rows="2" placeholder="Alamat" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Perusahaan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Badan Hukum</h2>
                <p class="text-sm text-gray-500">PT/CV/yayasan di bawah company.</p>
            </div>
            <form action="{{ route('settings.organization.legal-entities.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="npwp" placeholder="NPWP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama badan hukum" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="nib" placeholder="NIB" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Jadikan utama
                </label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Badan Hukum
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Cabang / Site</h2>
                <p class="text-sm text-gray-500">Lokasi kerja, kantor, area, atau site.</p>
            </div>
            <form action="{{ route('settings.organization.branches.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <select name="legal_entity_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Badan hukum opsional</option>
                    @foreach($legalEntityOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="city" placeholder="Kota" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama cabang/site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_head_office" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Head Office
                </label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Cabang
                </button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Unit</h2>
                <p class="text-sm text-gray-500">Divisi, departemen, section, atau team.</p>
            </div>
            <form action="{{ route('settings.organization.units.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Cabang/site opsional</option>
                    @foreach($branchOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Parent unit opsional</option>
                    @foreach($organizationUnitOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode unit" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="unit_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="department">Departemen</option>
                        <option value="division">Divisi</option>
                        <option value="section">Section</option>
                        <option value="team">Team</option>
                    </select>
                </div>
                <input name="name" required placeholder="Nama unit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="manager_name" placeholder="Manager/PIC" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Unit
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Grade</h2>
                <p class="text-sm text-gray-500">Level jabatan dan salary band.</p>
            </div>
            <form action="{{ route('settings.organization.job-grades.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode grade" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="level" required type="number" min="1" placeholder="Level" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama grade" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="minimum_salary" type="number" min="0" placeholder="Gaji minimum" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="maximum_salary" type="number" min="0" placeholder="Gaji maksimum" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Grade
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Cost Center</h2>
                <p class="text-sm text-gray-500">Kode biaya untuk payroll dan laporan.</p>
            </div>
            <form action="{{ route('settings.organization.cost-centers.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Cabang/site opsional</option>
                    @foreach($branchOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode biaya" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="owner_name" placeholder="Owner/PIC" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama cost center" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_billable" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Billable ke project/client
                </label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Cost Center
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Posisi</h2>
                <p class="text-sm text-gray-500">Jabatan dengan unit, grade, dan cost center.</p>
            </div>
            <form action="{{ route('settings.organization.job-positions.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companyOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <select name="organization_unit_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih unit opsional</option>
                    @foreach($organizationUnitOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} - {{ $option->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="job_grade_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Grade opsional</option>
                        @foreach($jobGradeOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }} / L{{ $option->level }}</option>
                        @endforeach
                    </select>
                    <select name="cost_center_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Cost center opsional</option>
                        @foreach($costCenterOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->company?->code }} - {{ $option->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode posisi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="headcount_plan" required type="number" min="1" value="1" placeholder="Headcount" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama posisi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="employment_category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="staff">Staff</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="manager">Manager</option>
                    <option value="executive">Executive</option>
                    <option value="temporary">Temporary</option>
                </select>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Posisi
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-5">
        @foreach($companies as $company)
            @php
                $companyUnits = $organizationUnitsByCompany->get($company->id, collect());
                $companyGrades = $jobGradesByCompany->get($company->id, collect());
                $companyCostCenters = $costCentersByCompany->get($company->id, collect());
                $companyPositions = $jobPositionsByCompany->get($company->id, collect());
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h2>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $company->code }}</span>
                            <span class="rounded-full {{ $company->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }} px-3 py-1 text-xs font-semibold">
                                {{ $company->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ $company->brand_name ?: 'Brand belum diisi' }} - {{ $company->address ?: 'Alamat belum diisi' }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-right text-sm">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $company->legal_entities_count }}</div>
                            <div class="text-xs text-gray-500">Badan hukum</div>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $company->branches_count }}</div>
                            <div class="text-xs text-gray-500">Cabang</div>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $company->users_count }}</div>
                            <div class="text-xs text-gray-500">User</div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-5 xl:grid-cols-[.9fr_1.2fr]">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Badan Hukum</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $company->legalEntities->count() }}</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($company->legalEntities as $entity)
                                <div class="rounded-lg bg-white p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $entity->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $entity->code }} - NPWP {{ $entity->npwp ?: '-' }}</div>
                                        </div>
                                        @if($entity->is_primary)
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">Utama</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Cabang / Site</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $company->branches->count() }}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">Kode</th>
                                        <th class="px-4 py-3">Cabang</th>
                                        <th class="px-4 py-3">Kota</th>
                                        <th class="px-4 py-3 text-right">User</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($company->branches as $branch)
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $branch->code }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $branch->name }}</div>
                                                @if($branch->is_head_office)
                                                    <div class="text-xs font-medium text-blue-600">Head Office</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $branch->city ?: '-' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $branch->users_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 border-t border-gray-100 p-5 xl:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Unit Organisasi</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $companyUnits->count() }}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">Kode</th>
                                        <th class="px-4 py-3">Unit</th>
                                        <th class="px-4 py-3">Tipe</th>
                                        <th class="px-4 py-3">PIC</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($companyUnits as $unit)
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $unit->code }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $unit->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $unit->branch?->code ?? 'All site' }}{{ $unit->parent ? ' / parent ' . $unit->parent->code : '' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($unit->unit_type) }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $unit->manager_name ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada unit.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Grade Jabatan</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $companyGrades->count() }}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">Grade</th>
                                        <th class="px-4 py-3">Level</th>
                                        <th class="px-4 py-3">Salary Band</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($companyGrades as $grade)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $grade->code }}</div>
                                                <div class="text-xs text-gray-500">{{ $grade->name }}</div>
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">L{{ $grade->level }}</td>
                                            <td class="px-4 py-3 text-gray-600">
                                                @if($grade->minimum_salary || $grade->maximum_salary)
                                                    Rp {{ number_format((float) $grade->minimum_salary, 0, ',', '.') }} - Rp {{ number_format((float) $grade->maximum_salary, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada grade.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Cost Center</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $companyCostCenters->count() }}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">Kode</th>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">Scope</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($companyCostCenters as $costCenter)
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $costCenter->code }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $costCenter->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $costCenter->owner_name ?: 'Owner belum diisi' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $costCenter->branch?->code ?? 'All site' }}{{ $costCenter->is_billable ? ' / Billable' : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada cost center.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Posisi Jabatan</h3>
                            <span class="text-xs font-semibold text-blue-600">{{ $companyPositions->count() }}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-4 py-3">Posisi</th>
                                        <th class="px-4 py-3">Unit</th>
                                        <th class="px-4 py-3">Grade</th>
                                        <th class="px-4 py-3 text-right">HC</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($companyPositions as $position)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $position->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $position->code }} / {{ ucfirst($position->employment_category) }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $position->organizationUnit?->code ?? '-' }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $position->jobGrade?->code ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $position->headcount_plan }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada posisi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
