<?php

namespace App\Http\Controllers;

use App\Models\AccessReview;
use App\Models\AccessReviewItem;
use App\Models\Permission;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AccessReviewController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage settings');

        $reviews = AccessReview::withCount([
            'items',
            'items as pending_items_count' => fn ($query) => $query->where('review_status', 'pending'),
            'items as flagged_items_count' => fn ($query) => $query->whereNotNull('risk_flags')->where('risk_flags', '!=', '[]'),
        ])->latest()->take(12)->get();

        $activeReview = AccessReview::with(['items' => fn ($query) => $query->orderByRaw("review_status = 'pending' desc")->orderBy('user_name')])
            ->latest()
            ->first();

        $summary = [
            'campaigns' => AccessReview::count(),
            'pending' => AccessReviewItem::where('review_status', 'pending')->count(),
            'flagged' => AccessReviewItem::whereNotNull('risk_flags')->where('risk_flags', '!=', '[]')->count(),
            'approved' => AccessReviewItem::where('review_status', 'approved')->count(),
        ];

        return view('settings.access-reviews', [
            'active' => 'access-reviews',
            'reviews' => $reviews,
            'activeReview' => $activeReview,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = AccessReview::create([
            ...$data,
            'status' => 'launched',
            'created_by' => auth()->id(),
            'launched_at' => now(),
        ]);

        $allPermissions = Permission::orderBy('name')->pluck('name')->all();
        $baselines = $this->permissionBaselines($allPermissions);

        User::with(['role.permissions', 'company', 'legalEntity', 'branch'])
            ->orderBy('name')
            ->get()
            ->each(function (User $user) use ($review, $baselines) {
                $current = $user->role?->permissions->pluck('name')->sort()->values()->all() ?? [];
                $expected = $baselines[$user->role?->name] ?? $current;
                $missing = array_values(array_diff($expected, $current));
                $extra = array_values(array_diff($current, $expected));

                AccessReviewItem::create([
                    'access_review_id' => $review->id,
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'company_id' => $user->company_id,
                    'legal_entity_id' => $user->legal_entity_id,
                    'branch_id' => $user->branch_id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'role_name' => $user->role?->name,
                    'current_permissions' => $current,
                    'expected_permissions' => $expected,
                    'missing_permissions' => $missing,
                    'extra_permissions' => $extra,
                    'risk_flags' => $this->riskFlags($user, $current, $missing, $extra),
                    'review_status' => 'pending',
                ]);
            });

        AuditLogger::record(
            'access_review.launched',
            "Campaign access review '{$review->name}' dibuat untuk {$review->items()->count()} user.",
            $review,
            [],
            $review->only(['name', 'period_start', 'period_end', 'status']),
            $request
        );

        return redirect()->route('settings.access-reviews')->with('success', 'Campaign access review berhasil dibuat.');
    }

    public function updateItem(Request $request, AccessReviewItem $item): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'review_status' => ['required', 'in:approved,needs_change,revoked'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = $item->only(['review_status', 'review_note']);
        $item->update([
            ...$data,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogger::record(
            'access_review.item_reviewed',
            "Access review user '{$item->user_name}' ditandai {$item->review_status}.",
            $item,
            $oldValues,
            $item->only(['review_status', 'review_note']),
            $request
        );

        return redirect()->route('settings.access-reviews')->with('success', 'Status review akses berhasil diperbarui.');
    }

    private function permissionBaselines(array $allPermissions): array
    {
        return [
            'Super Admin' => $allPermissions,
            'HR Admin' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'delete employees',
                'view attendance', 'approve attendance', 'view holiday calendar', 'manage holiday calendar',
                'view leave', 'approve leave',
                'view documents', 'manage documents',
                'manage modules', 'view delivery progress',
            ],
            'Payroll Admin' => [
                'view dashboard',
                'view employees',
                'view payroll', 'approve payroll', 'manage payroll settings',
                'view documents',
                'view delivery progress',
            ],
            'Manager' => [
                'view dashboard',
                'view employees',
                'view attendance', 'approve attendance', 'view holiday calendar',
                'view leave', 'approve leave',
                'view documents',
            ],
            'Employee' => [
                'view dashboard',
                'view employees',
                'view attendance', 'view holiday calendar',
                'view leave',
                'view documents',
            ],
        ];
    }

    private function riskFlags(User $user, array $current, array $missing, array $extra): array
    {
        $flags = [];

        if (!$user->role_id) {
            $flags[] = 'Belum ada role';
        }

        if (!$user->company_id || !$user->branch_id) {
            $flags[] = 'Scope organisasi belum lengkap';
        }

        if ($user->role?->name === 'Super Admin') {
            $flags[] = 'Akses super admin';
        }

        $sensitive = array_intersect($current, ['manage settings', 'manage payroll settings', 'delete employees']);
        if ($sensitive) {
            $flags[] = 'Memiliki izin sensitif: ' . implode(', ', $sensitive);
        }

        if ($missing || $extra) {
            $flags[] = 'Selisih permission dari baseline role';
        }

        return $flags;
    }
}
