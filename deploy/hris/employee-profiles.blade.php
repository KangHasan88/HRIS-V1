@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Data Karyawan 360</h1>
            <p class="mt-1 text-sm text-gray-500">Profil pekerjaan, keluarga, bank, pajak, BPJS, dan ringkasan dokumen karyawan.</p>
        </div>
        <a href="{{ route('payroll.tax-profiles') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-invoice-dollar"></i>
            Master PPh 21
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
                ['label' => 'Total', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Tetap', 'value' => $summary['permanent'], 'color' => 'text-blue-600'],
                ['label' => 'Kontrak', 'value' => $summary['contract'], 'color' => 'text-yellow-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Tambah Karyawan</h2>
            <p class="text-sm text-gray-500">Input ringkas untuk membentuk profil 360 awal.</p>
        </div>
        <form action="{{ route('hris.employees.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-4">
                <select name="user_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Tanpa user login</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                    @endforeach
                </select>
                <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="tax_profile_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Link profil pajak</option>
                    @foreach($taxProfiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->employee_number }} - {{ $profile->employee_name }}</option>
                    @endforeach
                </select>
                <input name="employee_number" required placeholder="NIK karyawan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="full_name" required placeholder="Nama lengkap" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="employment_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="probation">Probation</option>
                    <option value="contract">Kontrak</option>
                    <option value="permanent">Tetap</option>
                    <option value="intern">Magang</option>
                    <option value="alumni">Alumni</option>
                </select>
                <input name="position" list="job-position-options" placeholder="Jabatan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="department" list="organization-unit-options" placeholder="Departemen / Unit" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="grade" list="job-grade-options" placeholder="Grade" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="cost_center" list="cost-center-options" placeholder="Cost center" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <datalist id="job-position-options">
                    @foreach($jobPositions as $option)
                        <option value="{{ $option->name }}">{{ $option->code }}</option>
                    @endforeach
                </datalist>
                <datalist id="organization-unit-options">
                    @foreach($organizationUnits as $option)
                        <option value="{{ $option->name }}">{{ $option->code }}</option>
                    @endforeach
                </datalist>
                <datalist id="job-grade-options">
                    @foreach($jobGrades as $option)
                        <option value="{{ $option->code }}">{{ $option->name }} / L{{ $option->level }}</option>
                    @endforeach
                </datalist>
                <datalist id="cost-center-options">
                    @foreach($costCenters as $option)
                        <option value="{{ $option->code }}">{{ $option->name }}</option>
                    @endforeach
                </datalist>
                <input type="date" name="join_date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input type="date" name="contract_end_date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="work_email" placeholder="Email kerja" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="personal_email" placeholder="Email personal" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="phone" placeholder="Telepon" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="address" placeholder="Alamat" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 xl:col-span-2">
                <input name="emergency_contact_name" placeholder="Kontak darurat" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="emergency_contact_phone" placeholder="No. kontak darurat" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-900">Keluarga</div>
                        <button type="button" id="add-family-member" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                            <i class="fas fa-plus"></i>
                            Tambah
                        </button>
                    </div>
                    <div id="family-members" class="space-y-3">
                        <div class="family-member-row rounded-lg border border-gray-200 bg-white p-3" data-family-index="0">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="family-member-title text-xs font-semibold uppercase tracking-wide text-gray-400">Anggota 1</div>
                            </div>
                            <div class="grid gap-2 md:grid-cols-2">
                                <input name="family_members[0][name]" placeholder="Nama keluarga" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <select name="family_members[0][relationship]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Pilih hubungan</option>
                                    <option value="Orang Tua">Orang tua</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak">Anak</option>
                                </select>
                                <input type="date" name="family_members[0][birth_date]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="family_members[0][is_dependent]" value="1" class="rounded border-gray-300 text-blue-600"> Tanggungan</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3 text-sm font-semibold text-gray-900">Bank</div>
                    <div class="space-y-3">
                        <input name="bank_name" placeholder="Nama bank" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="account_number" placeholder="Nomor rekening" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="account_holder_name" placeholder="Nama pemilik rekening" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3 text-sm font-semibold text-gray-900">BPJS</div>
                    <div class="space-y-3">
                        <input name="bpjs_kesehatan_number" placeholder="BPJS Kesehatan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="bpjs_ketenagakerjaan_number" placeholder="BPJS Ketenagakerjaan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="bpjs_class" placeholder="Kelas BPJS" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600"> Karyawan aktif</label>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-plus"></i>
                    Simpan Karyawan
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Karyawan</h2>
            <p class="text-sm text-gray-500">Ringkasan profil 360 yang sudah terbentuk.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Organisasi</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Pajak</th>
                        <th class="px-5 py-3">Bank</th>
                        <th class="px-5 py-3">BPJS</th>
                        <th class="px-5 py-3">Keluarga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $employee->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $employee->employee_number }} - {{ $employee->position ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->company?->code ?? '-' }}<div class="text-xs text-gray-500">{{ $employee->branch?->code ?? '-' }} / {{ $employee->department ?? '-' }}</div></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($employee->employment_status) }}</span></td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->work_email ?? '-' }}<div class="text-xs text-gray-500">{{ $employee->phone ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->taxProfile?->npwp ?? '-' }}<div class="text-xs text-gray-500">{{ $employee->taxProfile?->ptkp_status ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->bankAccounts->first()?->bank_name ?? '-' }}<div class="text-xs text-gray-500">{{ $employee->bankAccounts->first()?->account_number ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->bpjsProfile?->bpjs_kesehatan_number ?? '-' }}<div class="text-xs text-gray-500">{{ $employee->bpjsProfile?->bpjs_ketenagakerjaan_number ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->familyMembers->count() }} data</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada profil karyawan 360.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('family-members');
        const addButton = document.getElementById('add-family-member');
        let familyIndex = 1;

        const relationshipOptions = `
            <option value="">Pilih hubungan</option>
            <option value="Orang Tua">Orang tua</option>
            <option value="Suami">Suami</option>
            <option value="Istri">Istri</option>
            <option value="Anak">Anak</option>
        `;

        const refreshTitles = () => {
            wrapper.querySelectorAll('.family-member-row').forEach((row, index) => {
                row.querySelector('.family-member-title').textContent = `Anggota ${index + 1}`;
            });
        };

        addButton?.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'family-member-row rounded-lg border border-gray-200 bg-white p-3';
            row.dataset.familyIndex = familyIndex;
            row.innerHTML = `
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div class="family-member-title text-xs font-semibold uppercase tracking-wide text-gray-400">Anggota ${wrapper.children.length + 1}</div>
                    <button type="button" class="remove-family-member inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                        <i class="fas fa-trash"></i>
                        Hapus
                    </button>
                </div>
                <div class="grid gap-2 md:grid-cols-2">
                    <input name="family_members[${familyIndex}][name]" placeholder="Nama keluarga" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="family_members[${familyIndex}][relationship]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">${relationshipOptions}</select>
                    <input type="date" name="family_members[${familyIndex}][birth_date]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="family_members[${familyIndex}][is_dependent]" value="1" class="rounded border-gray-300 text-blue-600"> Tanggungan</label>
                </div>
            `;
            familyIndex += 1;
            wrapper.appendChild(row);
            refreshTitles();
        });

        wrapper?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-family-member');
            if (!removeButton) {
                return;
            }

            removeButton.closest('.family-member-row')?.remove();
            refreshTitles();
        });
    });
</script>
@endsection
