<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now();
$board = DB::table('boards')->where('id', 6)->first();
if (!$board) {
    throw new RuntimeException('HRIS board ID 6 not found.');
}

$backlog = DB::table('task_lists')
    ->where('board_id', $board->id)
    ->where('name', 'Backlog')
    ->first();
if (!$backlog) {
    throw new RuntimeException('Backlog list not found.');
}

$user = DB::table('users')->where('username', 'codex')->first()
    ?: DB::table('users')->where('role', 'admin')->orderBy('id')->first()
    ?: DB::table('users')->orderBy('id')->first();

$labelDefs = [
    'Benchmark' => '#7c3aed',
    'Product Readiness' => '#0f766e',
    'Support' => '#f59e0b',
    'Security' => '#b91c1c',
    'Feature Flag' => '#2563eb',
    'Web Admin' => '#123b7a',
    'Mobile ESS' => '#16a34a',
];

$labelIds = [];
foreach ($labelDefs as $name => $color) {
    $label = DB::table('labels')->where('board_id', $board->id)->where('name', $name)->first();
    if (!$label) {
        $id = DB::table('labels')->insertGetId([
            'name' => $name,
            'color' => $color,
            'description' => "HRIS readiness label: {$name}",
            'board_id' => $board->id,
            'user_id' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } else {
        $id = $label->id;
    }
    $labelIds[$name] = $id;
}

$maxOrder = (int) DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $board->id)
    ->max('tasks.order');

$cards = [
    [
        'title' => '[BENCH-001] Market benchmark parity matrix and release gate',
        'scope' => 'Matriks pembanding internal untuk memastikan HRIS kita minimal setara fitur pasar dan punya pembeda jelas sebelum rilis.',
        'start' => '2026-08-03',
        'due' => '2026-08-05',
        'priority' => 'high',
        'labels' => ['Benchmark', 'Product Readiness', 'Feature Flag', 'Web Admin', 'Mobile ESS'],
        'items' => [
            'List market baseline features: core HR, attendance, payroll, ESS, performance, recruitment, analytics, document, security, integration',
            'Map every baseline item to existing module/card, owner, target date, and demo evidence',
            'Mark product differentiators: module enable/disable, Coretax readiness, WebView mobile shell, AI analytics, workflow builder, API/webhook',
            'Define release gate: must-have parity completed, differentiator demoable, no competitor brand wording in product/backlog',
            'Create QA checklist for desktop admin, responsive ESS, Android WebView, and iOS WebView',
            'Update Kanban status and attach comparison summary before UAT',
        ],
    ],
    [
        'title' => '[READY-001] In-app guide, help center, release notes, and user training package',
        'scope' => 'Kesiapan adopsi produk agar HR, manager, employee, finance, dan admin bisa onboard tanpa bergantung full ke tim teknis.',
        'start' => '2026-08-04',
        'due' => '2026-08-07',
        'priority' => 'medium',
        'labels' => ['Product Readiness', 'Support', 'Web Admin', 'Mobile ESS'],
        'items' => [
            'Define role-based quickstart for Super Admin, HR Admin, Payroll, Finance, Manager, and Employee',
            'Build contextual help drawer for high-risk flows: payroll finalization, tax report, attendance correction, approval, data import',
            'Prepare release notes format with new feature, changed behavior, migration note, known limitation, and rollback note',
            'Prepare demo dataset and guided walkthrough for HRIS admin and ESS mobile WebView',
            'Add searchable FAQ and operational runbook links inside admin settings',
            'Test guide readability on desktop, tablet, Android WebView, and iOS WebView',
        ],
    ],
    [
        'title' => '[READY-002] Customer support SLA, issue triage, and escalation workflow',
        'scope' => 'Sistem support internal untuk incident, bug, request, dan compliance escalation agar operasional HRIS siap dipakai bisnis.',
        'start' => '2026-08-05',
        'due' => '2026-08-08',
        'priority' => 'medium',
        'labels' => ['Product Readiness', 'Support', 'Web Admin'],
        'items' => [
            'Define ticket severity P0-P3, response SLA, resolution target, and escalation owner',
            'Create issue intake fields for tenant, module flag, affected role, browser/device, payroll period, and evidence attachment',
            'Define incident workflow from reported, triaged, assigned, fix ready, testing, resolved, and postmortem',
            'Create support dashboard for aging tickets, breach risk, module category, and recurring issue patterns',
            'Prepare runbook for payroll/tax emergency, attendance outage, import failure, login/security incident, and mobile WebView issue',
            'Add audit trail for support actions touching sensitive employee/payroll/tax data',
        ],
    ],
    [
        'title' => '[READY-003] Security certification evidence and compliance readiness pack',
        'scope' => 'Paket bukti teknis dan proses untuk menunjukkan keamanan data HR, payroll, pajak, dan dokumen sudah siap diaudit.',
        'start' => '2026-08-06',
        'due' => '2026-08-09',
        'priority' => 'high',
        'labels' => ['Product Readiness', 'Security', 'Web Admin'],
        'items' => [
            'Prepare access control matrix for tenant admin, HR, payroll, finance, manager, employee, auditor, and support',
            'Document encryption, secret handling, backup, retention, deletion, export masking, and legal hold behavior',
            'Define MFA/session/device policy for web admin and mobile WebView shell',
            'Create audit evidence checklist for login, role change, payroll run, tax export, document access, and data import',
            'Prepare vulnerability management workflow: dependency scan, security patch SLA, pentest issue tracking, and retest evidence',
            'Validate that sensitive views mask NIK, NPWP, bank account, salary, and tax data by default',
        ],
    ],
];

$created = 0;
$updated = 0;

foreach ($cards as $idx => $card) {
    $flag = 'hris.readiness.' . Str::slug(preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $card['title']));
    $description = implode("\n", [
        'Module: Product Readiness & Market Benchmark',
        'Feature: ' . preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $card['title']),
        "Feature Flag / Enable-Disable: {$flag}",
        'Platform: Web Admin + ESS Responsive Web + Android/iOS Full WebView Shell',
        '',
        'Scope:',
        $card['scope'],
        '',
        'Acceptance Criteria:',
        '- Tidak ada penyebutan brand kompetitor di product copy, backlog, atau dokumentasi user-facing.',
        '- Ada checklist bukti yang bisa dipakai sebagai release gate sebelum UAT.',
        '- Ada path enable/disable module dan permission guard.',
        '- Ada coverage untuk desktop admin dan mobile WebView.',
    ]);

    $existing = DB::table('tasks')->where('title', $card['title'])->first();
    $payload = [
        'title' => $card['title'],
        'description' => $description,
        'order' => $maxOrder + $idx + 1,
        'priority' => $card['priority'],
        'task_list_id' => $backlog->id,
        'duration' => 3,
        'progress' => 0,
        'start_date' => $card['start'],
        'due_date' => $card['due'],
        'archived_at' => null,
        'cover_enabled' => true,
        'updated_at' => $now,
    ];

    if ($existing) {
        DB::table('tasks')->where('id', $existing->id)->update($payload);
        $taskId = $existing->id;
        $updated++;
    } else {
        $payload['created_at'] = $now;
        $taskId = DB::table('tasks')->insertGetId($payload);
        $created++;
    }

    foreach ($card['labels'] as $labelName) {
        DB::table('task_label')->updateOrInsert(
            ['task_id' => $taskId, 'label_id' => $labelIds[$labelName]],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    foreach ([
        'Definition of Ready / Done' => [
            'Owner and reviewer assigned',
            'Release gate expected output is clear',
            'Module enable/disable and permission behavior defined',
            'Desktop and mobile WebView acceptance paths defined',
            'Evidence or artifact attached before moving to Done',
        ],
        'Implementation Work Breakdown' => $card['items'],
    ] as $checklistName => $items) {
        $checklist = DB::table('checklists')->where('task_id', $taskId)->where('name', $checklistName)->first();
        if ($checklist) {
            $checklistId = $checklist->id;
            DB::table('checklists')->where('id', $checklistId)->update(['updated_at' => $now]);
        } else {
            $checklistId = DB::table('checklists')->insertGetId([
                'name' => $checklistName,
                'task_id' => $taskId,
                'order' => $checklistName === 'Definition of Ready / Done' ? 0 : 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($items as $order => $item) {
            DB::table('checklist_items')->updateOrInsert(
                ['checklist_id' => $checklistId, 'name' => mb_substr($item, 0, 250)],
                ['order' => $order, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

$brandMentions = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->leftJoin('checklists', 'checklists.task_id', '=', 'tasks.id')
    ->leftJoin('checklist_items', 'checklist_items.checklist_id', '=', 'checklists.id')
    ->where('task_lists.board_id', $board->id)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) {
        $query->where('tasks.title', 'like', '%Talenta%')
            ->orWhere('tasks.description', 'like', '%Talenta%')
            ->orWhere('checklist_items.name', 'like', '%Talenta%');
    })
    ->count();

echo json_encode([
    'board_id' => $board->id,
    'created' => $created,
    'updated' => $updated,
    'cards' => array_column($cards, 'title'),
    'brand_mentions_after' => $brandMentions,
    'url' => 'https://31.97.106.123/kanban/boards/' . $board->id,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
