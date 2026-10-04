<?php

namespace Database\Seeders;

use App\Models\ModuleSetting;
use Illuminate\Database\Seeder;

class ModuleSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['code' => 'hris.auth.login', 'name' => 'Login & Session', 'group' => 'Foundation', 'description' => 'Login, logout, remember me, session, throttle, dan reset password.', 'platform' => 'Web Admin + ESS WebView', 'is_enabled' => true, 'is_core' => true, 'progress' => 65, 'sort_order' => 10],
            ['code' => 'hris.admin.shell', 'name' => 'Admin Panel Shell', 'group' => 'Foundation', 'description' => 'Sidebar, topbar, dashboard awal, navigation pattern, dan layout admin.', 'platform' => 'Web Admin', 'is_enabled' => true, 'is_core' => true, 'progress' => 75, 'sort_order' => 20],
            ['code' => 'hris.admin.modules', 'name' => 'Module Switcher', 'group' => 'Foundation', 'description' => 'Enable/disable modul per perusahaan sebagai fondasi modular HRIS.', 'platform' => 'Web Admin', 'is_enabled' => true, 'is_core' => true, 'progress' => 70, 'sort_order' => 30],
            ['code' => 'hris.admin.progress', 'name' => 'Progress Dashboard', 'group' => 'Foundation', 'description' => 'Ringkasan Kanban, WBS, Gantt, dan status delivery.', 'platform' => 'Web Admin', 'is_enabled' => true, 'is_core' => false, 'progress' => 55, 'sort_order' => 40],
            ['code' => 'hris.core.employee', 'name' => 'Core HR & Employee Master', 'group' => 'Core HR', 'description' => 'Data karyawan, struktur organisasi, kontrak, mutasi, dan riwayat kerja.', 'platform' => 'Web Admin + ESS WebView', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 100],
            ['code' => 'hris.attendance', 'name' => 'Attendance & Shift', 'group' => 'Core HR', 'description' => 'Absensi, jadwal, shift, overtime, koreksi, dan approval.', 'platform' => 'Web Admin + ESS WebView', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 110],
            ['code' => 'hris.leave', 'name' => 'Leave & Permit', 'group' => 'Core HR', 'description' => 'Cuti, izin, sakit, saldo cuti, delegasi, dan approval.', 'platform' => 'Web Admin + ESS WebView', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 120],
            ['code' => 'hris.payroll.tax', 'name' => 'Payroll, PPh 21 & Coretax', 'group' => 'Payroll', 'description' => 'Payroll engine, BPJS, PPh 21, slip gaji, dan integrasi Coretax.', 'platform' => 'Web Admin + ESS WebView + API', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 200],
            ['code' => 'hris.recruitment.assessment', 'name' => 'Recruitment & Psikotest Integration', 'group' => 'Talent', 'description' => 'Hiring pipeline dan integrasi modul psikotest eksternal.', 'platform' => 'Web Admin + API', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 300],
            ['code' => 'hris.performance.learning', 'name' => 'Performance & Learning', 'group' => 'Talent', 'description' => 'KPI, OKR, review, learning, sertifikasi, dan career path.', 'platform' => 'Web Admin + ESS WebView', 'is_enabled' => false, 'is_core' => false, 'progress' => 0, 'sort_order' => 310],
        ];

        foreach ($modules as $module) {
            ModuleSetting::updateOrCreate(
                ['code' => $module['code']],
                $module
            );
        }
    }
}
