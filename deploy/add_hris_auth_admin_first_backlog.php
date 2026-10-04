<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Str;

$boardId = 6;
$now = now();

$board = DB::table('boards')->where('id', $boardId)->first();
if (!$board) {
    throw new RuntimeException('HRIS board not found.');
}

$lists = DB::table('task_lists')->where('board_id', $boardId)->pluck('id', 'name');
$readyId = $lists['Ready'] ?? null;
$backlogId = $lists['Backlog'] ?? null;
if (!$readyId || !$backlogId) {
    throw new RuntimeException('Ready/Backlog list not found.');
}

$user = DB::table('users')->where('username', 'codex')->first()
    ?: DB::table('users')->where('role', 'admin')->orderBy('id')->first()
    ?: DB::table('users')->orderBy('id')->first();

$labelDefs = [
    'Auth' => '#0A66C2',
    'Admin Panel' => '#10B981',
    'P0 Foundation' => '#dc2626',
    'Web Admin' => '#123b7a',
    'Feature Flag' => '#2563eb',
    'Security' => '#b91c1c',
];

$labelIds = [];
foreach ($labelDefs as $name => $color) {
    $label = DB::table('labels')->where('board_id', $boardId)->where('name', $name)->first();
    if ($label) {
        $labelIds[$name] = $label->id;
        continue;
    }
    $labelIds[$name] = DB::table('labels')->insertGetId([
        'board_id' => $boardId,
        'user_id' => $user->id,
        'name' => $name,
        'color' => $color,
        'description' => "HRIS {$name}",
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

$cards = [
    [
        'title' => '[AUTH-001] Login, logout, session, remember me, dan reset password',
        'date' => '2026-07-10',
        'priority' => 'high',
        'scope' => 'Membangun akses awal HRIS agar user bisa login, logout, reset password, dan menjaga session secara aman.',
        'items' => [
            'Buat halaman login dengan style SBB + accent violet',
            'Implement login/logout/session guard',
            'Implement reset password dan email/token flow',
            'Tambahkan remember me dan proteksi brute force/rate limit',
            'Tambahkan audit log login berhasil/gagal/logout/reset password',
            'Test desktop dan mobile responsive',
        ],
    ],
    [
        'title' => '[AUTH-002] Role awal Super Admin, HR Admin, Payroll, Manager, Employee',
        'date' => '2026-07-10',
        'priority' => 'high',
        'scope' => 'Menyiapkan role awal dan permission guard agar admin panel dan fitur berikutnya bisa dikontrol sejak hari pertama.',
        'items' => [
            'Definisikan role awal dan permission baseline',
            'Buat seeder user demo untuk Super Admin, HR Admin, Payroll, Manager, Employee',
            'Implement middleware permission untuk halaman admin',
            'Implement fallback unauthorized/forbidden page',
            'Tambahkan audit log perubahan role dan permission',
            'Test akses per role',
        ],
    ],
    [
        'title' => '[ADMIN-001] Admin panel shell, sidebar, topbar, dan dashboard progress',
        'date' => '2026-07-11',
        'priority' => 'high',
        'scope' => 'Membangun kerangka admin panel agar progress development bisa dilihat dari browser sebelum modul HRIS lain selesai.',
        'items' => [
            'Buat layout admin panel mengikuti template SBB dengan accent violet',
            'Buat sidebar modul: Dashboard, Karyawan, Absensi, Payroll, Rekrutmen, WBS, Settings',
            'Buat topbar user menu, notification placeholder, dan quick action',
            'Buat dashboard progress dari Kanban/WBS/Gantt summary',
            'Buat empty state untuk modul yang belum aktif',
            'Test layout desktop dan mobile responsive',
        ],
    ],
    [
        'title' => '[ADMIN-002] Module switcher dan status enable/disable di admin panel',
        'date' => '2026-07-11',
        'priority' => 'high',
        'scope' => 'Menampilkan kontrol awal module enable/disable agar owner bisa melihat modul mana yang aktif, pending, atau belum tersedia.',
        'items' => [
            'Buat halaman module settings per tenant/perusahaan',
            'Tampilkan status modul aktif, nonaktif, coming soon, dan locked',
            'Hubungkan module flag ke middleware/guard',
            'Tampilkan progress implementasi modul dari WBS/Card',
            'Tambahkan confirmation modal untuk perubahan status modul',
            'Test disabled-module guard dan UI state',
        ],
    ],
    [
        'title' => '[ADMIN-003] Halaman progress delivery: Kanban, WBS, dan Gantt summary',
        'date' => '2026-07-12',
        'priority' => 'high',
        'scope' => 'Menyediakan halaman ringkasan progress sehingga owner dapat melihat status backlog, WBS, dan Gantt tanpa membuka detail satu per satu.',
        'items' => [
            'Buat card ringkasan total backlog, ready, in progress, testing, done',
            'Buat ringkasan WBS linked/unlinked, overdue, dan progress',
            'Buat ringkasan Gantt target harian 1 bulan',
            'Tambahkan link cepat ke Kanban, WBS, dan Gantt',
            'Tambahkan highlight blocker dan overdue',
            'Test data sinkron dari card yang sama',
        ],
    ],
];

$maxTaskOrder = (int) DB::table('tasks')->whereIn('task_list_id', $lists->values())->max('order');
$maxWbsOrder = (int) DB::table('wbs_items')->where('board_id', $boardId)->max('sort_order');
$existingCodes = DB::table('wbs_items')->where('board_id', $boardId)->pluck('code')->filter()->map(fn ($v) => (string) $v)->all();
$numericCodes = array_values(array_filter(array_map(fn ($v) => ctype_digit((string) $v) ? (int) $v : null, $existingCodes), fn ($v) => $v !== null));
$nextCode = $numericCodes ? max($numericCodes) + 1 : 1;

$created = 0;
$updated = 0;
$taskIds = [];

foreach ($cards as $idx => $card) {
    $feature = preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $card['title']);
    $flag = 'hris.auth-admin.' . Str::slug($feature);
    $description = implode("\n", [
        'Modul: Auth & Admin Panel',
        "Fitur: {$feature}",
        "Feature Flag / Aktif-Nonaktif: {$flag}",
        'Platform: Web Admin + responsive web',
        '',
        'Ruang Lingkup:',
        $card['scope'],
        '',
        'Kriteria Penerimaan:',
        '- Bisa diakses dari browser sebagai progress awal sebelum modul lain selesai.',
        '- Mengikuti template/design/font SBB dengan accent hijau #10B981.',
        '- Data tanggal, status, dan progres tersinkron antara Card, WBS, dan Gantt.',
        '- Ada permission guard, audit trail, empty state, error state, dan responsive state.',
        '- Checklist Kanban wajib diperbarui saat implementasi, testing, review, dan blocker.',
    ]);

    $existing = DB::table('tasks')->where('title', $card['title'])->first();
    $payload = [
        'title' => $card['title'],
        'description' => $description,
        'task_list_id' => $readyId,
        'priority' => $card['priority'],
        'progress' => 0,
        'duration' => 1,
        'start_date' => $card['date'],
        'due_date' => $card['date'],
        'cover_enabled' => true,
        'archived_at' => null,
        'updated_at' => $now,
    ];
    if ($existing) {
        DB::table('tasks')->where('id', $existing->id)->update($payload);
        $taskId = $existing->id;
        $updated++;
    } else {
        $payload['order'] = ++$maxTaskOrder;
        $payload['created_at'] = $now;
        $taskId = DB::table('tasks')->insertGetId($payload);
        $created++;
    }
    $taskIds[] = $taskId;

    foreach (['Auth', 'Admin Panel', 'P0 Foundation', 'Web Admin', 'Feature Flag', 'Security'] as $labelName) {
        DB::table('task_label')->updateOrInsert(
            ['task_id' => $taskId, 'label_id' => $labelIds[$labelName]],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    foreach ([
        'Definition of Ready / Done' => [
            'Owner dan reviewer sudah jelas',
            'Scope login/admin panel bisa didemo dari browser',
            'Permission dan audit requirement sudah disepakati',
            'Style mengikuti template SBB dengan accent violet',
            'Tanggal/progress tersinkron ke WBS dan Gantt',
        ],
        'Implementation Work Breakdown' => $card['items'],
    ] as $checklistName => $items) {
        $checklist = DB::table('checklists')->where('task_id', $taskId)->where('name', $checklistName)->first();
        if ($checklist) {
            $checklistId = $checklist->id;
            DB::table('checklists')->where('id', $checklistId)->update(['updated_at' => $now]);
        } else {
            $checklistId = DB::table('checklists')->insertGetId([
                'task_id' => $taskId,
                'name' => $checklistName,
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

    $target = "Modul: Auth & Admin Panel. Fitur: {$feature}. Target: {$card['scope']}";
    $wbs = DB::table('wbs_items')->where('board_id', $boardId)->where('task_id', $taskId)->first();
    if ($wbs) {
        DB::table('wbs_items')->where('id', $wbs->id)->update([
            'title' => $card['title'],
            'target' => $target,
            'updated_at' => $now,
        ]);
    } else {
        while (in_array((string) $nextCode, $existingCodes, true)) {
            $nextCode++;
        }
        DB::table('wbs_items')->insert([
            'board_id' => $boardId,
            'parent_id' => null,
            'task_id' => $taskId,
            'code' => (string) $nextCode,
            'title' => $card['title'],
            'target' => $target,
            'notes' => null,
            'sort_order' => ++$maxWbsOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $existingCodes[] = (string) $nextCode;
        $nextCode++;
    }
}

// Keep day markers aligned with the new prerequisite.
$dayUpdates = [
    '[DAY-01] 10 Jul 2026 - Kickoff fondasi' => '[DAY-01] 10 Jul 2026 - Login & admin panel awal',
    '[DAY-02] 11 Jul 2026 - Arsitektur dan tenancy' => '[DAY-02] 11 Jul 2026 - Admin shell & module switcher',
    '[DAY-03] 12 Jul 2026 - Marketplace modul' => '[DAY-03] 12 Jul 2026 - Progress dashboard & WBS/Gantt summary',
    '[DAY-04] 13 Jul 2026 - RBAC dan audit' => '[DAY-04] 13 Jul 2026 - RBAC, audit, dan tenancy',
];

foreach ($dayUpdates as $old => $new) {
    $task = DB::table('tasks')->where('title', $old)->first();
    if (!$task) {
        continue;
    }
    DB::table('tasks')->where('id', $task->id)->update(['title' => $new, 'updated_at' => $now]);
    DB::table('wbs_items')->where('board_id', $boardId)->where('task_id', $task->id)->update(['title' => $new, 'updated_at' => $now]);
}

$summary = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->selectRaw('COUNT(*) as total_cards, SUM(CASE WHEN tasks.start_date IS NULL OR tasks.due_date IS NULL THEN 1 ELSE 0 END) as missing_dates, MIN(tasks.start_date) as min_start, MAX(tasks.due_date) as max_due')
    ->first();

$wbsSummary = DB::table('wbs_items')
    ->leftJoin('tasks', 'tasks.id', '=', 'wbs_items.task_id')
    ->where('wbs_items.board_id', $boardId)
    ->selectRaw('COUNT(wbs_items.id) as wbs_items, COUNT(tasks.id) as linked_items, SUM(CASE WHEN tasks.id IS NOT NULL AND (tasks.start_date IS NULL OR tasks.due_date IS NULL) THEN 1 ELSE 0 END) as linked_missing_dates')
    ->first();

echo json_encode([
    'board_id' => $boardId,
    'created' => $created,
    'updated' => $updated,
    'auth_admin_task_ids' => $taskIds,
    'date_summary' => $summary,
    'wbs_summary' => $wbsSummary,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
