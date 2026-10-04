<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Str;

$boardId = 6;
$now = now();

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
    'ROADMAP' => 'Roadmap Produk',
    'TAX' => 'Payroll Tax & Coretax',
    'MOBILE' => 'Mobile WebView',
    'DAY' => 'Target Harian',
    'BENCH' => 'Benchmark Produk',
    'READY' => 'Kesiapan Produk',
    'ASSESS' => 'Integrasi Assessment',
];

$platformByPrefix = [
    'ESSMOB' => 'Mobile ESS responsive web + Android/iOS full WebView',
    'MOBILE' => 'Android/iOS full WebView shell + web responsive',
    'RECRUI' => 'Web Admin + Candidate Portal + Mobile WebView',
    'ASSESS' => 'Web Admin + Candidate Mobile WebView + Provider API eksternal',
    'TAX' => 'Web Admin + Payroll Engine + ESS dokumen pajak',
    'PAYROL' => 'Web Admin + Payroll Engine + ESS',
    'INTEGR' => 'Web Admin + API/Webhook + service connector',
    'ANALYT' => 'Web Admin + dashboard analytics',
    'MANAGE' => 'Web Admin + Manager Self Service',
    'DAY' => 'WBS + Card + Gantt',
];

function prefixCode(string $title): string
{
    return preg_match('/^\[([A-Z]+)-\d+\]/', $title, $m) ? $m[1] : 'OTHER';
}

function cleanTitle(string $title): string
{
    return trim(preg_replace('/^\[[A-Z]+-\d+\]\s*/', '', $title));
}

function flagKey(string $title, string $prefix): string
{
    $base = Str::slug(cleanTitle($title));
    $module = strtolower($prefix);
    return "hris.{$module}.{$base}";
}

function scopeFor(string $prefix, string $feature): string
{
    return match ($prefix) {
        'ROADMAP' => 'Mengatur arah produk, prioritas rilis, slicing pekerjaan, dan release gate agar seluruh delivery HRIS terkendali.',
        'DAY' => 'Menjadi milestone harian untuk memantau target kerja, dependencies, dan progres di WBS serta Gantt.',
        'PLATFO' => 'Membangun fondasi platform agar HRIS modular, aman, multi-perusahaan, dan mudah dikembangkan.',
        'HRCORE' => 'Mengelola data inti karyawan dan lifecycle kepegawaian sebagai sumber data utama untuk modul lain.',
        'ATTEND' => 'Mengelola absensi, shift, cuti, lembur, koreksi, dan kontrol kepatuhan waktu kerja.',
        'PAYROL' => 'Mengelola komponen gaji, perhitungan payroll, slip gaji, klaim, pinjaman, dan pencairan.',
        'TAX' => 'Mengelola PPh 21, TER, e-Bupot, laporan pajak, dan kesiapan integrasi Coretax.',
        'ESSMOB' => 'Memberikan pengalaman self service karyawan yang nyaman di mobile web dan WebView app.',
        'MOBILE' => 'Menyiapkan shell WebView Android/iOS dan kontrak bridge agar aplikasi mobile tetap satu basis dengan web ESS.',
        'MANAGE' => 'Memberikan alat kerja manager untuk melihat tim, approval, kalender, pengecualian, dan budget.',
        'RECRUI' => 'Mengelola kebutuhan tenaga kerja, pipeline kandidat, interview, offer, dan onboarding karyawan baru.',
        'ASSESS' => 'Menghubungkan HRIS dengan provider psikotest eksternal untuk undangan, sinkron hasil, mapping skor, consent, dan audit.',
        'PERFOR' => 'Mengelola KPI/OKR, review, kompetensi, learning, succession, engagement, dan career path.',
        'ANALYT' => 'Menyediakan analytics, insight, anomali, prediksi, dan asisten laporan untuk keputusan HR.',
        'INTEGR' => 'Menghubungkan HRIS dengan sistem eksternal melalui API, webhook, export, connector, dan monitoring integrasi.',
        'COMPLI' => 'Mengelola keamanan, audit, privasi, backup, SLA, compliance, dan lokalisasi operasional.',
        'BENCH' => 'Menjadi kontrol pembanding pasar agar cakupan fitur dan kualitas rilis memenuhi standar produk HRIS modern.',
        'READY' => 'Menyiapkan aspek readiness produk seperti panduan, support, SLA, training, release notes, dan bukti compliance.',
        default => "Menyelesaikan fitur {$feature} sebagai bagian dari delivery HRIS.",
    };
}

$tasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->select('tasks.id', 'tasks.title')
    ->orderBy('tasks.id')
    ->get();

$updated = 0;

foreach ($tasks as $task) {
    $prefix = prefixCode($task->title);
    $feature = cleanTitle($task->title);
    $module = $moduleNames[$prefix] ?? 'HRIS';
    $platform = $platformByPrefix[$prefix] ?? 'Web Admin + API + ESS responsive web';
    $flag = flagKey($task->title, $prefix);
    $scope = scopeFor($prefix, $feature);

    $description = implode("\n", [
        "Modul: {$module}",
        "Fitur: {$feature}",
        "Feature Flag / Aktif-Nonaktif: {$flag}",
        "Platform: {$platform}",
        '',
        'Ruang Lingkup:',
        $scope,
        '',
        'Kriteria Penerimaan:',
        '- Fitur mengikuti status module enable/disable per tenant/perusahaan.',
        '- Hak akses, validasi, empty state, error state, dan audit trail tersedia sesuai kebutuhan fitur.',
        '- Data tanggal, status, dan progres tersinkron antara Card, WBS, dan Gantt.',
        '- Alur utama diuji di Web Admin dan, bila relevan, di Mobile ESS/WebView.',
        '- Checklist Kanban wajib diperbarui saat implementasi, testing, review, dan saat ada blocker.',
    ]);

    DB::table('tasks')->where('id', $task->id)->update([
        'description' => $description,
        'updated_at' => $now,
    ]);
    DB::table('wbs_items')->where('board_id', $boardId)->where('task_id', $task->id)->update([
        'target' => "Modul: {$module}. Fitur: {$feature}. Target: {$scope}",
        'updated_at' => $now,
    ]);
    $updated++;
}

$englishSignals = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) {
        $query->where('tasks.description', 'like', '%Acceptance Criteria:%')
            ->orWhere('tasks.description', 'like', '%Scope:%')
            ->orWhere('tasks.description', 'like', '%Feature:%')
            ->orWhere('tasks.description', 'like', '%Module:%')
            ->orWhere('tasks.description', 'like', '%Architecture Decision:%');
    })
    ->count();

echo json_encode([
    'board_id' => $boardId,
    'updated_descriptions' => $updated,
    'english_structure_signals_remaining' => $englishSignals,
    'sample' => DB::table('tasks')->whereIn('id', [202, 203, 303, 361])->select('id', 'title', 'description')->get(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
