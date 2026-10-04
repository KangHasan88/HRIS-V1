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
    'Payroll' => '#0ea5e9',
    'Compliance' => '#b91c1c',
    'Integration' => '#64748b',
    'P0 Foundation' => '#dc2626',
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
            'description' => "HRIS tax/Coretax backlog label: {$name}",
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
        'title' => '[TAX-001] PPh 21 tax profile master: NPWP/NIK, PTKP, status, KLU, branch tax office',
        'mobile' => true,
        'scope' => 'Master data pajak karyawan dan perusahaan untuk semua perhitungan payroll dan pelaporan Coretax.',
    ],
    [
        'title' => '[TAX-002] TER monthly PPh 21 calculation engine for permanent employees',
        'mobile' => false,
        'scope' => 'Mesin hitung PPh 21 bulanan berbasis tarif efektif rata-rata, termasuk taxable/non-taxable component mapping.',
    ],
    [
        'title' => '[TAX-003] Annualized PPh 21 true-up and December recalculation',
        'mobile' => false,
        'scope' => 'Rekalkulasi tahunan, kurang/lebih bayar, dan penyesuaian masa Desember.',
    ],
    [
        'title' => '[TAX-004] THR, bonus, irregular income, and final tax treatment',
        'mobile' => false,
        'scope' => 'Perhitungan penghasilan tidak teratur, simulasi dampak pajak, dan audit formula.',
    ],
    [
        'title' => '[TAX-005] Non-permanent, daily worker, freelancer, and consultant PPh 21 rules',
        'mobile' => false,
        'scope' => 'Rule engine untuk pegawai tidak tetap, harian, borongan, tenaga ahli, dan penerima penghasilan bukan pegawai.',
    ],
    [
        'title' => '[TAX-006] Gross-up, gross, net, company-borne tax, and tax allowance modes',
        'mobile' => false,
        'scope' => 'Mode pajak per karyawan/komponen payroll: gross, gross-up, net, ditanggung perusahaan, dan tunjangan pajak.',
    ],
    [
        'title' => '[TAX-007] Taxable component mapping and payroll formula dependency graph',
        'mobile' => false,
        'scope' => 'Mapping komponen gaji ke objek pajak, dependency formula, validasi rounding, dan effective date.',
    ],
    [
        'title' => '[TAX-008] Coretax-ready monthly tax report package',
        'mobile' => false,
        'scope' => 'Paket data pajak bulanan siap upload/integrasi Coretax: masa pajak, employee tax detail, company summary, dan validation log.',
    ],
    [
        'title' => '[TAX-009] Coretax export template generator and schema versioning',
        'mobile' => false,
        'scope' => 'Generator file export sesuai template yang bisa berubah; schema versioning, preview, dan validation before download.',
    ],
    [
        'title' => '[TAX-010] Coretax API connector readiness with credential vault and retry log',
        'mobile' => false,
        'scope' => 'Arsitektur konektor Coretax jika API tersedia/digunakan: credential vault, request signing placeholder, retry, rate limit, dan audit.',
    ],
    [
        'title' => '[TAX-011] Coretax submission status tracker and reconciliation dashboard',
        'mobile' => false,
        'scope' => 'Tracking status draft/submitted/accepted/rejected, response mapping, dan rekonsiliasi dengan payroll batch.',
    ],
    [
        'title' => '[TAX-012] e-Bupot / withholding slip generation and employee ESS download',
        'mobile' => true,
        'scope' => 'Generate bukti potong, publish ke ESS, acknowledgement, resend, dan access control dokumen pajak.',
    ],
    [
        'title' => '[TAX-013] Tax correction, amended filing, and retroactive payroll adjustment',
        'mobile' => false,
        'scope' => 'Koreksi masa pajak, pembetulan, backpay, perubahan NPWP/PTKP, dan delta audit.',
    ],
    [
        'title' => '[TAX-014] Tax simulation sandbox before payroll finalization',
        'mobile' => false,
        'scope' => 'Simulasi PPh sebelum payroll final: compare current vs previous, anomaly, dan approval finance.',
    ],
    [
        'title' => '[TAX-015] Payroll tax audit trail and calculation explainability',
        'mobile' => false,
        'scope' => 'Trace formula pajak per karyawan: input, rule, formula, rounding, output, approver, dan timestamp.',
    ],
    [
        'title' => '[TAX-016] Tax compliance calendar, reminders, and SLA escalation',
        'mobile' => true,
        'scope' => 'Kalender masa pajak, reminder deadline, escalation ke finance/HR, dan dashboard compliance.',
    ],
    [
        'title' => '[TAX-017] Tax data privacy, masking, export permission, and legal retention',
        'mobile' => false,
        'scope' => 'Kontrol akses data pajak sensitif, masking NPWP/NIK, legal retention, dan export audit.',
    ],
    [
        'title' => '[TAX-018] Regression test suite for Indonesian PPh 21 and Coretax integration',
        'mobile' => false,
        'scope' => 'Golden test cases untuk berbagai skenario pajak, snapshot report, dan Coretax export/API contract tests.',
    ],
];

