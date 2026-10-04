<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductRoadmapController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view delivery progress');

        $boardId = 6;
        $today = Carbon::today('Asia/Jakarta');

        $lists = DB::connection('kanban')
            ->table('task_lists')
            ->where('board_id', $boardId)
            ->get(['id', 'name']);

        $cards = DB::connection('kanban')
            ->table('tasks')
            ->whereIn('task_list_id', $lists->pluck('id'))
            ->whereNull('archived_at')
            ->get(['id', 'title', 'priority', 'due_date', 'task_list_id', 'progress']);

        $wbsItems = DB::connection('kanban')
            ->table('wbs_items')
            ->where('board_id', $boardId)
            ->get(['id', 'task_id', 'code', 'title']);

        $cards = $cards->map(function ($card) use ($lists, $wbsItems, $today) {
            $code = Str::of($card->title)->match('/^\[([A-Z0-9-]+)\]/')->toString();

            return [
                'id' => $card->id,
                'code' => $code ?: 'UNCODED',
                'group' => $this->groupFromCode($code),
                'title' => $card->title,
                'status' => optional($lists->firstWhere('id', $card->task_list_id))->name ?: '-',
                'priority' => $card->priority ?: '-',
                'due_date' => $card->due_date ? Carbon::parse($card->due_date) : null,
                'progress' => (int) ($card->progress ?? 0),
                'wbs' => optional($wbsItems->firstWhere('task_id', $card->id))->code,
                'is_overdue' => $card->due_date && Carbon::parse($card->due_date)->lt($today) && ! in_array(optional($lists->firstWhere('id', $card->task_list_id))->name, ['Testing', 'Done'], true),
            ];
        });

        $releaseWindows = collect([
            ['name' => 'Foundation & Admin Shell', 'start' => '2026-07-10', 'end' => '2026-07-14', 'groups' => ['Foundation', 'Core HR']],
            ['name' => 'Employee, Attendance, Leave, ESS', 'start' => '2026-07-15', 'end' => '2026-07-22', 'groups' => ['Attendance', 'ESS Mobile', 'Manager']],
            ['name' => 'Payroll, Tax, Coretax', 'start' => '2026-07-23', 'end' => '2026-07-29', 'groups' => ['Payroll', 'Tax']],
            ['name' => 'Talent, Analytics, Compliance, Stabilization', 'start' => '2026-07-30', 'end' => '2026-08-09', 'groups' => ['Talent', 'Analytics', 'Compliance', 'Integration']],
        ])->map(function ($window) use ($cards) {
            $items = $cards->filter(fn ($card) => in_array($card['group'], $window['groups'], true));
            $doneLike = $items->whereIn('status', ['Testing', 'Done'])->count();

            return [
                ...$window,
                'cards' => $items->count(),
                'testing' => $doneLike,
                'progress' => $items->count() > 0 ? (int) round(($doneLike / $items->count()) * 100) : 0,
                'risk' => $items->where('is_overdue', true)->count(),
            ];
        });

        $moduleSummary = $cards
            ->groupBy('group')
            ->map(function ($items, $group) {
                $testing = $items->whereIn('status', ['Testing', 'Done'])->count();

                return [
                    'group' => $group,
                    'cards' => $items->count(),
                    'testing' => $testing,
                    'backlog' => $items->where('status', 'Backlog')->count(),
                    'ready' => $items->where('status', 'Ready')->count(),
                    'risk' => $items->where('is_overdue', true)->count(),
                    'progress' => $items->count() > 0 ? (int) round(($testing / $items->count()) * 100) : 0,
                ];
            })
            ->sortByDesc('cards')
            ->values();

        $gateSummary = [
            ['label' => 'Kanban aktif', 'value' => $cards->count(), 'status' => 'Live'],
            ['label' => 'WBS terhubung', 'value' => $cards->whereNotNull('wbs')->count(), 'status' => 'Sync'],
            ['label' => 'Siap testing', 'value' => $cards->where('status', 'Testing')->count(), 'status' => 'QA'],
            ['label' => 'Risiko overdue aktif', 'value' => $cards->where('is_overdue', true)->count(), 'status' => 'Monitor'],
        ];

        return view('settings.product-roadmap', [
            'active' => 'product-roadmap',
            'gateSummary' => $gateSummary,
            'releaseWindows' => $releaseWindows,
            'moduleSummary' => $moduleSummary,
            'riskCards' => $cards->where('is_overdue', true)->sortBy('due_date')->take(10),
            'nextCards' => $cards->whereNotIn('status', ['Testing', 'Done'])->sortBy('due_date')->take(10),
        ]);
    }

    private function groupFromCode(string $code): string
    {
        return match (true) {
            Str::startsWith($code, ['PLATFO', 'ADMIN', 'AUTH', 'DAY', 'ROADMAP']) => 'Foundation',
            Str::startsWith($code, 'HRCORE') => 'Core HR',
            Str::startsWith($code, 'ATTEND') => 'Attendance',
            Str::startsWith($code, 'ESSMOB') => 'ESS Mobile',
            Str::startsWith($code, 'MANAGE') => 'Manager',
            Str::startsWith($code, ['PAYROL', 'TAX']) => 'Payroll',
            Str::startsWith($code, 'RECRUI') => 'Talent',
            Str::startsWith($code, 'ANALYT') => 'Analytics',
            Str::startsWith($code, 'COMPLI') => 'Compliance',
            Str::startsWith($code, ['INTEGR', 'MOBILE']) => 'Integration',
            default => 'Lainnya',
        };
    }
}
