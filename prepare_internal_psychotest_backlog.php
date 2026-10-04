<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$backlog = TaskList::where('board_id', $boardId)->where('name', 'Backlog')->firstOrFail();
$ready = TaskList::where('board_id', $boardId)->where('name', 'Ready')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$labels = [];
foreach ([
    ['Assessment', '#8b5cf6', 'Psikotest, assessment, dan evaluasi kandidat/karyawan'],
    ['Internal Engine', '#0f766e', 'Engine internal tanpa ketergantungan penuh vendor'],
    ['Recruitment', '#2563eb', 'Workflow rekrutmen dan kandidat'],
    ['AI Assist', '#7c3aed', 'Fitur AI dengan review manusia'],
] as [$name, $color, $description]) {
    $labels[$name] = Label::firstOrCreate(
        ['board_id' => $boardId, 'name' => $name],
        ['color' => $color, 'description' => $description, 'user_id' => $actor?->id]
    );
}

$cards = [
    [
        'code' => 'ASSESS-002',
        'title' => '[ASSESS-002] Workflow undangan psikotest kandidat dan sinkron hasil internal',
        'status' => 'Ready',
        'start' => '2026-08-01',
        'due' => '2026-08-01',
        'priority' => 'high',
        'labels' => ['Assessment', 'Internal Engine', 'Recruitment'],
        'description' => 'Default flow psikotest memakai internal engine: HR memilih kandidat dan paket test internal, sistem membuat link unik, kandidat mengerjakan via portal, hasil tersimpan ke ATS, dan tetap menyediakan fallback provider eksternal jika diperlukan.',
        'target' => 'Module: Assessment Internal. Feature: invitation workflow kandidat, token/link test, expiry, consent, status tracking, hasil tersinkron ke profil kandidat dan pipeline recruitment.',
        'checklist' => [
            'Route dan menu undangan psikotest kandidat tersedia.',
            'HR bisa memilih kandidat, provider internal, paket test, deadline, dan instruksi.',
            'Sistem membuat token/link unik dengan expiry dan consent kandidat.',
            'Status invitation mencakup draft, sent, opened, in_progress, completed, expired, cancelled.',
            'Hasil test masuk ke profil kandidat dan bisa dipakai sebagai bahan screening/interview.',
            'Fallback provider eksternal tetap tersedia dari hub integrasi.',
            'Audit log invitation, resend, cancel, dan result sync tercatat.',
            'Feature test dan smoke HTTPS tersedia.',
        ],
    ],
    [
        'code' => 'ASSESS-003',
        'title' => '[ASSESS-003] Bank soal internal psikotest dan template battery',
        'status' => 'Backlog',
        'start' => '2026-08-02',
        'due' => '2026-08-02',
        'priority' => 'high',
        'labels' => ['Assessment', 'Internal Engine'],
        'description' => 'Master data internal psikotest untuk membuat kategori, dimensi, soal, opsi jawaban, scoring key, template battery, durasi, instruksi, dan versi soal.',
        'target' => 'Module: Assessment Internal. Feature: question bank, dimension mapping, scoring key, test template/battery, versioning, activation, import/export seed soal.',
        'checklist' => [
            'Tabel kategori, dimensi, soal, opsi jawaban, scoring key, dan template battery tersedia.',
            'UI tambah/edit soal dibagi per section agar mudah dibaca.',
            'Setiap soal punya tipe: pilihan tunggal, skala Likert, benar/salah, urutan prioritas, atau studi kasus.',
            'Template battery bisa menggabungkan beberapa section test.',
            'Versioning soal dan template tersedia untuk menjaga histori hasil lama.',
            'Mode aktif/nonaktif tersedia per kategori, soal, dan template.',
            'Import/export seed soal disiapkan untuk bulk entry awal.',
            'Feature test dan smoke HTTPS tersedia.',
        ],
    ],
    [
        'code' => 'ASSESS-004',
        'title' => '[ASSESS-004] Test runner kandidat internal dengan timer dan consent',
        'status' => 'Backlog',
        'start' => '2026-08-03',
        'due' => '2026-08-03',
        'priority' => 'high',
        'labels' => ['Assessment', 'Internal Engine', 'Recruitment'],
        'description' => 'Halaman test kandidat berbasis web/mobile WebView: consent, instruksi, timer, autosave jawaban, progress section, submit final, dan resume terbatas.',
        'target' => 'Module: Assessment Internal. Feature: candidate test runner, consent, timer, autosave, section progress, resume policy, anti-cheat basic, mobile WebView compatible.',
        'checklist' => [
            'Public/private test URL memakai token aman dan expiry.',
            'Consent kandidat wajib sebelum mulai test.',
            'Timer per test/section dan autosave jawaban tersedia.',
            'Progress section jelas untuk kandidat.',
            'Submit final mengunci jawaban dan mencatat timestamp.',
            'Resume terbatas tersedia jika koneksi putus.',
            'Basic anti-cheat mencatat tab switch, device fingerprint ringan, dan abnormal duration.',
            'Responsive untuk mobile WebView Android/iOS.',
            'Feature test dan smoke HTTPS tersedia.',
        ],
    ],
    [
        'code' => 'ASSESS-005',
        'title' => '[ASSESS-005] Scoring engine internal dan interpretasi hasil psikotest',
        'status' => 'Backlog',
        'start' => '2026-08-04',
        'due' => '2026-08-04',
        'priority' => 'high',
        'labels' => ['Assessment', 'Internal Engine', 'AI Assist'],
        'description' => 'Engine scoring internal untuk menghitung skor per dimensi, overall fit, rekomendasi, red flag, dan ringkasan interpretasi yang tetap bisa direview HR/psikolog.',
        'target' => 'Module: Assessment Internal. Feature: scoring engine, dimension score, fit score, risk flag, interpretation summary, reviewer approval, result lock.',
        'checklist' => [
            'Scoring engine mendukung weighted score, reverse score, threshold, dan dimension grouping.',
            'Hasil menampilkan skor per dimensi dan overall fit score.',
            'Red flag dan rekomendasi follow-up interview tersedia.',
            'Ringkasan interpretasi bisa dibuat otomatis namun wajib bisa direview manusia.',
            'Status hasil: draft, computed, reviewed, approved, locked.',
            'Hasil terkunci setelah approved untuk menjaga auditability.',
            'Histori perhitungan dan perubahan interpretasi tercatat.',
            'Feature test dan smoke HTTPS tersedia.',
        ],
    ],
    [
        'code' => 'ASSESS-006',
        'title' => '[ASSESS-006] Report psikotest internal untuk HR, user, dan kandidat',
        'status' => 'Backlog',
        'start' => '2026-08-05',
        'due' => '2026-08-05',
        'priority' => 'medium',
        'labels' => ['Assessment', 'Internal Engine', 'Recruitment'],
        'description' => 'Report hasil psikotest berbasis role: HR melihat detail, user interview melihat ringkasan fit/risk, kandidat hanya melihat status atau ringkasan yang disetujui perusahaan.',
        'target' => 'Module: Assessment Internal. Feature: role based report, print/PDF ready page, ATS attachment, visibility control, retention policy.',
        'checklist' => [
            'Report HR menampilkan skor detail, dimensi, rekomendasi, catatan reviewer, dan audit status.',
            'Report user interview menampilkan ringkasan fit, risiko, dan pertanyaan follow-up.',
            'Report kandidat mengikuti visibility policy perusahaan.',
            'Halaman report siap print/PDF.',
            'Report otomatis ter-link ke ATS profile kandidat.',
            'Retention policy data assessment tersedia.',
            'Feature test dan smoke HTTPS tersedia.',
        ],
    ],
];

