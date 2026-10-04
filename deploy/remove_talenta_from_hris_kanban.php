<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$now = now();

$before = [
    'boards' => DB::table('boards')->where('id', $boardId)->where(function ($q) {
        $q->where('name', 'like', '%Talenta%')->orWhere('description', 'like', '%Talenta%');
    })->count(),
    'tasks' => DB::table('tasks')
        ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
        ->where('task_lists.board_id', $boardId)
        ->where(function ($q) {
            $q->where('tasks.title', 'like', '%Talenta%')->orWhere('tasks.description', 'like', '%Talenta%');
        })
        ->count(),
];

$board = DB::table('boards')->where('id', $boardId)->first();
if (!$board) {
    throw new RuntimeException('HRIS board ID 6 not found.');
}

$boardName = str_replace('Talenta+', 'Enterprise', $board->name);
$boardDescription = str_replace(
    [
        'Talenta+',
        'Talenta',
        'dibanding aplikasi HRIS umum seperti .',
        'dibanding aplikasi HRIS umum seperti Talenta.',
    ],
    [
        'Enterprise',
        'HRIS benchmark',
        'sebagai HRIS enterprise-class.',
        'sebagai HRIS enterprise-class.',
    ],
    (string) $board->description
);
$boardDescription = str_replace('HRIS benchmark', 'enterprise HRIS benchmark', $boardDescription);

DB::table('boards')->where('id', $boardId)->update([
    'name' => $boardName === 'Kurmigo HRIS - Enterprise Backlog' ? 'HRIS' : $boardName,
    'description' => $boardDescription,
    'updated_at' => $now,
]);

$tasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->where(function ($q) {
        $q->where('tasks.title', 'like', '%Talenta%')->orWhere('tasks.description', 'like', '%Talenta%');
    })
    ->select('tasks.id', 'tasks.title', 'tasks.description')
    ->get();

$updatedTasks = 0;
foreach ($tasks as $task) {
    $title = str_replace(['Talenta+', 'Talenta'], ['Enterprise', 'enterprise HRIS benchmark'], $task->title);
    $description = str_replace(
        [
            'Talenta+',
            'Talenta',
            'dibanding aplikasi HRIS umum seperti enterprise HRIS benchmark.',
            'dibanding aplikasi HRIS umum seperti Talenta.',
        ],
        [
            'Enterprise',
            'enterprise HRIS benchmark',
            'sebagai HRIS enterprise-class.',
            'sebagai HRIS enterprise-class.',
        ],
        (string) $task->description
    );
    $description = str_replace(
        'HRIS mampu menghitung PPh 21 secara akurat, dapat diaudit, dan siap dilaporkan/diintegrasikan dengan Coretax.',
        'HRIS mampu menghitung PPh 21 secara akurat, dapat diaudit, dan siap dilaporkan/diintegrasikan dengan Coretax.',
        $description
    );

    DB::table('tasks')->where('id', $task->id)->update([
        'title' => $title,
        'description' => $description,
        'updated_at' => $now,
    ]);
    $updatedTasks++;
}

$after = [
    'boards' => DB::table('boards')->where('id', $boardId)->where(function ($q) {
        $q->where('name', 'like', '%Talenta%')->orWhere('description', 'like', '%Talenta%');
    })->count(),
    'tasks' => DB::table('tasks')
        ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
        ->where('task_lists.board_id', $boardId)
        ->where(function ($q) {
            $q->where('tasks.title', 'like', '%Talenta%')->orWhere('tasks.description', 'like', '%Talenta%');
        })
        ->count(),
];

echo json_encode([
    'board_id' => $boardId,
    'before' => $before,
    'updated_tasks' => $updatedTasks,
    'after' => $after,
    'board_name' => DB::table('boards')->where('id', $boardId)->value('name'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
