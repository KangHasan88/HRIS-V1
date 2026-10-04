<?php

namespace App\Http\Controllers;

use App\Models\AttendanceShiftAssignment;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\ModuleSetting;
use App\Models\OvertimeRequest;
use App\Models\PayrollPayslip;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HrisController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (request()->getPort() === 8092 || (int) request()->server('SERVER_PORT') === 8092 || str_ends_with((string) request()->getHttpHost(), ':8092')) {
            return redirect()->route('hris.ess');
        }

        return view('hris.dashboard', [
            'active' => 'dashboard',
            ...$this->sharedData(),
        ]);
    }

    public function ess(): View
    {
        $employee = $this->currentEmployee();
        $today = Carbon::now('Asia/Jakarta');
        $todayKey = strtolower($today->format('D'));
        $dayMap = ['mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri', 'sat' => 'sat', 'sun' => 'sun'];
        $workDay = $dayMap[$todayKey] ?? 'mon';

        $shift = $employee ? AttendanceShiftAssignment::with('shiftPattern')
            ->where('status', 'published')
            ->where(function ($query) use ($employee) {
                $query->where('employee_profile_id', $employee->id)
                    ->orWhere(function ($scope) use ($employee) {
                        $scope->where('assignment_scope', 'branch')->where('branch_id', $employee->branch_id);
                    })
                    ->orWhere(function ($scope) use ($employee) {
                        $scope->where('assignment_scope', 'company')->where('company_id', $employee->company_id);
                    });
            })
            ->whereDate('effective_start', '<=', $today->toDateString())
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_end')->orWhereDate('effective_end', '>=', $today->toDateString());
            })
            ->latest('effective_start')
            ->first() : null;

        $isOffDay = $shift && in_array($workDay, $shift->off_days ?? [], true);
        $pending = $employee ? collect([
            [
                'label' => 'Cuti menunggu approval',
                'value' => LeaveRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count(),
                'route' => route('ess.leave-requests'),
            ],
            [
                'label' => 'Lembur menunggu approval',
                'value' => OvertimeRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count(),
                'route' => route('ess.request-center'),
            ],
            [
                'label' => 'Dinas menunggu approval',
                'value' => BusinessTripRequest::where('employee_profile_id', $employee->id)->whereIn('approval_status', ['submitted', 'pending'])->count(),
                'route' => route('ess.request-center'),
            ],
            [
                'label' => 'Slip belum dikonfirmasi',
                'value' => PayrollPayslip::where('employee_profile_id', $employee->id)->where('status', 'published')->whereNull('acknowledged_at')->count(),
                'route' => route('ess.payslips'),
            ],
        ]) : collect();

        $lastPayslip = $employee ? PayrollPayslip::where('employee_profile_id', $employee->id)->where('status', 'published')->latest()->first() : null;

        return view('hris.ess-home', [
            'active' => 'ess',
            'employee' => $employee,
            'today' => $today,
            'shift' => $shift,
            'isOffDay' => $isOffDay,
            'pendingActions' => $pending,
            'lastPayslip' => $lastPayslip,
            ...$this->sharedData('ESS Mobile'),
        ]);
    }

    public function employees(): View
    {
        return view('hris.dashboard', [
            'active' => 'employees',
            ...$this->sharedData('Data Karyawan'),
        ]);
    }

    public function attendance(): View
    {
        return view('hris.dashboard', [
            'active' => 'attendance',
            ...$this->sharedData('Absensi & Waktu Kerja'),
        ]);
    }

    public function leave(): View
    {
        return view('hris.dashboard', [
            'active' => 'leave',
            ...$this->sharedData('Cuti & Approval'),
        ]);
    }

    public function payroll(): View
    {
        return view('hris.dashboard', [
            'active' => 'payroll',
            ...$this->sharedData('Payroll & Pajak'),
        ]);
    }

    public function documents(): View
    {
        return view('hris.dashboard', [
            'active' => 'documents',
            ...$this->sharedData('Dokumen HR'),
        ]);
    }

    private function sharedData(string $pageTitle = 'Dashboard HRIS'): array
    {
        $modules = ModuleSetting::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ModuleSetting $module) => [
                'name' => $module->name,
                'status' => $module->is_enabled ? ($module->progress > 0 ? 'In Progress' : 'Aktif') : 'Nonaktif',
                'progress' => $module->progress,
                'flag' => $module->code,
                'enabled' => $module->is_enabled,
            ])
            ->all();

        if ($modules === []) {
            $modules = $this->defaultModules();
        }

        return [
            'pageTitle' => $pageTitle,
            'stats' => [
                ['label' => 'Total Card', 'value' => '168', 'trend' => 'Seluruh backlog awal', 'icon' => 'bi-kanban', 'tone' => 'blue'],
                ['label' => 'WBS Terhubung', 'value' => '168', 'trend' => '0 card belum masuk WBS', 'icon' => 'bi-diagram-3', 'tone' => 'violet'],
                ['label' => 'Target Delivery', 'value' => '31 hari', 'trend' => '10 Jul - 09 Aug 2026', 'icon' => 'bi-calendar-check', 'tone' => 'cyan'],
                ['label' => 'Fase Saat Ini', 'value' => 'Auth + Admin', 'trend' => 'P0 Foundation', 'icon' => 'bi-shield-lock', 'tone' => 'amber'],
            ],
            'modules' => $modules,
            'deliveryRows' => [
                ['code' => 'AUTH-001', 'title' => 'Login, logout, session, remember me, dan reset password', 'date' => '10/07/2026', 'status' => 'In Progress', 'progress' => 65],
                ['code' => 'AUTH-002', 'title' => 'Role awal Super Admin, HR Admin, Payroll, Manager, Employee', 'date' => '10/07/2026', 'status' => 'In Progress', 'progress' => 70],
                ['code' => 'ADMIN-001', 'title' => 'Admin panel shell, sidebar, topbar, dan dashboard progress', 'date' => '11/07/2026', 'status' => 'In Progress', 'progress' => 75],
                ['code' => 'ADMIN-002', 'title' => 'Module switcher dan status enable/disable di admin panel', 'date' => '11/07/2026', 'status' => 'Ready', 'progress' => 40],
                ['code' => 'ADMIN-003', 'title' => 'Halaman progress delivery: Kanban, WBS, dan Gantt summary', 'date' => '12/07/2026', 'status' => 'Ready', 'progress' => 50],
            ],
            'activity' => [
                ['time' => 'Hari 1', 'title' => 'Login & auth guard', 'detail' => 'Akses browser, remember me, throttle login, dan logout.'],
                ['time' => 'Hari 2', 'title' => 'Admin panel shell', 'detail' => 'Sidebar, topbar, module switcher, dan empty state modul.'],
                ['time' => 'Hari 3', 'title' => 'Progress dashboard', 'detail' => 'Ringkasan Kanban, WBS, dan Gantt agar progress bisa dipantau.'],
            ],
        ];
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

    private function defaultModules(): array
    {
        return [
            ['name' => 'Login & Session', 'status' => 'In Progress', 'progress' => 65, 'flag' => 'hris.auth.login', 'enabled' => true],
            ['name' => 'Admin Panel Shell', 'status' => 'In Progress', 'progress' => 75, 'flag' => 'hris.admin.shell', 'enabled' => true],
            ['name' => 'Module Switcher', 'status' => 'Ready', 'progress' => 70, 'flag' => 'hris.admin.modules', 'enabled' => true],
            ['name' => 'Progress Dashboard', 'status' => 'Ready', 'progress' => 55, 'flag' => 'hris.admin.progress', 'enabled' => true],
            ['name' => 'Core HR & Employee Master', 'status' => 'Nonaktif', 'progress' => 0, 'flag' => 'hris.core.employee', 'enabled' => false],
            ['name' => 'Payroll, PPh 21 & Coretax', 'status' => 'Nonaktif', 'progress' => 0, 'flag' => 'hris.payroll.tax', 'enabled' => false],
        ];
    }
}
