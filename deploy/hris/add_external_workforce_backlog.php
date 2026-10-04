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

$labelDefinitions = [
    ['name' => 'External Workforce', 'color' => '#E0F2FE', 'description' => 'Tenaga outsource, harian, borongan, vendor workforce'],
    ['name' => 'Outsourcing', 'color' => '#F3E8FF', 'description' => 'Vendor penyedia jasa SDM dan kontrak outsourcing'],
    ['name' => 'Vendor Invoice', 'color' => '#FEF3C7', 'description' => 'Rekonsiliasi dan tagihan vendor'],
    ['name' => 'Attendance', 'color' => '#DCFCE7', 'description' => 'Absensi, mandays, shift, dan approval kehadiran'],
    ['name' => 'Payroll', 'color' => '#DBEAFE', 'description' => 'Payroll, kompensasi, dan perhitungan biaya tenaga kerja'],
    ['name' => 'Compliance', 'color' => '#FEE2E2', 'description' => 'Pajak, BPJS, HSE, dokumen kontrak, dan audit compliance'],
];

$labels = [];
foreach ($labelDefinitions as $labelData) {
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
        'code' => 'EXTWF-096',
        'title' => '[EXTWF-096] Master vendor outsourcing dan kontrak jasa SDM',
        'start' => '2026-07-24',
        'due' => '2026-07-25',
        'priority' => 'High',
        'labels' => ['External Workforce', 'Outsourcing', 'Compliance'],
        'featureFlag' => 'hris.external-workforce.vendor-contract-master',
        'scope' => 'Mencatat vendor penyedia tenaga kerja, kontrak jasa, PIC vendor, area kerja, periode kontrak, SLA, dokumen legal, dan status kerja sama.',
        'checklist' => [
            'Buat master vendor: kode, nama, NPWP/NIB, alamat, PIC, kontak, status aktif',
            'Buat kontrak jasa SDM: nomor kontrak, periode, scope lokasi, jenis tenaga, SLA',
            'Buat upload/link dokumen kontrak, addendum, PKS, dan dokumen compliance',
            'Buat mapping vendor ke company, legal entity, branch/site, dan cost center',
            'Buat status kontrak: draft, aktif, hampir berakhir, expired, terminated',
            'Buat audit trail perubahan vendor dan kontrak',
            'Tampilkan ringkasan vendor aktif, kontrak akan expired, dan dokumen kurang',
        ],
    ],
    [
        'code' => 'EXTWF-097',
        'title' => '[EXTWF-097] Master worker outsource, harian, borongan, freelance, dan konsultan',
        'start' => '2026-07-25',
        'due' => '2026-07-26',
        'priority' => 'High',
        'labels' => ['External Workforce', 'Outsourcing', 'Payroll'],
        'featureFlag' => 'hris.external-workforce.non-employee-worker-master',
        'scope' => 'Mencatat pekerja non-employee yang beroperasi di site/perusahaan: outsource vendor, harian lepas, borongan, freelance, konsultan, dan pekerja project.',
        'checklist' => [
            'Buat profil worker non-employee dengan worker type dan vendor/source',
            'Buat status kerja: aktif, suspend, selesai kontrak, blacklist, inactive',
            'Buat field lokasi kerja, fungsi/jabatan, mandor/supervisor internal, dan badge/access id',
            'Buat relasi ke vendor contract jika worker berasal dari outsourcing',
            'Buat flag apakah worker masuk payroll internal, vendor invoice, atau hanya operasional',
            'Buat validasi dokumen wajib: KTP, kontrak/PKWT vendor, HSE induction, akses area',
            'Buat filter worker type dan export data external workforce',
        ],
    ],
    [
        'code' => 'EXTWF-098',
        'title' => '[EXTWF-098] Attendance, mandays, dan approval tenaga harian',
        'start' => '2026-07-26',
        'due' => '2026-07-27',
        'priority' => 'High',
        'labels' => ['External Workforce', 'Attendance', 'Payroll'],
        'featureFlag' => 'hris.external-workforce.daily-worker-attendance-mandays',
        'scope' => 'Mengelola pencatatan kehadiran tenaga harian, rekap mandays, shift, lembur, approval supervisor, dan handoff ke payroll/vendor invoice.',
        'checklist' => [
            'Buat attendance register untuk worker type harian dan outsource harian',
            'Buat rekap mandays per worker, vendor, lokasi, cost center, dan periode',
            'Buat input shift, jam masuk/keluar, lembur, tidak hadir, dan alasan koreksi',
            'Buat approval supervisor/mandor internal sebelum menjadi payable',
            'Buat aturan tarif harian dan lembur opsional per vendor/kontrak/site',
            'Buat handoff ke payroll internal atau vendor invoice sesuai sumber worker',
            'Buat laporan mandays, biaya harian estimasi, dan exception belum approval',
        ],
    ],
    [
        'code' => 'EXTWF-099',
        'title' => '[EXTWF-099] Output borongan, tarif pekerjaan, dan approval mandor',
        'start' => '2026-07-27',
        'due' => '2026-07-28',
        'priority' => 'High',
        'labels' => ['External Workforce', 'Payroll', 'Vendor Invoice'],
        'featureFlag' => 'hris.external-workforce.piece-rate-output-approval',
        'scope' => 'Mencatat hasil kerja borongan berbasis volume/output seperti unit, tonase, ritase, meter, progress pekerjaan, tarif borongan, dan approval mandor/supervisor.',
        'checklist' => [
            'Buat master jenis pekerjaan borongan dan satuan output',
            'Buat tarif borongan per vendor/kontrak/site/pekerjaan/periode',
            'Buat input output harian/mingguan: volume, satuan, lokasi, foto/lampiran opsional',
            'Buat approval mandor atau supervisor internal sebelum output menjadi payable',
            'Buat validasi duplikasi output dan koreksi volume',
            'Buat perhitungan nilai borongan: volume x tarif, adjustment, penalty',
            'Buat laporan output borongan per vendor, worker/team, pekerjaan, dan periode',
        ],
    ],
    [
        'code' => 'EXTWF-100',
        'title' => '[EXTWF-100] Rekonsiliasi tagihan vendor dari mandays dan output borongan',
        'start' => '2026-07-28',
        'due' => '2026-07-29',
        'priority' => 'High',
        'labels' => ['External Workforce', 'Vendor Invoice', 'Payroll'],
        'featureFlag' => 'hris.external-workforce.vendor-invoice-reconciliation',
        'scope' => 'Membuat dasar tagihan vendor dari mandays tenaga harian, output borongan, adjustment, penalty, approval invoice, dan export ke finance/accounting.',
        'checklist' => [
            'Buat draft invoice vendor berdasarkan attendance harian dan output borongan approved',
            'Buat grouping invoice per vendor, kontrak, company, site, cost center, dan periode',
            'Buat adjustment: koreksi absensi/output, penalty, biaya tambahan, pembulatan',
            'Buat approval invoice internal sebelum diserahkan ke finance',
            'Buat status invoice: draft, review, approved, sent to finance, paid, disputed',
            'Buat export invoice detail dan summary untuk finance/accounting',
            'Buat audit trail setiap perubahan nilai invoice dan approval',
        ],
    ],
    [
        'code' => 'EXTWF-101',
        'title' => '[EXTWF-101] Compliance outsourcing: pajak, BPJS, HSE, akses area, dan tanggung jawab vendor',
        'start' => '2026-07-29',
        'due' => '2026-07-30',
        'priority' => 'Medium',
        'labels' => ['External Workforce', 'Outsourcing', 'Compliance'],
        'featureFlag' => 'hris.external-workforce.compliance-tax-bpjs-hse',
        'scope' => 'Mencatat matriks tanggung jawab compliance untuk tenaga outsource/harian/borongan: PPh, BPJS, HSE induction, akses area, dokumen wajib, dan bukti tanggung jawab vendor.',
        'checklist' => [
            'Buat matrix responsibility: ditanggung vendor, perusahaan, atau shared',
            'Buat mapping PPh/BPJS/HSE/access badge per worker type dan kontrak',
            'Buat checklist dokumen compliance wajib per vendor/worker',
            'Buat reminder dokumen expired, HSE induction expired, atau akses area habis',
            'Buat register bukti vendor: payroll proof, BPJS proof, tax proof, HSE proof',
            'Buat exception report worker aktif dengan compliance belum lengkap',
            'Buat audit trail approval dan perubahan responsibility matrix',
        ],
    ],
];

