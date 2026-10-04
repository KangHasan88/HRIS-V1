<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[COMPLI-093]%')
    ->firstOrFail();

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();

$task->task_list_id = $testingList->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)->update(['is_checked' => true]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Testing 90%: COMPLI-093 Two-factor authentication dan konfirmasi aksi privileged selesai. Implementasi mencakup migration security_two_factor_policies, user_two_factor_settings, privileged_action_confirmations, model policy/user setting/action confirmation, SecurityTwoFactorController, route /settings/two-factor-security, menu 2FA & Aksi Sensitif di System Administration, policy 2FA per role/company, daftar aksi privileged, enrollment user 2FA dengan secret mask dan backup code mask, confirmation log untuk aksi sensitif, risk snapshot, audit log security_2fa_policy.created/security_2fa_user.enrolled/security_privileged_action.confirmed, feature test, smoke HTTPS, dan full regression. URL: https://31.97.106.123:8091/settings/two-factor-security. Targeted test: 1 passed / 28 assertions. Full test: 140 passed / 1947 assertions. Smoke HTTPS: status 200 page ok.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'COMPLI-093',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $testingList->name,
    'progress' => $task->progress ?? null,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT).PHP_EOL;
