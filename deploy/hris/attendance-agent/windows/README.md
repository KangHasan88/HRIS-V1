# Kurmigo HRIS Local Attendance Agent

Agent ini dipasang di PC/server Windows yang satu jaringan dengan mesin absensi atau aplikasi Attendance Management.

## Alur

1. Mesin/aplikasi attendance export CSV ke folder lokal.
2. Agent membaca file CSV.
3. Agent push data ke endpoint HRIS `/api/attendance-agent/clock-events`.
4. HRIS membuat batch Import Absensi dan row rekonsiliasi.
5. Jika internet/VPS tidak bisa dihubungi, file dipindah ke folder retry dan dicoba lagi pada polling berikutnya.

## Format CSV MVP

Header minimal:

```csv
employee_number,employee_name,event_time,event_type,source_device
KRM-0005,Dimas Wirawan,2026-07-21 08:00:00,clock_in,BIO-HO-01
```

`event_type` bisa memakai `clock_in`, `clock_out`, `break_start`, `break_end`, atau format sederhana seperti `clock-in`.

## Cara pakai development

1. Copy `config.example.json` menjadi `config.json`.
2. Isi `connector_code` dan `agent_token` sesuai Biometric Connector HRIS.
3. Jalankan:

```powershell
python attendance_agent.py
```

## Build EXE nanti

Untuk MVP berikutnya, script ini bisa dibundle dengan PyInstaller:

```powershell
pyinstaller --onefile --name kurmigo-attendance-agent attendance_agent.py
```

File `.exe` hasil build tinggal dijalankan sebagai startup task atau Windows Service.
