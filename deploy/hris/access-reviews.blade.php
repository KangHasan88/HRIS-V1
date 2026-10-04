@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Access Review</h1>
            <p class="mt-1 text-sm text-gray-500">Campaign review akses user, scope organisasi, role risk, dan selisih permission.</p>
        </div>
        <a href="{{ route('settings.users') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users-cog"></i>
            User Access
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
                ['label' => 'Campaign', 'value' => $summary['campaigns'], 'color' => 'text-blue-600'],
                ['label' => 'Pending Review', 'value' => $summary['pending'], 'color' => 'text-yellow-600'],
                ['label' => 'Flag Risiko', 'value' => $summary['flagged'], 'color' => 'text-red-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Campaign</h2>
                    <p class="text-sm text-gray-500">Snapshot akses user saat ini untuk direview berkala.</p>
                </div>
                <form action="{{ route('settings.access-reviews.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input name="name" required value="Access Review {{ now()->timezone('Asia/Jakarta')->format('M Y') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="date" name="period_start" required value="{{ now()->startOfMonth()->toDateString() }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input type="date" name="period_end" required value="{{ now()->endOfMonth()->toDateString() }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <textarea name="notes" rows="3" placeholder="Catatan review" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-play"></i>
                        Jalankan Review
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Campaign</h2>
                    <p class="text-sm text-gray-500">12 campaign terakhir.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                        <div class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $review->name }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $review->period_start->format('d M Y') }} - {{ $review->period_end->format('d M Y') }}</div>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-blue-50 px-2 py-1 font-semibold text-blue-600">{{ $review->items_count }} user</span>
                                <span class="rounded-full bg-yellow-50 px-2 py-1 font-semibold text-yellow-600">{{ $review->pending_items_count }} pending</span>
                                <span class="rounded-full bg-red-50 px-2 py-1 font-semibold text-red-600">{{ $review->flagged_items_count }} flag</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-sm text-gray-500">Belum ada campaign access review.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Hasil Review Terbaru</h2>
                <p class="text-sm text-gray-500">{{ $activeReview ? $activeReview->name : 'Belum ada campaign.' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Role / Scope</th>
                            <th class="px-5 py-3">Selisih Permission</th>
                            <th class="px-5 py-3">Flag</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(($activeReview?->items ?? collect()) as $item)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $item->user_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->user_email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $item->role_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Company {{ $item->company_id ?: '-' }} / Branch {{ $item->branch_id ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-xs text-gray-500">Missing: {{ count($item->missing_permissions ?? []) }}</div>
                                    <div class="text-xs text-gray-500">Extra: {{ count($item->extra_permissions ?? []) }}</div>
                                    @if($item->extra_permissions)
                                        <div class="mt-2 max-w-xs text-xs text-red-600">{{ implode(', ', array_slice($item->extra_permissions, 0, 3)) }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex max-w-xs flex-wrap gap-2">
                                        @forelse($item->risk_flags ?? [] as $flag)
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-600">{{ $flag }}</span>
                                        @empty
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-600">OK</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $item->review_status === 'approved' ? 'bg-green-50 text-green-600' : ($item->review_status === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }} px-2 py-1 text-xs font-semibold">
                                        {{ str_replace('_', ' ', $item->review_status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <form action="{{ route('settings.access-reviews.items.update', $item) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <select name="review_status" class="w-36 rounded-lg border border-gray-300 px-2 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            <option value="approved">Approved</option>
                                            <option value="needs_change">Needs Change</option>
                                            <option value="revoked">Revoked</option>
                                        </select>
                                        <button class="inline-flex w-36 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                            <i class="fas fa-check"></i>
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada hasil review.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
