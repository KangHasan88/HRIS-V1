<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByModule = [
            'Dashboard' => [
                'view dashboard' => 'Melihat ringkasan operasional HRIS dan progress delivery',
            ],
            'Employees' => [
                'view employees' => 'Melihat daftar dan profil karyawan',
                'create employees' => 'Menambahkan karyawan baru',
                'edit employees' => 'Mengubah data karyawan',
                'delete employees' => 'Menghapus data karyawan',
            ],
            'Attendance' => [
                'view attendance' => 'Melihat data absensi',
                'approve attendance' => 'Menyetujui koreksi/lembur absensi',
                'manage shift schedules' => 'Mengelola pola shift, roster rotasi, split shift, long shift, dan jadwal kerja',
                'manage attendance imports' => 'Mengelola import absensi dari mesin biometric, CSV, API, dan rekonsiliasi anomali',
                'view holiday calendar' => 'Melihat kalender libur nasional, perusahaan, cabang, agama, dan wilayah',
                'manage holiday calendar' => 'Mengelola kalender libur untuk absensi, cuti, payroll, dan ESS',
            ],
            'Leave' => [
                'view leave' => 'Melihat daftar pengajuan cuti',
                'approve leave' => 'Menyetujui atau menolak cuti',
                'manage leave policies' => 'Mengelola jenis cuti, kuota, carry forward, unpaid leave, dan aturan saldo',
            ],
            'Payroll' => [
                'view payroll' => 'Melihat data gaji dan slip',
                'approve payroll' => 'Menyetujui batch payroll bulanan',
                'manage payroll settings' => 'Mengatur komponen payroll, BPJS, PPh 21, dan integrasi Coretax',
                'manage tax privacy' => 'Mengatur masking NIK/NPWP, izin ekspor, dan retensi data pajak',
            ],
            'Documents' => [
                'view documents' => 'Melihat daftar dokumen perusahaan',
                'manage documents' => 'Mengunggah dan mengelola dokumen perusahaan',
            ],
            'Recruitment' => [
                'view recruitment' => 'Melihat pipeline rekrutmen dan kebutuhan manpower',
                'manage recruitment' => 'Mengelola pengajuan manpower, approval, kandidat, dan hiring pipeline',
            ],
            'Settings' => [
                'manage settings' => 'Mengakses pengaturan sistem dan RBAC',
                'manage modules' => 'Mengaktifkan atau menonaktifkan modul per perusahaan',
                'view delivery progress' => 'Melihat progress backlog, WBS, dan Gantt HRIS',
            ],
        ];

        $allPermissions = [];
        foreach ($permissionsByModule as $module => $perms) {
            foreach ($perms as $name => $description) {
                $allPermissions[$name] = Permission::updateOrCreate(
                    ['name' => $name],
                    ['module' => $module, 'description' => $description]
                );
            }
        }

        $roles = [
            'Super Admin' => [
                'description' => 'Akses penuh ke seluruh modul sistem dan konfigurasi',
                'permissions' => array_keys($allPermissions),
            ],
            'HR Admin' => [
                'description' => 'Mengelola master karyawan, absensi, cuti, dokumen HR, dan konfigurasi modul HR',
                'permissions' => [
                    'view dashboard',
                    'view employees', 'create employees', 'edit employees', 'delete employees',
                    'view attendance', 'approve attendance', 'manage shift schedules', 'manage attendance imports', 'view holiday calendar', 'manage holiday calendar',
                    'view leave', 'approve leave', 'manage leave policies',
                    'view documents', 'manage documents',
                    'view recruitment', 'manage recruitment',
                    'manage modules', 'view delivery progress',
                ],
            ],
            'Payroll Admin' => [
                'description' => 'Mengelola payroll, BPJS, PPh 21, slip gaji, dan integrasi Coretax',
                'permissions' => [
                    'view dashboard',
                    'view employees',
                    'view payroll', 'approve payroll', 'manage payroll settings', 'manage tax privacy',
                    'view documents',
                    'view delivery progress',
                ],
            ],
            'Manager' => [
                'description' => 'Mengelola approval tim untuk absensi, cuti, dan pemantauan data anggota',
                'permissions' => [
                    'view dashboard',
                    'view employees',
                    'view attendance', 'approve attendance', 'manage shift schedules', 'view holiday calendar',
                    'view leave', 'approve leave', 'manage leave policies',
                    'view documents',
                ],
            ],
            'Employee' => [
                'description' => 'Akses terbatas untuk kebutuhan ESS karyawan sendiri',
                'permissions' => [
                    'view dashboard',
                    'view employees',
                    'view attendance', 'view holiday calendar',
                    'view leave',
                    'view documents',
                ],
            ],
            'HR Manager' => [
                'description' => 'Alias legacy untuk HR Admin agar data lama tetap kompatibel',
                'permissions' => [
                    'view dashboard',
                    'view employees', 'create employees', 'edit employees', 'delete employees',
                    'view attendance', 'approve attendance', 'manage shift schedules', 'manage attendance imports', 'view holiday calendar', 'manage holiday calendar',
                    'view leave', 'approve leave',
                    'view documents', 'manage documents',
                    'view recruitment', 'manage recruitment',
                    'manage modules', 'view delivery progress',
                ],
            ],
            'Finance Manager' => [
                'description' => 'Alias legacy untuk Payroll Admin agar data lama tetap kompatibel',
                'permissions' => [
                    'view dashboard',
                    'view employees',
                    'view payroll', 'approve payroll', 'manage payroll settings', 'manage tax privacy',
                    'view documents',
                    'view delivery progress',
                ],
            ],
        ];

        foreach ($roles as $roleName => $data) {
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                ['description' => $data['description']]
            );

            $permissionIds = [];
            foreach ($data['permissions'] as $permName) {
                if (isset($allPermissions[$permName])) {
                    $permissionIds[] = $allPermissions[$permName]->id;
                }
            }
            $role->permissions()->sync($permissionIds);
        }
    }
}
