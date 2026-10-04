<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ESSMOB-046]%')
    ->firstOrFail();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UX/copy refinement ESS: istilah employee-facing diseragamkan agar tidak terlalu teknis dan tidak berlebihan memakai kata "Saya". Sidebar dan halaman ESS memakai Layanan Karyawan, Pusat Pengajuan, Update Profil, Klaim Reimbursement, Cuti & Izin, Slip Gaji, Dokumen, dan Bukti Potong PPh 21. Copy "Employee Profile" diganti menjadi "profil karyawan". Halaman Cuti & Izin juga tidak lagi 404 saat akun belum link employee; sekarang tetap tampil dengan warning dan form aktif setelah HR/admin menghubungkan akun. Full Laravel test 86 passed / 1062 assertions; smoke HTTPS 8092 untuk /ess, request-center, profile-updates, reimbursements, leave-requests, payslips, documents, dan tax-documents semua 200.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'comment' => 'ESS wording refinement recorded',
], JSON_PRETTY_PRINT) . PHP_EOL;