$maxOrder = (int) Task::where('task_list_id', $backlog->id)->max('order');
$maxWbsSort = (int) WbsItem::where('board_id', $boardId)->max('sort_order');
$maxWbsCode = WbsItem::where('board_id', $boardId)
    ->whereRaw('code REGEXP "^[0-9]+$"')
    ->selectRaw('MAX(CAST(code AS UNSIGNED)) as max_code')
    ->value('max_code') ?: 0;

foreach ($cards as $index => $card) {
    $list = $card['status'] === 'Ready' ? $ready : $backlog;
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '['.$card['code'].']%')
        ->first();

    $payload = [
        'title' => $card['title'],
        'description' => $card['description']."\n\nFeature Flag / Enable-Disable: hris.assessment.internal.".strtolower(str_replace('-', '_', $card['code']))."\nPlatform: Web Admin + Candidate Portal + ESS/Mobile WebView compatible.\nTarget: internal psikotest menjadi default, external provider tetap fallback.",
        'priority' => $card['priority'],
        'start_date' => Carbon::parse($card['start']),
        'due_date' => Carbon::parse($card['due']),
        'duration' => 1,
        'progress' => 0,
        'task_list_id' => $list->id,
    ];

    if ($task) {
        $task->update($payload);
    } else {
        $task = Task::create($payload + ['order' => ++$maxOrder]);
    }

    $task->labels()->syncWithoutDetaching(array_map(fn ($label) => $labels[$label]->id, $card['labels']));

    $checklist = Checklist::firstOrCreate(
        ['task_id' => $task->id, 'name' => 'Checklist Delivery'],
        ['order' => 1]
    );

    foreach ($card['checklist'] as $order => $item) {
        ChecklistItem::firstOrCreate(
            ['checklist_id' => $checklist->id, 'name' => $item],
            ['order' => $order + 1, 'is_checked' => false]
        );
    }

    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
    $wbsPayload = [
            'parent_id' => null,
            'title' => $card['title'],
            'target' => $card['target'],
            'notes' => 'Disiapkan untuk arah dominan internal psikotest engine. Card, WBS, dan Gantt wajib sync saat coding.',
            'sort_order' => ++$maxWbsSort,
    ];

    if ($wbs) {
        $wbs->update($wbsPayload);
    } else {
        WbsItem::create($wbsPayload + [
            'board_id' => $boardId,
            'task_id' => $task->id,
            'code' => (string) ++$maxWbsCode,
        ]);
    }
}

$assess001 = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ASSESS-001]%')
    ->first();

if ($assess001) {
    $assess001->description = trim($assess001->description)."\n\nUpdate arah: provider type internal_engine akan menjadi default untuk psikotest internal; external provider tetap fallback.";
    $assess001->save();
}

Comment::create([
    'task_id' => Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))->where('title', 'like', '[ASSESS-002]%')->firstOrFail()->id,
    'user_id' => $actor?->id,
    'content' => 'Backlog disempurnakan: arah psikotest dominan internal. ASSESS-002 diubah menjadi workflow undangan internal + sinkron hasil, dan ditambah ASSESS-003 s/d ASSESS-006 untuk bank soal, test runner, scoring engine, dan report psikotest internal. Semua card sudah punya checklist detail, tanggal Gantt, label Assessment/Internal Engine, dan link WBS.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'prepared' => array_column($cards, 'code'),
    'ready' => Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId)->where('name', 'Ready'))->where('title', 'like', '[ASSESS-002]%')->exists(),
    'wbs_count' => WbsItem::where('board_id', $boardId)->where('title', 'like', '[ASSESS-%')->count(),
], JSON_PRETTY_PRINT).PHP_EOL;
