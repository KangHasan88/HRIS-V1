<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$start = Carbon::create(2026, 7, 10)->startOfDay();
$end = Carbon::create(2026, 8, 9)->startOfDay();
$now = now();

$board = DB::table('boards')->where('id', $boardId)->first();
if (!$board) {
    throw new RuntimeException('HRIS board ID 6 not found.');
}

$lists = DB::table('task_lists')
    ->where('board_id', $boardId)
    ->pluck('id', 'name');

if (!$lists->has('Backlog') || !$lists->has('Ready')) {
    throw new RuntimeException('Backlog/Ready list missing.');
}

$user = DB::table('users')->where('username', 'codex')->first()
    ?: DB::table('users')->where('role', 'admin')->orderBy('id')->first()
    ?: DB::table('users')->orderBy('id')->first();

$label = DB::table('labels')
    ->where('board_id', $boardId)
    ->where('name', 'P0 Foundation')
    ->first();

$p0LabelId = $label?->id;

$dailyPlan = [
    1 => ['Foundation kickoff', 'Finalize scope, repo baseline, module flags, coding workflow, Kanban discipline'],
    2 => ['Architecture & tenancy', 'Multi-company, branch, legal entity, environment, and deployment baseline'],
    3 => ['Module marketplace', 'Enable/disable registry, package gates, tenant config UI'],
    4 => ['RBAC & audit', 'Permission templates, policy guard, audit event model'],
    5 => ['Workflow builder', 'Approval routing, delegation, SLA, notification hooks'],
    6 => ['Employee master 1', 'Employee 360 profile, employment status, family, bank, tax, BPJS'],
    7 => ['Employee master 2', 'Org structure, position, grade, mutation, promotion, document vault'],
    8 => ['Onboarding/offboarding', 'Onboarding packs, assets, contracts, clearance, exit workflow'],
    9 => ['Attendance core', 'Shift roster, geofence, clock-in/out, biometric import contract'],
    10 => ['Leave & overtime', 'Leave policy, balance simulation, overtime approval, correction workflow'],
    11 => ['Mobile WebView shell', 'Android/iOS WebView wrapper, responsive ESS shell, native bridge contract'],
    12 => ['ESS employee flows', 'Profile update, request forms, payslip/doc download, helpdesk'],
    13 => ['Manager self service', 'Approval inbox, team calendar, attendance exception, budget visibility'],
    14 => ['Payroll foundation', 'Component builder, formula graph, salary structure, payroll batch'],
    15 => ['Payroll calculations', 'Proration, allowance, reimbursement, loan, adjustment, anomaly checks'],
    16 => ['PPh 21 foundation', 'Tax profile, PTKP, NPWP/NIK, taxable mapping, TER monthly'],
    17 => ['PPh 21 advanced', 'Annual true-up, THR/bonus, non-permanent, gross-up/net modes'],
    18 => ['Coretax integration', 'Coretax export schema, connector readiness, submission tracker'],
    19 => ['Payslip & tax docs', 'Payslip publish, e-Bupot, ESS download, confidential access'],
    20 => ['Recruitment', 'Manpower request, ATS pipeline, candidate portal, offer letter'],
    21 => ['Performance & talent', 'OKR, review cycle, 360, competency, IDP, succession'],
    22 => ['Learning & engagement', 'Training catalog, certificate, survey, eNPS, feedback'],
    23 => ['Analytics & AI', 'People analytics, payroll variance, attrition, anomaly, NL reports'],
    24 => ['Integrations', 'SSO, biometric sync, WhatsApp, calendar, ERP, bank, public API'],
    25 => ['Compliance & security', '2FA, access review, retention, masking, consent, backup'],
    26 => ['Import/export & reports', 'Bulk import validation, export pack, BI snapshot, audit reports'],
    27 => ['Mobile QA & hardening', 'WebView QA matrix, push, file upload, GPS, offline/network loss'],
    28 => ['System QA', 'Feature flag, permission, payroll, attendance, ESS regression test'],
    29 => ['UAT & fixes', 'Stakeholder UAT, bug fixing, demo data, docs, operational runbook'],
    30 => ['Production readiness', 'Release checklist, smoke test, monitoring, handover'],
    31 => ['Final buffer & sign-off', 'Final regression buffer, stakeholder sign-off, and go-live decision'],
];