$maxOrder = (int) Task::where('task_list_id', $backlog->id)->max('order');
$maxWbsCode = (int) WbsItem::where('board_id', $boardId)->whereRaw('code REGEXP "^[0-9]+$"')->max(DB::raw('CAST(code AS UNSIGNED)'));
$maxWbsSort = (int) WbsItem::where('board_id', $boardId)->max('sort_order');
$results = [];

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

    $description = "Modul: External Workforce\n"
        . "Fitur: " . preg_replace('/^\[[^\]]+\]\s*/', '', $card['title']) . "\n"
        . "Feature Flag / Aktif-Nonaktif: {$card['featureFlag']}\n"
        . "Platform: Web Admin + ESS/WebView opsional + Payroll/Vendor Invoice + Approval\n\n"
        . "Ruang Lingkup:\n{$card['scope']}\n\n"
        . "Kriteria Penerimaan:\n"
        . "- Worker outsourcing/harian/borongan tidak tercampur dengan payroll employee internal.\n"
        . "- Data vendor, worker, attendance/output, approval, dan invoice saling terlacak.\n"
        . "- Hak akses membedakan HR, payroll, supervisor/mandor, finance, dan vendor-facing user bila nanti diaktifkan.\n"
        . "- Data tanggal, status, dan progres tersinkron antara Card, WBS, dan Gantt.\n"
        . "- Checklist Kanban wajib diperbarui saat implementasi, testing, review, dan blocker.";

    $task->title = $card['title'];
    $task->description = $description;
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

    $checklistId = DB::table('checklists')
        ->where('task_id', $task->id)
        ->where('name', 'Detail Pekerjaan')
        ->value('id');

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
        $wbs->code = (string) ($maxWbsCode + count($results) + 1);
        $wbs->sort_order = $maxWbsSort + count($results) + 1;
    }
    $wbs->title = $card['title'];
    $wbs->target = mb_substr($description, 0, 900);
    $wbs->notes = 'Linked ke card ' . $card['code'] . '. Scope dibuat untuk pengguna jasa outsourcing agar pekerja tercatat operasional, tetapi payroll employee internal tetap terpisah.';
    $wbs->save();

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => ($isNew ? 'Backlog baru dibuat: ' : 'Backlog diperbarui: ') . $card['code'] . ' untuk External Workforce: outsourcing, harian, borongan, vendor invoice, dan compliance.',
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $results[] = [
        'code' => $card['code'],
        'task_id' => $task->id,
        'list' => $task->taskList()->value('name'),
        'start_date' => (string) $task->start_date,
        'due_date' => (string) $task->due_date,
        'wbs' => $wbs->code,
        'checklist_items' => count($card['checklist']),
    ];
}

echo json_encode([
    'module' => 'External Workforce',
    'created_or_updated' => $results,
], JSON_PRETTY_PRINT) . PHP_EOL;
