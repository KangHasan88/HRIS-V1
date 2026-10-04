<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$taskId = (int) ($argv[1] ?? 0);
$task = Task::with(['taskList', 'comments', 'checklists.items', 'labels'])->findOrFail($taskId);

echo $task->title . PHP_EOL;
echo 'LIST: ' . $task->taskList->name . ' PROGRESS: ' . ($task->progress ?? 0) . ' DUE: ' . ($task->due_date ?: '-') . PHP_EOL;
echo 'DESC:' . PHP_EOL . ($task->description ?: '-') . PHP_EOL;
echo 'LABELS:' . PHP_EOL;
foreach ($task->labels as $label) {
    echo '- ' . $label->name . PHP_EOL;
}
echo 'CHECKLISTS:' . PHP_EOL;
foreach ($task->checklists as $checklist) {
    echo '# ' . $checklist->title . PHP_EOL;
    foreach ($checklist->items as $item) {
        echo ($item->is_completed ? '[x] ' : '[ ] ') . $item->title . PHP_EOL;
    }
}
echo 'COMMENTS:' . PHP_EOL;
foreach ($task->comments->take(-5) as $comment) {
    echo '- ' . str_replace(["\r", "\n"], ' ', $comment->content) . PHP_EOL;
}
