<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MobileQaMatrixController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        $scenarios = [
            ['area' => 'Login & Session', 'scenario' => 'Login ESS port 8092, session terpisah dari admin 8091', 'android' => 'Ready', 'ios' => 'Ready', 'priority' => 'P0'],
            ['area' => 'Navigation', 'scenario' => 'Sidebar drawer mobile, backdrop close, scroll menu tidak lompat', 'android' => 'Ready', 'ios' => 'Ready', 'priority' => 'P0'],
            ['area' => 'Request Center', 'scenario' => 'Cuti, lembur, dinas, koreksi absensi single-column dan readable', 'android' => 'Ready', 'ios' => 'Ready', 'priority' => 'P0'],
            ['area' => 'Bridge', 'scenario' => 'KurmigoNative / kurmigoNative bridge menerima postMessage', 'android' => 'Ready', 'ios' => 'Ready', 'priority' => 'P0'],
            ['area' => 'Device Permission', 'scenario' => 'Kamera, lokasi, biometric, notifikasi punya permission copy dan fallback', 'android' => 'Planned', 'ios' => 'Planned', 'priority' => 'P1'],
            ['area' => 'Offline', 'scenario' => 'Queue ESS tetap tersimpan saat koneksi turun dan sync saat online', 'android' => 'Planned', 'ios' => 'Planned', 'priority' => 'P1'],
            ['area' => 'Document', 'scenario' => 'Download slip gaji/bukti potong dan upload lampiran tidak keluar dari shell', 'android' => 'Planned', 'ios' => 'Planned', 'priority' => 'P1'],
            ['area' => 'Security', 'scenario' => 'Trusted device, timeout session, logout, dan pin/biometric fallback', 'android' => 'Planned', 'ios' => 'Planned', 'priority' => 'P1'],
        ];

        return view('settings.mobile-qa-matrix', [
            'active' => 'mobile-qa-matrix',
            'summary' => [
                'scenarios' => count($scenarios),
                'ready_android' => collect($scenarios)->where('android', 'Ready')->count(),
                'ready_ios' => collect($scenarios)->where('ios', 'Ready')->count(),
                'p0' => collect($scenarios)->where('priority', 'P0')->count(),
            ],
            'scenarios' => $scenarios,
            'artifacts' => [
                'Android shell' => base_path('mobile/android/mobile-shell-contract.json'),
                'iOS shell' => base_path('mobile/ios/mobile-shell-contract.json'),
                'QA matrix' => base_path('mobile/qa/mobile-webview-qa-matrix.json'),
            ],
        ]);
    }
}