function bucketForTask(string $title, string $description): int
{
    $module = '';
    $feature = '';
    $scope = '';
    foreach (explode("\n", $description) as $line) {
        if (str_starts_with($line, 'Module: ')) {
            $module = substr($line, strlen('Module: '));
        } elseif (str_starts_with($line, 'Feature: ')) {
            $feature = substr($line, strlen('Feature: '));
        } elseif (str_starts_with($line, '- ') && $scope === '') {
            $scope = $line;
        }
    }
    $text = strtolower($title . ' ' . $module . ' ' . $feature . ' ' . $scope);

    $rules = [
        2 => ['multi-company', 'branch', 'legal entity', 'tenancy'],
        3 => ['module marketplace', 'package gates', 'tenant config'],
        4 => ['role-based', 'permission', 'audit trail', 'activity log', 'access review'],
        5 => ['approval workflow', 'workflow builder', 'notification center', 'conditional routing'],
        6 => ['employee 360', 'tax profile master', 'npwp', 'nik', 'ptkp', 'bpjs'],
        7 => ['organization structure', 'position', 'job grade', 'mutation', 'promotion', 'document vault', 'contract'],
        8 => ['onboarding', 'offboarding', 'asset assignment', 'clearance', 'exit interview'],
        9 => ['shift roster', 'clock-in', 'clock', 'geofence', 'biometric', 'attendance'],
        10 => ['leave', 'overtime', 'holiday calendar', 'attendance correction', 'fatigue'],
        11 => ['android full webview', 'ios full wkwebview', 'responsive ess web app', 'native bridge', 'mobile webview'],
        12 => ['mobile ess home', 'profile update', 'reimbursement submission', 'hr helpdesk', 'payslip', 'download'],
        13 => ['manager', 'team calendar', 'bulk approval', 'headcount planning', 'team document'],
        14 => ['payroll component', 'salary structure', 'payroll run', 'bank disbursement'],
        15 => ['reimbursement', 'employee loan', 'retroactive', 'backpay', 'payroll audit', 'taxable component mapping'],
        16 => ['ter monthly', 'pph 21 tax profile', 'tax profile', 'coretax-ready monthly'],
        17 => ['annualized', 'true-up', 'thr', 'bonus', 'non-permanent', 'freelancer', 'gross-up', 'gross, net'],
        18 => ['coretax', 'submission status', 'reconciliation', 'export template', 'api connector'],
        19 => ['e-bupot', 'withholding slip', 'tax correction', 'tax simulation', 'calculation explainability', 'tax compliance calendar'],
        20 => ['recruitment', 'candidate', 'interview', 'offer letter', 'background check', 'new hire'],
        21 => ['okr', 'performance', '360', 'competency', 'succession', 'career path'],
        22 => ['training', 'learning', 'survey', 'engagement', 'enps', 'feedback'],
        23 => ['analytics', 'ai', 'attrition', 'anomaly', 'natural language', 'predictive'],
        24 => ['sso', 'whatsapp', 'integration', 'public api', 'webhook', 'accounting', 'bank host', 'warehouse export'],
        25 => ['two-factor', 'data retention', 'consent', 'security event', 'backup', 'localization', 'privacy', 'masking'],
        26 => ['bulk import', 'export', 'report', 'bi snapshot'],
        27 => ['webview qa', 'mobile qa', 'push notification', 'offline', 'device'],
        28 => ['regression test', 'test suite'],
    ];

    foreach ($rules as $day => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return $day;
            }
        }
    }

    return 29;
}

