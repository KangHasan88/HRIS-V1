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
    'Recruitment' => '#7c3aed',
    'Assessment' => '#db2777',
    'Integration' => '#64748b',
    'Compliance' => '#b91c1c',
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
            'description' => "HRIS psychotest integration label: {$name}",
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
        'title' => '[ASSESS-001] External psychotest provider integration hub',
        'scope' => 'Connector layer untuk vendor/modul psikotest eksternal: konfigurasi provider, credential, package test, callback URL, retry, dan log.',
        'start' => '2026-07-29',
        'due' => '2026-07-31',
        'priority' => 'high',
        'labels' => ['Recruitment', 'Assessment', 'Integration', 'Feature Flag', 'Web Admin'],
        'items' => [
            'Define provider configuration: base URL, API key/secret, callback token, package catalog, and tenant enable/disable',
            'Define API contract for candidate identity, job vacancy, assessment package, invitation expiry, and callback result',
            'Implement credential vault, encrypted secret storage, retry queue, request/response audit, and rate limit handling',
            'Build admin UI for provider setup, connection test, package mapping, and integration health status',
            'Add webhook verification, idempotency key, failed callback replay, and manual result resync',
            'Write contract tests with mock provider and disabled-module guard',
        ],
    ],
    [
        'title' => '[ASSESS-002] Candidate psychotest invitation and result sync workflow',
        'scope' => 'Workflow dari recruitment pipeline untuk mengirim invitation psikotest, memantau status, dan menyimpan hasil ke profil kandidat.',
        'start' => '2026-07-30',
        'due' => '2026-08-01',
        'priority' => 'high',
        'labels' => ['Recruitment', 'Assessment', 'Integration', 'Web Admin', 'Mobile ESS'],
        'items' => [
            'Add assessment stage action in recruitment pipeline: invite, resend, cancel, expire, and manual complete',
            'Generate secure invitation link via provider and send through email/WhatsApp notification channel',
            'Track candidate status: not invited, invited, opened, in progress, completed, expired, failed, and needs review',
            'Sync raw score, normalized score, report URL/file, completion timestamp, and provider reference ID',
            'Show candidate-facing mobile WebView status page with instruction, expiry, and contact support action',
            'Write tests for invitation lifecycle, duplicate callback, expired invitation, and provider failure',
        ],
    ],
    [
        'title' => '[ASSESS-003] Psychotest score mapping and hiring recommendation dashboard',
        'scope' => 'Mapping hasil psikotest eksternal ke kompetensi, role fit, risk indicator, dan recommendation summary untuk recruiter/hiring manager.',
        'start' => '2026-08-01',
        'due' => '2026-08-03',
        'priority' => 'medium',
        'labels' => ['Recruitment', 'Assessment', 'Web Admin'],
        'items' => [
            'Define competency dictionary: cognitive, personality, leadership, integrity, sales fit, service fit, and role-specific traits',
            'Create score mapping per job family with threshold, weight, interpretation text, and reviewer override',
            'Build dashboard for score comparison, role-fit indicator, report attachment, and interview follow-up notes',
            'Separate automated recommendation from final hiring decision with clear human approval requirement',
            'Add exportable candidate assessment summary for hiring committee',
            'Write tests for score mapping, threshold boundary, permission visibility, and report download access',
        ],
    ],
    [
        'title' => '[ASSESS-004] Psychotest consent, privacy, retention, and audit trail',
        'scope' => 'Kontrol kepatuhan untuk data psikologi kandidat/karyawan: consent, masking, retention, deletion request, dan akses terbatas.',
        'start' => '2026-08-02',
        'due' => '2026-08-04',
        'priority' => 'high',
        'labels' => ['Recruitment', 'Assessment', 'Compliance', 'Web Admin'],
        'items' => [
            'Define consent text, purpose limitation, data shared with provider, and candidate acknowledgement timestamp',
            'Restrict psychotest result access by role: recruiter, HR head, hiring manager, auditor, and support',
            'Mask sensitive psychological details by default and expose detailed report only with permission',
            'Set retention rule for rejected candidates, hired employees, legal hold, and deletion/anonymization request',
            'Log every invitation, callback, report view, download, override, and deletion action',
            'Write compliance tests for consent required, access denied, masking, retention, and audit log completeness',
        ],
    ],
];

$created = 0;
$updated = 0;

foreach ($cards as $idx => $card) {
    $feature = preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $card['title']);
    $flag = 'hris.assessment.' . Str::slug($feature);
    $description = implode("\n", [
        'Module: Recruitment & External Assessment Integration',
        "Feature: {$feature}",
        "Feature Flag / Enable-Disable: {$flag}",
        'Platform: Web Admin + Candidate Mobile WebView + External Provider API',
        '',
        'Scope:',
        $card['scope'],
        '',
        'Architecture Decision:',
        '- Psychotest engine/report can live outside HRIS as vendor/module eksternal.',
        '- HRIS owns workflow, consent, invitation, result sync, score mapping, permission, and audit trail.',
        '',
        'Acceptance Criteria:',
        '- Fitur tidak aktif jika module flag disabled.',
        '- Bisa connect ke provider mock dan siap diadaptasi ke provider real.',
        '- Semua data sensitif punya permission, masking, retention, dan audit log.',
        '- Candidate flow tetap nyaman di browser mobile dan Android/iOS full WebView.',
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
            'External provider contract or mock provider contract is defined',
            'Tenant-level enable/disable behavior is defined',
            'Candidate consent and privacy treatment are approved',
            'Recruiter/admin permission boundaries are defined',
            'Desktop admin and mobile WebView acceptance paths are tested',
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

echo json_encode([
    'board_id' => $board->id,
    'created' => $created,
    'updated' => $updated,
    'cards' => array_column($cards, 'title'),
    'url' => 'https://31.97.106.123/kanban/boards/' . $board->id,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
