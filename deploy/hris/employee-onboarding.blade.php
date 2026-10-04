@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Onboarding Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Template dan checklist onboarding berdasarkan role, lokasi, dan departemen.</p>
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
                ['label' => 'Template Aktif', 'value' => $summary['templates'], 'color' => 'text-blue-600'],
                ['label' => 'Open', 'value' => $summary['open'], 'color' => 'text-yellow-600'],
                ['label' => 'Progress', 'value' => $summary['in_progress'], 'color' => 'text-indigo-600'],
                ['label' => 'Selesai', 'value' => $summary['completed'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Buat Paket Onboarding</h2>
                <p class="text-sm text-gray-500">Definisikan paket tugas standar untuk karyawan baru.</p>
            </div>
            <form action="{{ route('hris.employee-onboarding.templates') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="code" required placeholder="Kode paket" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="name" required placeholder="Nama paket onboarding" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="role_scope" placeholder="Berlaku untuk jabatan/role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="department_scope" placeholder="Berlaku untuk departemen" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Semua perusahaan</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Semua cabang/site</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="task_names[]" required placeholder="Tugas onboarding 1, contoh: Lengkapi dokumen personal" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="task_names[]" required placeholder="Tugas onboarding 2, contoh: Setup akun dan perangkat kerja" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="task_names[]" required placeholder="Tugas onboarding 3, contoh: Induksi HSE dan orientasi departemen" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="description" rows="2" placeholder="Catatan paket" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Paket
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Generate Checklist</h2>
                <p class="text-sm text-gray-500">Assign paket onboarding ke karyawan baru.</p>
            </div>
            <form action="{{ route('hris.employee-onboarding.assign') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->department ?? '-' }}</option>
                    @endforeach
                </select>
                <select name="employee_onboarding_template_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih paket onboarding</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->code }} - {{ $template->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input type="date" name="base_due_date" required title="Due date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="owner_role" placeholder="Owner role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="owner_name" placeholder="Owner name" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-list-check"></i>
                    Generate Task
                </button>
            </form>

            <div class="mt-6 border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Paket Aktif</h3>
                <div class="mt-3 space-y-2">
                    @forelse($templates as $template)
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="font-semibold text-gray-900">{{ $template->code }} - {{ $template->name }}</div>
                            <div class="text-xs text-gray-500">{{ $template->role_scope ?: 'Semua role' }} / {{ $template->department_scope ?: 'Semua departemen' }} / {{ $template->branch?->code ?? 'Semua lokasi' }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Belum ada paket onboarding.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Checklist Onboarding</h2>
            <p class="text-sm text-gray-500">Tracking task onboarding per karyawan dan owner.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Task</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Owner</th>
                        <th class="px-5 py-3">Due</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tasks as $task)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $task->task_name }}</div>
                                <div class="text-xs text-gray-500">{{ $task->task_code }} / {{ $task->template?->code ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $task->employeeProfile?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $task->employeeProfile?->employee_number }} / {{ $task->employeeProfile?->branch?->code ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $task->owner_role ?: '-' }}
                                <div class="text-xs text-gray-500">{{ $task->owner_name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <form action="{{ route('hris.employee-onboarding.tasks.update', $task) }}" method="POST" class="flex min-w-72 items-center gap-2">
                                    @csrf
                                    <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        @foreach(['open' => 'Open', 'in_progress' => 'Progress', 'completed' => 'Selesai', 'blocked' => 'Blocked', 'cancelled' => 'Batal'] as $value => $label)
                                            <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="completed_at" value="{{ $task->completed_at?->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <button class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada checklist onboarding.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
