<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurmigo HRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#0A66C2', 600: '#2563EB', 700: '#1D4ED8' },
                        accent: { DEFAULT: '#10B981', 50: '#ECFDF5', 100: '#D1FAE5', 200: '#A7F3D0', 400: '#34D399' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); }
        .sidebar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-right: 1px solid rgba(255,255,255,.05);
            backdrop-filter: blur(10px);
            z-index: 40;
            contain: layout paint;
            transform: translateZ(0);
            backface-visibility: hidden;
            min-height: 0;
        }
        .sidebar-header { background: linear-gradient(135deg, rgba(10,102,194,.2), rgba(16,185,129,.2)); backdrop-filter: blur(10px); }
        .logo-container { background: linear-gradient(135deg, rgba(10,102,194,.1), rgba(16,185,129,.1)); backdrop-filter: blur(10px); border: 1px solid rgba(10,102,194,.2); }
        .user-avatar { background: linear-gradient(135deg, rgba(10,102,194,.2), rgba(16,185,129,.2)); backdrop-filter: blur(10px); }
        .gradient-text { background: linear-gradient(135deg, #0A66C2 0%, #10B981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar-nav { flex: 1 1 auto; min-height: 0; overflow-y: auto; overflow-x: hidden; scrollbar-gutter: stable; overscroll-behavior: contain; }
        .sidebar-footer { background: rgba(30,41,59,.96); }
        .nav-link { transition: all .3s ease; position: relative; }
        .nav-link:hover { background: linear-gradient(to right, rgba(10,102,194,.1), rgba(16,185,129,.1)); transform: translateX(4px); }
        .nav-link.active { background: linear-gradient(to right, rgba(10,102,194,.2), rgba(16,185,129,.2)); border-left: 3px solid; border-image: linear-gradient(to bottom, #0A66C2, #10B981) 1; }
        .topbar { background: rgba(255,255,255,.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,.05); z-index: 30; }
        .bottom-status { background: rgba(255,255,255,.96); border-top: 1px solid rgba(15,23,42,.08); color: #64748b; }
        .panel { background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
        .btn-primary { background: #2563eb; color: #fff; transition: all .2s ease; }
        .btn-primary:hover { background: #1d4ed8; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,.05); border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #0A66C2, #10B981); border-radius: 3px; }
        .mobile-menu-button { display: none; }
        .mobile-backdrop { display: none; }
        @media (max-width: 767px) {
            body { height: auto; min-height: 100vh; overflow: hidden; background: #f1f5f9; }
            .app-shell { height: 100dvh; }
            .sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(82vw, 300px);
                transform: translateX(-102%);
                transition: transform .2s ease;
                z-index: 60;
            }
            body.mobile-nav-open .sidebar { transform: translateX(0); }
            .mobile-backdrop {
                position: fixed;
                inset: 0;
                z-index: 50;
                background: rgba(15, 23, 42, .45);
                backdrop-filter: blur(2px);
            }
            body.mobile-nav-open .mobile-backdrop { display: block; }
            .mobile-menu-button { display: inline-flex; }
            .topbar {
                height: auto;
                min-height: 56px;
                padding: max(env(safe-area-inset-top), 8px) 12px 8px;
            }
            .topbar .desktop-admin-label,
            .topbar .desktop-notification,
            .topbar .desktop-user-name,
            .topbar .desktop-divider,
            .topbar .logout-text {
                display: none;
            }
            #appMain {
                padding: 14px 12px calc(72px + env(safe-area-inset-bottom));
                overscroll-behavior-y: contain;
            }
            .bottom-status {
                position: fixed;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 35;
                padding: 8px 12px calc(8px + env(safe-area-inset-bottom));
            }
            .bottom-status div { gap: 8px; }
            .bottom-status span:last-child { display: none; }
            .panel, .mobile-card { border-radius: 14px; }
            input, select, textarea { font-size: 16px !important; }
            table { min-width: 720px; }
        }
    </style>
</head>
<body class="h-screen overflow-hidden text-slate-800 {{ request()->getPort() === 8092 ? 'mobile-webview-shell' : '' }}">
    <div id="mobileBackdrop" class="mobile-backdrop"></div>
    <div class="app-shell flex h-screen">
        <aside class="sidebar flex w-64 flex-shrink-0 flex-col text-white">
            @php
                $currentUser = auth()->user();
                $roleName = $currentUser?->role?->name;
                $isEmployeePortal = $roleName === 'Employee' || request()->getPort() === 8092 || (int) request()->server('SERVER_PORT') === 8092 || str_ends_with((string) request()->getHttpHost(), ':8092');
                $appTitle = $isEmployeePortal ? 'ESS' : 'HRIS';
                $appSubtitle = $isEmployeePortal ? 'Layanan Karyawan' : 'Administration Panel';
                $topbarContext = $isEmployeePortal ? 'Layanan Karyawan' : 'Admin';
            @endphp
            <div class="sidebar-header flex h-20 items-center border-b border-gray-800 px-6">
                <div class="flex items-center space-x-3">
                    <div class="logo-container flex h-12 w-12 items-center justify-center rounded-xl">
                        <span class="text-lg font-bold gradient-text">HR</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight text-white sidebar-text">{{ $appTitle }}</h1>
                        <p class="mt-1 text-xs text-gray-400 sidebar-text">{{ $appSubtitle }}</p>
                    </div>
                </div>
            </div>
            @php
                $adminMenuGroups = [
                    'Dashboard' => [
                        ['key' => 'dashboard', 'label' => 'HRIS Dashboard', 'route' => 'hris.dashboard', 'icon' => 'fas fa-home'],
                    ],
                    'Core HR' => [
                        ['key' => 'employees', 'label' => 'Data Karyawan', 'route' => 'hris.employees', 'icon' => 'fas fa-users'],
                        ['key' => 'employee-status', 'label' => 'Status Kerja', 'route' => 'hris.employee-status', 'icon' => 'fas fa-id-badge'],
                        ['key' => 'employee-contracts', 'label' => 'Kontrak Kerja', 'route' => 'hris.employee-contracts', 'icon' => 'fas fa-file-contract'],
                        ['key' => 'employee-documents', 'label' => 'Dokumen Karyawan', 'route' => 'hris.employee-documents', 'icon' => 'fas fa-folder-open'],
                        ['key' => 'employee-profile-updates', 'label' => 'Approval Update Profil', 'route' => 'hris.employee-profile-updates', 'icon' => 'fas fa-user-pen'],
                        ['key' => 'employee-assets', 'label' => 'Aset Karyawan', 'route' => 'hris.employee-assets', 'icon' => 'fas fa-laptop'],
                        ['key' => 'employee-movements', 'label' => 'Mutasi Karyawan', 'route' => 'hris.employee-movements', 'icon' => 'fas fa-right-left'],
                        ['key' => 'employee-disciplinary-records', 'label' => 'Disiplin & Coaching', 'route' => 'hris.employee-disciplinary-records', 'icon' => 'fas fa-user-check'],
                        ['key' => 'employee-onboarding', 'label' => 'Onboarding', 'route' => 'hris.employee-onboarding', 'icon' => 'fas fa-list-check'],
                        ['key' => 'employee-offboarding', 'label' => 'Offboarding', 'route' => 'hris.employee-offboarding', 'icon' => 'fas fa-person-walking-arrow-right'],
                        ['key' => 'hr-helpdesk', 'label' => 'Helpdesk HR', 'route' => 'hris.helpdesk', 'icon' => 'fas fa-headset'],
                        ['key' => 'job-structure', 'label' => 'Master Jabatan', 'route' => 'settings.job-structure', 'icon' => 'fas fa-sitemap'],
                        ['key' => 'organization', 'label' => 'Organisasi', 'route' => 'settings.organization', 'icon' => 'fas fa-building'],
                    ],
                    'Operasional HR' => [
                        ['key' => 'attendance', 'label' => 'Absensi & Jadwal', 'route' => 'hris.attendance', 'icon' => 'fas fa-clock'],
                        ['key' => 'attendance-clock', 'label' => 'Clock Mobile', 'route' => 'attendance.clock-events', 'icon' => 'fas fa-mobile-screen-button'],
                        ['key' => 'attendance-geofences', 'label' => 'Geofence Attendance', 'route' => 'attendance.geofences', 'icon' => 'fas fa-location-dot'],
                        ['key' => 'attendance-imports', 'label' => 'Import Absensi', 'route' => 'attendance.imports', 'icon' => 'fas fa-file-import'],
                        ['key' => 'attendance-corrections', 'label' => 'Koreksi Absensi', 'route' => 'attendance.corrections', 'icon' => 'fas fa-pen-to-square'],
                        ['key' => 'attendance-alerts', 'label' => 'Alert Absensi', 'route' => 'attendance.alerts', 'icon' => 'fas fa-bell'],
                        ['key' => 'attendance-shifts', 'label' => 'Jadwal Shift', 'route' => 'attendance.shift-schedules', 'icon' => 'fas fa-business-time'],
                        ['key' => 'holidays', 'label' => 'Kalender Libur', 'route' => 'attendance.holidays', 'icon' => 'fas fa-calendar-days'],
                        ['key' => 'leave', 'label' => 'Cuti & Izin', 'route' => 'hris.leave', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'leave-policies', 'label' => 'Kebijakan Cuti', 'route' => 'leave.policies', 'icon' => 'fas fa-scale-balanced'],
                        ['key' => 'leave-requests', 'label' => 'Pengajuan Cuti', 'route' => 'leave.requests', 'icon' => 'fas fa-paper-plane'],
                        ['key' => 'overtime-requests', 'label' => 'Pengajuan Lembur', 'route' => 'attendance.overtime-requests', 'icon' => 'fas fa-stopwatch'],
                    ],
                    'External Workforce' => [
                        ['key' => 'external-workforce-vendors', 'label' => 'Vendor Outsourcing', 'route' => 'external-workforce.vendors', 'icon' => 'fas fa-people-carry-box'],
                        ['key' => 'external-workforce-workers', 'label' => 'Worker Non-Employee', 'route' => 'external-workforce.workers', 'icon' => 'fas fa-user-gear'],
                        ['key' => 'external-workforce-attendance', 'label' => 'Attendance & Mandays', 'route' => 'external-workforce.attendance', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'external-workforce-piecework', 'label' => 'Output Borongan', 'route' => 'external-workforce.piecework', 'icon' => 'fas fa-clipboard-check'],
                        ['key' => 'external-workforce-vendor-invoices', 'label' => 'Tagihan Vendor', 'route' => 'external-workforce.vendor-invoices', 'icon' => 'fas fa-file-invoice-dollar'],
                    ],
                    'Payroll & Tax' => [
                        ['key' => 'payroll', 'label' => 'Payroll & Pajak', 'route' => 'hris.payroll', 'icon' => 'fas fa-wallet'],
                        ['key' => 'payroll-components', 'label' => 'Komponen Payroll', 'route' => 'payroll.components', 'icon' => 'fas fa-sliders'],
                        ['key' => 'payroll-salary-structures', 'label' => 'Struktur Gaji', 'route' => 'payroll.salary-structures', 'icon' => 'fas fa-lock'],
                        ['key' => 'payroll-run-wizard', 'label' => 'Proses Payroll', 'route' => 'payroll.run-wizard', 'icon' => 'fas fa-list-check'],
                        ['key' => 'payroll-calculation', 'label' => 'Hitung Payroll', 'route' => 'payroll.calculation', 'icon' => 'fas fa-calculator'],
                        ['key' => 'payroll-disbursements', 'label' => 'Pencairan Gaji', 'route' => 'payroll.disbursements', 'icon' => 'fas fa-money-bill-transfer'],
                        ['key' => 'payroll-reimbursements', 'label' => 'Klaim Reimbursement', 'route' => 'payroll.reimbursements', 'icon' => 'fas fa-receipt'],
                        ['key' => 'payroll-loans', 'label' => 'Pinjaman & Potongan', 'route' => 'payroll.loans', 'icon' => 'fas fa-hand-holding-dollar'],
                        ['key' => 'payroll-adjustments', 'label' => 'Penyesuaian Payroll', 'route' => 'payroll.adjustments', 'icon' => 'fas fa-scale-balanced'],
                        ['key' => 'payroll-document-center', 'label' => 'Pusat Dokumen Payroll', 'route' => 'payroll.document-center', 'icon' => 'fas fa-folder-open'],
                        ['key' => 'payroll-payslips', 'label' => 'Slip Gaji', 'route' => 'payroll.payslips', 'icon' => 'fas fa-file-invoice'],
                        ['key' => 'tax-foundation', 'label' => 'Fondasi PPh 21', 'route' => 'payroll.tax-foundation', 'icon' => 'fas fa-shield-halved'],
                        ['key' => 'tax-advanced-control', 'label' => 'Kontrol PPh 21', 'route' => 'payroll.tax-advanced-control', 'icon' => 'fas fa-chart-line'],
                        ['key' => 'tax-profiles', 'label' => 'Master PPh 21', 'route' => 'payroll.tax-profiles', 'icon' => 'fas fa-file-invoice-dollar'],
                        ['key' => 'bpjs-profiles', 'label' => 'BPJS Payroll', 'route' => 'payroll.bpjs-profiles', 'icon' => 'fas fa-notes-medical'],
                        ['key' => 'tax-monthly', 'label' => 'Hitung PPh 21 Bulanan', 'route' => 'payroll.tax-monthly', 'icon' => 'fas fa-percent'],
                        ['key' => 'tax-report-packages', 'label' => 'Laporan Pajak Bulanan', 'route' => 'payroll.tax-report-packages', 'icon' => 'fas fa-file-export'],
                        ['key' => 'coretax-integration', 'label' => 'Integrasi Coretax', 'route' => 'payroll.coretax-integration', 'icon' => 'fas fa-network-wired'],
                        ['key' => 'coretax-connector', 'label' => 'Coretax Connector', 'route' => 'payroll.coretax-connector', 'icon' => 'fas fa-plug'],
                        ['key' => 'integration-export-templates', 'label' => 'Export Pajak & BPJS', 'route' => 'integrations.export-templates', 'icon' => 'fas fa-file-csv'],
                        ['key' => 'tax-annual', 'label' => 'True-up PPh 21 Tahunan', 'route' => 'payroll.tax-annual', 'icon' => 'fas fa-scale-balanced'],
                        ['key' => 'tax-irregular', 'label' => 'Penghasilan Tidak Teratur', 'route' => 'payroll.tax-irregular', 'icon' => 'fas fa-gift'],
                        ['key' => 'tax-non-permanent', 'label' => 'PPh 21 Non Permanen', 'route' => 'payroll.tax-non-permanent', 'icon' => 'fas fa-user-clock'],
                        ['key' => 'tax-withholding-documents', 'label' => 'Bukti Potong', 'route' => 'payroll.tax-withholding-documents', 'icon' => 'fas fa-file-signature'],
                        ['key' => 'tax-corrections', 'label' => 'Koreksi Pajak', 'route' => 'payroll.tax-corrections', 'icon' => 'fas fa-rotate'],
                        ['key' => 'tax-simulations', 'label' => 'Sandbox Pajak', 'route' => 'payroll.tax-simulations', 'icon' => 'fas fa-flask'],
                        ['key' => 'tax-compliance-calendar', 'label' => 'Kalender Compliance', 'route' => 'payroll.tax-compliance-calendar', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'tax-regression-cases', 'label' => 'Regression Pajak', 'route' => 'payroll.tax-regression-cases', 'icon' => 'fas fa-vial-circle-check'],
                        ['key' => 'tax-audits', 'label' => 'Riwayat PPh 21', 'route' => 'payroll.tax-audits', 'icon' => 'fas fa-calculator'],
                        ['key' => 'tax-privacy', 'label' => 'Privasi Pajak', 'route' => 'payroll.tax-privacy', 'icon' => 'fas fa-user-lock'],
                    ],
                    'Integrasi' => [
                        ['key' => 'biometric-connectors', 'label' => 'Biometric Connector', 'route' => 'integrations.biometric-connectors', 'icon' => 'fas fa-fingerprint'],
                        ['key' => 'email-calendar', 'label' => 'Email & Kalender', 'route' => 'integrations.email-calendar', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'whatsapp-gateway', 'label' => 'WhatsApp Gateway', 'route' => 'integrations.whatsapp-gateway', 'icon' => 'fab fa-whatsapp'],
                    ],
                    'Layanan Karyawan' => [
                        ['key' => 'ess', 'label' => 'ESS Mobile', 'route' => 'hris.ess', 'icon' => 'fas fa-mobile-screen-button'],
                        ['key' => 'mobile-bridge', 'label' => 'Mobile Bridge', 'route' => 'settings.mobile-bridge', 'icon' => 'fas fa-code'],
                        ['key' => 'mobile-qa-matrix', 'label' => 'QA Mobile WebView', 'route' => 'settings.mobile-qa-matrix', 'icon' => 'fas fa-clipboard-check'],
                        ['key' => 'ess-security', 'label' => 'ESS Security', 'route' => 'settings.ess-security', 'icon' => 'fas fa-shield-halved'],
                        ['key' => 'ess-offline-queue', 'label' => 'Offline Queue', 'route' => 'ess.offline-queue', 'icon' => 'fas fa-cloud-arrow-up'],
                        ['key' => 'ess-request-center', 'label' => 'Pusat Pengajuan', 'route' => 'ess.request-center', 'icon' => 'fas fa-paper-plane'],
                        ['key' => 'ess-profile-updates', 'label' => 'Update Profil', 'route' => 'ess.profile-updates', 'icon' => 'fas fa-user-pen'],
                        ['key' => 'ess-reimbursements', 'label' => 'Klaim Reimbursement', 'route' => 'ess.reimbursements', 'icon' => 'fas fa-receipt'],
                        ['key' => 'ess-loans', 'label' => 'Pinjaman Saya', 'route' => 'ess.loans', 'icon' => 'fas fa-hand-holding-dollar'],
                        ['key' => 'ess-payroll-adjustments', 'label' => 'Adjustment Payroll', 'route' => 'ess.payroll-adjustments', 'icon' => 'fas fa-scale-balanced'],
                        ['key' => 'ess-helpdesk', 'label' => 'Helpdesk HR', 'route' => 'ess.helpdesk', 'icon' => 'fas fa-headset'],
                        ['key' => 'ess-leave-requests', 'label' => 'Cuti & Izin', 'route' => 'ess.leave-requests', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'ess-payslips', 'label' => 'Slip Gaji', 'route' => 'ess.payslips', 'icon' => 'fas fa-file-invoice'],
                        ['key' => 'ess-documents', 'label' => 'Dokumen', 'route' => 'ess.documents', 'icon' => 'fas fa-folder-open'],
                        ['key' => 'ess-tax-simulations', 'label' => 'Simulasi Pajak', 'route' => 'ess.tax-simulations', 'icon' => 'fas fa-flask'],
                        ['key' => 'ess-tax-documents', 'label' => 'Bukti Potong', 'route' => 'ess.tax-documents', 'icon' => 'fas fa-file-download'],
                    ],
                    'Manager Self Service' => [
                        ['key' => 'manager-self-service', 'label' => 'Beranda MSS', 'route' => 'manager.self-service', 'icon' => 'fas fa-layer-group'],
                        ['key' => 'manager-dashboard', 'label' => 'Dashboard Manager', 'route' => 'manager.dashboard', 'icon' => 'fas fa-chart-line'],
                        ['key' => 'manager-approval-inbox', 'label' => 'Inbox Approval', 'route' => 'manager.approval-inbox', 'icon' => 'fas fa-inbox'],
                        ['key' => 'manager-performance-journal', 'label' => 'Jurnal Performa', 'route' => 'manager.performance-journal', 'icon' => 'fas fa-clipboard-user'],
                        ['key' => 'manager-headcount-requisitions', 'label' => 'Headcount Tim', 'route' => 'manager.headcount-requisitions', 'icon' => 'fas fa-user-plus'],
                        ['key' => 'manager-team-documents', 'label' => 'Dokumen Tim', 'route' => 'manager.team-documents', 'icon' => 'fas fa-folder-open'],
                        ['key' => 'manager-team-calendar', 'label' => 'Kalender Tim', 'route' => 'manager.team-calendar', 'icon' => 'fas fa-calendar-days'],
                        ['key' => 'manager-budget', 'label' => 'Budget Tim', 'route' => 'manager.budget', 'icon' => 'fas fa-chart-pie'],
                        ['key' => 'manager-attendance-exceptions', 'label' => 'Review Pengecualian Absensi', 'route' => 'manager.attendance-exceptions', 'icon' => 'fas fa-map-location-dot'],
                        ['key' => 'manager-leave-review', 'label' => 'Review Cuti Tim', 'route' => 'leave.requests', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'manager-overtime-review', 'label' => 'Review Lembur Tim', 'route' => 'attendance.overtime-requests', 'icon' => 'fas fa-stopwatch'],
                    ],
                    'Talent Acquisition' => [
                        ['key' => 'recruitment-command-center', 'label' => 'Pusat Rekrutmen', 'route' => 'recruitment.command-center', 'icon' => 'fas fa-chart-pie'],
                        ['key' => 'recruitment-manpower', 'label' => 'Pengajuan Manpower', 'route' => 'recruitment.manpower-requests', 'icon' => 'fas fa-user-plus'],
                        ['key' => 'recruitment-candidates', 'label' => 'ATS Pipeline', 'route' => 'recruitment.candidates', 'icon' => 'fas fa-filter-circle-dollar'],
                        ['key' => 'recruitment-candidate-portal', 'label' => 'Portal Kandidat', 'route' => 'recruitment.candidate-portal', 'icon' => 'fas fa-id-card-clip'],
                        ['key' => 'recruitment-interviews', 'label' => 'Jadwal Interview', 'route' => 'recruitment.interviews', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'recruitment-offer-letters', 'label' => 'Offer Letter', 'route' => 'recruitment.offer-letters', 'icon' => 'fas fa-file-signature'],
                        ['key' => 'recruitment-candidate-checks', 'label' => 'Pengecekan Kandidat', 'route' => 'recruitment.candidate-checks', 'icon' => 'fas fa-user-check'],
                        ['key' => 'recruitment-employee-conversions', 'label' => 'Konversi Employee', 'route' => 'recruitment.employee-conversions', 'icon' => 'fas fa-user-tie'],
                        ['key' => 'recruitment-onboarding-journeys', 'label' => 'Welcome Journey', 'route' => 'recruitment.onboarding-journeys', 'icon' => 'fas fa-person-walking-arrow-right'],
                    ],
                    'Performance & Talent' => [
                        ['key' => 'performance-goal-okr', 'label' => 'Goal & OKR', 'route' => 'performance.goal-okr', 'icon' => 'fas fa-bullseye'],
                        ['key' => 'performance-review-360', 'label' => '360 Feedback', 'route' => 'performance.review-360', 'icon' => 'fas fa-people-arrows'],
                        ['key' => 'performance-calibration', 'label' => 'Kalibrasi Review', 'route' => 'performance.calibration', 'icon' => 'fas fa-scale-balanced'],
                        ['key' => 'performance-competency-matrix', 'label' => 'Matriks Kompetensi', 'route' => 'performance.competency-matrix', 'icon' => 'fas fa-table-cells-large'],
                        ['key' => 'training-learning', 'label' => 'Learning & Training', 'route' => 'performance.training-learning', 'icon' => 'fas fa-graduation-cap'],
                    ],
                    'Analytics & AI' => [
                        ['key' => 'attendance-anomalies', 'label' => 'Anomali Absensi', 'route' => 'analytics.attendance-anomalies', 'icon' => 'fas fa-magnifying-glass-chart'],
                        ['key' => 'workforce-planning', 'label' => 'Workforce Planning', 'route' => 'analytics.workforce-planning', 'icon' => 'fas fa-people-arrows'],
                        ['key' => 'leave-overtime-capacity', 'label' => 'Prediksi Cuti & Lembur', 'route' => 'analytics.leave-overtime-capacity', 'icon' => 'fas fa-chart-line'],
                    ],
                    'System Administration' => [
                        ['key' => 'roles', 'label' => 'Role & Permission', 'route' => 'settings.roles', 'icon' => 'fas fa-user-shield'],
                        ['key' => 'users', 'label' => 'User Access', 'route' => 'settings.users', 'icon' => 'fas fa-users-cog'],
                        ['key' => 'access-reviews', 'label' => 'Access Review', 'route' => 'settings.access-reviews', 'icon' => 'fas fa-clipboard-check'],
                        ['key' => 'approval-workflows', 'label' => 'Workflow Approval', 'route' => 'settings.approval-workflows', 'icon' => 'fas fa-route'],
                        ['key' => 'notification-center', 'label' => 'Pusat Notifikasi', 'route' => 'settings.notification-center', 'icon' => 'fas fa-bell'],
                        ['key' => 'modules', 'label' => 'Module Switcher', 'route' => 'settings.modules', 'icon' => 'fas fa-toggle-on'],
                        ['key' => 'audit-trail', 'label' => 'Audit Trail', 'route' => 'settings.audit-trail', 'icon' => 'fas fa-clipboard-list'],
                    ],
                    'Project Delivery' => [
                        ['key' => 'product-roadmap', 'label' => 'Product Roadmap', 'route' => 'settings.product-roadmap', 'icon' => 'fas fa-road'],
                        ['key' => 'delivery-progress', 'label' => 'Progress Delivery', 'route' => 'settings.delivery-progress', 'icon' => 'fas fa-chart-line'],
                    ],
                ];

                $employeeMenuGroups = [
                    'Layanan Karyawan' => [
                        ['key' => 'ess', 'label' => 'Beranda', 'route' => 'hris.ess', 'icon' => 'fas fa-mobile-screen-button'],
                        ['key' => 'ess-flow-center', 'label' => 'Alur ESS Saya', 'route' => 'ess.flow-center', 'icon' => 'fas fa-route'],
                        ['key' => 'ess-request-center', 'label' => 'Pusat Pengajuan', 'route' => 'ess.request-center', 'icon' => 'fas fa-paper-plane'],
                        ['key' => 'ess-profile-updates', 'label' => 'Update Profil', 'route' => 'ess.profile-updates', 'icon' => 'fas fa-user-pen'],
                        ['key' => 'ess-reimbursements', 'label' => 'Klaim Reimbursement', 'route' => 'ess.reimbursements', 'icon' => 'fas fa-receipt'],
                        ['key' => 'ess-helpdesk', 'label' => 'Helpdesk HR', 'route' => 'ess.helpdesk', 'icon' => 'fas fa-headset'],
                        ['key' => 'ess-leave-requests', 'label' => 'Cuti & Izin', 'route' => 'ess.leave-requests', 'icon' => 'fas fa-calendar-check'],
                        ['key' => 'ess-payslips', 'label' => 'Slip Gaji', 'route' => 'ess.payslips', 'icon' => 'fas fa-file-invoice'],
                        ['key' => 'ess-documents', 'label' => 'Dokumen', 'route' => 'ess.documents', 'icon' => 'fas fa-folder-open'],
                        ['key' => 'ess-tax-documents', 'label' => 'Bukti Potong', 'route' => 'ess.tax-documents', 'icon' => 'fas fa-file-download'],
                    ],
                ];

                $menuGroups = $isEmployeePortal ? $employeeMenuGroups : $adminMenuGroups;
            @endphp
            <nav id="sidebarNav" class="sidebar-nav flex-1 space-y-1 px-4 py-6">
                @foreach($menuGroups as $groupLabel => $items)
                    <div class="{{ $loop->first ? '' : 'pt-5' }}">
                        <div class="px-4 pb-2">
                            <div class="sidebar-text text-xs font-medium uppercase tracking-wider text-gray-400">{{ $groupLabel }}</div>
                        </div>
                        <div class="space-y-1">
                            @foreach($items as $menu)
                                @php
                                    $isExternalEssRequestCenter = $menu['key'] === 'ess-request-center' && ! $isEmployeePortal;
                                    $menuHref = $isExternalEssRequestCenter
                                        ? 'https://31.97.106.123:8092/ess/request-center-entry'
                                        : route($menu['route']);
                                @endphp
                                <a href="{{ $menuHref }}" @if($isExternalEssRequestCenter) target="_blank" rel="noopener noreferrer" @endif class="nav-link {{ ($active ?? '') === $menu['key'] ? 'active' : '' }} flex items-center gap-3 rounded-lg px-4 py-3">
                                    <i class="{{ $menu['icon'] }} w-5 text-center text-gray-300"></i>
                                    <span class="sidebar-text">{{ $menu['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <div class="border-t border-gray-800 p-4">
                <div class="mb-3 flex items-center space-x-3">
                    <div class="user-avatar flex h-8 w-8 items-center justify-center rounded-full">
                        <span class="text-sm font-medium gradient-text">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <div class="sidebar-text min-w-0">
                        <div class="truncate text-xs font-medium text-white">{{ auth()->user()->name ?? '-' }}</div>
                        <div class="text-[11px] uppercase text-gray-400">{{ auth()->user()->role->name ?? 'No Role' }}</div>
                    </div>
                </div>
                <div class="sidebar-text mb-2 text-xs text-gray-400">
                    <i class="fas fa-shield-alt mr-1"></i> Session: {{ substr(session()->getId(), 0, 8) }}...
                </div>
                <div class="sidebar-text text-xs text-gray-400">Copyright 2026 Kurmigo HRIS</div>
            </div>
        </aside>

        <main class="flex min-w-0 flex-1 flex-col">
            <header class="topbar flex h-16 flex-shrink-0 items-center justify-between px-6">
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <button id="mobileMenuButton" type="button" class="mobile-menu-button h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700" title="Buka menu">
                        <i class="fas fa-bars"></i>
                    </button>
                        <span class="font-medium">{{ $topbarContext }}</span>
                </div>
                <div class="flex h-full items-center">
                    <button class="desktop-notification px-5 text-gray-500 hover:text-gray-900" title="Notifikasi">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="desktop-divider h-11 border-l border-gray-200"></div>
                    <div class="flex items-center gap-3 px-4">
                        <span class="desktop-user-name text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'User' }}</span>
                        <div class="user-avatar flex h-10 w-10 items-center justify-center rounded-full">
                            <span class="font-semibold gradient-text">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt"></i> <span class="logout-text">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>
            <section id="appMain" class="page-transition flex-1 overflow-y-auto p-6">
                @yield('content')
            </section>
            <footer class="bottom-status flex-shrink-0 border-t border-gray-200 bg-white px-6 py-3 text-xs text-gray-500">
                <div class="flex items-center justify-between">
                    <span><i class="fas fa-shield-alt mr-1"></i> Secured by Kurmigo HRIS</span>
                    <span><i class="fas fa-server mr-1"></i> Server: development <i class="far fa-clock ml-4 mr-1"></i> {{ now()->timezone('Asia/Jakarta')->format('H:i.s') }}</span>
                </div>
            </footer>
        </main>
    </div>
    <script>
        (() => {
            const nav = document.getElementById('sidebarNav');
            const body = document.body;
            const menuButton = document.getElementById('mobileMenuButton');
            const backdrop = document.getElementById('mobileBackdrop');
            const storageKey = 'kurmigo_hris_sidebar_scroll_top';

            if (!nav) {
                return;
            }

            const restoreScroll = () => {
                const saved = Number(window.sessionStorage.getItem(storageKey) || 0);
                if (Number.isFinite(saved) && saved > 0) {
                    nav.scrollTop = saved;
                }
            };

            restoreScroll();
            window.addEventListener('pageshow', restoreScroll);

            nav.addEventListener('scroll', () => {
                window.sessionStorage.setItem(storageKey, String(nav.scrollTop));
            }, { passive: true });

            nav.querySelectorAll('a.nav-link').forEach((link) => {
                link.addEventListener('click', () => {
                    window.sessionStorage.setItem(storageKey, String(nav.scrollTop));
                    body.classList.remove('mobile-nav-open');
                });
            });

            menuButton?.addEventListener('click', () => {
                body.classList.toggle('mobile-nav-open');
            });

            backdrop?.addEventListener('click', () => {
                body.classList.remove('mobile-nav-open');
            });
        })();
    </script>
</body>
</html>
