<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DeliveryProgressController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view delivery progress');

        $boardId = 6;
        $lists = DB::connection('kanban')
            ->table('task_lists')
            ->where('board_id', $boardId)
            ->orderBy('order')
            ->get(['id', 'name', 'color']);

        $listIds = $lists->pluck('id');
        $cards = DB::connection('kanban')
            ->table('tasks')
            ->whereIn('task_list_id', $listIds)
            ->whereNull('archived_at')
            ->get(['id', 'title', 'priority', 'due_date', 'task_list_id']);

        $wbsItems = DB::connection('kanban')
            ->table('wbs_items')
            ->where('board_id', $boardId)
            ->get(['id', 'task_id', 'code', 'title']);

        $linkedTaskIds = $wbsItems->whereNotNull('task_id')->pluck('task_id')->unique();
        $today = Carbon::today('Asia/Jakarta');
        $overdue = $cards->filter(fn ($card) => $card->due_date && Carbon::parse($card->due_date)->lt($today))->count();

        $listSummary = $lists->map(function ($list) use ($cards) {
            $items = $cards->where('task_list_id', $list->id);

            return [
                'name' => $list->name,
                'color' => $list->color,
                'count' => $items->count(),
                'high' => $items->where('priority', 'high')->count(),
            ];
        });

        $latestCards = $cards
            ->sortBy('due_date')
            ->take(10)
            ->map(function ($card) use ($lists, $wbsItems) {
                return [
                    'title' => $card->title,
                    'list' => optional($lists->firstWhere('id', $card->task_list_id))->name,
                    'wbs' => optional($wbsItems->firstWhere('task_id', $card->id))->code,
                    'due_date' => $card->due_date ? Carbon::parse($card->due_date)->format('d/m/Y') : '-',
                    'priority' => $card->priority ?: '-',
                ];
            });

        return view('settings.delivery-progress', [
            'active' => 'delivery-progress',
            'summary' => [
                'cards' => $cards->count(),
                'wbs' => $wbsItems->count(),
                'linked' => $linkedTaskIds->count(),
                'notLinked' => max($cards->count() - $linkedTaskIds->count(), 0),
                'overdue' => $overdue,
                'done' => $listSummary->firstWhere('name', 'Done')['count'] ?? 0,
            ],
            'listSummary' => $listSummary,
            'latestCards' => $latestCards,
        ]);
    }
}
