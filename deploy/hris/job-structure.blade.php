@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Master Jabatan</h1>
            <p class="mt-1 text-sm text-gray-500">Struktur unit, posisi, grade, dan cost center untuk employee master.</p>
        </div>
        <a href="{{ route('hris.employees') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users"></i>
            Data Karyawan
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
                ['label' => 'Unit', 'value' => $summary['units'], 'color' => 'text-indigo-600'],
                ['label' => 'Posisi', 'value' => $summary['positions'], 'color' => 'text-blue-600'],
                ['label' => 'Grade', 'value' => $summary['grades'], 'color' => 'text-purple-600'],
                ['label' => 'Cost Center', 'value' => $summary['cost_centers'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="min-h-[72px] border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Unit</h2>
                <p class="text-sm text-gray-500">Divisi, departemen, section, atau team.</p>
            </div>
            <form action="{{ route('settings.job-structure.units.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Cabang/site opsional</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->company?->code }} - {{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Parent unit opsional</option>
                    @foreach($organizationUnits as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->company?->code }} - {{ $unit->code }} - {{ $unit->name }}</option>
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
            <div class="min-h-[72px] border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Grade</h2>
                <p class="text-sm text-gray-500">Level jabatan dan salary band.</p>
            </div>
            <form action="{{ route('settings.job-structure.job-grades.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode grade" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="level" required type="number" min="1" placeholder="Level" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="name" required placeholder="Nama grade" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Grade
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="min-h-[72px] border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Cost Center</h2>
                <p class="text-sm text-gray-500">Kode biaya payroll dan laporan.</p>
            </div>
            <form action="{{ route('settings.job-structure.cost-centers.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Cabang/site opsional</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->company?->code }} - {{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
                <input name="code" required placeholder="Kode biaya" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="name" required placeholder="Nama cost center" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="owner_name" placeholder="Owner/PIC" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Cost Center
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="min-h-[72px] border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Posisi</h2>
                <p class="text-sm text-gray-500">Jabatan, unit, grade, dan cost center.</p>
            </div>
            <form action="{{ route('settings.job-structure.job-positions.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="company_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih perusahaan</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="organization_unit_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih unit opsional</option>
                    @foreach($organizationUnits as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->company?->code }} - {{ $unit->code }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="job_grade_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Grade opsional</option>
                        @foreach($jobGrades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->company?->code }} - {{ $grade->code }} / L{{ $grade->level }}</option>
                        @endforeach
                    </select>
                    <select name="cost_center_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Cost center opsional</option>
                        @foreach($costCenters as $costCenter)
                            <option value="{{ $costCenter->id }}">{{ $costCenter->company?->code }} - {{ $costCenter->code }}</option>
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

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Posisi</h2>
            <p class="text-sm text-gray-500">Mapping posisi ke unit, grade, cost center, dan headcount plan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Posisi</th>
                        <th class="px-5 py-3">Perusahaan</th>
                        <th class="px-5 py-3">Unit</th>
                        <th class="px-5 py-3">Grade</th>
                        <th class="px-5 py-3">Cost Center</th>
                        <th class="px-5 py-3 text-right">HC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jobPositions as $position)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $position->name }}</div>
                                <div class="text-xs text-gray-500">{{ $position->code }} / {{ ucfirst($position->employment_category) }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $position->company?->code ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $position->organizationUnit?->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $position->jobGrade?->code ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $position->costCenter?->code ?? '-' }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">{{ $position->headcount_plan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada posisi jabatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
