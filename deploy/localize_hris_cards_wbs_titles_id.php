<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Str;

$now = now();
$boardId = 6;

$titles = [
    202 => '[PLATFO-001] Tenancy multi-perusahaan, cabang, dan badan hukum',
    203 => '[PLATFO-002] Marketplace modul dengan aktif/nonaktif per perusahaan',
    204 => '[PLATFO-003] Hak akses berbasis peran dengan template izin',
    205 => '[PLATFO-004] Builder workflow approval dengan routing kondisional',
    206 => '[PLATFO-005] Audit trail, log aktivitas, dan riwayat perubahan permanen',
    207 => '[PLATFO-006] Custom field dan builder form dinamis untuk objek HR',
    208 => '[PLATFO-007] Pusat notifikasi: email, WhatsApp, push, dan in-app',
    209 => '[PLATFO-008] Pencarian global karyawan, pengajuan, dokumen, dan payroll',
    210 => '[PLATFO-009] Mesin impor/ekspor massal dengan preview validasi',
    211 => '[PLATFO-010] Kontrol privasi data: masking, consent, dan retensi',
    212 => '[HRCORE-011] Profil karyawan 360: pekerjaan, keluarga, bank, pajak, BPJS',
    213 => '[HRCORE-012] Struktur organisasi, posisi, grade jabatan, dan cost center',
    214 => '[HRCORE-013] Siklus status kerja: probation, kontrak, tetap, alumni',
    215 => '[HRCORE-014] Kontrak PKWT/PKWTT, perpanjangan, dan reminder dokumen',
    216 => '[HRCORE-015] Arsip dokumen karyawan dengan metadata OCR dan versi',
    217 => '[HRCORE-016] Penugasan dan pengembalian aset laptop, seragam, ID card',
    218 => '[HRCORE-017] Workflow mutasi, promosi, demosi, dan transfer',
    219 => '[HRCORE-018] Riwayat disiplin, surat peringatan, dan coaching',
    220 => '[HRCORE-019] Paket tugas onboarding berdasarkan role, lokasi, departemen',
    221 => '[HRCORE-020] Offboarding, clearance, exit interview, dan final settlement',
    222 => '[ATTEND-021] Penyusunan jadwal shift rotasi, split, long shift, dan pola kerja',
    223 => '[ATTEND-022] Clock-in/out mobile dengan GPS, selfie, device binding, dan liveness',
    224 => '[ATTEND-023] Geofence kantor, WFH, client site, dan perjalanan dinas',
    225 => '[ATTEND-024] Impor absensi biometric/mesin dengan rekonsiliasi anomali',
    226 => '[ATTEND-025] Mesin kebijakan cuti: tahunan, sakit, khusus, unpaid, carry forward',
    227 => '[ATTEND-026] Pengajuan dan approval cuti dengan simulasi saldo',
    228 => '[ATTEND-027] Pengajuan lembur, approval, perhitungan, dan handoff payroll',
    229 => '[ATTEND-028] Workflow koreksi absensi dan missing punch',
    230 => '[ATTEND-029] Kalender libur per perusahaan, cabang, agama, dan wilayah',
    231 => '[ATTEND-030] Alert fatigue, keterlambatan, absensi, dan kepatuhan',
    232 => '[PAYROL-031] Builder komponen payroll: pendapatan, potongan, formula, prorata',
    233 => '[PAYROL-032] Struktur gaji, matriks grade, dan akses kompensasi rahasia',
    234 => '[PAYROL-033] Manajemen BPJS Kesehatan/Ketenagakerjaan dan profil pajak',
    235 => '[PAYROL-034] Simulasi PPh 21 bulanan, THR, bonus, pajak final, dan gross-up',
    236 => '[PAYROL-035] Wizard proses payroll dengan validasi awal dan deteksi anomali',
    237 => '[PAYROL-036] Publikasi, download, acknowledgement, dan resend slip gaji',
    238 => '[PAYROL-037] Workflow reimbursement dan klaim dengan lampiran struk dan OCR',
    239 => '[PAYROL-038] Pinjaman karyawan, potongan koperasi, cicilan, dan saldo',
    240 => '[PAYROL-039] Pembuatan file transfer bank dan approval pencairan gaji',
    241 => '[PAYROL-040] Penyesuaian retroaktif, backpay, dan laporan audit payroll',
    242 => '[ESSMOB-041] Home Mobile ESS dengan jadwal hari ini, aksi pending, shortcut slip gaji',
    243 => '[ESSMOB-042] Login biometric mobile, fallback PIN, dan trusted device',
    244 => '[ESSMOB-043] Pengajuan update profil dengan approval dan bukti dokumen',
    245 => '[ESSMOB-044] Pengajuan cuti, lembur, dinas, dan koreksi absensi',
    246 => '[ESSMOB-045] Download slip gaji, bukti potong pajak, kartu BPJS, kontrak, dan surat',
    247 => '[ESSMOB-046] Pengajuan reimbursement dengan foto struk dari kamera',
    248 => '[ESSMOB-047] Tiket helpdesk HR dengan SLA, kategori, lampiran, dan chat',
    249 => '[ESSMOB-048] Pengumuman perusahaan, acknowledgement kebijakan, dan broadcast',
    250 => '[ESSMOB-049] Survey karyawan, pulse check, eNPS, dan feedback anonim',
    251 => '[ESSMOB-050] Offline queue untuk clock-in/out dan sinkron draft pengajuan',
    252 => '[MANAGE-051] Dashboard manager untuk absensi tim, cuti, lembur, dan ulang tahun',
    253 => '[MANAGE-052] Inbox approval massal dengan indikator risiko dan delegasi',
    254 => '[MANAGE-053] Kalender tim untuk cuti, shift, dinas, dan cutoff payroll',
    255 => '[MANAGE-054] Catatan performa dan log one-on-one tiap bawahan',
    256 => '[MANAGE-055] Pengajuan rencana headcount dan requisition pengganti',
    257 => '[MANAGE-056] Pengajuan dokumen tim dan approval surat keterangan kerja',
    258 => '[MANAGE-057] Review pengecualian absensi dengan bukti peta/foto',
    259 => '[MANAGE-058] Visibilitas budget manager untuk lembur, klaim, dan training',
    260 => '[RECRUI-059] Pengajuan manpower dan approval rekrutmen',
    261 => '[RECRUI-060] Pipeline ATS: sourcing, screening, interview, offer, hired',
    262 => '[RECRUI-061] Portal kandidat dengan upload dokumen dan tracking status',
    263 => '[RECRUI-062] Jadwal interview dengan kalender dan scorecard feedback',
    264 => '[RECRUI-063] Pembuatan offer letter dan tracking tanda tangan elektronik',
    265 => '[RECRUI-064] Tracker medical check-up, background check, dan reference check',
    266 => '[RECRUI-065] Konversi data kandidat diterima ke employee master',
    267 => '[RECRUI-066] Onboarding buddy, checklist, probation plan, dan welcome journey',
    268 => '[PERFOR-067] Setup Goal/OKR, cascading, check-in, dan scoring',
    269 => '[PERFOR-068] Siklus performance review dengan kalibrasi dan distribusi rating',
    270 => '[PERFOR-069] Form 360 feedback, peer review, dan review manager',
    271 => '[PERFOR-070] Matriks kompetensi per role, grade, dan career path',
    272 => '[PERFOR-071] Individual development plan dan rekomendasi learning',
    273 => '[PERFOR-072] Katalog training, enrollment, attendance, sertifikat, evaluasi',
    274 => '[PERFOR-073] Succession planning, talent pool, dan readiness matrix',
    275 => '[PERFOR-074] Analytics engagement survey dan action planning',
    276 => '[PERFOR-075] Eksplorasi career path dan marketplace mobilitas internal',
    277 => '[ANALYT-076] Dashboard people analytics: headcount, turnover, absensi, biaya payroll',
    278 => '[ANALYT-077] Asisten penjelasan varians dan anomali payroll',
    279 => '[ANALYT-078] Sinyal risiko attrition dengan faktor transparan dan rekomendasi aksi',
    280 => '[ANALYT-079] Detektor anomali absensi untuk fraud, telat berulang, ghost punch',
    281 => '[ANALYT-080] Simulator workforce planning untuk hiring, lembur, dan skenario biaya',
    282 => '[ANALYT-081] Asisten dokumen HR untuk Q&A kebijakan dan draft surat karyawan',
    283 => '[ANALYT-082] Builder laporan natural language untuk leader HR',
    284 => '[ANALYT-083] Prediksi kapasitas cuti dan lembur',
    285 => '[INTEGR-084] Integrasi SSO SAML/OIDC dan identity provider perusahaan',
    286 => '[INTEGR-085] Connector perangkat biometric dan sinkron absensi terjadwal',
    287 => '[INTEGR-086] Gateway WhatsApp untuk approval, reminder, dan notifikasi slip gaji',
    288 => '[INTEGR-087] Integrasi email dan kalender untuk interview, training, dan cuti',
    289 => '[INTEGR-088] Integrasi akuntansi/ERP untuk jurnal payroll dan cost center',
    290 => '[INTEGR-089] Host-to-host bank atau ekspor file untuk pencairan gaji',
    291 => '[INTEGR-090] Template ekspor pajak dan BPJS dengan validasi',
    292 => '[INTEGR-091] Public API, webhook, API key, rate limit, dan log integrasi',
    293 => '[INTEGR-092] Ekspor data warehouse dan snapshot BI terjadwal',
    294 => '[COMPLI-093] Two-factor authentication dan konfirmasi aksi privileged',
    295 => '[COMPLI-094] Kampanye access review dan laporan selisih permission',
    296 => '[COMPLI-095] Kebijakan retensi data, arsip, dan legal hold',
    297 => '[COMPLI-096] Manajemen consent data pribadi dan akses dokumen',
    298 => '[COMPLI-097] Log event keamanan, login mencurigakan, dan manajemen device',
    299 => '[COMPLI-098] Backup, simulasi restore, dan ekspor data tenant',
    300 => '[COMPLI-099] Monitoring SLA, dashboard job queue, dan health integrasi',
    301 => '[COMPLI-100] Lokalisasi: Bahasa Indonesia, English, timezone, currency, format tanggal',
    302 => '[ROADMAP-000] Strategi produk HRIS enterprise dan slicing rilis',
    303 => '[TAX-001] Master profil PPh 21: NPWP/NIK, PTKP, status, KLU, KPP cabang',
    304 => '[TAX-002] Mesin hitung PPh 21 bulanan TER untuk pegawai tetap',
    305 => '[TAX-003] True-up PPh 21 tahunan dan rekalkulasi Desember',
    306 => '[TAX-004] Perlakuan pajak THR, bonus, penghasilan tidak teratur, dan pajak final',
    307 => '[TAX-005] Aturan PPh 21 pegawai tidak tetap, harian, freelancer, konsultan',
    308 => '[TAX-006] Mode gross-up, gross, net, pajak ditanggung perusahaan, tunjangan pajak',
    309 => '[TAX-007] Mapping komponen kena pajak dan dependency formula payroll',
    310 => '[TAX-008] Paket laporan pajak bulanan siap Coretax',
    311 => '[TAX-009] Generator template ekspor Coretax dan versioning schema',
    312 => '[TAX-010] Kesiapan connector API Coretax dengan credential vault dan retry log',
    313 => '[TAX-011] Tracker status submit Coretax dan dashboard rekonsiliasi',
    314 => '[TAX-012] Generate e-Bupot/bukti potong dan download via ESS',
    315 => '[TAX-013] Koreksi pajak, pembetulan pelaporan, dan penyesuaian payroll retroaktif',
    316 => '[TAX-014] Sandbox simulasi pajak sebelum finalisasi payroll',
    317 => '[TAX-015] Audit trail pajak payroll dan explainability perhitungan',
    318 => '[TAX-016] Kalender compliance pajak, reminder, dan eskalasi SLA',
    319 => '[TAX-017] Privasi data pajak, masking, izin ekspor, dan retensi legal',
    320 => '[TAX-018] Regression test PPh 21 Indonesia dan integrasi Coretax',
    321 => '[MOBILE-001] Shell Android full WebView untuk HRIS/ESS',
    322 => '[MOBILE-002] Shell iOS full WKWebView untuk HRIS/ESS',
    323 => '[MOBILE-003] Web ESS responsif yang dioptimalkan untuk shell WebView',
    324 => '[MOBILE-004] Kontrak native bridge dan JavaScript SDK untuk kapabilitas WebView',
    325 => '[MOBILE-005] Matriks QA Mobile WebView untuk Android dan iOS',
    326 => '[DAY-01] 10 Jul 2026 - Kickoff fondasi',
    327 => '[DAY-02] 11 Jul 2026 - Arsitektur dan tenancy',
    328 => '[DAY-03] 12 Jul 2026 - Marketplace modul',
    329 => '[DAY-04] 13 Jul 2026 - RBAC dan audit',
    330 => '[DAY-05] 14 Jul 2026 - Workflow builder',
    331 => '[DAY-06] 15 Jul 2026 - Employee master 1',
    332 => '[DAY-07] 16 Jul 2026 - Employee master 2',
    333 => '[DAY-08] 17 Jul 2026 - Onboarding/offboarding',
    334 => '[DAY-09] 18 Jul 2026 - Core attendance',
    335 => '[DAY-10] 19 Jul 2026 - Cuti dan lembur',
    336 => '[DAY-11] 20 Jul 2026 - Shell Mobile WebView',
    337 => '[DAY-12] 21 Jul 2026 - Flow karyawan ESS',
    338 => '[DAY-13] 22 Jul 2026 - Manager self service',
    339 => '[DAY-14] 23 Jul 2026 - Fondasi payroll',
    340 => '[DAY-15] 24 Jul 2026 - Perhitungan payroll',
    341 => '[DAY-16] 25 Jul 2026 - Fondasi PPh 21',
    342 => '[DAY-17] 26 Jul 2026 - PPh 21 lanjutan',
    343 => '[DAY-18] 27 Jul 2026 - Integrasi Coretax',
    344 => '[DAY-19] 28 Jul 2026 - Slip gaji dan dokumen pajak',
    345 => '[DAY-20] 29 Jul 2026 - Rekrutmen',
    346 => '[DAY-21] 30 Jul 2026 - Performance dan talent',
    347 => '[DAY-22] 31 Jul 2026 - Learning dan engagement',
    348 => '[DAY-23] 01 Aug 2026 - Analytics dan AI',
    349 => '[DAY-24] 02 Aug 2026 - Integrasi',
    350 => '[DAY-25] 03 Aug 2026 - Compliance dan security',
    351 => '[DAY-26] 04 Aug 2026 - Import/export dan laporan',
    352 => '[DAY-27] 05 Aug 2026 - QA mobile dan hardening',
    353 => '[DAY-28] 06 Aug 2026 - QA sistem',
    354 => '[DAY-29] 07 Aug 2026 - UAT dan perbaikan',
    355 => '[DAY-30] 08 Aug 2026 - Kesiapan production',
    356 => '[DAY-31] 09 Aug 2026 - Buffer final dan sign-off',
    357 => '[BENCH-001] Matriks benchmark pasar dan release gate',
    358 => '[READY-001] Panduan in-app, help center, release notes, dan paket training user',
    359 => '[READY-002] SLA customer support, triage issue, dan workflow eskalasi',
    360 => '[READY-003] Paket bukti sertifikasi keamanan dan kesiapan compliance',
    361 => '[ASSESS-001] Hub integrasi provider psikotest eksternal',
    362 => '[ASSESS-002] Workflow undangan psikotest kandidat dan sinkron hasil',
    363 => '[ASSESS-003] Mapping skor psikotest dan dashboard rekomendasi hiring',
    364 => '[ASSESS-004] Consent, privasi, retensi, dan audit trail psikotest',
];

