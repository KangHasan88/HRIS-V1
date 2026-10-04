@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Workflow Approval</h1>
            <p class="mt-1 text-sm text-gray-500">Builder approval lintas modul dengan routing kondisional, fallback, SLA, dan delegasi.</p>
        </div>
        <a href="{{ route('settings.audit-trail') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-clipboard-list"></i>
            Audit Trail
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
                ['label' => 'Workflow', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-blue-600'],
                ['label' => 'Step', 'value' => $summary['steps'], 'color' => 'text-yellow-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Buat Workflow</h2>
            <p class="text-sm text-gray-500">Definisikan trigger, kondisi, fallback, dan maksimal tiga step approval awal.</p>
        </div>
        <form action="{{ route('settings.approval-workflows.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-4">
                <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <input name="name" required placeholder="Nama workflow" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="module_key" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="leave">Cuti & Izin</option>
                    <option value="overtime">Lembur</option>
                    <option value="attendance_correction">Koreksi Absensi</option>
                    <option value="employee_mutation">Mutasi Karyawan</option>
                    <option value="payroll">Payroll</option>
                    <option value="recruitment">Rekrutmen</option>
                </select>
                <input name="trigger_event" required value="submitted" placeholder="Trigger event" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
                <select name="condition_field" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="always">Selalu aktif</option>
                    <option value="amount">Amount</option>
                    <option value="days">Jumlah hari</option>
                    <option value="branch">Branch</option>
                    <option value="grade">Grade</option>
                </select>
                <select name="condition_operator" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="always">Always</option>
                    <option value="greater_than">Lebih dari</option>
                    <option value="less_than">Kurang dari</option>
                    <option value="equals">Sama dengan</option>
                    <option value="between">Di antara</option>
                </select>
                <input name="condition_value" placeholder="Nilai kondisi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="fallback_approver" placeholder="Fallback approver" value="HR Admin" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="description" placeholder="Deskripsi workflow" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 xl:col-span-2">
                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_enabled" value="1" checked class="rounded border-gray-300 text-blue-600">
                    Aktif
                </label>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                @foreach([0, 1, 2] as $index)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-3 text-sm font-semibold text-gray-900">Step {{ $index + 1 }}</div>
                        <div class="space-y-3">
                            <input name="steps[{{ $index }}][step_name]" {{ $index === 0 ? 'required' : '' }} placeholder="Nama step" value="{{ $index === 0 ? 'Review atasan' : '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <select name="steps[{{ $index }}][approver_type]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="manager">Manager</option>
                                <option value="role">Role</option>
                                <option value="branch_head">Branch Head</option>
                                <option value="hr_admin">HR Admin</option>
                                <option value="payroll_admin">Payroll Admin</option>
                                <option value="user">User tertentu</option>
                            </select>
                            <input name="steps[{{ $index }}][approver_value]" {{ $index === 0 ? 'required' : '' }} placeholder="Approver value" value="{{ $index === 0 ? 'direct_manager' : '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input name="steps[{{ $index }}][routing_condition]" placeholder="Kondisi routing" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <input type="number" name="steps[{{ $index }}][sla_hours]" min="1" max="720" value="24" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <div class="flex items-center gap-4 text-xs text-gray-600">
                                <label class="inline-flex items-center gap-2"><input type="checkbox" name="steps[{{ $index }}][is_parallel]" value="1" class="rounded border-gray-300 text-blue-600"> Parallel</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" name="steps[{{ $index }}][can_delegate]" value="1" checked class="rounded border-gray-300 text-blue-600"> Delegasi</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-route"></i>
                Simpan Workflow
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Workflow</h2>
            <p class="text-sm text-gray-500">Workflow approval yang siap dipakai modul HRIS.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Workflow</th>
                        <th class="px-5 py-3">Company</th>
                        <th class="px-5 py-3">Modul</th>
                        <th class="px-5 py-3">Kondisi</th>
                        <th class="px-5 py-3">Step</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($workflows as $workflow)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $workflow->code }}</div>
                                <div class="text-xs text-gray-500">{{ $workflow->name }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $workflow->company?->code ?? 'All' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', ucfirst($workflow->module_key)) }}<div class="text-xs text-gray-500">{{ $workflow->trigger_event }}</div></td>
                            <td class="px-5 py-4 text-xs text-gray-500">{{ $workflow->condition_schema['field'] ?? 'always' }} {{ $workflow->condition_schema['operator'] ?? '' }} {{ $workflow->condition_schema['value'] ?? '' }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                @foreach($workflow->steps as $step)
                                    <div class="mb-1">{{ $step->step_order }}. {{ $step->step_name }} <span class="text-xs text-gray-400">({{ $step->approver_value }})</span></div>
                                @endforeach
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($workflow->status) }}</span>
                                <div class="mt-1 text-xs {{ $workflow->is_enabled ? 'text-green-600' : 'text-gray-400' }}">{{ $workflow->is_enabled ? 'Aktif' : 'Nonaktif' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada workflow approval.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