$baseChecklist = [
    'Confirm latest Indonesian PPh 21/Coretax business rules with Finance/Tax owner',
    'Define feature flag, package availability, and tenant-level enable/disable behavior',
    'Define role permissions for HR Payroll, Finance, Auditor, Employee, and Super Admin',
    'Design data model, effective date, migration, rollback, and audit fields',
    'Design calculation/API/export contract with validation and error codes',
    'Implement backend service, policy, validation, and immutable audit trail',
    'Build web admin UI with preview, validation result, empty state, and error state',
    'Add report/export/import behavior and reconciliation summary',
    'Write tests for enabled path, disabled-module guard, permissions, and validation',
    'Write regression test with realistic payroll/tax examples and rounding cases',
    'Document setup, operational runbook, and Coretax handoff process',
    'Run VPS smoke test and update Kanban status/checklist progress',
];

$created = 0;
$updated = 0;

foreach ($cards as $idx => $card) {
    $flag = 'hris.tax-coretax.' . Str::slug(preg_replace('/^\[TAX-\d+\]\s*/', '', $card['title']));
    $description = implode("\n", [
        'Module: Payroll Tax & Coretax Integration',
        'Feature: ' . preg_replace('/^\[TAX-\d+\]\s*/', '', $card['title']),
        "Feature Flag / Enable-Disable: {$flag}",
        'Platform: Web Admin + Payroll Engine' . ($card['mobile'] ? ' + Mobile ESS' : ''),
        'Priority: high',
        '',
        'Business Goal:',
        '- HRIS mampu menghitung PPh 21 secara akurat, dapat diaudit, dan siap dilaporkan/diintegrasikan dengan Coretax.',
        '- Semua fitur tax wajib bisa dimatikan/dinyalakan per tenant karena kesiapan perusahaan bisa berbeda.',
        '',
        'Scope:',
        '- ' . $card['scope'],
        '- Wajib mendukung audit trail, permission, validation, dan report/export.',
        ($card['mobile'] ? '- Dampak Mobile ESS wajib jelas, terutama dokumen pajak, reminder, dan notifikasi karyawan.' : '- Mobile impact minimal berupa status/read-only notification bila relevan.'),
        '',
        'Acceptance Criteria:',
        '- Fitur tidak berjalan jika module flag disabled.',
        '- Formula/output bisa ditelusuri dari input sampai hasil akhir.',
        '- Ada validation report sebelum payroll/tax final.',
        '- Ada permission guard untuk data pajak sensitif.',
        '- Ada test untuk skenario pajak utama dan edge case.',
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

    $labels = ['Payroll', 'Compliance', 'Integration', 'P0 Foundation', 'Feature Flag', 'Web Admin'];
    if ($card['mobile']) {
        $labels[] = 'Mobile ESS';
    }
    foreach ($labels as $labelName) {
        DB::table('task_label')->updateOrInsert(
            ['task_id' => $taskId, 'label_id' => $labelIds[$labelName]],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    foreach ([
        'Definition of Ready / Done' => [
            'Confirm owner, legal reference, and latest regulation assumption',
            'Define module setting toggle and default state',
            'Define role permissions and approval ownership',
            'Define audit, retention, masking, and export requirements',
            'Implement disabled-module guard and automated tests',
        ],
        'Implementation Work Breakdown' => $baseChecklist,
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
    'total_tax_cards' => count($cards),
    'url' => 'https://31.97.106.123/kanban/boards/' . $board->id,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
