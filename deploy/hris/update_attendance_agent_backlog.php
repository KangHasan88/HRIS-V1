<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$backlog = TaskList::where('board_id', $boardId)->where('name', 'Backlog')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$labels = [];
foreach ([
    ['name' => 'Integration', 'color' => '#DBEAFE', 'description' => 'Integrasi sistem dan perangkat eksternal'],
    ['name' => 'Biometric', 'color' => '#E0F2FE', 'description' => 'Mesin absensi biometric/fingerprint/face recognition'],
    ['name' => 'Agent', 'color' => '#F3E8FF', 'description' => 'Local agent untuk koneksi dari jaringan lokal ke HRIS'],
    ['name' => 'Security', 'color' => '#FEE2E2', 'description' => 'Keamanan token, pairing, dan kontrol akses integrasi'],
] as $labelData) {
    $label = DB::table('labels')
        ->where('board_id', $boardId)
        ->where('name', $labelData['name'])
        ->first();

    if (! $label) {
        $labelId = DB::table('labels')->insertGetId([
            'name' => $labelData['name'],
            'color' => $labelData['color'],
            'description' => $labelData['description'],
            'board_id' => $boardId,
            'user_id' => $actor?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $label = DB::table('labels')->where('id', $labelId)->first();
    }

    $labels[$labelData['name']] = $label->id;
}

$cards = [
    [
        'code' => 'INTEGR-093',
        'title' => '[INTEGR-093] Local Attendance Agent MVP untuk mesin biometric lokal',
        'start' => '2026-07-20',
        'due' => '2026-07-21',
        'priority' => 'High',
        'labels' => ['Integration', 'Biometric', 'Agent'],
        'description' => "Modul: Integrasi Attendance Agent\nFitur: Local Attendance Agent MVP untuk mesin biometric lokal\nFeature Flag / Aktif-Nonaktif: hris.integr.local-attendance-agent-mvp\nPlatform: Windows local agent + HRIS API + Biometric Connector\n\nRuang Lingkup:\nMembuat agent lokal yang dipasang di PC/server yang satu jaringan dengan mesin absensi atau aplikasi Attendance Management. Agent membaca data dari source lokal lalu push ke HRIS lewat HTTPS agar server tidak perlu akses langsung ke IP lokal mesin.\n\nKriteria Penerimaan:\n- Agent punya konfigurasi pairing ke Biometric Connector HRIS.\n- Support source awal: folder CSV export dan mode API/local app placeholder.\n- Push data clock event ke endpoint HRIS dengan batch id, machine code, employee identifier, event time, event type, dan raw payload.\n- Retry queue lokal tersedia jika internet/VPS tidak bisa diakses.\n- Log agent bisa dilihat di HRIS connector/sync log.\n- Data hasil sync masuk ke batch Import Absensi untuk rekonsiliasi.\n- Smoke test end-to-end: CSV lokal -> agent -> HRIS -> batch import.",
        'checklist' => [
            'Definisikan kontrak payload agent ke HRIS API',
            'Buat endpoint ingest HRIS untuk agent push',
            'Buat pairing token antara HRIS connector dan agent lokal',
            'Buat agent MVP untuk Windows/local service atau console runner',
            'Buat CSV folder watcher sebagai source awal',
            'Buat retry queue dan log lokal',
            'Buat sync log di HRIS dari payload agent',
            'Test end-to-end sampai batch Import Absensi terbentuk',
        ],
    ],
    [
        'code' => 'INTEGR-094',
        'title' => '[INTEGR-094] Keamanan, pairing, heartbeat, dan monitoring Attendance Agent',
        'start' => '2026-07-22',
        'due' => '2026-07-23',
        'priority' => 'High',
        'labels' => ['Integration', 'Agent', 'Security'],
        'description' => "Modul: Integrasi Attendance Agent\nFitur: Keamanan, pairing, heartbeat, dan monitoring Attendance Agent\nFeature Flag / Aktif-Nonaktif: hris.integr.attendance-agent-security-monitoring\nPlatform: HRIS Admin + Agent runtime + API\n\nRuang Lingkup:\nMenyempurnakan agent agar aman dipakai production: token pairing, rotasi credential, heartbeat, status online/offline, versi agent, kontrol disable dari HRIS, dan monitoring queue.\n\nKriteria Penerimaan:\n- Agent terdaftar sebagai perangkat/source resmi di HRIS.\n- Token tidak ditulis plain di UI dan bisa di-rotate/revoke.\n- Payload memakai signature atau token scoped per connector.\n- Heartbeat menyimpan last seen, versi agent, hostname, dan queue pending.\n- Admin bisa pause/disable connector sehingga agent tidak diterima sementara.\n- Alert/log error tampil jika agent offline atau gagal sync berulang.\n- Audit trail tersedia untuk pairing, rotate token, disable, dan reconnect.",
        'checklist' => [
            'Buat tabel/field agent identity dan secret hash',
            'Buat flow generate pairing token di HRIS',
            'Buat endpoint heartbeat agent',
            'Buat indikator online/offline dan queue pending',
            'Buat rotate/revoke token',
            'Validasi signature/token scoped per connector',
            'Tambahkan audit trail pairing dan disable agent',
            'Test security negative case token invalid/revoked',
        ],
    ],
    [
        'code' => 'INTEGR-095',
        'title' => '[INTEGR-095] Adapter vendor mesin biometric dan aplikasi attendance lokal',
        'start' => '2026-07-24',
        'due' => '2026-07-26',
        'priority' => 'Medium',
        'labels' => ['Integration', 'Biometric', 'Agent'],
        'description' => "Modul: Integrasi Attendance Agent\nFitur: Adapter vendor mesin biometric dan aplikasi attendance lokal\nFeature Flag / Aktif-Nonaktif: hris.integr.biometric-vendor-adapter-pack\nPlatform: Agent plugin/adapter + HRIS mapping template\n\nRuang Lingkup:\nMenambahkan adapter source untuk vendor/format umum seperti ZKTeco, Solution, FingerTec, local database, dan export file dari aplikasi Attendance Management. Card ini dikerjakan setelah MVP agent dan security siap.\n\nKriteria Penerimaan:\n- Tersedia mapping template per vendor/source.\n- Support minimal CSV vendor dan struktur field umum: PIN/NIK, nama, waktu, tipe event, device id.\n- Adapter bisa normalisasi timezone, duplicate event, dan event type clock-in/out.\n- Local database reader disiapkan sebagai opsi jika aplikasi attendance menyimpan data di MySQL/MSSQL/Access/SQLite.\n- Hasil adapter tetap masuk pipeline agent -> HRIS -> Import Absensi.\n- Dokumentasi setup per vendor tersedia.",
        'checklist' => [
            'Inventaris format mesin/aplikasi attendance yang dipakai user',
            'Buat mapping template ZKTeco/Solution/FingerTec',
            'Buat normalisasi field PIN/NIK/event time/event type',
            'Buat dedupe key per mesin dan timestamp',
            'Siapkan opsi local database reader',
            'Buat dokumentasi setup per vendor/source',
            'Test minimal satu CSV vendor sampai masuk HRIS',
        ],
    ],
];

$created = [];
$maxOrder = (int) Task::where('task_list_id', $backlog->id)->max('order');
$maxWbsCode = (int) WbsItem::where('board_id', $boardId)->whereRaw('code REGEXP "^[0-9]+$"')->max(DB::raw('CAST(code AS UNSIGNED)'));
$maxWbsSort = (int) WbsItem::where('board_id', $boardId)->max('sort_order');

foreach ($cards as $index => $card) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $card['code'] . ']%')
        ->first();

    $isNew = false;
    if (! $task) {
        $task = new Task();
        $task->task_list_id = $backlog->id;
        $task->order = $maxOrder + $index + 1;
        $isNew = true;
    }

    $task->title = $card['title'];
    $task->description = $card['description'];
    $task->priority = $card['priority'];
    $task->start_date = $card['start'];
    $task->due_date = $card['due'];
    $task->duration = (new DateTime($card['start']))->diff(new DateTime($card['due']))->days + 1;
    $task->progress = 0;
    $task->save();

    foreach ($card['labels'] as $labelName) {
        DB::table('task_label')->updateOrInsert(
            ['task_id' => $task->id, 'label_id' => $labels[$labelName]],
            ['created_at' => now(), 'updated_at' => now()],
        );
    }

    $checklistId = DB::table('checklists')->where('task_id', $task->id)->where('name', 'Detail Pekerjaan')->value('id');
    if (! $checklistId) {
        $checklistId = DB::table('checklists')->insertGetId([
            'name' => 'Detail Pekerjaan',
            'task_id' => $task->id,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('checklist_items')->where('checklist_id', $checklistId)->delete();
    foreach ($card['checklist'] as $itemIndex => $item) {
        DB::table('checklist_items')->insert([
            'name' => $item,
            'is_checked' => false,
            'checklist_id' => $checklistId,
            'order' => $itemIndex + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
    if (! $wbs) {
        $wbs = new WbsItem();
        $wbs->board_id = $boardId;
        $wbs->task_id = $task->id;
        $wbs->code = (string) ($maxWbsCode + count($created) + 1);
        $wbs->sort_order = $maxWbsSort + count($created) + 1;
    }
    $wbs->title = $card['title'];
    $wbs->target = mb_substr($card['description'], 0, 900);
    $wbs->notes = 'Linked ke card ' . $card['code'] . '. Dibuat sebagai pemisahan scope agent lokal dari server-side Biometric Connector INTEGR-085.';
    $wbs->save();

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => ($isNew ? 'Backlog baru dibuat: ' : 'Backlog diperbarui: ') . $card['code'] . ' untuk scope Attendance Agent lokal. Card ini menjawab kebutuhan koneksi mesin lokal/aplikasi Attendance Management yang tidak bisa diakses langsung dari VPS.',
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $created[] = [
        'code' => $card['code'],
        'task_id' => $task->id,
        'title' => $task->title,
        'list' => $task->taskList()->value('name'),
        'start_date' => (string) $task->start_date,
        'due_date' => (string) $task->due_date,
        'wbs' => $wbs->code,
        'checklist_items' => count($card['checklist']),
    ];
}

$integr085 = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[INTEGR-085]%')
    ->first();

if ($integr085) {
    $note = "\n\nPenyempurnaan scope agent:\n- INTEGR-085 adalah sisi HRIS/server connector untuk direct cloud/vendor API dan penerimaan sync log.\n- Jika mesin/aplikasi Attendance Management berada di jaringan lokal, koneksi dilakukan melalui Local Attendance Agent.\n- Backlog lanjutan: INTEGR-093 agent MVP, INTEGR-094 security/pairing/monitoring, INTEGR-095 adapter vendor/source lokal.";
    if (! str_contains($integr085->description ?? '', 'Penyempurnaan scope agent')) {
        $integr085->description = rtrim((string) $integr085->description) . $note;
        $integr085->save();
    }

    Comment::create([
        'task_id' => $integr085->id,
        'user_id' => $actor?->id,
        'content' => 'Scope disempurnakan: INTEGR-085 tetap sebagai server-side Biometric Connector. Untuk mesin lokal/aplikasi Attendance Management yang hanya bisa diakses dari PC/site, pekerjaan dipisah ke INTEGR-093 Local Attendance Agent MVP, INTEGR-094 security/pairing/monitoring, dan INTEGR-095 adapter vendor.',
        'mentions' => [],
        'likes_count' => 0,
    ]);
}

echo json_encode([
    'updated_existing' => 'INTEGR-085',
    'created_or_updated' => $created,
], JSON_PRETTY_PRINT) . PHP_EOL;
