@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Biometric Connector</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola koneksi mesin absensi, jadwal sinkron, dan hasil sync ke batch Import Absensi.</p>
        </div>
        <a href="{{ route('attendance.imports') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-import"></i> Import Absensi
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Connector', 'value' => $summary['connectors'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Sync Success', 'value' => $summary['sync_success'], 'color' => 'text-indigo-600'],
                ['label' => 'Agent Online', 'value' => $summary['agent_online'], 'color' => 'text-blue-600'],
                ['label' => 'Perlu Cek', 'value' => $summary['agent_attention'], 'color' => 'text-amber-600'],
                ['label' => 'Failed Rows', 'value' => $summary['failed_rows'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Connector</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai biometric connector',
                    'description' => 'Form ini dipakai untuk mendaftarkan sumber absensi dari mesin fingerprint/face recognition/API vendor agar data bisa ditarik otomatis ke batch Import Absensi.',
                    'steps' => [
                        'Isi identitas connector sesuai vendor atau lokasi mesin.',
                        'Pilih tipe koneksi: API pull, webhook, SFTP CSV, atau local agent.',
                        'Tentukan mapping field supaya nomor karyawan, waktu event, dan tipe event terbaca konsisten.',
                        'Jika status Active, sistem menyiapkan jadwal sinkron berikutnya sesuai interval.',
                    ],
                    'notes' => [
                        'Sync manual di daftar connector akan membuat batch Import Absensi untuk direkonsiliasi.',
                        'Rows gagal tidak langsung hilang; tetap masuk log agar HR bisa cek mismatch nomor karyawan atau event duplikat.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Satu connector idealnya mewakili satu mesin, satu API vendor, atau satu source file terjadwal.</p>
        </div>

        <form action="{{ route('integrations.biometric-connectors.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Identitas Connector</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Connector</label>
                        <input name="connector_code" required placeholder="Contoh: BIO-HO-01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Connector</label>
                        <input name="connector_name" required placeholder="Contoh: Fingerprint HO Lobby" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                        <select name="vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="generic">Generic</option>
                            <option value="solution">Solution</option>
                            <option value="fingertec">FingerTec</option>
                            <option value="zkteco">ZKTeco</option>
                            <option value="suprema">Suprema</option>
                            <option value="attendance_management">Attendance Management</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Koneksi & Scope</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Koneksi</label>
                        <select name="connection_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="api_pull">API Pull</option>
                            <option value="webhook">Webhook</option>
                            <option value="sftp_csv">SFTP CSV</option>
                            <option value="local_agent">Local Agent</option>
                            <option value="local_db">Local Database Adapter</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Auth Mode</label>
                        <select name="auth_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="api_key">API Key</option>
                            <option value="basic_auth">Basic Auth</option>
                            <option value="bearer_token">Bearer Token</option>
                            <option value="sftp_key">SFTP Key</option>
                            <option value="local_agent">Local Agent</option>
                            <option value="db_credentials">Database Credentials</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Token Agent Lokal</label>
                        <input name="agent_token" placeholder="Minimal 8 karakter untuk pairing agent" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Agent</label>
                        <input name="agent_config_note" placeholder="Contoh: PC admin attendance HO" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                        <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                        <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua cabang/site</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Endpoint / Path Source</label>
                        <input name="endpoint_url" placeholder="URL API, webhook source, path SFTP, atau agent id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Mesin</label>
                        <input name="machine_code" placeholder="Contoh: BIO-HO-01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Lokasi Mesin</label>
                        <input name="machine_location" placeholder="Contoh: Lobby HO" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Adapter Database Lokal</h3>
                <p class="mt-1 text-xs text-gray-500">Diisi jika tipe koneksi Local Database Adapter. HRIS menyimpan mapping dan template; koneksi real ke DB Attendance Management lokal dijalankan oleh Windows agent.</p>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Driver Database</label>
                        <select name="db_driver" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="mysql">MySQL</option>
                            <option value="sqlserver">SQL Server</option>
                            <option value="postgresql">PostgreSQL</option>
                            <option value="access_odbc">Access ODBC</option>
                            <option value="sqlite">SQLite</option>
                            <option value="odbc">ODBC Generic</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Host / DSN</label>
                        <input name="db_host" placeholder="127.0.0.1 / ATTENDANCE_DSN" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Port</label>
                        <input type="number" name="db_port" placeholder="3306 / 1433" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Database</label>
                        <input name="db_name" placeholder="attendance_db" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Table / View</label>
                        <input name="db_table" placeholder="att_log / view_attendance" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Username</label>
                        <input name="db_username" placeholder="Readonly user" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Password</label>
                        <input type="password" name="db_password" placeholder="Disimpan sebagai status credential" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Poll Mode</label>
                        <select name="db_poll_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="timestamp_window">Timestamp window</option>
                            <option value="incremental_id">Incremental ID</option>
                            <option value="full_scan_preview">Preview full scan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kolom Karyawan</label>
                        <input name="db_employee_column" value="employee_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kolom Waktu</label>
                        <input name="db_time_column" value="event_time" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kolom Event</label>
                        <input name="db_event_column" value="event_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kolom Device</label>
                        <input name="db_device_column" value="device_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kolom Increment</label>
                        <input name="db_increment_column" placeholder="id / log_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Timezone</label>
                        <input name="db_timezone" value="Asia/Jakarta" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Query Template</label>
                        <input name="db_query_template" placeholder="SELECT emp_code, punch_time, punch_state FROM att_log WHERE punch_time BETWEEN :from AND :to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Mapping & Jadwal</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Field Karyawan</label>
                        <select name="mapping_employee_field" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="employee_number">Nomor karyawan</option>
                            <option value="nik">NIK</option>
                            <option value="raw_identifier">Raw identifier mesin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Field Waktu Event</label>
                        <input name="mapping_time_field" value="event_time" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Field Tipe Event</label>
                        <input name="mapping_event_field" value="event_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Interval Sync (menit)</label>
                        <input type="number" name="sync_interval_minutes" value="60" min="5" max="10080" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="lg:col-span-4">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan koneksi, PIC vendor, atau aturan khusus sync." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i> Simpan Connector
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Connector</h2>
            <p class="text-sm text-gray-500">Run sync manual akan membuat batch Import Absensi dari source connector.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Connector</th>
                        <th class="px-5 py-3">Koneksi</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Jadwal</th>
                        <th class="px-5 py-3">Sync Manual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($connectors as $connector)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $connector->connector_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $connector->connector_code }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ $connector->status === 'active' ? 'bg-green-50 text-green-700' : ($connector->status === 'error' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-700') }} px-2 py-1 text-xs font-semibold">{{ ucfirst($connector->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ strtoupper($connector->vendor) }}</div>
                                <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $connector->connection_type) }} / {{ str_replace('_', ' ', $connector->auth_mode) }}</div>
                                <div class="mt-1 max-w-xs truncate text-xs text-gray-500">{{ $connector->endpoint_url ?: '-' }}</div>
                                @if($connector->connection_type === 'local_agent')
                                    <div class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                        <div class="font-semibold">Endpoint Agent</div>
                                        <div class="break-all font-mono">{{ $connector->agent_config['api_endpoint'] ?? url('/api/attendance-agent/clock-events') }}</div>
                                        <div class="mt-1 break-all font-mono">{{ $connector->agent_config['heartbeat_endpoint'] ?? url('/api/attendance-agent/heartbeat') }}</div>
                                        <div class="mt-1">Pairing: <span class="font-mono">{{ $connector->agent_pairing_code ?: '-' }}</span></div>
                                    </div>
                                @endif
                                @if($connector->connection_type === 'local_db')
                                    <div class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                        <div class="font-semibold">Adapter DB Lokal</div>
                                        <div>Driver: {{ $connector->database_adapter_config['driver'] ?? '-' }}</div>
                                        <div>DB/Table: {{ $connector->database_adapter_config['database'] ?? '-' }} / {{ $connector->database_adapter_config['table'] ?? '-' }}</div>
                                        <div>Status: {{ str_replace('_', ' ', $connector->adapter_status ?: 'not configured') }}</div>
                                        <div>Last preview: {{ $connector->adapter_last_test_at?->format('d M Y H:i') ?: '-' }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $connector->company?->code ?: 'Semua perusahaan' }}</div>
                                <div class="text-xs text-gray-500">{{ $connector->branch?->code ?: 'Semua cabang/site' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $connector->machine_location ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Interval {{ $connector->sync_interval_minutes }} menit</div>
                                <div class="mt-1 text-xs text-gray-500">Last: {{ $connector->last_sync_at?->format('d M Y H:i') ?: '-' }}</div>
                                <div class="text-xs text-gray-500">Next: {{ $connector->next_sync_at?->format('d M Y H:i') ?: '-' }}</div>
                                @if($connector->connection_type === 'local_agent')
                                    @php
                                        $agentFresh = $connector->agent_last_seen_at && $connector->agent_last_seen_at->gte(now()->subMinutes(max((int) $connector->sync_interval_minutes * 2, 10)));
                                        $agentBadge = $agentFresh && in_array($connector->agent_status, ['online', 'idle', 'syncing', 'synced_with_anomaly'], true)
                                            ? 'bg-green-50 text-green-700'
                                            : ($connector->agent_status === 'blocked' || $connector->agent_status === 'error' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700');
                                    @endphp
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $agentBadge }}">
                                            Agent {{ str_replace('_', ' ', $connector->agent_status ?: 'unpaired') }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">Seen: {{ $connector->agent_last_seen_at?->format('d M Y H:i') ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">IP: {{ $connector->agent_last_ip ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Versi: {{ $connector->agent_version ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Heartbeat: {{ $connector->agent_heartbeat_count ?? 0 }}x</div>
                                @endif
                                <div class="mt-1 text-xs text-gray-500">{{ $connector->sync_logs_count }} log sync</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($connector->connection_type === 'local_db')
                                    <form action="{{ route('integrations.biometric-connectors.adapter-test', $connector) }}" method="POST" class="mb-3 grid min-w-[520px] gap-2 md:grid-cols-3">
                                        @csrf
                                        <input type="number" name="preview_rows" value="0" min="0" placeholder="Preview" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <input type="number" name="matched_rows" value="0" min="0" placeholder="Matched" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <input type="number" name="failed_rows" value="0" min="0" placeholder="Failed" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <input name="sample_employee" placeholder="Sample employee" class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <input type="datetime-local" name="sample_time" class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <input name="sample_event" placeholder="clock_in" class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        <button class="md:col-span-3 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-database"></i> Catat Preview Adapter
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('integrations.biometric-connectors.sync', $connector) }}" method="POST" class="grid min-w-[520px] gap-2 md:grid-cols-5">
                                    @csrf
                                    <input type="date" name="period_start" value="{{ now()->toDateString() }}" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <input type="date" name="period_end" value="{{ now()->toDateString() }}" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <input type="number" name="pulled_rows" value="0" min="0" placeholder="Pulled" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <input type="number" name="matched_rows" value="0" min="0" placeholder="Matched" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <input type="number" name="failed_rows" value="0" min="0" placeholder="Failed" required class="rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <button class="md:col-span-5 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                        <i class="fas fa-arrows-rotate"></i> Run Sync Manual
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada connector biometric.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Sync</h2>
            <p class="text-sm text-gray-500">Log ini membantu audit source data, mismatch karyawan, dan hasil batch import.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Sync</th>
                        <th class="px-5 py-3">Connector</th>
                        <th class="px-5 py-3">Rows</th>
                        <th class="px-5 py-3">Batch</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs font-semibold text-gray-900">{{ $log->sync_number }}</div>
                                <div class="text-xs text-gray-500">{{ $log->started_at?->format('d M Y H:i') }} - {{ $log->finished_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $log->connector?->connector_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $log->connector?->connector_code }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Pulled: {{ $log->pulled_rows }}</div>
                                <div class="text-xs text-gray-500">Matched: {{ $log->matched_rows }}</div>
                                <div class="text-xs text-red-600">Failed: {{ $log->failed_rows }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-700">{{ $log->batch?->batch_number ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->message }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full {{ in_array($log->status, ['success'], true) ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }} px-2 py-1 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($log->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada riwayat sync connector.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
