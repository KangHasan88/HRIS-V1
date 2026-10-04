<?php
use App\Models\Task;
use App\Models\TaskList;
use App\Models\Comment;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$task = Task::whereHas('taskList', fn($q) => $q->where('board_id', 6))->where('title', 'like', '[PAYROL-037]%')->firstOrFail();
$task->task_list_id = TaskList::where('board_id', 6)->where('name', 'In Progress')->firstOrFail()->id;
$task->progress = 10;
$task->save();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();
Comment::create(['task_id'=>$task->id,'user_id'=>$actor?->id,'content'=>'Mulai implement PAYROL-037: workflow reimbursement/klaim, OCR receipt, review payroll/admin, ESS submit, audit trail, dan sync Kanban/WBS/Gantt.','mentions'=>[],'likes_count'=>0]);
echo json_encode(['task_id'=>$task->id,'list'=>$task->taskList()->value('name'),'progress'=>$task->progress], JSON_PRETTY_PRINT).PHP_EOL;
