@extends('layouts.sidebar')

@section('title', 'Struktur Gaji')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Struktur Gaji</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola salary band, assignment kompensasi karyawan, dan akses data gaji rahasia.</p>
        </div>
        <a href="{{ route('payroll.components') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            <i class="fas fa-sliders mr-2"></i> Komponen Payroll
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <div class="text-xs font-semibold uppercase text-gray-400">Salary Band</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ $summary['bands'] }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase text-gray-400">Assignment</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ $summary['assignments'] }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase text-gray-400">Akses Policy</div>
                <div class="mt-1 text-2xl font-bold text-amber-600">{{ $summary['policies'] }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase text-gray-400">Restricted</div>
                <div class="mt-1 text-2xl font-bold text-red-600">{{ $summary['restricted'] }}</div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Buat Salary Band</h2>
                    <p class="text-sm text-gray-600">Range gaji per grade dan periode efektif.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Grade Matrix</span>
            </div>
            <form method="POST" action="{{ route('payroll.salary-structures.bands') }}" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-bold text-gray-900">Identitas Band</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Perusahaan</span>
                            <select name="company_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Grade</span>
                            <select name="job_grade_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Tanpa link grade</option>
                                @foreach($jobGrades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->code }} - {{ $grade->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Kode Band</span>
                            <input name="band_code" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: G5-BMP-2026">
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Nama Band</span>
                            <input name="band_name" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Supervisor Site">
                        </label>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-bold text-gray-900">Range Gaji</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-4">
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Level</span>
                            <input type="number" name="level" value="1" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Minimum</span>
                            <input type="number" name="minimum_salary" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="0">
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Midpoint</span>
                            <input type="number" name="midpoint_salary" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="0">
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Maximum</span>
                            <input type="number" name="maximum_salary" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="0">
                        </label>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-4">
                    <input name="currency" value="IDR" class="rounded-lg border-gray-300 text-sm">
                    <input type="date" name="effective_start" class="rounded-lg border-gray-300 text-sm">
                    <input type="date" name="effective_end" class="rounded-lg border-gray-300 text-sm">
                    <select name="status" class="rounded-lg border-gray-300 text-sm">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan salary band, referensi survey market, atau approval kompensasi."></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Simpan Band</button>
                </div>
            </form>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-bold text-gray-900">Assignment Kompensasi</h2>
                <p class="text-sm text-gray-600">Pasang salary band ke karyawan dengan status approval dan level kerahasiaan.</p>
            </div>
            <form method="POST" action="{{ route('payroll.salary-structures.assignments') }}" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-bold text-gray-900">Data Karyawan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Karyawan</span>
                            <select name="employee_profile_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase text-gray-500">Salary Band</span>
                            <select name="payroll_salary_band_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Belum link band</option>
                                @foreach($bands as $band)
                                    <option value="{{ $band->id }}">{{ $band->band_code }} - {{ $band->band_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase text-gray-500">Posisi</span>
                            <select name="job_position_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Opsional</option>
                                @foreach($jobPositions as $position)
                                    <option value="{{ $position->id }}">{{ $position->code }} - {{ $position->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-bold text-gray-900">Nilai Kompensasi</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <input type="number" name="base_salary" class="rounded-lg border-gray-300 text-sm" placeholder="Gaji pokok">
                        <input type="number" name="fixed_allowance" class="rounded-lg border-gray-300 text-sm" placeholder="Tunjangan tetap">
                        <input type="number" name="variable_allowance" class="rounded-lg border-gray-300 text-sm" placeholder="Tunjangan variabel">
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-5">
                    <select name="payroll_frequency" class="rounded-lg border-gray-300 text-sm">
                        <option value="monthly">Bulanan</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                    </select>
                    <input type="date" name="effective_start" class="rounded-lg border-gray-300 text-sm">
                    <input type="date" name="effective_end" class="rounded-lg border-gray-300 text-sm">
                    <select name="approval_status" class="rounded-lg border-gray-300 text-sm">
                        <option value="draft">Draft</option>
                        <option value="pending_approval">Menunggu Approval</option>
                        <option value="approved">Approved</option>
                    </select>
                    <select name="confidentiality_level" class="rounded-lg border-gray-300 text-sm">
                        <option value="restricted">Restricted</option>
                        <option value="payroll_only">Payroll Only</option>
                        <option value="hr_payroll">HR + Payroll</option>
                        <option value="executive">Executive</option>
                    </select>
                </div>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Catatan perubahan salary, approval, atau dokumen pendukung."></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-lock mr-2"></i>Simpan Assignment</button>
                </div>
            </form>
        </section>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-bold text-gray-900">Policy Akses Kompensasi</h2>
            <p class="text-sm text-gray-600">Atur siapa yang boleh melihat, mengubah, atau export data salary.</p>
        </div>
        <form method="POST" action="{{ route('payroll.salary-structures.policies') }}" class="mt-5 grid gap-3 lg:grid-cols-6">
            @csrf
            <input name="policy_name" class="rounded-lg border-gray-300 text-sm lg:col-span-2" placeholder="Nama policy, contoh: Payroll Admin Full Access">
            <input name="role_scope" class="rounded-lg border-gray-300 text-sm" placeholder="Role scope">
            <select name="access_level" class="rounded-lg border-gray-300 text-sm">
                <option value="masked">Masked</option>
                <option value="summary">Summary</option>
                <option value="detail">Detail</option>
                <option value="export">Export</option>
            </select>
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-shield-halved mr-2"></i>Simpan Policy</button>
            <div class="grid gap-3 lg:col-span-6 md:grid-cols-4">
                <label class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="can_view_salary" value="1" class="mr-2 rounded">Boleh lihat salary</label>
                <label class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="can_edit_salary" value="1" class="mr-2 rounded">Boleh ubah salary</label>
                <label class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="can_export_salary" value="1" class="mr-2 rounded">Boleh export salary</label>
                <label class="rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="requires_approval" value="1" checked class="mr-2 rounded">Perlu approval</label>
            </div>
            <textarea name="notes" rows="2" class="rounded-lg border-gray-300 text-sm lg:col-span-6" placeholder="Catatan policy akses, masking, dan approval export."></textarea>
        </form>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-5">
            <h2 class="text-lg font-bold text-gray-900">Daftar Struktur Gaji</h2>
            <p class="text-sm text-gray-600">Ringkasan salary band dan assignment kompensasi aktif.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                        <th class="px-5 py-3">Band</th>
                        <th class="px-5 py-3">Grade</th>
                        <th class="px-5 py-3">Range</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Assignment Terbaru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bands as $band)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $band->band_code }}</div>
                                <div class="text-xs text-gray-500">{{ $band->band_name }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $band->jobGrade?->code ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $band->currency }} {{ number_format((float) $band->minimum_salary, 0, ',', '.') }} - {{ number_format((float) $band->maximum_salary, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Midpoint {{ number_format((float) $band->midpoint_salary, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($band->status) }}</span></td>
                            <td class="px-5 py-4">{{ $band->salaryAssignments()->count() }} karyawan</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada salary band.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