$moduleNames = [
    'PLATFO' => 'Platform & Arsitektur',
    'HRCORE' => 'Core HR',
    'ATTEND' => 'Absensi & Waktu Kerja',
    'PAYROL' => 'Payroll',
    'ESSMOB' => 'ESS Mobile',
    'MANAGE' => 'Manager Self Service',
    'RECRUI' => 'Rekrutmen',
    'PERFOR' => 'Performance & Talent',
    'ANALYT' => 'Analytics & AI',
    'INTEGR' => 'Integrasi',
    'COMPLI' => 'Compliance & Security',
    'ROADMAP' => 'Roadmap',
    'TAX' => 'Payroll Tax & Coretax',
    'MOBILE' => 'Mobile WebView',
    'DAY' => 'Target Harian',
    'BENCH' => 'Benchmark Produk',
    'READY' => 'Kesiapan Produk',
    'ASSESS' => 'Integrasi Assessment',
];

function prefixCode(string $title): string
{
    return preg_match('/^\[([A-Z]+)-\d+\]/', $title, $m) ? $m[1] : 'OTHER';
}

function cleanTitle(string $title): string
{
    return trim(preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $title));
}

$updatedTasks = 0;
$updatedWbs = 0;

foreach ($titles as $taskId => $newTitle) {
    $task = DB::table('tasks')->where('id', $taskId)->first();
    if (!$task) {
        continue;
    }

    $prefix = prefixCode($newTitle);
    $module = $moduleNames[$prefix] ?? 'HRIS';
    $feature = cleanTitle($newTitle);

    $description = (string) $task->description;
    if ($description !== '') {
        $description = preg_replace('/^Module:/m', 'Modul:', $description);
        $description = preg_replace('/^Feature:/m', 'Fitur:', $description);
        $description = preg_replace('/^Scope:/m', 'Ruang Lingkup:', $description);
        $description = preg_replace('/^Acceptance Criteria:/m', 'Kriteria Penerimaan:', $description);
        $description = preg_replace('/Feature Flag \/ Enable-Disable:/', 'Feature Flag / Aktif-Nonaktif:', $description);
        $description = preg_replace('/Fitur: .*/', 'Fitur: ' . $feature, $description, 1);
    }

    DB::table('tasks')->where('id', $taskId)->update([
        'title' => $newTitle,
        'description' => $description,
        'updated_at' => $now,
    ]);
    $updatedTasks++;

    $target = "Modul: {$module}. Fitur: {$feature}. Target: pekerjaan ini menjadi bagian dari WBS, Card, dan Gantt yang saling tersinkron untuk delivery HRIS.";
    DB::table('wbs_items')->where('board_id', $boardId)->where('task_id', $taskId)->update([
        'title' => $newTitle,
        'target' => Str::limit($target, 3000),
        'updated_at' => $now,
    ]);
    $updatedWbs++;
}

$englishSignals = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) {
        $query->where('tasks.title', 'like', '%Multi-company%')
            ->orWhere('tasks.title', 'like', '%marketplace with enable%')
            ->orWhere('tasks.title', 'like', '%Role-based access%')
            ->orWhere('tasks.title', 'like', '%workflow builder with conditional%')
            ->orWhere('tasks.title', 'like', '%audit trail, activity log%');
    })
    ->count();

echo json_encode([
    'board_id' => $boardId,
    'updated_tasks' => $updatedTasks,
    'updated_wbs_items' => $updatedWbs,
    'english_signal_titles_remaining' => $englishSignals,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
