<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MobileBridgeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        return view('mobile-bridge', [
            'active' => 'mobile-bridge',
            'sdkUrl' => asset('js/kurmigo-mobile-bridge.js'),
            'capabilities' => [
                [
                    'name' => 'device.info',
                    'label' => 'Info Device',
                    'request' => '{}',
                    'response' => '{"platform":"android","appVersion":"1.0.0","deviceId":"hashed-device-id"}',
                    'status' => 'Ready',
                ],
                [
                    'name' => 'location.current',
                    'label' => 'Lokasi GPS',
                    'request' => '{"accuracy":"high","timeout":15000}',
                    'response' => '{"lat":-6.2,"lng":106.8,"accuracy":12}',
                    'status' => 'Ready',
                ],
                [
                    'name' => 'camera.selfie',
                    'label' => 'Selfie Attendance',
                    'request' => '{"purpose":"attendance_clock_in","liveness":true}',
                    'response' => '{"fileToken":"tmp/selfie-token","livenessScore":0.98}',
                    'status' => 'Ready',
                ],
                [
                    'name' => 'file.download',
                    'label' => 'Download Dokumen ESS',
                    'request' => '{"url":"/ess/documents/tax/1/download","filename":"bukti-potong.pdf"}',
                    'response' => '{"opened":true}',
                    'status' => 'Ready',
                ],
                [
                    'name' => 'biometric.verify',
                    'label' => 'Verifikasi Biometrik',
                    'request' => '{"reason":"Login ESS","fallbackPin":true,"deviceUid":"hashed-device-id"}',
                    'response' => '{"verified":true,"method":"fingerprint","deviceTrusted":true}',
                    'status' => 'Ready',
                ],
                [
                    'name' => 'notification.badge',
                    'label' => 'Badge Notifikasi',
                    'request' => '{"count":5}',
                    'response' => '{"synced":true}',
                    'status' => 'Ready',
                ],
            ],
            'events' => [
                ['name' => 'kurmigo:bridge-ready', 'direction' => 'Web', 'payload' => '{"host":"android|ios|browser"}'],
                ['name' => 'kurmigo:bridge-response', 'direction' => 'Native ke Web', 'payload' => '{"id":"uuid","ok":true,"data":{}}'],
                ['name' => 'kurmigo:bridge-error', 'direction' => 'Native ke Web', 'payload' => '{"id":"uuid","ok":false,"error":"Permission denied"}'],
                ['name' => 'kurmigo:session-expired', 'direction' => 'Web ke Native', 'payload' => '{"redirect":"/login"}'],
            ],
        ]);
    }
}
