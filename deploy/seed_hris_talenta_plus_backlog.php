<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now();
$user = DB::table('users')
    ->where('username', 'codex')
    ->orWhere('email', 'codex')
    ->orWhere('email', 'codex@kurmigo.local')
    ->first();

if (!$user) {
    $user = DB::table('users')->where('role', 'admin')->orderBy('id')->first()
        ?: DB::table('users')->orderBy('id')->first();
}

if (!$user) {
    throw new RuntimeException('No Kanban user found.');
}

$boardName = 'Kurmigo HRIS - Talenta+ Backlog';
$boardDescription = "Backlog komprehensif untuk membangun Kurmigo HRIS yang lebih kuat dari Talenta: HR Core, ESS Mobile, payroll, attendance, talent, analytics, integrasi, compliance, dan mode enable/disable per modul.";

DB::transaction(function () use ($boardName, $boardDescription, $user, $now) {
    $board = DB::table('boards')->where('name', $boardName)->first();
    if (!$board) {
        $boardId = DB::table('boards')->insertGetId([
            'name' => $boardName,
            'description' => $boardDescription,
            'user_id' => $user->id,
            'auto_archive_enabled' => false,
            'auto_archive_days' => 14,
            'auto_archive_list_name' => 'Done',
            'cover_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $board = DB::table('boards')->where('id', $boardId)->first();
    } else {
        DB::table('boards')->where('id', $board->id)->update([
            'description' => $boardDescription,
            'updated_at' => $now,
        ]);
    }

    $lists = [
        ['Backlog', '#e2e8f0'],
        ['Ready', '#bfdbfe'],
        ['In Progress', '#fde68a'],
        ['Review', '#ddd6fe'],
        ['Testing', '#fed7aa'],
        ['Done', '#bbf7d0'],
        ['Icebox', '#cbd5e1'],
    ];

    $listIds = [];
    foreach ($lists as $order => [$name, $color]) {
        $list = DB::table('task_lists')->where('board_id', $board->id)->where('name', $name)->first();
        if (!$list) {
            $id = DB::table('task_lists')->insertGetId([
                'name' => $name,
                'order' => $order,
                'color' => $color,
                'board_id' => $board->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $id = $list->id;
            DB::table('task_lists')->where('id', $id)->update(['order' => $order, 'color' => $color, 'updated_at' => $now]);
        }
        $listIds[$name] = $id;
    }

    $labelDefs = [
        'P0 Foundation' => '#dc2626',
        'P1 Core HR' => '#f97316',
        'P2 Differentiator' => '#7c3aed',
        'Platform' => '#0f172a',
        'Feature Flag' => '#2563eb',
        'Mobile ESS' => '#16a34a',
        'Web Admin' => '#123b7a',
        'Payroll' => '#0ea5e9',
        'Attendance' => '#14b8a6',
        'Talent' => '#a855f7',
        'Analytics AI' => '#db2777',
        'Integration' => '#64748b',
        'Compliance' => '#b91c1c',
    ];

    $labelIds = [];
    foreach ($labelDefs as $name => $color) {
        $label = DB::table('labels')->where('board_id', $board->id)->where('name', $name)->first();
        if (!$label) {
            $id = DB::table('labels')->insertGetId([
                'name' => $name,
                'color' => $color,
                'description' => "HRIS backlog label: {$name}",
                'board_id' => $board->id,
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $id = $label->id;
            DB::table('labels')->where('id', $id)->update(['color' => $color, 'updated_at' => $now]);
        }
        $labelIds[$name] = $id;
    }

    $modules = [
        'Platform & Architecture' => [
            'label' => ['P0 Foundation', 'Platform', 'Feature Flag', 'Web Admin'],
            'priority' => 'high',
            'platform' => 'Web Admin + API + Mobile-ready',
            'features' => [
                'Multi-company, branch, and legal entity tenancy',
                'Module marketplace with enable/disable per company',
                'Role-based access control with permission templates',
                'Approval workflow builder with conditional routing',
                'Audit trail, activity log, and immutable change history',
                'Custom fields and dynamic form builder for HR objects',
                'Notification center: email, WhatsApp, push, and in-app',
                'Global search across employees, requests, documents, payroll',
                'Bulk import/export engine with validation preview',
                'Data privacy controls: masking, consent, and retention policy',
            ],
        ],
        'HR Core & Employee Master' => [
            'label' => ['P1 Core HR', 'Web Admin', 'Mobile ESS'],
            'priority' => 'high',
            'platform' => 'Web Admin + ESS read-only profile',
            'features' => [
                'Employee 360 profile with employment, family, bank, tax, BPJS',
                'Organization structure, position, job grade, and cost center',
                'Employment status lifecycle: probation, contract, permanent, alumni',
                'Contract, PKWT/PKWTT, renewal, and document expiry tracking',
                'Employee document vault with OCR metadata and version history',
                'Asset assignment and return tracking for laptop, uniform, ID card',
                'Mutation, promotion, demotion, and transfer workflow',
                'Disciplinary action, warning letter, and coaching history',
                'Onboarding task packs by role, location, and department',
                'Offboarding, clearance sheet, exit interview, and final settlement',
            ],
        ],
        'Attendance, Shift & Leave' => [
            'label' => ['P1 Core HR', 'Attendance', 'Mobile ESS'],
            'priority' => 'high',
            'platform' => 'Web Admin + Mobile ESS',
            'features' => [
                'Shift roster builder with rotating, split, long shift, and pattern rules',
                'Mobile clock-in/out with GPS, selfie, device binding, and liveness flag',
                'Office geofence, work-from-home, client site, and business trip attendance',
                'Biometric and machine attendance import with anomaly reconciliation',
                'Leave policy engine: annual, sick, special, unpaid, carry forward',
                'Leave request and approval with balance simulation',
                'Overtime request, approval, calculation, and payroll handoff',
                'Attendance correction and missing punch workflow',
                'Holiday calendar per company, branch, religion, and region',
                'Fatigue, lateness, absenteeism, and compliance alerts',
            ],
        ],
        'Payroll, Benefit & Finance' => [
            'label' => ['P1 Core HR', 'Payroll', 'Web Admin', 'Mobile ESS'],
            'priority' => 'high',
            'platform' => 'Web Admin + ESS payslip',
            'features' => [
                'Payroll component builder: earnings, deductions, formulas, proration',
                'Salary structure, grade matrix, and confidential compensation access',
                'BPJS Kesehatan/Ketenagakerjaan and tax profile management',
                'PPh 21 monthly, THR, bonus, final tax, and gross-up simulation',
                'Payroll run wizard with preflight validation and anomaly detection',
                'Payslip publish, download, acknowledgement, and resend control',
                'Reimbursement and claim workflow with receipt attachment and OCR',
                'Employee loan, koperasi deduction, installment, and balance tracking',
                'Bank disbursement file generation and approval',
                'Retroactive adjustment, backpay, and payroll audit report',
            ],
        ],
        'ESS Mobile & Employee Experience' => [
            'label' => ['P1 Core HR', 'Mobile ESS', 'Feature Flag'],
            'priority' => 'high',
            'platform' => 'Native/PWA Mobile ESS + Web ESS',
            'features' => [
                'Mobile ESS home with today schedule, pending action, and payslip shortcut',
                'Mobile biometric login, PIN fallback, and trusted device management',
                'Profile update request with approval and document proof',
                'Leave, overtime, business trip, and attendance correction request',
                'Payslip, tax slip, BPJS card, contract, and company letter download',
                'Reimbursement submission with camera receipt capture',
                'HR helpdesk ticket with SLA, category, attachment, and chat thread',
                'Company announcement, policy acknowledgement, and broadcast targeting',
                'Employee survey, pulse check, eNPS, and anonymous feedback',
                'Offline queue for clock-in/out and request draft sync',
            ],
        ],
        'Manager Self Service' => [
            'label' => ['P2 Differentiator', 'Web Admin', 'Mobile ESS'],
            'priority' => 'medium',
            'platform' => 'Web Manager + Mobile approval',
            'features' => [
                'Manager dashboard for team attendance, leave, overtime, and birthdays',
                'Bulk approval inbox with risk indicators and delegation',
                'Team calendar with leave, shift, trip, and payroll cutoff visibility',
                'Performance notes and one-on-one log for each subordinate',
                'Headcount planning request and replacement requisition',
                'Team document request and employment letter approval',
                'Attendance exception review with map/photo evidence',
                'Manager budget visibility for overtime, claim, and training',
            ],
        ],
        'Recruitment & Onboarding' => [
            'label' => ['P2 Differentiator', 'Talent', 'Web Admin'],
            'priority' => 'medium',
            'platform' => 'Web Admin + Candidate portal',
            'features' => [
                'Manpower request and recruitment approval',
                'ATS pipeline: sourcing, screening, interview, offer, hired',
                'Candidate portal with document upload and status tracking',
                'Interview scheduling with calendar and feedback scorecard',
                'Offer letter generation and e-signature tracking',
                'Pre-employment medical, background check, and reference check tracker',
                'New hire data conversion into employee master',
                'Onboarding buddy, checklist, probation plan, and welcome journey',
            ],
        ],
        'Performance, OKR & Talent' => [
            'label' => ['P2 Differentiator', 'Talent', 'Mobile ESS'],
            'priority' => 'medium',
            'platform' => 'Web Admin + ESS/MSS',
            'features' => [
                'Goal/OKR setup, cascading, check-in, and scoring',
                'Performance review cycle with calibration and rating distribution',
                '360 feedback, peer review, and manager review forms',
                'Competency matrix by role, grade, and career path',
                'Individual development plan and learning recommendation',
                'Training catalog, enrollment, attendance, certificate, and evaluation',
                'Succession planning, talent pool, and readiness matrix',
                'Engagement survey analytics and action planning',
                'Career path explorer and internal mobility marketplace',
            ],
        ],
        'Analytics, AI & Decision Support' => [
            'label' => ['P2 Differentiator', 'Analytics AI', 'Web Admin'],
            'priority' => 'medium',
            'platform' => 'Web Admin + Executive dashboard',
            'features' => [
                'People analytics dashboard: headcount, turnover, absenteeism, payroll cost',
                'Payroll variance and anomaly explanation assistant',
                'Attrition risk signal with transparent factors and action recommendation',
                'Attendance anomaly detector for fraud, repeated lateness, and ghost punch',
                'Workforce planning simulator for hiring, overtime, and cost scenarios',
                'HR document assistant for policy Q&A and employee letter draft',
                'Natural language report builder for HR leaders',
                'Predictive leave and overtime capacity planning',
            ],
        ],
        'Integration & Open Platform' => [
            'label' => ['P1 Core HR', 'Integration', 'Feature Flag'],
            'priority' => 'medium',
            'platform' => 'API + Web Admin',
            'features' => [
                'SSO SAML/OIDC and company identity provider integration',
                'Biometric device connector and scheduled attendance sync',
                'WhatsApp gateway for approval, reminder, and payslip notification',
                'Email and calendar integration for interview, training, and leave',
                'Accounting/ERP integration for payroll journal and cost center',
                'Bank host-to-host or file export for salary disbursement',
                'Tax and BPJS export templates with validation',
                'Public API, webhook, API key, rate limit, and integration logs',
                'Data warehouse export and scheduled BI snapshot',
            ],
        ],
        'Compliance, Security & Operations' => [
            'label' => ['P0 Foundation', 'Compliance', 'Platform'],
            'priority' => 'high',
            'platform' => 'Web Admin + Security service',
            'features' => [
                'Two-factor authentication and privileged action confirmation',
                'Access review campaign and permission diff report',
                'Data retention, archive, and legal hold policy',
                'Consent management for personal data and document access',
                'Security event log, suspicious login, and device management',
                'Backup, restore drill, and tenant data export',
                'SLA monitoring, job queue dashboard, and integration health',
                'Localization: Bahasa Indonesia, English, timezone, currency, date format',
            ],
        ],
    ];

    $created = 0;
    $updated = 0;
    $order = 0;

    foreach ($modules as $module => $config) {
        foreach ($config['features'] as $feature) {
            $code = strtoupper(Str::slug($module, ''));
            $code = substr(preg_replace('/[^A-Z]/', '', $code), 0, 6);
            $title = '[' . $code . '-' . str_pad((string) ($order + 1), 3, '0', STR_PAD_LEFT) . '] ' . $feature;
            $isMobile = str_contains(strtolower($config['platform'] . ' ' . $module . ' ' . $feature), 'mobile') || str_contains(strtolower($feature), 'mobile');
            $flagKey = 'hris.' . Str::slug($module) . '.' . Str::slug($feature);
            $description = implode("\n", [
                "Module: {$module}",
                "Feature: {$feature}",
                "Feature Flag / Enable-Disable: {$flagKey}",
                "Platform: {$config['platform']}",
                "Priority: {$config['priority']}",
                "",
                "Business Goal:",
                "- Membuat HRIS Kurmigo lebih lengkap, configurable, dan scalable dibanding aplikasi HRIS umum seperti Talenta.",
                "- Modul harus bisa ON/OFF per company/tenant, per role, dan per package/subscription.",
                "",
                "Scope:",
                "- Web admin untuk konfigurasi, monitoring, approval, dan reporting.",
                ($isMobile ? "- Mobile ESS/MSS experience harus first-class: cepat, aman, push notification, dan nyaman untuk karyawan lapangan." : "- Dampak mobile harus didefinisikan walau fitur utama berada di web admin."),
                "- Permission, audit trail, import/export, notification, dan integration event harus dipikirkan sejak awal.",
                "",
                "Acceptance Criteria:",
                "- Admin dapat enable/disable fitur dari Module Settings tanpa deploy ulang.",
                "- Permission matrix tersedia untuk Admin, HR, Manager, Employee, Finance, dan Auditor.",
                "- Semua create/update/delete penting masuk audit trail.",
                "- Jika fitur memiliki request/approval, status dan SLA terlihat di web dan mobile.",
                "- Ada empty state, validation, error state, dan export/report bila relevan.",
                "- Test minimal mencakup happy path, permission denied, disabled module, dan validation error.",
            ]);

            $task = DB::table('tasks')->where('title', $title)->first();
            $payload = [
                'title' => $title,
                'description' => $description,
                'order' => $order,
                'priority' => $config['priority'],
                'due_date' => null,
                'start_date' => null,
                'duration' => 5,
                'progress' => 0,
                'task_list_id' => $listIds['Backlog'],
                'updated_at' => $now,
                'archived_at' => null,
                'cover_enabled' => true,
            ];

            if ($task) {
                DB::table('tasks')->where('id', $task->id)->update($payload);
                $taskId = $task->id;
                $updated++;
            } else {
                $payload['created_at'] = $now;
                $taskId = DB::table('tasks')->insertGetId($payload);
                $created++;
            }

            $labels = array_unique(array_merge($config['label'], [$isMobile ? 'Mobile ESS' : 'Web Admin']));
            foreach ($labels as $labelName) {
                if (isset($labelIds[$labelName])) {
                    DB::table('task_label')->updateOrInsert(
                        ['task_id' => $taskId, 'label_id' => $labelIds[$labelName]],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            }

            $checklistName = 'Definition of Ready / Done';
            $checklist = DB::table('checklists')->where('task_id', $taskId)->where('name', $checklistName)->first();
            if (!$checklist) {
                $checklistId = DB::table('checklists')->insertGetId([
                    'name' => $checklistName,
                    'task_id' => $taskId,
                    'order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $checklistId = $checklist->id;
                DB::table('checklists')->where('id', $checklistId)->update(['updated_at' => $now]);
            }

            $items = [
                'Define module setting toggle and default state',
                'Define role permissions and approval ownership',
                'Design web admin screen and empty/error states',
                'Design ESS/MSS mobile behavior where applicable',
                'Define data model, audit log, notification, and export needs',
                'Implement validation, disabled-module guard, and tests',
                'Document rollout, migration, and operational runbook',
            ];
            foreach ($items as $idx => $itemName) {
                DB::table('checklist_items')->updateOrInsert(
                    ['checklist_id' => $checklistId, 'name' => $itemName],
                    ['order' => $idx, 'is_checked' => false, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $order++;
        }
    }

    $summaryTitle = '[ROADMAP-000] HRIS Talenta+ Product Strategy and Release Slicing';
    $summaryDescription = "Tujuan: board ini menjadi backlog utama project /var/www/kurmigo-hris.\n\nRelease Slice:\n- R0 Foundation: tenant, module toggle, RBAC, audit, notification.\n- R1 HR Core + ESS basic: employee master, attendance, leave, profile, mobile login.\n- R2 Payroll + reimbursement + payslip.\n- R3 Talent, performance, recruitment, onboarding.\n- R4 AI analytics, integrations, compliance hardening.\n\nMode enable/disable wajib ada di setiap modul agar Kurmigo HRIS bisa dipakai bertahap per company, branch, role, dan package.\n\nMobile ESS wajib dianggap produk utama, bukan add-on.";
    $summaryTask = DB::table('tasks')->where('title', $summaryTitle)->first();
    $summaryPayload = [
        'title' => $summaryTitle,
        'description' => $summaryDescription,
        'order' => -1,
        'priority' => 'high',
        'task_list_id' => $listIds['Ready'],
        'duration' => 3,
        'progress' => 0,
        'updated_at' => $now,
        'archived_at' => null,
        'cover_enabled' => true,
    ];
    if ($summaryTask) {
        DB::table('tasks')->where('id', $summaryTask->id)->update($summaryPayload);
    } else {
        $summaryPayload['created_at'] = $now;
        $summaryId = DB::table('tasks')->insertGetId($summaryPayload);
        foreach (['P0 Foundation', 'Platform', 'Feature Flag'] as $labelName) {
            DB::table('task_label')->insert([
                'task_id' => $summaryId,
                'label_id' => $labelIds[$labelName],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    echo json_encode([
        'board_id' => $board->id,
        'board_name' => $boardName,
        'url' => 'https://31.97.106.123/kanban/boards/' . $board->id,
        'cards_created' => $created,
        'cards_updated' => $updated,
        'feature_cards_total' => $order,
        'lists' => array_keys($listIds),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
});
