<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\EssProfileUpdateRequest;
use App\Models\HrHelpdeskTicket;
use App\Models\LeaveRequest;
use App\Models\ManagerClaimRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollPayslip;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EssFlowController extends Controller
{
    public function index(): View
    {
        $employee = $this->currentEmployee();
        $flows = $employee ? $this->employeeFlows($employee) : collect();
        $total = max(1, $flows->count());
        $done = $flows->where('status', 'done')->count();

        return view('ess.flow-center', [
            'active' => 'ess-flow-center',
            'employee' => $employee,
            'flows' => $flows,
            'summary' => [
                'done' => $done,
                'in_progress' => $flows->where('status', 'in_progress')->count(),
                'attention' => $flows->where('status', 'attention')->count(),
                'progress' => (int) round(($done / $total) * 100),
            ],
        ]);
    }

    private function employeeFlows(EmployeeProfile $employee): Collection
    {
        $pendingRequests = LeaveRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count()
            + OvertimeRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count()
            + BusinessTripRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count()
            + AttendanceCorrection::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count();

        $pendingReimbursements = ManagerClaimRequest::where('employee_profile_id', $employee->id)
            ->where('request_source', 'ess_webview')
            ->whereIn('approval_status', ['submitted', 'pending'])
            ->count();

        $profileUpdates = EssProfileUpdateRequest::where('employee_profile_id', $employee->id)
            ->whereIn('approval_status', ['submitted', 'pending'])
            ->count();

        $documents = EmployeeDocument::where('employee_profile_id', $employee->id)->count();
        $payslips = PayrollPayslip::where('employee_profile_id', $employee->id)->where('status', 'published')->count();
        $unreadPayslips = PayrollPayslip::where('employee_profile_id', $employee->id)->where('status', 'published')->whereNull('acknowledged_at')->count();
        $helpdesk = HrHelpdeskTicket::where('employee_profile_id', $employee->id)->whereIn('status', ['submitted', 'in_progress', 'waiting_employee'])->count();

        return collect([
            [
                'group' => 'Profil & Data Pribadi',
                'title' => 'Profil karyawan terhubung',
                'description' => 'Akun login sudah tersambung ke data karyawan, company, cabang/site, dan email kerja.',
                'status' => $employee->user_id ? 'done' : 'attention',
                'meta' => $employee->employee_number.' - '.$employee->full_name,
                'route' => route('ess.profile-updates'),
                'action' => 'Update Profil',
            ],
            [
                'group' => 'Profil & Data Pribadi',
                'title' => 'Update profil menunggu approval',
                'description' => 'Perubahan data pribadi dari ESS diproses HR sebelum masuk master karyawan.',
                'status' => $profileUpdates > 0 ? 'in_progress' : 'done',
                'meta' => $profileUpdates.' request aktif',
                'route' => route('ess.profile-updates'),
                'action' => 'Lihat Update',
            ],
            [
                'group' => 'Pengajuan Operasional',
                'title' => 'Cuti, lembur, dinas, dan koreksi absensi',
                'description' => 'Semua request operasional karyawan masuk ke approval atasan/HR dari Pusat Pengajuan.',
                'status' => $pendingRequests > 0 ? 'in_progress' : 'done',
                'meta' => $pendingRequests.' request menunggu',
                'route' => route('ess.request-center'),
                'action' => 'Buka Pengajuan',
            ],
            [
                'group' => 'Klaim & Payroll',
                'title' => 'Reimbursement karyawan',
                'description' => 'Klaim biaya dari kamera, file, atau referensi manual dipantau sampai disetujui payroll.',
                'status' => $pendingReimbursements > 0 ? 'in_progress' : 'done',
                'meta' => $pendingReimbursements.' klaim menunggu',
                'route' => route('ess.reimbursements'),
                'action' => 'Lihat Klaim',
            ],
            [
                'group' => 'Klaim & Payroll',
                'title' => 'Slip gaji dan konfirmasi terima',
                'description' => 'Karyawan melihat slip gaji terbit dan melakukan acknowledgement dari ESS.',
                'status' => $unreadPayslips > 0 ? 'attention' : ($payslips > 0 ? 'done' : 'in_progress'),
                'meta' => $payslips.' slip terbit, '.$unreadPayslips.' belum dikonfirmasi',
                'route' => route('ess.payslips'),
                'action' => 'Buka Slip',
            ],
            [
                'group' => 'Dokumen & Bantuan',
                'title' => 'Dokumen ESS tersedia',
                'description' => 'Dokumen karyawan, bukti potong, dan file HR bisa diakses dari portal karyawan.',
                'status' => $documents > 0 ? 'done' : 'in_progress',
                'meta' => $documents.' dokumen karyawan',
                'route' => route('ess.documents'),
                'action' => 'Lihat Dokumen',
            ],
            [
                'group' => 'Dokumen & Bantuan',
                'title' => 'Helpdesk HR',
                'description' => 'Pertanyaan atau kendala karyawan tercatat sebagai tiket agar follow-up jelas.',
                'status' => $helpdesk > 0 ? 'in_progress' : 'done',
                'meta' => $helpdesk.' tiket aktif',
                'route' => route('ess.helpdesk'),
                'action' => 'Buka Helpdesk',
            ],
        ]);
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return EmployeeProfile::with(['company', 'branch'])
            ->where('user_id', $user->id)
            ->orWhere('work_email', $user->email)
            ->orWhere('full_name', $user->name)
            ->first();
    }
}
