<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[INTEGR-095]%')
    ->firstOrFail();

$task->title = '[INTEGR-095] Adapter database Attendance Management lokal dan vendor biometric';
$task->description = "Modul: Integrasi Attendance Agent\nFitur: Adapter database Attendance Management lokal dan vendor biometric\nFeature Flag / Aktif-Nonaktif: hris.integr.attendance-management-db-and-biometric-adapter\nPlatform: Windows Local Agent + database lokal + adapter vendor + HRIS mapping template\n\nRuang Lingkup:\nMenambahkan adapter source untuk agent Windows agar bisa menarik data dari aplikasi Attendance Management lokal yang ada di PC/site, termasuk database lokal dan format export vendor biometric. Card ini dikerjakan setelah MVP agent dan security siap.\n\nSkenario Utama:\n- Agent dipasang di komputer yang menjalankan Attendance Management System atau satu jaringan dengan database aplikasi tersebut.\n- Agent membaca database lokal seperti MySQL, MSSQL, Access, SQLite, atau file DB lain yang disepakati.\n- Agent juga tetap support export CSV/vendor seperti ZKTeco, Solution, FingerTec, dan format custom.\n- Data dinormalisasi menjadi payload HRIS: employee identifier, device id, event time, event type, source, raw payload.\n- Hasil adapter masuk pipeline agent -> HRIS -> Biometric Connector -> batch Import Absensi -> rekonsiliasi.\n\nKriteria Penerimaan:\n- Tersedia mapping template untuk database Attendance Management lokal.\n- Tersedia opsi koneksi database lokal: host/path, port, database name, username, password/secret, table/view, dan query template.\n- Credential database disimpan aman di agent lokal, tidak ditampilkan plain di HRIS.\n- Support minimal satu mode database reader dan satu mode CSV vendor untuk MVP adapter.\n- Adapter bisa normalisasi timezone, duplicate event, PIN/NIK/employee number, dan event type clock-in/out.\n- Local database reader punya incremental sync memakai last pulled timestamp/id.\n- Error koneksi database, query kosong, dan mapping field salah tercatat di log agent dan HRIS sync log.\n- Dokumentasi setup per source tersedia.";
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $wbs->title = $task->title;
    $wbs->target = mb_substr($task->description, 0, 900);
    $wbs->notes = 'Scope ditegaskan: adapter ini mencakup database lokal Attendance Management System di Windows/PC site dan adapter vendor biometric.';
    $wbs->save();
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
foreach ([
    'Inventaris aplikasi Attendance Management yang dipakai dan jenis database lokalnya',
    'Buat konfigurasi koneksi database lokal di agent Windows',
    'Buat query template untuk mengambil data clock event secara incremental',
    'Buat mapping field PIN/NIK/employee number/event time/event type/device id',
    'Buat penyimpanan credential lokal yang tidak tampil plain di HRIS',
    'Buat adapter CSV vendor ZKTeco/Solution/FingerTec sebagai fallback',
    'Buat normalisasi timezone dan tipe event clock-in/out',
    'Buat dedupe key per mesin, identifier, timestamp, dan event type',
    'Log error database/query/mapping dikirim ke HRIS sync log',
    'Test end-to-end database Attendance Management lokal -> agent -> HRIS -> Import Absensi',
    'Dokumentasi setup database dan CSV per vendor/source',
] as $index => $item) {
    DB::table('checklist_items')->insert([
        'name' => $item,
        'is_checked' => false,
        'checklist_id' => $checklistId,
        'order' => $index + 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Scope INTEGR-095 ditegaskan: card ini mencakup agent Windows yang menarik data dari database Attendance Management System lokal, selain adapter vendor biometric/CSV. Checklist diperbarui sampai konfigurasi DB, credential lokal, query incremental, mapping field, dedupe, logging error, dan test end-to-end.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'INTEGR-095',
    'task_id' => $task->id,
    'title' => $task->title,
    'wbs' => $wbs?->code,
    'checklist_items' => 11,
], JSON_PRETTY_PRINT) . PHP_EOL;
