<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now();
$boardName = 'HRIS';
$board = DB::table('boards')
    ->where('id', 6)
    ->orWhere('name', $boardName)
    ->orWhere('name', 'Kurmigo HRIS - Talenta+ Backlog')
    ->first();

if (!$board) {
    throw new RuntimeException("Board not found: {$boardName}");
}

$tasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $board->id)
    ->whereNull('tasks.archived_at')
    ->select('tasks.id', 'tasks.title', 'tasks.description')
    ->orderBy('tasks.order')
    ->get();

$added = 0;
$updatedTasks = 0;

foreach ($tasks as $task) {
    if (str_starts_with($task->title, '[ROADMAP-000]')) {
        continue;
    }

    $module = 'General';
    $platform = 'Web Admin + API';
    foreach (explode("\n", (string) $task->description) as $line) {
        if (str_starts_with($line, 'Module: ')) {
            $module = trim(substr($line, strlen('Module: ')));
        }
        if (str_starts_with($line, 'Platform: ')) {
            $platform = trim(substr($line, strlen('Platform: ')));
        }
    }

    $titleLower = strtolower($task->title . ' ' . $module . ' ' . $platform);
    $needsMobile = str_contains($titleLower, 'mobile')
        || str_contains($titleLower, 'ess')
        || str_contains($titleLower, 'clock')
        || str_contains($titleLower, 'leave')
        || str_contains($titleLower, 'payslip')
        || str_contains($titleLower, 'request')
        || str_contains($titleLower, 'approval');
    $needsIntegration = str_contains($titleLower, 'integration')
        || str_contains($titleLower, 'api')
        || str_contains($titleLower, 'webhook')
        || str_contains($titleLower, 'biometric')
        || str_contains($titleLower, 'whatsapp')
        || str_contains($titleLower, 'bank')
        || str_contains($titleLower, 'sso');
    $needsPayroll = str_contains($titleLower, 'payroll')
        || str_contains($titleLower, 'salary')
        || str_contains($titleLower, 'tax')
        || str_contains($titleLower, 'pph')
        || str_contains($titleLower, 'bpjs')
        || str_contains($titleLower, 'payslip');
    $needsAnalytics = str_contains($titleLower, 'analytics')
        || str_contains($titleLower, 'ai')
        || str_contains($titleLower, 'dashboard')
        || str_contains($titleLower, 'report')
        || str_contains($titleLower, 'anomaly');

    $items = [
        "Confirm business rules and edge cases for {$module}",
        'Define feature flag key, default state, and package availability',
        'Define permission matrix for Admin, HR, Manager, Employee, Finance, Auditor',
        'Design database tables, relationships, indexes, and migration rollback',
        'Design API contract, validation rules, error responses, and pagination/filtering',
        'Implement backend service, controller, policy, request validation, and audit events',
        'Build web admin UI with DMS-style layout, empty state, loading state, and error state',
        'Add import/export/reporting behavior if operationally relevant',
        'Add notification events: in-app, email, WhatsApp, and push where relevant',
        'Write tests for enabled module happy path and disabled module guard',
        'Write tests for permission denied, invalid input, and audit trail',
        'Prepare seed/demo data for HRIS preview and stakeholder review',
        'Update operational documentation and rollout notes',
        'Run smoke test on VPS and update Kanban card progress/status',
    ];

    if ($needsMobile) {
        $items[] = 'Design Mobile ESS/MSS screen flow, offline behavior, and push notification states';
        $items[] = 'Implement mobile API response shape optimized for low bandwidth and field employees';
        $items[] = 'Test mobile permission, device/session behavior, and request approval UX';
    }

    if ($needsIntegration) {
        $items[] = 'Define integration credentials, webhook/API logs, retry strategy, and rate limits';
        $items[] = 'Build integration health check and failure recovery workflow';
        $items[] = 'Test sandbox/mock integration and document production switch steps';
    }

    if ($needsPayroll) {
        $items[] = 'Validate payroll formula impact, rounding, proration, and cutoff behavior';
        $items[] = 'Add payroll audit comparison before/after calculation';
        $items[] = 'Test confidential access, payslip visibility, and finance approval flow';
    }

    if ($needsAnalytics) {
        $items[] = 'Define metrics, dimensions, freshness SLA, and drill-down behavior';
        $items[] = 'Add explainability notes for AI/anomaly output so HR can trust the result';
        $items[] = 'Test report export and stale-data indicator';
    }

    $checklistName = 'Implementation Work Breakdown';
    $checklist = DB::table('checklists')
        ->where('task_id', $task->id)
        ->where('name', $checklistName)
        ->first();

    if (!$checklist) {
        $checklistId = DB::table('checklists')->insertGetId([
            'name' => $checklistName,
            'task_id' => $task->id,
            'order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } else {
        $checklistId = $checklist->id;
        DB::table('checklists')->where('id', $checklistId)->update(['updated_at' => $now]);
    }

    foreach (array_values(array_unique($items)) as $order => $item) {
        DB::table('checklist_items')->updateOrInsert(
            ['checklist_id' => $checklistId, 'name' => mb_substr($item, 0, 250)],
            ['order' => $order, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
        );
        $added++;
    }
    $updatedTasks++;
}

echo json_encode([
    'board_id' => $board->id,
    'tasks_updated' => $updatedTasks,
    'checklist_items_upserted' => $added,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
