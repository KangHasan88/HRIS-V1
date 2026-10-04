@extends('layouts.sidebar')

@section('title', 'Retensi & Legal Hold')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Retensi & Legal Hold</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola masa simpan, arsip, disposal, legal hold, dan log eksekusi data HRIS.</p>
        </div>
        <a href="{{ route('settings.data-privacy') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-user-lock"></i>
            Data Privacy
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Policy', 'value' => $summary['policies'], 'color' => 'text-blue-600'],
                ['label' => 'Policy Aktif', 'value' => $summary['active_policies'], 'color' => 'text-green-600'],
                ['label' => 'Legal Hold', 'value' => $summary['legal_holds'], 'color' => 'text-red-600'],
                ['label' => 'Approval', 'value' => $summary['pending_approval'], 'color' => 'text-amber-600'],
                ['label' => 'Execution', 'value' => $summary['executions'], 'color' => 'text-blue-600'],
                ['label' => 'Blocked', 'value' => $summary['blocked'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Buat Policy Retensi</h2>
            <p class="mt-1 text-sm text-gray-500">Atur domain data, masa simpan, strategi arsip, dan disposal.</p>
            <form method="POST" action="{{ route('settings.data-retention-compliance.policies') }}" class="mt-5 space-y-3">
                @csrf
                <input name="policy_code" required placeholder="Contoh: RET-PAY-095" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="policy_name" required placeholder="Nama policy retensi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Global semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="data_domain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($domains as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="retention_years" min="1" max="30" value="7" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="archive_strategy" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="cold_storage">Cold storage</option>
                        <option value="encrypted_archive">Encrypted archive</option>
                        <option value="tenant_export">Tenant export</option>
                        <option value="keep_online">Keep online</option>
                    </select>
                    <select name="disposal_action" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="anonymize">Anonymize</option>
                        <option value="delete">Delete</option>
                        <option value="archive_only">Archive only</option>
                        <option value="manual_review">Manual review</option>
                    </select>
                </div>
                <input name="owner_role" placeholder="Owner/PIC, contoh: Compliance Officer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                        <input type="checkbox" name="requires_approval" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Wajib approval
                    </label>
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                        <input type="checkbox" name="legal_hold_override" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Legal hold override
                    </label>
                </div>
                <textarea name="notes" rows="3" placeholder="Catatan policy, pengecualian, atau dasar hukum." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-box-archive mr-2"></i>Simpan Policy</button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Buat Legal Hold</h2>
            <p class="mt-1 text-sm text-gray-500">Tahan proses disposal karena audit, sengketa, investigasi, atau regulator.</p>
            <form method="POST" action="{{ route('settings.data-retention-compliance.legal-holds') }}" class="mt-5 space-y-3">
                @csrf
                <select name="data_retention_policy_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tanpa link policy</option>
                    @foreach($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->policy_code }} - {{ $policy->policy_name }}</option>
                    @endforeach
                </select>
                <input name="case_number" required placeholder="Contoh: HOLD-095" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="case_title" required placeholder="Judul legal hold" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Global</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="data_domain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($domains as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="reason_category" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="legal_dispute">Legal dispute</option>
                        <option value="audit">Audit</option>
                        <option value="investigation">Investigation</option>
                        <option value="regulator_request">Regulator request</option>
                        <option value="employee_relation">Employee relation</option>
                    </select>
                    <input name="scope_reference" placeholder="Scope, contoh: ex-employee 2020" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="date" name="hold_start_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="date" name="hold_end_date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="approval_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="active">Active</option>
                        <option value="released">Released</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <input name="custodian_name" placeholder="Custodian/PIC evidence" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="notes" rows="3" placeholder="Catatan legal/compliance dan evidence." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-scale-balanced mr-2"></i>Simpan Legal Hold</button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Catat Execution</h2>
            <p class="mt-1 text-sm text-gray-500">Log rencana/hasil arsip, anonymize, delete, atau blocked by legal hold.</p>
            <form method="POST" action="{{ route('settings.data-retention-compliance.executions') }}" class="mt-5 space-y-3">
                @csrf
                <select name="data_retention_policy_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tanpa policy</option>
                    @foreach($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->policy_code }}</option>
                    @endforeach
                </select>
                <select name="data_legal_hold_case_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tidak terblokir legal hold</option>
                    @foreach($holds as $hold)
                        <option value="{{ $hold->id }}">{{ $hold->case_number }} - {{ $hold->case_title }}</option>
                    @endforeach
                </select>
                <input name="execution_number" required placeholder="Contoh: EXEC-095" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Global</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }}</option>
                        @endforeach
                    </select>
                    <select name="data_domain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($domains as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="cutoff_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="action_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="archive">Archive</option>
                        <option value="anonymize">Anonymize</option>
                        <option value="delete">Delete</option>
                        <option value="legal_hold_review">Legal hold review</option>
                    </select>
                    <input type="number" name="records_scanned" min="0" value="0" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="number" name="records_affected" min="0" value="0" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="planned">Planned</option>
                    <option value="running">Running</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="blocked_by_legal_hold">Blocked by legal hold</option>
                </select>
                <textarea name="notes" rows="3" placeholder="Catatan hasil eksekusi, exception, atau approval." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-list-check mr-2"></i>Simpan Execution</button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Ringkasan Retensi</h2>
            <p class="text-sm text-gray-500">Policy, legal hold, dan execution log terbaru.</p>
        </div>
        <div class="grid gap-0 lg:grid-cols-3">
            @foreach([
                ['title' => 'Policy', 'rows' => $policies, 'empty' => 'Belum ada policy retensi.'],
                ['title' => 'Legal Hold', 'rows' => $holds, 'empty' => 'Belum ada legal hold.'],
                ['title' => 'Execution', 'rows' => $executions, 'empty' => 'Belum ada execution log.'],
            ] as $group)
                <div class="border-b border-gray-100 p-5 lg:border-b-0 lg:border-r last:border-r-0">
                    <h3 class="font-semibold text-gray-900">{{ $group['title'] }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($group['rows']->take(5) as $row)
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $row->policy_code ?? $row->case_number ?? $row->execution_number }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $row->policy_name ?? $row->case_title ?? ucfirst(str_replace('_', ' ', $row->action_type)) }}
                                </div>
                                <div class="mt-2 inline-flex rounded-full bg-white px-2 py-1 text-xs font-semibold text-gray-600">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">{{ $group['empty'] }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
