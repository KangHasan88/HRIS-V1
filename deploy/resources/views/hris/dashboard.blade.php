<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurmigo HRIS & ESS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --k-white: #ffffff;
            --k-white-off: #f6f8fb;
            --k-gray-50: #f8fafc;
            --k-gray-100: #eef3f8;
            --k-gray-200: #d8e2ee;
            --k-gray-300: #c6d3e1;
            --k-gray-400: #8fa0b6;
            --k-gray-500: #64748b;
            --k-gray-600: #475569;
            --k-gray-700: #334155;
            --k-gray-800: #1e293b;
            --k-gray-900: #0f172a;
            --k-blue: #123b7a;
            --k-blue-dark: #0b2759;
            --k-blue-darker: #071a3d;
            --k-blue-light: #edf4ff;
            --k-orange: #f97316;
            --k-orange-dark: #c2410c;
            --k-orange-light: #fff1df;
            --k-success: #16a34a;
            --k-success-light: #e6f7e6;
            --k-red: #dc2626;
            --k-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
            --k-shadow-md: 0 10px 28px rgba(15, 23, 42, 0.06);
            --k-shadow-lg: 0 18px 44px rgba(15, 23, 42, 0.10);
            --k-shadow-blue: 0 8px 18px rgba(18, 59, 122, 0.12);
            --k-font-xs: 0.72rem;
            --k-font-sm: 0.78rem;
            --k-font-md: 0.82rem;
            --k-font-lg: 1.02rem;
            --k-font-page-title: 1.12rem;
            --k-font-stat: 1.3rem;
        }
        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #f3f7fc 100%);
            color: var(--k-gray-800);
            font-size: 14px;
            line-height: 1.5;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select { font: inherit; }
        .app-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.98);
            color: var(--k-gray-800);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            border-right: 1px solid var(--k-gray-200);
            box-shadow: 8px 0 28px rgba(15, 23, 42, 0.04);
            z-index: 1000;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: var(--k-gray-100); }
        .sidebar::-webkit-scrollbar-thumb { background: var(--k-gray-300); border-radius: 4px; }
        .sidebar-header {
            padding: 1.1rem 0.95rem 0.65rem;
            margin-bottom: 0.2rem;
            min-height: 72px;
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            min-height: 44px;
        }
        .logo-text h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--k-blue-darker);
            margin: 0;
            line-height: 1;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .nav-menu { padding: 0 0.75rem 0.75rem; }
        .nav-section { margin-bottom: 0.9rem; }
        .nav-section-title {
            font-size: 0.62rem;
            text-transform: uppercase;
            color: #718198;
            font-weight: 700;
            padding: 0 0.6rem;
            margin-bottom: 0.4rem;
            letter-spacing: 0.04em;
        }
        .nav-item { list-style: none; margin-bottom: 0.1rem; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.46rem 0.62rem;
            color: var(--k-gray-600);
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 600;
        }
        .nav-link i { font-size: 1rem; width: 18px; color: var(--k-gray-500); }
        .nav-link span { font-size: var(--k-font-sm); font-weight: 600; }
        .nav-link:hover { background: var(--k-blue-light); color: var(--k-blue); }
        .nav-link:hover i { color: var(--k-blue); }
        .nav-link.active {
            background: linear-gradient(135deg, var(--k-blue-darker), var(--k-blue));
            color: var(--k-white);
            box-shadow: var(--k-shadow-blue);
        }
        .nav-link.active i { color: var(--k-white); }
        .nav-badge {
            margin-left: auto;
            background: var(--k-orange);
            color: var(--k-white);
            font-size: 0.62rem;
            font-weight: 700;
            padding: 0.1rem 0.4rem;
            border-radius: 30px;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(237, 244, 255, 0.72) 0, rgba(246, 248, 251, 0) 11rem),
                var(--k-white-off);
        }
        .top-bar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            padding: 0.85rem 1.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--k-gray-200);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }
        .page-title h1 {
            font-size: var(--k-font-page-title);
            font-weight: 700;
            color: var(--k-blue-darker);
            margin: 0;
            line-height: 1.22;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #63758c;
            font-size: var(--k-font-xs);
            margin-top: 0.2rem;
        }
        .top-bar-right { display: flex; align-items: center; gap: 0.6rem; }
        .language-toggle, .user-info-card, .date-display, .logout-btn {
            background: var(--k-white);
            border: 1px solid var(--k-gray-200);
            box-shadow: var(--k-shadow-sm);
        }
        .language-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
            padding: 0.15rem;
            border-radius: 24px;
        }
        .language-toggle button {
            border: 0;
            border-radius: 20px;
            padding: 0.2rem 0.45rem;
            background: transparent;
            color: var(--k-gray-500);
            font-size: 0.68rem;
            font-weight: 700;
        }
        .language-toggle button.active { background: var(--k-blue); color: var(--k-white); }
        .user-info-card {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.2rem 0.6rem 0.2rem 0.4rem;
            border-radius: 24px;
        }
        .avatar-small {
            width: 24px;
            height: 24px;
            background: var(--k-blue);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-small i { font-size: 0.8rem; color: var(--k-white); }
        .user-detail .name { font-weight: 600; color: var(--k-gray-800); font-size: var(--k-font-xs); }
        .user-detail .role { font-size: 0.62rem; color: var(--k-gray-500); display: flex; align-items: center; gap: 0.2rem; }
        .role-badge {
            background: var(--k-orange-light);
            color: var(--k-orange-dark);
            padding: 0.05rem 0.3rem;
            border-radius: 20px;
            font-size: 0.58rem;
            font-weight: 700;
        }
        .date-display, .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 24px;
            color: #4a5b70;
            font-size: var(--k-font-xs);
        }
        .date-display i { color: var(--k-orange); font-size: 0.7rem; }
        .logout-btn {
            border-color: var(--k-gray-300);
            color: var(--k-gray-600);
            font-weight: 600;
            cursor: pointer;
        }
        .content-area { padding: 1.25rem 1.45rem 1.5rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.875rem;
            margin-bottom: 1.25rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 8px;
            padding: 0.95rem;
            border: 1px solid var(--k-gray-200);
            box-shadow: var(--k-shadow-md);
        }
        .stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .stat-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--k-blue-light), #ffffff);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--k-blue);
        }
        .stat-trend {
            font-size: 0.6rem;
            padding: 0.1rem 0.4rem;
            border-radius: 20px;
            background: var(--k-success-light);
            color: var(--k-success);
            font-weight: 600;
        }
        .stat-value { font-size: var(--k-font-stat); font-weight: 700; color: var(--k-gray-900); margin-bottom: 0.1rem; line-height: 1.15; }
        .stat-label { color: var(--k-gray-600); font-size: var(--k-font-xs); font-weight: 600; line-height: 1.35; }
        .dms-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 8px;
            padding: 1.15rem;
            border: 1px solid var(--k-gray-200);
            box-shadow: var(--k-shadow-md);
        }
        .dms-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.1rem;
        }
        .dms-section-title { font-size: var(--k-font-lg); font-weight: 700; line-height: 1.25; color: var(--k-blue-darker); margin: 0; }
        .dms-section-subtitle { font-size: var(--k-font-sm); color: #5d7088; margin: 0.25rem 0 0; }
        .dms-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding: 0.8rem;
            background: #fbfdff;
            border: 1px solid var(--k-gray-200);
            border-radius: 8px;
        }
        .dms-search-form { display: flex; gap: 0.5rem; flex: 1 1 320px; min-width: min(100%, 280px); }
        .dms-search-field { position: relative; flex: 1; }
        .dms-search-field i {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--k-gray-400);
            pointer-events: none;
        }
        .dms-search-field .form-control { padding-left: 2.25rem; }
        .dms-toolbar-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .dms-toolbar-actions .form-control { width: auto; min-width: 180px; }
        .dms-btn {
            min-height: 36px;
            padding: 0.42rem 0.9rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: var(--k-font-sm);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }
        .dms-btn-primary {
            background: linear-gradient(135deg, var(--k-blue-darker), var(--k-blue));
            color: var(--k-white);
            box-shadow: var(--k-shadow-blue);
        }
        .dms-btn-outline { background: var(--k-white); border: 1px solid #d6e1ef; color: #52657d; }
        .dms-btn-sm {
            min-width: 32px;
            width: 32px;
            height: 32px;
            padding: 0;
            font-size: 0.65rem;
            justify-content: center;
            flex: 0 0 32px;
        }
        .form-control {
            width: 100%;
            min-height: 40px;
            padding: 0.55rem 0.75rem;
            background: var(--k-white);
            border: 1px solid var(--k-gray-300);
            border-radius: 8px;
            color: var(--k-gray-900);
            font-size: var(--k-font-md);
            transition: all 0.2s;
        }
        .dms-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e3ebf5;
            border-radius: 8px;
            background: var(--k-white);
        }
        .dms-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .dms-table th {
            text-align: left;
            padding: 0.64rem 0.78rem;
            color: #5b6f86;
            font-size: var(--k-font-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 1px solid #e3ebf5;
            background: #f8fafc;
        }
        .dms-table td {
            padding: 0.62rem 0.78rem;
            border-bottom: 1px solid #edf2f7;
            color: #475569;
            font-size: 0.8rem;
            line-height: 1.4;
        }
        .dms-table tbody tr:last-child td { border-bottom: 0; }
        .dms-table tbody tr:hover { background: #fbfdff; }
        .dms-actions { display: flex; align-items: center; justify-content: center; gap: 0.35rem; flex-wrap: nowrap; white-space: nowrap; }
        .dms-strong { font-weight: 500; color: #0f172a; }
        .dms-muted { color: var(--k-gray-500); font-size: 0.65rem; }
        .dms-money { font-weight: 600; color: #17345f; white-space: nowrap; }
        .dms-badge { padding: 0.22rem 0.62rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; display: inline-block; line-height: 1.2; }
        .dms-badge-success { background: #ecfdf3; color: #15803d; }
        .dms-badge-warning { background: var(--k-orange-light); color: var(--k-orange-dark); }
        .dms-badge-info { background: #f1f5f9; color: #1e3a5f; }
        .dms-badge-primary { background: var(--k-blue); color: var(--k-white); }
        .dms-badge-secondary { background: var(--k-gray-100); color: var(--k-gray-600); }
        .dms-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .dms-pagination-summary { font-size: var(--k-font-md); color: #52657d; }
        .advanced-search {
            display: none;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--k-gray-50);
            border-radius: 8px;
        }
        .advanced-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-label { display: block; margin-bottom: 0.3rem; color: var(--k-gray-700); font-size: var(--k-font-sm); font-weight: 600; }
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; }
            .main-content { margin-left: 0; }
            .app-container { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
            .top-bar { align-items: flex-start; flex-direction: column; }
            .top-bar-right .date-display, .top-bar-right .user-detail { display: none; }
            .dms-section-header, .dms-toolbar, .dms-search-form, .dms-toolbar-actions, .dms-pagination { align-items: stretch; flex-direction: column; }
            .dms-btn, .dms-search-form .dms-btn, .dms-toolbar-actions .dms-btn, .dms-toolbar-actions .form-control { width: 100%; justify-content: center; }
            .advanced-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    $titles = [
        'dashboard' => ['Dashboard HRIS', 'Beranda / Dashboard HRIS', 'Ringkasan absensi, cuti, payroll, dan aktivitas HR hari ini.'],
        'ess' => ['ESS Portal', 'Beranda / Employee Self Service', 'Kelola profil, cuti, klaim, dan payslip karyawan.'],
        'employees' => ['Data Karyawan', 'Master Data / Data Karyawan', 'Kelola data karyawan dari onboarding sampai offboarding.'],
        'attendance' => ['Absensi', 'Operasional / Absensi', 'Pantau clock-in, mode kerja, dan pengecualian absensi harian.'],
        'leave' => ['Manajemen Cuti', 'Operasional / Manajemen Cuti', 'Kelola permohonan cuti dan approval karyawan.'],
        'payroll' => ['Payroll', 'Finance / Payroll', 'Review komponen payroll sebelum finalisasi penggajian.'],
        'documents' => ['Dokumen HR', 'Administrasi / Dokumen HR', 'Kelola policy, template, dan dokumen legal HR.'],
    ];
    $current = $titles[$active] ?? $titles['dashboard'];
    $navGroups = [
        'Utama' => [
            'dashboard' => ['Dashboard', route('hris.dashboard'), 'bi-speedometer2', null],
            'ess' => ['ESS Portal', route('hris.ess'), 'bi-person-badge', null],
        ],
        'Operasional' => [
            'employees' => ['Data Karyawan', route('hris.employees'), 'bi-people', null],
            'attendance' => ['Absensi', route('hris.attendance'), 'bi-clock-history', 7],
            'leave' => ['Manajemen Cuti', route('hris.leave'), 'bi-calendar-check', 6],
        ],
        'Payroll & Admin' => [
            'payroll' => ['Payroll', route('hris.payroll'), 'bi-cash-stack', null],
            'documents' => ['Dokumen HR', route('hris.documents'), 'bi-folder2-open', null],
        ],
    ];
    $sectionTitle = [
        'dashboard' => ['Data Karyawan', 'Pantau karyawan aktif, status kerja, dan posisi operasional HR.'],
        'ess' => ['Data Layanan ESS', 'Kelola request mandiri karyawan dari cuti, klaim, sampai payslip.'],
        'employees' => ['Data Karyawan', 'Kelola master data karyawan, divisi, status, dan lokasi kerja.'],
        'attendance' => ['Data Absensi', 'Kelola presensi harian dari kantor, remote, dan cuti.'],
        'leave' => ['Data Pengajuan Cuti', 'Kelola approval cuti, sakit, dan perjalanan dinas.'],
        'payroll' => ['Data Payroll', 'Kelola komponen gaji, allowance, pajak, dan reimburse.'],
        'documents' => ['Data Dokumen', 'Kelola policy, template kontrak, dan dokumen perusahaan.'],
    ][$active] ?? ['Data Karyawan', 'Pantau operasional HR.'];
@endphp

<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('hris.dashboard') }}" class="logo-wrapper">
                <div class="logo-text"><h2>HRIS</h2></div>
            </a>
        </div>

        <nav class="nav-menu">
            @foreach ($navGroups as $group => $items)
                <div class="nav-section">
                    <div class="nav-section-title">{{ $group }}</div>
                    <ul>
                        @foreach ($items as $key => $item)
                            <li class="nav-item">
                                <a href="{{ $item[1] }}" class="nav-link {{ $active === $key ? 'active' : '' }}">
                                    <i class="bi {{ $item[2] }}"></i>
                                    <span>{{ $item[0] }}</span>
                                    @if ($item[3])
                                        <span class="nav-badge">{{ $item[3] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="page-title">
                <h1>{{ $current[0] }}</h1>
                <div class="breadcrumb">
                    <a href="{{ route('hris.dashboard') }}">Beranda</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>{{ $current[1] }}</span>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="language-toggle">
                    <button class="active">ID</button>
                    <button>EN</button>
                </div>
                <div class="user-info-card">
                    <div class="avatar-small"><i class="bi bi-person-circle"></i></div>
                    <div class="user-detail">
                        <div class="name">Super Admin</div>
                        <div class="role"><i class="bi bi-shield-check"></i> super-admin <span class="role-badge">KURMIGO</span></div>
                    </div>
                </div>
                <div class="date-display"><i class="bi bi-calendar"></i> 03 Jun 2026</div>
                <button class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</button>
            </div>
        </header>

        <section class="content-area">
            <div class="stats-grid">
                @foreach ($stats as $index => $stat)
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon"><i class="bi {{ ['bi-people', 'bi-clock-history', 'bi-calendar-check', 'bi-cash-stack'][$index] }}"></i></div>
                            <span class="stat-trend">{{ $stat['trend'] }}</span>
                        </div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="dms-card">
                <div class="dms-section-header">
                    <div>
                        <h3 class="dms-section-title">{{ $sectionTitle[0] }}</h3>
                        <p class="dms-section-subtitle">{{ $sectionTitle[1] }}</p>
                    </div>
                    <button class="dms-btn dms-btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Data
                    </button>
                </div>

                <div class="dms-toolbar">
                    <form class="dms-search-form">
                        <div class="dms-search-field">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari nama, ID, departemen..." class="form-control">
                        </div>
                        <button type="button" class="dms-btn dms-btn-primary">Cari</button>
                    </form>
                    <div class="dms-toolbar-actions">
                        <button type="button" onclick="toggleAdvancedSearch()" class="dms-btn dms-btn-outline">
                            <i class="bi bi-sliders2"></i> Advanced Search
                        </button>
                        <select class="form-control">
                            <option>10 per halaman</option>
                            <option>20 per halaman</option>
                            <option>50 per halaman</option>
                        </select>
                    </div>
                </div>

                <div id="advancedSearch" class="advanced-search">
                    <div class="advanced-grid">
                        <div>
                            <label class="form-label">Departemen</label>
                            <select class="form-control"><option>-- Semua Departemen --</option><option>HR</option><option>Engineering</option><option>Finance</option></select>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select class="form-control"><option>-- Semua Status --</option><option>Permanent</option><option>Contract</option><option>Probation</option></select>
                        </div>
                    </div>
                </div>

                @if (in_array($active, ['dashboard', 'employees']))
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead>
                                <tr>
                                    <th>ID Karyawan</th>
                                    <th>Karyawan</th>
                                    <th>Departemen</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Bergabung</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($employees as $index => $person)
                                <tr>
                                    <td><strong style="font-family: monospace; font-size: 0.75rem;">KRM{{ str_pad((string) ($index + 18), 5, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td><div style="display:flex; flex-direction:column;"><span class="dms-strong">{{ $person['name'] }}</span><span class="dms-muted">{{ $person['role'] }}</span></div></td>
                                    <td>{{ $person['team'] }}</td>
                                    <td>{{ $person['location'] }}</td>
                                    <td><span class="dms-badge dms-badge-{{ $person['status'] === 'Probation' ? 'warning' : 'success' }}">{{ $person['status'] }}</span></td>
                                    <td>{{ $person['joined'] }}</td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-pencil"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($employees) }} dari {{ count($employees) }} karyawan</div></div>
                @elseif ($active === 'ess')
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead><tr><th>Layanan</th><th>Karyawan</th><th>Kategori</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                            <tbody>
                            @foreach ($quickActions as $action)
                                <tr>
                                    <td><span class="dms-strong">{{ $action }}</span></td>
                                    <td><div style="display:flex; flex-direction:column;"><span class="dms-strong">{{ $employee['name'] }}</span><span class="dms-muted">{{ $employee['employee_id'] }}</span></div></td>
                                    <td><span class="dms-badge dms-badge-info">ESS</span></td>
                                    <td><span class="dms-badge dms-badge-success">Aktif</span></td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($quickActions) }} dari {{ count($quickActions) }} layanan</div></div>
                @elseif ($active === 'attendance')
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead><tr><th>Karyawan</th><th>Clock In</th><th>Clock Out</th><th>Mode</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                            <tbody>
                            @foreach ($attendance as $record)
                                <tr>
                                    <td><span class="dms-strong">{{ $record['name'] }}</span></td>
                                    <td>{{ $record['clock_in'] }}</td>
                                    <td>{{ $record['clock_out'] }}</td>
                                    <td><span class="dms-badge dms-badge-info">{{ $record['mode'] }}</span></td>
                                    <td><span class="dms-badge dms-badge-{{ $record['status'] === 'Late' ? 'warning' : 'success' }}">{{ $record['status'] }}</span></td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($attendance) }} dari {{ count($attendance) }} absensi</div></div>
                @elseif ($active === 'leave')
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead><tr><th>Karyawan</th><th>Jenis</th><th>Tanggal</th><th>Hari</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                            <tbody>
                            @foreach ($leaveRequests as $request)
                                <tr>
                                    <td><span class="dms-strong">{{ $request['name'] }}</span></td>
                                    <td>{{ $request['type'] }}</td>
                                    <td>{{ $request['dates'] }}</td>
                                    <td>{{ $request['days'] }}</td>
                                    <td><span class="dms-badge dms-badge-{{ $request['status'] === 'Pending' ? 'warning' : 'success' }}">{{ $request['status'] }}</span></td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-check2"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($leaveRequests) }} dari {{ count($leaveRequests) }} pengajuan</div></div>
                @elseif ($active === 'payroll')
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead><tr><th>Komponen</th><th style="text-align:right;">Total</th><th>Catatan</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                            <tbody>
                            @foreach ($payroll as $item)
                                <tr>
                                    <td><span class="dms-strong">{{ $item['component'] }}</span></td>
                                    <td style="text-align:right;"><span class="dms-money">{{ $item['amount'] }}</span></td>
                                    <td>{{ $item['note'] }}</td>
                                    <td><span class="dms-badge dms-badge-warning">Review</span></td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($payroll) }} dari {{ count($payroll) }} komponen</div></div>
                @else
                    <div class="dms-table-wrap">
                        <table class="dms-table">
                            <thead><tr><th>Dokumen</th><th>Pemilik</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                            <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td><span class="dms-strong">{{ $document['title'] }}</span></td>
                                    <td>{{ $document['owner'] }}</td>
                                    <td><span class="dms-badge dms-badge-{{ $document['status'] === 'Needs review' ? 'warning' : 'success' }}">{{ $document['status'] }}</span></td>
                                    <td><div class="dms-actions"><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-eye"></i></button><button class="dms-btn dms-btn-outline dms-btn-sm"><i class="bi bi-download"></i></button></div></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dms-pagination"><div class="dms-pagination-summary">Menampilkan 1 - {{ count($documents) }} dari {{ count($documents) }} dokumen</div></div>
                @endif
            </div>
        </section>
    </main>
</div>

<script>
function toggleAdvancedSearch() {
    const advancedSearch = document.getElementById('advancedSearch');
    advancedSearch.style.display = advancedSearch.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
