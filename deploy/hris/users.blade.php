@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">User & Role</h1>
            <p class="mt-1 text-sm text-gray-500">Atur role sistem, company, badan hukum, dan cabang user.</p>
        </div>
        <a href="{{ route('settings.roles') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-shield-halved"></i>
            Role & Permission
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_420px]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar User</h2>
                    <p class="text-sm text-gray-500">Pilih user untuk melihat detail akses di panel kanan.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $users->count() }} user aktif</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach ($users as $u)
                    <button type="button"
                            data-select-user="{{ $u->id }}"
                            class="user-row flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-gray-50 {{ $loop->first ? 'bg-blue-50/50' : '' }}">
                        <div class="hidden font-mono text-xs font-semibold text-gray-600 sm:block">KRM-{{ str_pad($u->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-semibold text-gray-900">{{ $u->name }}</div>
                            <div class="truncate text-sm text-gray-500">{{ $u->email }}</div>
                        </div>
                        <div class="hidden min-w-36 text-sm lg:block">
                            <div class="font-semibold text-gray-900">{{ $u->company?->code ?? '-' }}</div>
                            <div class="truncate text-xs text-gray-500">{{ $u->branch?->name ?? 'Belum ada cabang' }}</div>
                        </div>
                        <div class="hidden min-w-32 md:block">
                            @if ($u->role)
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $u->role->name === 'Super Admin' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-600' }}">
                                    {{ $u->role->name }}
                                </span>
                            @else
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">Belum Ada</span>
                            @endif
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="xl:sticky xl:top-6 xl:self-start">
            @foreach ($users as $u)
                <section id="user-panel-{{ $u->id }}" class="user-panel {{ $loop->first ? '' : 'hidden' }} rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-600">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">{{ $u->name }}</h2>
                                    <p class="text-sm text-gray-500">{{ $u->email }}</p>
                                </div>
                            </div>
                            <span class="font-mono text-xs font-semibold text-gray-500">KRM-{{ str_pad($u->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Role</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $u->role?->name ?? '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Company</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $u->company?->code ?? '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Badan Hukum</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $u->legalEntity?->code ?? '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Cabang</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $u->branch?->code ?? '-' }}</div>
                            </div>
                        </div>

                        <button type="button" data-open-edit="{{ $u->id }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            <i class="fas fa-pen"></i>
                            Edit Akses
                        </button>

                        <form id="user-edit-form-{{ $u->id }}" action="{{ route('settings.users.role', $u->id) }}" method="POST" class="hidden space-y-4 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Role</label>
                                <select name="role_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ $u->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Company</label>
                                <select name="company_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" {{ $u->company_id == $company->id ? 'selected' : '' }}>{{ $company->code }} - {{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Badan Hukum</label>
                                <select name="legal_entity_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Badan hukum</option>
                                    @foreach ($legalEntities as $entity)
                                        <option value="{{ $entity->id }}" {{ $u->legal_entity_id == $entity->id ? 'selected' : '' }}>{{ $entity->code }} - {{ $entity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang</label>
                                <select name="branch_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">Cabang</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $u->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->code }} - {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" data-cancel-edit="{{ $u->id }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                    <i class="fas fa-xmark"></i>
                                    Batal
                                </button>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    <i class="fas fa-save"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>

<script>
    const userRows = document.querySelectorAll('[data-select-user]');
    const userPanels = document.querySelectorAll('.user-panel');

    userRows.forEach((row) => {
        row.addEventListener('click', () => {
            const id = row.getAttribute('data-select-user');
            userRows.forEach((item) => item.classList.remove('bg-blue-50/50'));
            row.classList.add('bg-blue-50/50');
            userPanels.forEach((panel) => panel.classList.add('hidden'));
            document.getElementById(`user-panel-${id}`)?.classList.remove('hidden');
        });
    });

    document.querySelectorAll('[data-open-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-open-edit');
            document.getElementById(`user-edit-form-${id}`)?.classList.remove('hidden');
            button.classList.add('hidden');
        });
    });

    document.querySelectorAll('[data-cancel-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-cancel-edit');
            const form = document.getElementById(`user-edit-form-${id}`);
            form?.reset();
            form?.classList.add('hidden');
            document.querySelector(`[data-open-edit="${id}"]`)?.classList.remove('hidden');
        });
    });
</script>
@endsection
