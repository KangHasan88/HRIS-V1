<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$tasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->leftJoin('checklists', 'checklists.task_id', '=', 'tasks.id')
    ->leftJoin('checklist_items', 'checklist_items.checklist_id', '=', 'checklists.id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->select(
        'tasks.id',
        'tasks.title',
        'tasks.description',
        'tasks.start_date',
        'tasks.due_date',
        'task_lists.name as list',
        DB::raw("GROUP_CONCAT(checklist_items.name SEPARATOR ' | ') as checklist_text")
    )
    ->groupBy('tasks.id', 'tasks.title', 'tasks.description', 'tasks.start_date', 'tasks.due_date', 'task_lists.name')
    ->orderBy('tasks.title')
    ->get();

$categories = [
    'Core HR / employee database' => ['employee', 'karyawan', 'employment', 'jabatan', 'organisasi', 'department', 'grade', 'database'],
    'ESS web & mobile WebView' => ['ess', 'webview', 'android', 'ios', 'mobile', 'self service', 'wkwebview'],
    'Attendance, shift, overtime' => ['attendance', 'absensi', 'shift', 'jadwal', 'overtime', 'lembur', 'timesheet'],
    'Leave & approvals' => ['leave', 'cuti', 'approval', 'workflow', 'delegation', 'persetujuan'],
    'Payroll engine' => ['payroll', 'gaji', 'salary', 'payslip', 'slip gaji', 'thr', 'bonus'],
    'PPh 21 & Coretax' => ['pph', 'coretax', 'e-bupot', 'ebupot', 'tax', 'pajak', 'ter'],
    'Claim, reimbursement, loan' => ['claim', 'reimbursement', 'expense', 'kasbon', 'loan', 'pinjaman'],
    'Recruitment & onboarding' => ['recruit', 'rekrut', 'ats', 'candidate', 'onboarding', 'offboarding'],
    'Performance & talent' => ['performance', 'kpi', 'okr', 'review', 'talent', 'succession', 'competency'],
    'Learning & knowledge' => ['learning', 'training', 'lms', 'course', 'sertifikasi', 'knowledge'],
    'Engagement, pulse, helpdesk' => ['engagement', 'pulse', 'survey', 'helpdesk', 'ticket', 'case'],
    'Document, e-sign, policy' => ['document', 'dokumen', 'sign', 'signature', 'policy', 'sop', 'contract'],
    'Analytics, AI, anomaly' => ['analytics', 'dashboard', 'report', 'ai', 'anomaly', 'insight', 'forecast'],
    'Integration, API, webhook' => ['integration', 'integrasi', 'api', 'webhook', 'sso', 'biometric', 'bank'],
    'Security, audit, compliance' => ['security', 'audit', 'privacy', 'compliance', 'rbac', 'permission', 'masking'],
    'Admin module enable/disable' => ['enable', 'disable', 'module', 'tenant', 'package', 'subscription', 'feature flag'],
];

$coverage = [];
foreach ($categories as $category => $keywords) {
    $matched = [];
    foreach ($tasks as $task) {
        $text = strtolower($task->title . ' ' . $task->description . ' ' . $task->checklist_text);
        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                $matched[$task->id] = $task->title;
                break;
            }
        }
    }
    $coverage[$category] = [
        'cards' => count($matched),
        'sample_titles' => array_slice(array_values($matched), 0, 8),
    ];
}

$mentions = [
    'competitor_brand_mentions' => $tasks->filter(function ($task) {
        return stripos($task->title . ' ' . $task->description . ' ' . $task->checklist_text, 'talenta') !== false;
    })->count(),
];

$dateCoverage = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->selectRaw('COUNT(*) as total_cards, SUM(CASE WHEN tasks.start_date IS NULL OR tasks.due_date IS NULL THEN 1 ELSE 0 END) as missing_dates, MIN(tasks.start_date) as min_start, MAX(tasks.due_date) as max_due')
    ->first();

echo json_encode([
    'board_id' => $boardId,
    'date_coverage' => $dateCoverage,
    'category_coverage' => $coverage,
    'brand_hygiene' => $mentions,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
