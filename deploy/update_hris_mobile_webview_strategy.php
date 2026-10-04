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

$backlog = DB::table('task_lists')->where('board_id', $board->id)->where('name', 'Backlog')->first();
if (!$backlog) {
    throw new RuntimeException('Backlog list not found.');
}

$user = DB::table('users')->where('username', 'codex')->first()
    ?: DB::table('users')->where('role', 'admin')->orderBy('id')->first()
    ?: DB::table('users')->orderBy('id')->first();

$labelDefs = [
    'Mobile ESS' => '#16a34a',
    'Platform' => '#0f172a',
    'Feature Flag' => '#2563eb',
    'P0 Foundation' => '#dc2626',
    'Web Admin' => '#123b7a',
];
$labelIds = [];
foreach ($labelDefs as $name => $color) {
    $label = DB::table('labels')->where('board_id', $board->id)->where('name', $name)->first();
    if ($label) {
        $labelIds[$name] = $label->id;
    } else {
        $labelIds[$name] = DB::table('labels')->insertGetId([
            'name' => $name,
            'color' => $color,
            'description' => "HRIS mobile WebView label: {$name}",
            'board_id' => $board->id,
            'user_id' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

$webviewNote = "\n\nMobile Architecture Decision:\n- Android dan iOS menggunakan full WebView shell, bukan native screen per fitur.\n- Core experience dibangun sebagai responsive web/PWA yang dimuat oleh WebView.\n- Native layer hanya untuk shell capability: push notification, camera/file picker, GPS permission, biometric/PIN bridge, deep link, app version, secure storage token, dan download/share.\n- Semua fitur mobile wajib tetap bisa diatur melalui module enable/disable dan permission server-side.\n- QA wajib test di browser mobile, Android WebView, dan iOS WKWebView.";

$updatedMobileTasks = 0;
$mobileTasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $board->id)
    ->whereNull('tasks.archived_at')
    ->where(function ($q) {
        $q->where('tasks.description', 'like', '%Mobile%')
            ->orWhere('tasks.description', 'like', '%ESS%')
            ->orWhere('tasks.title', 'like', '%Mobile%')
            ->orWhere('tasks.title', 'like', '%ESS%')
            ->orWhere('tasks.title', 'like', '%clock%')
            ->orWhere('tasks.title', 'like', '%payslip%')
            ->orWhere('tasks.title', 'like', '%download%');
    })
    ->select('tasks.id', 'tasks.description')
    ->get();

foreach ($mobileTasks as $task) {
    $description = (string) $task->description;
    if (!str_contains($description, 'Mobile Architecture Decision:')) {
        DB::table('tasks')->where('id', $task->id)->update([
            'description' => rtrim($description) . $webviewNote,
            'updated_at' => $now,
        ]);
        $updatedMobileTasks++;
    }

    $checklist = DB::table('checklists')
        ->where('task_id', $task->id)
        ->where('name', 'Mobile WebView Requirements')
        ->first();
    if ($checklist) {
        $checklistId = $checklist->id;
    } else {
        $checklistId = DB::table('checklists')->insertGetId([
            'name' => 'Mobile WebView Requirements',
            'task_id' => $task->id,
            'order' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $items = [
        'Confirm responsive web screen works inside Android WebView and iOS WKWebView',
        'Define native bridge needs: push, camera/file, GPS, biometric/PIN, download/share',
        'Ensure session/token handling is secure in WebView shell',
        'Add module disabled state and permission denied state for WebView',
        'Test back button, deep link, file upload, download, and network loss behavior',
    ];
    foreach ($items as $order => $item) {
        DB::table('checklist_items')->updateOrInsert(
            ['checklist_id' => $checklistId, 'name' => $item],
            ['order' => $order, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
        );
    }
}

$maxOrder = (int) DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $board->id)
    ->max('tasks.order');

$cards = [
    [
        'title' => '[MOBILE-001] Android full WebView shell for HRIS/ESS',
        'scope' => 'Android app wrapper yang membuka responsive HRIS/ESS web app dengan support push notification, file/camera, GPS, biometric/PIN bridge, secure token, deep link, download/share, app update prompt, dan crash logging.',
    ],
    [
        'title' => '[MOBILE-002] iOS full WKWebView shell for HRIS/ESS',
        'scope' => 'iOS app wrapper yang membuka responsive HRIS/ESS web app dengan support APNs push, file/camera, location permission, Face ID/Touch ID bridge, secure token, universal link, download/share, dan App Store readiness.',
    ],
    [
        'title' => '[MOBILE-003] Responsive ESS web app optimized for WebView shell',
        'scope' => 'Web ESS harus terasa seperti mobile app: navigation, bottom tabs, safe area, touch target, offline notice, skeleton loading, low-bandwidth mode, and WebView-safe file upload.',
    ],
    [
        'title' => '[MOBILE-004] Native bridge contract and JavaScript SDK for WebView capabilities',
        'scope' => 'Kontrak bridge JS untuk push token, device info, GPS request, camera/file picker, biometric verification, download/share, logout, refresh token, dan app version check.',
    ],
    [
        'title' => '[MOBILE-005] Mobile WebView QA matrix for Android and iOS',
        'scope' => 'Matrix testing WebView: login, session timeout, back button, upload receipt, selfie/GPS clock-in, payslip download, push notification tap, deep link, network loss, and disabled module state.',
    ],
];

$created = 0;
$updated = 0;
foreach ($cards as $idx => $card) {
    $flag = 'hris.mobile-webview.' . Str::slug(preg_replace('/^\[MOBILE-\d+\]\s*/', '', $card['title']));
    $description = implode("\n", [
        'Module: Mobile WebView Shell',
        'Feature: ' . preg_replace('/^\[MOBILE-\d+\]\s*/', '', $card['title']),
        "Feature Flag / Enable-Disable: {$flag}",
        'Platform: Android WebView + iOS WKWebView + Responsive Web/PWA',
        'Priority: high',
        '',
        'Business Goal:',
        '- Mobile HRIS/ESS tersedia di Android dan Apple tanpa membangun ulang semua screen secara native.',
        '- Semua fitur tetap dikontrol server-side melalui module settings, permission, dan audit.',
        '',
        'Scope:',
        '- ' . $card['scope'],
        $webviewNote,
        '',
        'Acceptance Criteria:',
        '- App shell hanya wrapper WebView plus native bridge capability.',
        '- Semua screen utama berasal dari responsive web HRIS/ESS.',
        '- WebView shell tidak bypass permission, feature flag, atau audit trail.',
        '- QA pass di Android dan iOS untuk login, session, upload, GPS, push, download, dan back navigation.',
    ]);

    $existing = DB::table('tasks')->where('title', $card['title'])->first();
    $payload = [
        'title' => $card['title'],
        'description' => $description,
        'order' => $maxOrder + $idx + 1,
        'priority' => 'high',
        'task_list_id' => $backlog->id,
        'duration' => 5,
        'progress' => 0,
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

    foreach (['Mobile ESS', 'Platform', 'Feature Flag', 'P0 Foundation', 'Web Admin'] as $labelName) {
        DB::table('task_label')->updateOrInsert(
            ['task_id' => $taskId, 'label_id' => $labelIds[$labelName]],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    foreach ([
        'Definition of Ready / Done' => [
            'Confirm WebView-only mobile architecture with product owner',
            'Define bridge capability list and security boundary',
            'Define feature flag and server-side permission behavior',
            'Define Android/iOS release, signing, and store distribution path',
            'Define QA device/browser matrix',
        ],
        'Implementation Work Breakdown' => [
            'Build responsive mobile web route/shell entry point',
            'Implement Android WebView wrapper and app config',
            'Implement iOS WKWebView wrapper and app config',
            'Implement JS bridge for native capabilities',
            'Implement push token registration and notification tap routing',
            'Implement secure session/token lifecycle and logout',
            'Test camera/file upload, GPS, download/share, back navigation',
            'Test disabled module and permission denied states',
            'Document mobile release and rollback runbook',
            'Run VPS smoke test and update Kanban progress/status',
        ],
    ] as $checklistName => $items) {
        $checklist = DB::table('checklists')->where('task_id', $taskId)->where('name', $checklistName)->first();
        $checklistId = $checklist
            ? $checklist->id
            : DB::table('checklists')->insertGetId([
                'name' => $checklistName,
                'task_id' => $taskId,
                'order' => $checklistName === 'Definition of Ready / Done' ? 0 : 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        foreach ($items as $order => $item) {
            DB::table('checklist_items')->updateOrInsert(
                ['checklist_id' => $checklistId, 'name' => $item],
                ['order' => $order, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}

echo json_encode([
    'board_id' => $board->id,
    'mobile_existing_cards_updated_with_webview_note' => $updatedMobileTasks,
    'webview_cards_created' => $created,
    'webview_cards_updated' => $updated,
    'url' => 'https://31.97.106.123/kanban/boards/' . $board->id,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