DB::transaction(function () use ($boardId, $board, $lists, $user, $p0LabelId, $dailyPlan, $start, $end, $now) {
    DB::table('boards')->where('id', $boardId)->update([
        'description' => trim((string) $board->description) . "\n\nTarget Delivery: 1 bulan, 10 Jul 2026 - 9 Aug 2026. Semua progress coding wajib update Kanban status, checklist, dan komentar blocker.",
        'updated_at' => $now,
    ]);

    $featureTasks = DB::table('tasks')
        ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
        ->where('task_lists.board_id', $boardId)
        ->whereNull('tasks.archived_at')
        ->where('tasks.title', 'not like', '[DAY-%')
        ->select('tasks.id', 'tasks.title', 'tasks.description')
        ->orderBy('tasks.order')
        ->get();

    $scheduled = 0;
    foreach ($featureTasks as $task) {
        if (str_starts_with($task->title, '[ROADMAP-000]')) {
            DB::table('tasks')->where('id', $task->id)->update([
                'start_date' => $start->toDateString(),
                'due_date' => $end->toDateString(),
                'duration' => 30,
                'updated_at' => $now,
            ]);
            continue;
        }

        $day = bucketForTask($task->title, (string) $task->description);
        $date = $start->copy()->addDays($day - 1);
        if ($date->greaterThan($end)) {
            $date = $end->copy();
        }

        DB::table('tasks')->where('id', $task->id)->update([
            'start_date' => $date->toDateString(),
            'due_date' => $date->toDateString(),
            'duration' => 1,
            'updated_at' => $now,
        ]);
        $scheduled++;
    }

    $readyListId = $lists['Ready'];
    $createdDays = 0;
    $updatedDays = 0;

    foreach ($dailyPlan as $day => [$headline, $deliverable]) {
        $date = $start->copy()->addDays($day - 1);
        $title = '[DAY-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT) . '] ' . $date->format('d M Y') . ' - ' . $headline;
        $description = implode("\n", [
            'Daily Target for HRIS 1-Month Delivery',
            'Date: ' . $date->toDateString(),
            'Focus: ' . $headline,
            'Deliverable: ' . $deliverable,
            '',
            'Daily Kanban Rules:',
            '- Move relevant feature cards into In Progress when coding starts.',
            '- Tick checklist items as work is completed.',
            '- Add comment for blocker, decision, test result, or deployment note.',
            '- End each day by moving completed cards to Review/Testing/Done or leaving blocker comment.',
        ]);

        $existing = DB::table('tasks')->where('title', $title)->first();
        $payload = [
            'title' => $title,
            'description' => $description,
            'order' => -1000 + $day,
            'priority' => 'high',
            'task_list_id' => $readyListId,
            'start_date' => $date->toDateString(),
            'due_date' => $date->toDateString(),
            'duration' => 1,
            'progress' => 0,
            'archived_at' => null,
            'cover_enabled' => true,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('tasks')->where('id', $existing->id)->update($payload);
            $taskId = $existing->id;
            $updatedDays++;
        } else {
            $payload['created_at'] = $now;
            $taskId = DB::table('tasks')->insertGetId($payload);
            $createdDays++;
        }

        if ($p0LabelId) {
            DB::table('task_label')->updateOrInsert(
                ['task_id' => $taskId, 'label_id' => $p0LabelId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $checklist = DB::table('checklists')->where('task_id', $taskId)->where('name', 'Daily Execution Checklist')->first();
        $checklistId = $checklist
            ? $checklist->id
            : DB::table('checklists')->insertGetId([
                'name' => 'Daily Execution Checklist',
                'task_id' => $taskId,
                'order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        $items = [
            'Pull planned feature cards for this day into In Progress',
            'Confirm module enable/disable and permission impact before coding',
            'Complete implementation checklist items on each related card',
            'Run tests or smoke checks for finished work',
            'Update Kanban status, checklist, and blocker/comment before end of day',
        ];
        foreach ($items as $order => $item) {
            DB::table('checklist_items')->updateOrInsert(
                ['checklist_id' => $checklistId, 'name' => $item],
                ['order' => $order, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    echo json_encode([
        'board_id' => $boardId,
        'target_start' => $start->toDateString(),
        'target_end' => $end->toDateString(),
        'feature_cards_scheduled' => $scheduled,
        'daily_cards_created' => $createdDays,
        'daily_cards_updated' => $updatedDays,
        'url' => 'https://31.97.106.123/kanban/boards/' . $boardId,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
});
